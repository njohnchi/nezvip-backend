<?php

use App\Models\CaseVerification;
use App\Models\FormDraft;
use Illuminate\Support\Str;

function issueDraftToken(string $email): string
{
    $token = 'draft-token-'.Str::random(24);

    CaseVerification::query()->create([
        'email' => strtolower($email),
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
    ]);

    return $token;
}

it('creates a draft and returns a draft reference', function () {
    $response = $this->postJson('/api/v1/form-drafts', [
        'form_type' => 'venture_synthesis',
        'email' => 'Founder@Example.com',
        'data' => ['venture_name' => 'Draft Venture'],
        'current_step' => 'section-a',
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $reference = $response->json('data.draft_reference');
    expect($reference)->toStartWith('DRF-');

    $draft = FormDraft::query()->where('draft_reference', $reference)->firstOrFail();
    expect($draft->email)->toBe('founder@example.com');
    expect($draft->data)->toBe(['venture_name' => 'Draft Venture']);
    expect($draft->current_step)->toBe('section-a');
});

it('updates an existing draft when a reference is supplied', function () {
    $draft = FormDraft::factory()->create(['form_type' => 'venture_synthesis']);

    $this->postJson('/api/v1/form-drafts', [
        'draft_reference' => $draft->draft_reference,
        'form_type' => 'venture_synthesis',
        'data' => ['venture_name' => 'Updated Venture'],
        'current_step' => 'section-b',
    ])->assertOk();

    expect($draft->refresh()->current_step)->toBe('section-b');
    expect($draft->data)->toBe(['venture_name' => 'Updated Venture']);
    expect(FormDraft::query()->count())->toBe(1);
});

it('does not attach a case reference without a verified token', function () {
    $response = $this->postJson('/api/v1/form-drafts', [
        'form_type' => 'venture_synthesis',
        'email' => 'founder@example.com',
        'case_reference' => 'CASE-000123',
        'data' => ['venture_name' => 'Draft Venture'],
    ])->assertOk();

    $draft = FormDraft::query()
        ->where('draft_reference', $response->json('data.draft_reference'))
        ->firstOrFail();

    expect($draft->case_reference)->toBeNull();
});

it('attaches a case reference when the continuation token is verified', function () {
    $token = issueDraftToken('founder@example.com');

    $response = $this->postJson('/api/v1/form-drafts', [
        'form_type' => 'venture_synthesis',
        'email' => 'ignored@example.com',
        'case_reference' => 'CASE-000123',
        'data' => ['venture_name' => 'Draft Venture'],
        'token' => $token,
    ])->assertOk();

    $draft = FormDraft::query()
        ->where('draft_reference', $response->json('data.draft_reference'))
        ->firstOrFail();

    expect($draft->email)->toBe('founder@example.com');
    expect($draft->case_reference)->toBe('CASE-000123');
});

it('preserves an existing case reference on an unverified update', function () {
    $draft = FormDraft::factory()->create([
        'form_type' => 'venture_synthesis',
        'email' => 'founder@example.com',
        'case_reference' => 'CASE-000900',
    ]);

    $this->postJson('/api/v1/form-drafts', [
        'draft_reference' => $draft->draft_reference,
        'form_type' => 'venture_synthesis',
        'data' => ['venture_name' => 'Updated Venture'],
    ])->assertOk();

    expect($draft->refresh()->case_reference)->toBe('CASE-000900');
});

it('resumes an unattached draft by its reference', function () {
    $draft = FormDraft::factory()->create([
        'email' => null,
        'data' => ['venture_name' => 'Resume Venture'],
        'current_step' => 'section-c',
    ]);

    $this->getJson('/api/v1/form-drafts/'.$draft->draft_reference)
        ->assertOk()
        ->assertJsonPath('data.draft_reference', $draft->draft_reference)
        ->assertJsonPath('data.data.venture_name', 'Resume Venture')
        ->assertJsonPath('data.current_step', 'section-c');
});

it('resumes an email-linked draft when a matching continuation token is supplied', function () {
    $draft = FormDraft::factory()->create([
        'email' => 'founder@example.com',
        'data' => ['venture_name' => 'Resume Venture'],
    ]);

    $token = issueDraftToken('founder@example.com');

    $this->getJson('/api/v1/form-drafts/'.$draft->draft_reference.'?token='.$token)
        ->assertOk()
        ->assertJsonPath('data.draft_reference', $draft->draft_reference)
        ->assertJsonPath('data.data.venture_name', 'Resume Venture');
});

it('hides an email-linked draft from an anonymous caller', function () {
    $draft = FormDraft::factory()->create(['email' => 'founder@example.com']);

    $this->getJson('/api/v1/form-drafts/'.$draft->draft_reference)->assertForbidden();
});

it('hides an email-linked draft from a token for another email', function () {
    $draft = FormDraft::factory()->create(['email' => 'founder@example.com']);

    $token = issueDraftToken('someone-else@example.com');

    $this->getJson('/api/v1/form-drafts/'.$draft->draft_reference.'?token='.$token)
        ->assertForbidden();
});

it('returns not found for an unknown draft reference', function () {
    $this->getJson('/api/v1/form-drafts/DRF-UNKNOWN')->assertNotFound();
});

it('lists drafts for a verified email', function () {
    FormDraft::factory()->create(['email' => 'founder@example.com']);
    FormDraft::factory()->create(['email' => 'founder@example.com']);
    FormDraft::factory()->create(['email' => 'someone-else@example.com']);

    $token = issueDraftToken('founder@example.com');

    $response = $this->getJson('/api/v1/form-drafts?token='.$token)
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($response->json('data'))->toHaveCount(2);
});

it('requires a valid token to list drafts', function () {
    $this->getJson('/api/v1/form-drafts?token=nope')->assertForbidden();
});

it('completes the matching in-progress draft when its form is submitted', function () {
    $draft = FormDraft::factory()->create([
        'form_type' => 'insights_subscribe',
        'email' => 'founder@example.com',
        'case_reference' => null,
        'status' => 'draft',
    ]);

    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'insights_subscribe',
        'data' => ['email' => 'founder@example.com'],
    ])->assertCreated();

    expect($draft->refresh()->status)->toBe('completed');

    $token = issueDraftToken('founder@example.com');

    $listed = $this->getJson('/api/v1/form-drafts?token='.$token)
        ->assertOk()
        ->json('data');

    expect($listed)->toBe([]);
});

