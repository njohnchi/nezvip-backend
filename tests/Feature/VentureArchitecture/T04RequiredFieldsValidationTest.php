<?php

use Illuminate\Support\Facades\Mail;

it('accepts a valid venture architecture request with all required fields', function () {
    Mail::fake();

    // Arrange: complete valid payload with all required fields present
    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'undertaking_name' => 'Alpha Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture.',
            'scope_description' => 'Full architecture design.',
            'expected_architecture_outcome' => 'Target operating model.',
            'authority_to_submit' => true,
        ],
    ];

    // Act
    $response = $this->postJson('/api/v1/form-submission', $payload);

    // Assert: valid submission is accepted
    $response->assertCreated()
        ->assertJsonPath('success', true);
});

it('rejects a venture architecture request missing the undertaking name', function () {
    Mail::fake();

    // Arrange: valid base payload with all required fields present
    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'undertaking_name' => 'Alpha Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture.',
            'scope_description' => 'Full architecture design.',
            'expected_architecture_outcome' => 'Target operating model.',
            'authority_to_submit' => true,
        ],
    ];

    // Act: omit undertaking_name
    unset($payload['data']['undertaking_name']);

    // Assert: 422 with field-specific validation error
    $this->postJson('/api/v1/form-submission', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['data.undertaking_name']);
});

it('rejects a venture architecture request missing the architecture entry path', function () {
    Mail::fake();

    // Arrange: valid base payload with all required fields present
    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'undertaking_name' => 'Alpha Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture.',
            'scope_description' => 'Full architecture design.',
            'expected_architecture_outcome' => 'Target operating model.',
            'authority_to_submit' => true,
        ],
    ];

    // Act: omit architecture_entry_path
    unset($payload['data']['architecture_entry_path']);

    // Assert: 422 with field-specific validation error
    $this->postJson('/api/v1/form-submission', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['data.architecture_entry_path']);
});

it('rejects a venture architecture request missing the expected architecture outcome', function () {
    Mail::fake();

    // Arrange: valid base payload with all required fields present
    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'undertaking_name' => 'Alpha Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture.',
            'scope_description' => 'Full architecture design.',
            'expected_architecture_outcome' => 'Target operating model.',
            'authority_to_submit' => true,
        ],
    ];

    // Act: omit expected_architecture_outcome
    unset($payload['data']['expected_architecture_outcome']);

    // Assert: 422 with field-specific validation error
    $this->postJson('/api/v1/form-submission', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['data.expected_architecture_outcome']);
});

it('rejects a venture architecture request missing the authority to submit checkbox', function () {
    Mail::fake();

    // Arrange: valid base payload with all required fields present
    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'undertaking_name' => 'Alpha Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture.',
            'scope_description' => 'Full architecture design.',
            'expected_architecture_outcome' => 'Target operating model.',
            'authority_to_submit' => true,
        ],
    ];

    // Act: omit authority_to_submit
    unset($payload['data']['authority_to_submit']);

    // Assert: 422 with field-specific validation error
    $this->postJson('/api/v1/form-submission', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['data.authority_to_submit']);
});
