<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

/**
 * Build a valid venture architecture request payload.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function ventureArchitecturePayload(array $overrides = []): array
{
    return array_merge([
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'founder@example.com',
            'organization' => 'Acme Corp',
            'undertaking_name' => 'Alpha Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'A venture being explored.',
            'scope_description' => 'Complete architecture design work.',
            'expected_architecture_outcome' => 'Operational clarity and venture intelligence.',
            'authority_to_submit' => true,
        ],
    ], $overrides);
}

it('does not auto-merge undertakings when two submissions share the same email', function () {
    Mail::fake();

    // Arrange: First submission creates a new undertaking with its own case reference.
    $first = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload())
        ->assertCreated()
        ->assertJsonPath('success', true);

    $firstCaseReference = $first->json('data.case_reference');
    $firstReference = $first->json('data.reference');

    expect($firstCaseReference)->toStartWith('CASE-');
    expect($firstCaseReference)->not->toBe($firstReference);

    // Act: Second submission with the same email but a different undertaking.
    $second = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload([
        'data' => array_merge(ventureArchitecturePayload()['data'], [
            'undertaking_name' => 'Beta Venture',
            'architecture_request_type' => 'SPECIFIC_SPECIALIST',
        ]),
    ]))->assertCreated();

    $secondCaseReference = $second->json('data.case_reference');
    $secondReference = $second->json('data.reference');

    // Assert: Different case references — no auto-merge based on matching email.
    expect($secondCaseReference)->toStartWith('CASE-');
    expect($secondCaseReference)->not->toBe($firstCaseReference);
    expect($secondCaseReference)->not->toBe($secondReference);
    expect($firstCaseReference)->not->toBe($secondCaseReference);

    // Both submissions exist independently in the database.
    expect(FormSubmission::query()->where('form_type', 'venture_architecture_request')->count())->toBe(2);
});

it('does not auto-merge undertakings when two submissions have similar venture names', function () {
    Mail::fake();

    // Arrange: First submission with a venture name.
    $first = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload([
        'data' => array_merge(ventureArchitecturePayload()['data'], [
            'undertaking_name' => 'Acme Architecture Platform',
        ]),
    ]))->assertCreated();

    $firstCaseReference = $first->json('data.case_reference');

    // Act: Second submission with a similar (but different) venture name, same email.
    $second = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload([
        'data' => array_merge(ventureArchitecturePayload()['data'], [
            'undertaking_name' => 'Acme Architecture Platform Ltd',
        ]),
    ]))->assertCreated();

    $secondCaseReference = $second->json('data.case_reference');

    // Assert: Different case references — no auto-merge based on similar names.
    expect($secondCaseReference)->toStartWith('CASE-');
    expect($secondCaseReference)->not->toBe($firstCaseReference);
});

it('does not auto-merge undertakings when two submissions share the same organization', function () {
    Mail::fake();

    // Arrange: First submission with an organization.
    $first = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload([
        'data' => array_merge(ventureArchitecturePayload()['data'], [
            'undertaking_name' => 'Alpha Venture',
            'organization' => 'Shared Organization Ltd',
        ]),
    ]))->assertCreated();

    $firstCaseReference = $first->json('data.case_reference');

    // Act: Second submission with the same organization but a different undertaking.
    $second = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload([
        'data' => array_merge(ventureArchitecturePayload()['data'], [
            'undertaking_name' => 'Beta Venture',
            'organization' => 'Shared Organization Ltd',
        ]),
    ]))->assertCreated();

    $secondCaseReference = $second->json('data.case_reference');

    // Assert: Different case references — no auto-merge based on shared organization.
    expect($secondCaseReference)->toStartWith('CASE-');
    expect($secondCaseReference)->not->toBe($firstCaseReference);
});

it('flags case linkage as UNCONFIRMED when a case reference is supplied without a valid token', function () {
    Mail::fake();

    // Arrange: Create an existing undertaking to reference.
    $existing = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload([
        'data' => array_merge(ventureArchitecturePayload()['data'], [
            'undertaking_name' => 'Existing Undertaking',
        ]),
    ]))->assertCreated();

    $victimCaseReference = $existing->json('data.case_reference');

    // Act: A different submission attempts to attach to the existing case reference
    // without providing a valid continuation token.
    $response = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload([
        'case_reference' => $victimCaseReference,
        'data' => array_merge(ventureArchitecturePayload()['data'], [
            'undertaking_name' => 'Unlinked Undertaking',
            'email' => 'attacker@example.com',
        ]),
    ]))->assertCreated();

    // Assert: The submission is NOT attached to the victim's case reference.
    $returnedCaseReference = $response->json('data.case_reference');
    expect($returnedCaseReference)->toStartWith('CASE-');
    expect($returnedCaseReference)->not->toBe($victimCaseReference);

    // Assert: CASE LINKAGE UNCONFIRMED flag is set on the submission data.
    $submission = FormSubmission::query()->where('data->email', 'attacker@example.com')->firstOrFail();
    expect($submission->data['case_linkage_status'])->toBe('UNCONFIRMED');
});

it('does not set case_linkage_status when no case reference is requested', function () {
    Mail::fake();

    // Arrange & Act: A normal submission with no case_reference supplied.
    $response = $this->postJson('/api/v1/form-submission', ventureArchitecturePayload())
        ->assertCreated();

    // Assert: No UNCONFIRMED flag when no linkage was attempted.
    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->data['case_linkage_status'] ?? null)->toBeNull();
});
