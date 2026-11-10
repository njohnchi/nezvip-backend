<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

it('stores known_constraints_requirements when provided', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'undertaking_name' => 'Sample Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'A venture being explored.',
            'scope_description' => 'Complete architecture design work.',
            'expected_architecture_outcome' => 'Operational clarity and venture intelligence.',
            'known_constraints_requirements' => 'Legacy system integration and budget cap of $50k.',
            'authority_to_submit' => true,
        ],
    ];

    // Act
    $response = $this->postJson('/api/v1/form-submission', $payload);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('success', true);

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->data['known_constraints_requirements'])->toBe('Legacy system integration and budget cap of $50k.');
});

it('stores supporting_documents_links when provided', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'undertaking_name' => 'Sample Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'A venture being explored.',
            'scope_description' => 'Complete architecture design work.',
            'expected_architecture_outcome' => 'Operational clarity and venture intelligence.',
            'supporting_documents_links' => 'https://docs.example.com/architecture.pdf, https://drive.example.com/links',
            'authority_to_submit' => true,
        ],
    ];

    // Act
    $response = $this->postJson('/api/v1/form-submission', $payload);

    // Assert
    $response->assertCreated()
        ->assertJsonPath('success', true);

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->data['supporting_documents_links'])->toBe('https://docs.example.com/architecture.pdf, https://drive.example.com/links');
});

it('succeeds without optional fields (never compulsory for standalone request)', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
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
        ->assertJsonPath('success', true);

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->data['known_constraints_requirements'])->toBeNull();
    expect($submission->data['supporting_documents_links'])->toBeNull();
});

it('asserts optional fields are nullable in the database', function () {
    // Arrange
    Mail::fake();

    $payload = [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
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

    // Assert: 201 Created proves optional fields are nullable (no NOT NULL violation)
    $response->assertCreated();

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->data['known_constraints_requirements'])->toBeNull();
    expect($submission->data['supporting_documents_links'])->toBeNull();
});
