<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

it('accepts a standalone venture architecture request without a diagnostic reference', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'organization' => 'NezVIP Labs',
            'undertaking_name' => 'Sample Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'A venture being explored.',
            'scope_description' => 'Complete architecture design work.',
            'expected_architecture_outcome' => 'Operational clarity and venture intelligence.',
            'authority_to_submit' => true,
        ],
    ];

    // Act
    $response = $this->postJson('/api/v1/form-submission', $payload);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.reference', fn (string $ref) => str_starts_with($ref, 'VA-'))
        ->assertJsonPath('data.case_reference', fn (string $ref) => str_starts_with($ref, 'CASE-'))
        ->assertJsonPath('data.submission_reference', fn (string $ref) => str_starts_with($ref, 'VA-'));

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->form_type)->toBe('venture_architecture_request');
    expect($submission->reference)->toStartWith('VA-');
    expect($submission->case_reference)->toStartWith('CASE-');
    expect($submission->submission_reference)->toStartWith('VA-');
    expect($submission->diagnostic_reference)->toBeNull();
    expect($submission->data['diagnostic_reference'])->toBeNull();
    expect($submission->architecture_entry_path)->toBe('STANDALONE');
    expect($submission->diagnostic_authorization)->toBe('NOT_APPLICABLE');
    expect($submission->request_status)->toBe('RECEIVED');
});

it('rejects a standalone venture architecture request missing required fields', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Incomplete',
        ],
    ];

    // Act
    $response = $this->postJson('/api/v1/form-submission', $payload);

    // Assert
    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors([
            'data.email',
            'data.undertaking_name',
            'data.architecture_entry_path',
            'data.architecture_request_type',
            'data.undertaking_context',
            'data.scope_description',
            'data.expected_architecture_outcome',
            'data.authority_to_submit',
        ]);

    expect(FormSubmission::query()->where('form_type', 'venture_architecture_request')->count())->toBe(0);
});
