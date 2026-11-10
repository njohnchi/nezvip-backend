<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

it('generates a new case reference and submission reference for a new undertaking', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'organization' => 'NezVIP Labs',
            'undertaking_name' => 'Alpha Architecture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture design.',
            'scope_description' => 'Full enterprise architecture review.',
            'expected_architecture_outcome' => 'Target operating model and roadmap.',
            'authority_to_submit' => true,
        ],
    ];

    // Act
    $response = $this->postJson('/api/v1/form-submission', $payload);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('success', true);

    expect($response->json('data.case_reference'))->not->toBeNull()
        ->toStartWith('CASE-');
    expect($response->json('data.submission_reference'))->not->toBeNull()
        ->toStartWith('VA-');
    expect($response->json('data.case_reference'))->not->toBe($response->json('data.submission_reference'));

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->case_reference)->not->toBeNull()
        ->toStartWith('CASE-');
    expect($submission->submission_reference)->not->toBeNull()
        ->toStartWith('VA-');
});

it('generates a unique submission reference across multiple new undertakings', function () {
    // Arrange
    Mail::fake();

    $basePayload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'undertaking_name' => 'Alpha Architecture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture design.',
            'scope_description' => 'Full enterprise architecture review.',
            'expected_architecture_outcome' => 'Target operating model and roadmap.',
            'authority_to_submit' => true,
        ],
    ];

    // Act - first submission
    $first = $this->postJson('/api/v1/form-submission', $basePayload)
        ->assertCreated();

    $firstReference = $first->json('data.submission_reference');

    // Act - second submission (new undertaking, different data)
    $secondPayload = array_merge($basePayload, [
        'data' => array_merge($basePayload['data'], [
            'email' => 'john@example.com',
            'undertaking_name' => 'Beta Architecture',
        ]),
    ]);

    $second = $this->postJson('/api/v1/form-submission', $secondPayload)
        ->assertCreated();

    $secondReference = $second->json('data.submission_reference');

    // Assert
    expect($firstReference)->not->toBeNull()
        ->toStartWith('VA-');
    expect($secondReference)->not->toBeNull()
        ->toStartWith('VA-');
    expect($secondReference)->not->toBe($firstReference);
});

it('rejects a new undertaking submission missing required fields', function () {
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
});
