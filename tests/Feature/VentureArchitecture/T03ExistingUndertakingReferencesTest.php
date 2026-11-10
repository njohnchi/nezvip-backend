<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

/**
 * Build a valid venture architecture request submission payload.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function ventureArchitecturePayload(array $overrides = []): array
{
    return [
        'form_type' => 'venture_architecture_request',
        'data' => array_merge([
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'organization' => 'Example Org',
            'undertaking_name' => 'Pilot Architecture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Existing venture needs redesign.',
            'scope_description' => 'Full enterprise architecture review.',
            'expected_architecture_outcome' => 'Target operating model.',
            'authority_to_submit' => true,
        ], $overrides),
    ];
}

it('retains the original case reference and generates a new submission reference for an existing undertaking', function () {
    Mail::fake();

    // Arrange: First submission creates the undertaking and issues a continuation token.
    $first = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload())
        ->assertCreated()
        ->assertJsonPath('success', true);

    $originalCaseReference = $first->json('data.case_reference');
    $originalSubmissionReference = $first->json('data.submission_reference');
    $continuationToken = $first->json('data.continuation_token');

    expect($originalCaseReference)->toStartWith('CASE-')->not->toBeEmpty();
    expect($originalSubmissionReference)->toStartWith('VA-')->not->toBeEmpty();
    expect($continuationToken)->toBeString()->not->toBeEmpty();

    // Act: Submit again with the same case reference and valid continuation token.
    $second = $this->postJson('/api/v1/form-submission', array_merge(
        ventureArchitecturePayload(),
        [
            'case_reference' => $originalCaseReference,
            'continuation_token' => $continuationToken,
        ],
    ))->assertCreated()->assertJsonPath('success', true);

    // Assert: Case reference is retained (never regenerated), submission reference is new.
    expect($second->json('data.case_reference'))->toBe($originalCaseReference);
    expect($second->json('data.submission_reference'))->not->toBe($originalSubmissionReference);
    expect($second->json('data.submission_reference'))->toStartWith('VA-');

    $submission = FormSubmission::query()->where('form_type', 'venture_architecture_request')->latest('id')->firstOrFail();
    expect($submission->case_reference)->toBe($originalCaseReference);
    expect($submission->submission_reference)->not->toBe($originalSubmissionReference);
});

it('does not retain a supplied case reference without a valid continuation token', function () {
    Mail::fake();

    // Arrange: First submission creates the undertaking.
    $first = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload())
        ->assertCreated();

    $originalCaseReference = $first->json('data.case_reference');

    // Act: Submit again with the case reference but no continuation token.
    $second = $this->postJson('/api/v1/form-submission', array_merge(
        ventureArchitecturePayload(),
        ['case_reference' => $originalCaseReference],
    ))->assertCreated();

    // Assert: Case reference is not retained; a new one is minted instead.
    expect($second->json('data.case_reference'))->not->toBe($originalCaseReference);
    expect($second->json('data.case_reference'))->toStartWith('CASE-');
    expect($second->json('data.submission_reference'))->not->toBe($first->json('data.submission_reference'));
});
