<?php

use App\Mail\CaseMagicLinkMail;
use App\Models\CaseVerification;
use Illuminate\Support\Facades\Mail;

/**
 * Build a valid venture deconstruction submission payload.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function ventureDeconstructionPayload(array $overrides = []): array
{
    return [
        'form_type' => 'venture_deconstruction',
        'data' => array_merge([
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'venture_description' => 'A venture being explored.',
            'value_claim' => 'Customers pay for clarity.',
            'current_uncertainty' => 'Which market to enter first.',
            'target_market' => 'africa',
        ], $overrides),
    ];
}

it('issues a usable continuation token after a form submission', function () {
    Mail::fake();

    $response = $this->postJson('/api/v1/form-submission', ventureDeconstructionPayload())
        ->assertCreated()
        ->assertJsonPath('success', true);

    $token = $response->json('data.continuation_token');
    $caseReference = $response->json('data.case_reference');

    expect($token)->toBeString()->not->toBeEmpty();
    expect($caseReference)->toBeString()->not->toBeEmpty();

    $this->getJson('/api/v1/cases?token='.$token)
        ->assertOk()
        ->assertJsonPath('data.email', 'founder@example.com');

    Mail::assertNotQueued(CaseMagicLinkMail::class);
});

it('issues a usable continuation token after a venture diagnostic submission', function () {
    Mail::fake();

    $response = $this->postJson('/api/v1/venture-diagnostic', ventureDiagnosticPayload([
        'email' => 'founder@example.com',
    ]))->assertCreated();

    $token = $response->json('data.continuation_token');

    expect($token)->toBeString()->not->toBeEmpty();

    $this->getJson('/api/v1/cases?token='.$token)
        ->assertOk()
        ->assertJsonPath('data.email', 'founder@example.com');

    Mail::assertNotQueued(CaseMagicLinkMail::class);
});

it('invalidates a prior continuation token when a new submission issues one', function () {
    Mail::fake();

    $firstToken = $this->postJson('/api/v1/form-submission', ventureDeconstructionPayload())
        ->assertCreated()
        ->json('data.continuation_token');

    $secondToken = $this->postJson('/api/v1/form-submission', ventureDeconstructionPayload())
        ->assertCreated()
        ->json('data.continuation_token');

    expect($secondToken)->not->toBe($firstToken);
    expect(CaseVerification::query()->whereNull('consumed_at')->count())->toBe(1);

    $this->getJson('/api/v1/cases?token='.$firstToken)->assertForbidden();
    $this->getJson('/api/v1/cases?token='.$secondToken)->assertOk();
});

it('links a continuation submission to the supplied case reference', function () {
    Mail::fake();

    $this->postJson('/api/v1/form-submission', ventureDeconstructionPayload([
        'case_reference' => 'VDS-000042',
    ]))
        ->assertCreated()
        ->assertJsonPath('data.case_reference', 'VDS-000042');

    $token = $this->postJson('/api/v1/form-submission', ventureDeconstructionPayload([
        'case_reference' => 'VDS-000042',
    ]))->json('data.continuation_token');

    $cases = $this->getJson('/api/v1/cases?token='.$token)->json('data.cases');

    expect(collect($cases)->pluck('case_reference'))->toContain('VDS-000042');
});
