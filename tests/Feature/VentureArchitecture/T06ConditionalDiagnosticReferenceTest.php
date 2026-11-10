<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

function validArchitecturePayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Founder Name',
        'email' => 'founder@example.com',
        'organization' => 'NezVIP Labs',
        'undertaking_name' => 'Pilot Venture',
        'architecture_entry_path' => 'STANDALONE',
        'architecture_request_type' => 'FULL_MASTER',
        'undertaking_context' => 'Late stage venture seeking architecture.',
        'scope_description' => 'Complete design work for the platform.',
        'expected_architecture_outcome' => 'A scalable architecture that supports growth.',
        'known_constraints_requirements' => 'Budget capped at 50k.',
        'supporting_documents_links' => 'https://example.com/deck',
        'diagnostic_reference' => '',
        'notes' => 'Additional context for reviewers.',
        'authority_to_submit' => true,
    ], $overrides);
}

it('accepts a standalone venture architecture request without a diagnostic reference', function () {
    Mail::fake();

    config(['nezvip.team_notification_email' => 'ops@example.com']);

    $response = $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_architecture_request',
        'data' => validArchitecturePayload([
            'architecture_entry_path' => 'STANDALONE',
            'diagnostic_reference' => '',
        ]),
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    expect($response->json('data.reference'))->toStartWith('VA-');
    expect($response->json('data.case_reference'))->toStartWith('CASE-');

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->form_type)->toBe('venture_architecture_request');
    expect($submission->reference)->toStartWith('VA-');
    expect($submission->data['diagnostic_reference'])->toBe('');
    expect($submission->diagnostic_authorization)->toBe('NOT_APPLICABLE');

    Mail::assertQueued(\App\Mail\TemplateAcknowledgementMail::class);
});

it('accepts a diagnostic-led venture architecture request with a valid diagnostic reference', function () {
    Mail::fake();

    config(['nezvip.team_notification_email' => 'ops@example.com']);

    $response = $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_architecture_request',
        'data' => validArchitecturePayload([
            'architecture_entry_path' => 'DIAGNOSTIC_LED',
            'diagnostic_reference' => 'VD-000001',
        ]),
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    expect($response->json('data.reference'))->toStartWith('VA-');

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->data['diagnostic_reference'])->toBe('VD-000001');
    expect($submission->diagnostic_authorization)->toBe('NOT_VERIFIED');

    Mail::assertQueued(\App\Mail\TemplateAcknowledgementMail::class);
});

it('rejects a diagnostic-led venture architecture request missing the diagnostic reference', function () {
    Mail::fake();

    config(['nezvip.team_notification_email' => 'ops@example.com']);

    $response = $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_architecture_request',
        'data' => validArchitecturePayload([
            'architecture_entry_path' => 'DIAGNOSTIC_LED',
            'diagnostic_reference' => '',
        ]),
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['data.diagnostic_reference']);

    expect(FormSubmission::query()->where('form_type', 'venture_architecture_request')->count())->toBe(0);
});
