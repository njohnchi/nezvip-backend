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

it('resumes a draft by its reference', function () {
    $draft = FormDraft::factory()->create([
        'data' => ['venture_name' => 'Resume Venture'],
        'current_step' => 'section-c',
    ]);

    $this->getJson('/api/v1/form-drafts/'.$draft->draft_reference)
        ->assertOk()
        ->assertJsonPath('data.draft_reference', $draft->draft_reference)
        ->assertJsonPath('data.data.venture_name', 'Resume Venture')
        ->assertJsonPath('data.current_step', 'section-c');
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
