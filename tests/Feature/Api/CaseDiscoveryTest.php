<?php

use App\Mail\CaseMagicLinkMail;
use App\Models\CaseVerification;
use App\Models\FormSubmission;
use App\Models\VentureDiagnostic;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function createCaseSubmission(string $caseReference, string $email, string $formType = 'venture_deconstruction', array $data = []): FormSubmission
{
    return FormSubmission::query()->create([
        'form_type' => $formType,
        'status' => 'pending',
        'case_reference' => $caseReference,
        'data' => [
            'full_name' => 'Founder Name',
            'email' => $email,
            'venture_name' => 'Pilot Venture',
            'venture_description' => 'A venture being explored.',
            'target_market' => 'africa',
            ...$data,
        ],
    ]);
}

function issueCaseToken(string $email): string
{
    $token = 'case-token-'.Str::random(24);

    CaseVerification::query()->create([
        'email' => strtolower($email),
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
    ]);

    return $token;
}

it('emails a magic link when a continuation is requested', function () {
    Mail::fake();

    $this->postJson('/api/v1/cases/request-link', ['email' => 'Founder@Example.com'])
        ->assertStatus(202)
        ->assertJsonPath('success', true);

    $verification = CaseVerification::query()->firstOrFail();
    expect($verification->email)->toBe('founder@example.com');

    Mail::assertQueued(CaseMagicLinkMail::class, function (CaseMagicLinkMail $mail) {
        return $mail->hasTo('founder@example.com')
            && str_contains($mail->magicLink, '/continue-case?token=');
    });
});

it('invalidates outstanding links when a new one is requested', function () {
    Mail::fake();

    $this->postJson('/api/v1/cases/request-link', ['email' => 'founder@example.com'])->assertStatus(202);
    $this->postJson('/api/v1/cases/request-link', ['email' => 'founder@example.com'])->assertStatus(202);

    expect(CaseVerification::query()->count())->toBe(2);
    expect(CaseVerification::query()->whereNull('consumed_at')->count())->toBe(1);
});

it('requires a valid email to request a continuation link', function () {
    $this->postJson('/api/v1/cases/request-link', ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns undertakings grouped by case reference for a verified email', function () {
    createCaseSubmission('VDS-000001', 'founder@example.com');
    createCaseSubmission('VDS-000001', 'founder@example.com', 'venture_synthesis');
    createCaseSubmission('VDS-000002', 'founder@example.com', 'venture_orientation');
    createCaseSubmission('VDS-000003', 'someone-else@example.com');

    $token = issueCaseToken('founder@example.com');

    $response = $this->getJson('/api/v1/cases?token='.$token)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'founder@example.com');

    $cases = $response->json('data.cases');
    expect($cases)->toHaveCount(2);

    $references = array_column($cases, 'case_reference');
    expect($references)->toContain('VDS-000001')->toContain('VDS-000002');
    expect($references)->not->toContain('VDS-000003');

    $first = collect($cases)->firstWhere('case_reference', 'VDS-000001');
    expect($first['services'])->toHaveCount(2);
    expect($first['display_name'])->toBe('Pilot Venture');
    expect($first['fields']['email'])->toBe('founder@example.com');
});

it('includes venture diagnostics in the discovered undertaking', function () {
    createCaseSubmission('VDS-000010', 'founder@example.com');

    VentureDiagnostic::query()->create(ventureDiagnosticPayload([
        'email' => 'founder@example.com',
        'case_reference' => 'VDS-000010',
    ]));

    $token = issueCaseToken('founder@example.com');

    $cases = $this->getJson('/api/v1/cases?token='.$token)->json('data.cases');
    $case = collect($cases)->firstWhere('case_reference', 'VDS-000010');

    expect($case['services'])->toHaveCount(2);
    expect(collect($case['services'])->pluck('type'))->toContain('venture_diagnostic');
});

it('rejects an invalid continuation token', function () {
    $this->getJson('/api/v1/cases?token=does-not-exist')
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

it('rejects an expired continuation token', function () {
    $token = 'expired-token';

    CaseVerification::query()->create([
        'email' => 'founder@example.com',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->subMinute(),
    ]);

    $this->getJson('/api/v1/cases?token='.$token)->assertForbidden();
});

it('returns reusable prefill fields for an owned undertaking', function () {
    createCaseSubmission('VDS-000020', 'founder@example.com', 'venture_deconstruction', [
        'venture_name' => 'Prefill Venture',
        'target_market' => 'europe',
    ]);

    $token = issueCaseToken('founder@example.com');

    $this->getJson("/api/v1/cases/VDS-000020/prefill?token={$token}")
        ->assertOk()
        ->assertJsonPath('data.case_reference', 'VDS-000020')
        ->assertJsonPath('data.fields.venture_name', 'Prefill Venture')
        ->assertJsonPath('data.fields.target_market', 'europe');
});

it('refuses prefill for an undertaking not linked to the verified email', function () {
    createCaseSubmission('VDS-000030', 'someone-else@example.com');

    $token = issueCaseToken('founder@example.com');

    $this->getJson("/api/v1/cases/VDS-000030/prefill?token={$token}")
        ->assertForbidden();
});
