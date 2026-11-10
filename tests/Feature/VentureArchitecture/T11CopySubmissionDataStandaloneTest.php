<?php

use App\Models\FormSubmission;
use App\Models\User;

it('exports standalone submission data matching Section 9 spec format', function () {
    // Arrange: authenticated admin and a standalone venture_architecture_request submission
    $user = User::factory()->create();

    $submission = FormSubmission::query()->create([
        'form_type' => 'venture_architecture_request',
        'status' => 'pending',
        'case_reference' => 'CASE-000100',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'organization' => 'NezVIP Labs',
            'undertaking_name' => 'Alpha Architecture',
            'case_linkage_status' => 'VERIFIED',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture seeking architecture design.',
            'scope_description' => 'Full enterprise architecture review.',
            'expected_architecture_outcome' => 'Target operating model and roadmap.',
            'known_constraints_requirements' => 'Budget constraint: under $50k.',
            'notes' => 'Please contact via email.',
            'supporting_documents_links' => ['https://example.com/deck'],
            'diagnostic_reference' => null,
            'authority_to_submit' => true,
        ],
        'request_status' => 'RECEIVED',
        'engagement_authorization' => 'NOT_VERIFIED',
        'diagnostic_authorization' => 'NOT_APPLICABLE',
        'processing_delivery_status' => 'NOT_STARTED',
        'authority_to_submit' => true,
        'authority_to_submit_at' => now(),
    ]);

    $submission->update(['submission_reference' => 'VA-000100']);

    // Act: fetch the admin submission show endpoint
    $this->withoutVite();
    $response = $this->actingAs($user)
        ->get("/admin/submissions/{$submission->id}")
        ->assertOk();

    // Assert: Section 9 spec format — identifiers
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->component('admin/submissions/Show')
        ->has('submission_reference', 'VA-000100')
        ->has('submission.case_reference', 'CASE-000100')
        ->has('submission.created_at')
        ->has('submission.request_status', 'RECEIVED')
    );

    // Assert: Section 9 spec format — field labels and submitted values
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->has('submission.data.full_name', 'Jane Founder')
        ->has('submission.data.email', 'jane@example.com')
        ->has('submission.data.organization', 'NezVIP Labs')
        ->has('submission.data.undertaking_name', 'Alpha Architecture')
        ->has('submission.data.case_linkage_status', 'VERIFIED')
        ->has('submission.data.architecture_entry_path', 'STANDALONE')
        ->has('submission.data.architecture_request_type', 'FULL_MASTER')
        ->has('submission.data.undertaking_context')
        ->has('submission.data.scope_description')
        ->has('submission.data.expected_architecture_outcome')
        ->has('submission.data.known_constraints_requirements')
        ->has('submission.data.notes')
        ->has('submission.data.supporting_documents_links')
        ->has('submission.data.authority_to_submit', true)
        ->has('submission.authority_to_submit_at')
    );

    // Assert: Section 9 spec format — current processing states
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->has('submission.engagement_authorization', 'NOT_VERIFIED')
        ->has('submission.diagnostic_authorization', 'NOT_APPLICABLE')
        ->has('submission.processing_delivery_status', 'NOT_STARTED')
    );

    // Assert: stable field IDs in export (Section 9 requirement)
    expect($submission->data)->toHaveKeys([
        'full_name', 'email', 'organization', 'undertaking_name',
        'case_linkage_status', 'architecture_entry_path', 'architecture_request_type',
        'undertaking_context', 'scope_description', 'expected_architecture_outcome',
        'known_constraints_requirements', 'notes', 'supporting_documents_links',
        'diagnostic_reference', 'authority_to_submit',
    ]);
});

it('shows NOT PROVIDED / NOT APPLICABLE for missing optional fields in standalone submission', function () {
    // Arrange: authenticated admin and a standalone submission with missing optional fields
    $user = User::factory()->create();

    $submission = FormSubmission::query()->create([
        'form_type' => 'venture_architecture_request',
        'status' => 'pending',
        'case_reference' => 'CASE-000200',
        'data' => [
            'full_name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'undertaking_name' => 'Alpha Architecture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Early-stage venture.',
            'scope_description' => 'Architecture review.',
            'expected_architecture_outcome' => 'Target model.',
            'authority_to_submit' => true,
            // Optional fields intentionally omitted:
            // organization, known_constraints_requirements, notes,
            // supporting_documents_links, diagnostic_reference
        ],
        'request_status' => 'RECEIVED',
        'engagement_authorization' => 'NOT_VERIFIED',
        'diagnostic_authorization' => 'NOT_APPLICABLE',
        'processing_delivery_status' => 'NOT_STARTED',
        'authority_to_submit' => true,
    ]);

    // Act: fetch the admin submission show endpoint
    $this->withoutVite();
    $response = $this->actingAs($user)
        ->get("/admin/submissions/{$submission->id}")
        ->assertOk();

    // Assert: missing optional fields are null (client renders as NOT PROVIDED / NOT APPLICABLE)
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->component('admin/submissions/Show')
        ->has('submission.data.organization', null)
        ->has('submission.data.known_constraints_requirements', null)
        ->has('submission.data.notes', null)
        ->has('submission.data.supporting_documents_links', null)
        ->has('submission.data.diagnostic_reference', null)
        ->has('submission.diagnostic_authorization', 'NOT_APPLICABLE')
        ->has('submission.engagement_authorization', 'NOT_VERIFIED')
        ->has('submission.processing_delivery_status', 'NOT_STARTED')
    );
});
