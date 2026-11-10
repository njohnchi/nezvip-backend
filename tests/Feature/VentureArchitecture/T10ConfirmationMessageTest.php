<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

const CONFIRMATION_MESSAGE = 'Your Venture Architecture request has been received. Your undertaking and request have been recorded. We will contact you if additional information is needed.';

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

it('returns the spec-mandated confirmation message on successful submission', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => validArchitecturePayload(),
    ];

    // Act
    $response = $this->postJson('/api/v1/form-submission', $payload);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', CONFIRMATION_MESSAGE);

    expect($response->json('message'))->toBe(CONFIRMATION_MESSAGE);
});

it('does not return the confirmation message when submission fails validation', function () {
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

    expect($response->json('message'))->not->toBe(CONFIRMATION_MESSAGE);
    expect(FormSubmission::query()->where('form_type', 'venture_architecture_request')->count())->toBe(0);
});