it('retires a resumed draft when the continuation token is no longer valid', function () {
    $draft = FormDraft::factory()->create([
        'form_type' => 'insights_subscribe',
        'email' => 'founder@example.com',
        'case_reference' => 'CASE-000777',
        'status' => 'draft',
    ]);

    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'insights_subscribe',
        'case_reference' => 'CASE-000777',
        'continuation_token' => 'expired-or-unknown',
        'data' => ['email' => 'founder@example.com'],
    ])->assertCreated();

    expect($draft->refresh()->status)->toBe('completed');
});

it('leaves another undertaking draft intact when a different case is submitted', function () {
    $other = FormDraft::factory()->create([
        'form_type' => 'insights_subscribe',
        'email' => 'founder@example.com',
        'case_reference' => 'CASE-000900',
        'status' => 'draft',
    ]);

    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'insights_subscribe',
        'data' => ['email' => 'founder@example.com'],
    ])->assertCreated();

    expect($other->refresh()->status)->toBe('draft');
});

it('completes a diagnostic draft when the diagnostic is submitted', function () {
    $draft = FormDraft::factory()->create([
        'form_type' => 'venture_diagnostic',
        'email' => 'founder@example.com',
        'case_reference' => null,
        'status' => 'draft',
    ]);

    $this->postJson('/api/v1/venture-diagnostic', ventureDiagnosticPayload([
        'email' => 'founder@example.com',
    ]))->assertCreated();

    expect($draft->refresh()->status)->toBe('completed');
});
