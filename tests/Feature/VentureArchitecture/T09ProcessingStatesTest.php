<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

function validVentureArchitecturePayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Founder Name',
        'email' => 'founder@example.com',
        'organization' => 'NezVIP Labs',
        'undertaking_name' => 'Sample Undertaking',
        'architecture_entry_path' => 'STANDALONE',
        'architecture_request_type' => 'FULL_MASTER',
        'undertaking_context' => 'Late stage venture needing architecture.',
        'scope_description' => 'Complete architecture design work.',
        'expected_architecture_outcome' => 'A target operating model.',
        'known_constraints_requirements' => 'Budget and timeline constraints.',
        'supporting_documents_links' => ['https://example.com/deck'],
        'diagnostic_reference' => null,
        'notes' => 'Submitted via standalone path.',
        'authority_to_submit' => true,
    ], $overrides);
}

it('stores four separately identifiable processing states for a standalone venture architecture request', function () {
    // Arrange
    Mail::fake();
    $payload = validVentureArchitecturePayload([
        'architecture_entry_path' => 'STANDALONE',
    ]);

    // Act
    $response = $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_architecture_request',
        'data' => $payload,
    ]);

    // Assert
    $response->assertCreated()->assertJsonPath('success', true);

    $submission = FormSubmission::query()->where('form_type', 'venture_architecture_request')->latest('id')->firstOrFail();

    expect($submission->request_status)->toBe('RECEIVED');
    expect($submission->engagement_authorization)->toBe('NOT_VERIFIED');
    expect($submission->diagnostic_authorization)->toBe('NOT_APPLICABLE');
    expect($submission->processing_delivery_status)->toBe('NOT_STARTED');
});

it('stores NOT_VERIFIED diagnostic authorization for a diagnostic-led venture architecture request', function () {
    // Arrange
    Mail::fake();
    $payload = validVentureArchitecturePayload([
        'architecture_entry_path' => 'DIAGNOSTIC_LED',
        'diagnostic_reference' => 'VD-000001',
    ]);

    // Act
    $response = $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_architecture_request',
        'data' => $payload,
    ]);

    // Assert
    $response->assertCreated()->assertJsonPath('success', true);

    $submission = FormSubmission::query()
        ->where('form_type', 'venture_architecture_request')
        ->where('data->architecture_entry_path', 'DIAGNOSTIC_LED')
        ->latest('id')
        ->firstOrFail();

    expect($submission->request_status)->toBe('RECEIVED');
    expect($submission->engagement_authorization)->toBe('NOT_VERIFIED');
    expect($submission->diagnostic_authorization)->toBe('NOT_VERIFIED');
    expect($submission->processing_delivery_status)->toBe('NOT_STARTED');
});

it('stores each processing state in its own database column, not combined into one status field', function () {
    // Arrange
    Mail::fake();
    $payload = validVentureArchitecturePayload();

    // Act
    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_architecture_request',
        'data' => $payload,
    ])->assertCreated();

    // Assert
    $submission = FormSubmission::query()->latest('id')->firstOrFail();

    $columns = [
        'request_status',
        'engagement_authorization',
        'diagnostic_authorization',
        'processing_delivery_status',
    ];

    foreach ($columns as $column) {
        expect($submission->{$column})->not->toBeNull();
    }

    expect($submission->request_status)->not->toBe($submission->engagement_authorization);
    expect($submission->engagement_authorization)->not->toBe($submission->diagnostic_authorization);
    expect($submission->diagnostic_authorization)->not->toBe($submission->processing_delivery_status);

    $this->assertDatabaseHas('form_submissions', [
        'form_type' => 'venture_architecture_request',
        'request_status' => 'RECEIVED',
        'engagement_authorization' => 'NOT_VERIFIED',
        'diagnostic_authorization' => 'NOT_APPLICABLE',
        'processing_delivery_status' => 'NOT_STARTED',
    ]);
});

it('rejects a venture architecture request missing required fields', function () {
    // Arrange
    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Incomplete',
        ],
    ])
        // Act & Assert
        ->assertUnprocessable()
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
});
