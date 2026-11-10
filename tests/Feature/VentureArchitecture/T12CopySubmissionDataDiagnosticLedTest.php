<?php

use App\Models\FormSubmission;
use App\Models\User;

function diagnosticLedArchitecturePayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Jane Founder',
        'email' => 'jane@example.com',
        'organization' => 'NezVIP Labs',
        'undertaking_name' => 'Alpha Architecture',
        'architecture_entry_path' => 'DIAGNOSTIC_LED',
        'architecture_request_type' => 'FULL_MASTER',
        'undertaking_context' => 'Early-stage venture seeking architecture design.',
        'scope_description' => 'Full enterprise architecture review.',
        'expected_architecture_outcome' => 'Target operating model and roadmap.',
        'known_constraints_requirements' => 'Budget capped at 50k.',
        'supporting_documents_links' => ['https://example.com/deck'],
        'diagnostic_reference' => 'VD-000001',
        'notes' => 'Continuing from existing diagnostic.',
        'authority_to_submit' => true,
    ], $overrides);
}

it('exposes all Section 9 Copy Submission Data fields for a diagnostic-led submission', function () {
    // Arrange
    $user = User::factory()->create();

    $submission = FormSubmission::query()->create([
        'form_type' => 'venture_architecture_request',
        'status' => 'pending',
        'case_reference' => 'CASE-000100',
        'data' => diagnosticLedArchitecturePayload(),
        'request_status' => 'RECEIVED',
        'engagement_authorization' => 'NOT_VERIFIED',
        'diagnostic_authorization' => 'NOT_VERIFIED',
        'processing_delivery_status' => 'NOT_STARTED',
        'authority_to_submit_at' => now(),
    ]);
    $submission->submission_reference = 'VA-000100';
    $submission->save();

    $this->withoutVite();

    // Act
    $response = $this->actingAs($user)
        ->get("/admin/submissions/{$submission->id}")
        ->assertOk();

    // Assert — Inertia page renders the admin submission show component
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->component('admin/submissions/Show')
        ->has('submission')
        ->has('submission_reference')
        ->has('request_status')
        ->has('engagement_authorization')
        ->has('diagnostic_authorization')
        ->has('processing_delivery_status')
        ->has('related')
        ->has('contradictions')
    );

    // Assert — submission data contains all Section 9 field IDs (stable identifiers)
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->where('submission.data.full_name', 'Jane Founder')
        ->where('submission.data.email', 'jane@example.com')
        ->where('submission.data.organization', 'NezVIP Labs')
        ->where('submission.data.undertaking_name', 'Alpha Architecture')
        ->where('submission.data.architecture_entry_path', 'DIAGNOSTIC_LED')
        ->where('submission.data.architecture_request_type', 'FULL_MASTER')
        ->where('submission.data.undertaking_context', 'Early-stage venture seeking architecture design.')
        ->where('submission.data.scope_description', 'Full enterprise architecture review.')
        ->where('submission.data.expected_architecture_outcome', 'Target operating model and roadmap.')
        ->where('submission.data.known_constraints_requirements', 'Budget capped at 50k.')
        ->where('submission.data.supporting_documents_links', ['https://example.com/deck'])
        ->where('submission.data.diagnostic_reference', 'VD-000001')
        ->where('submission.data.notes', 'Continuing from existing diagnostic.')
        ->where('submission.data.authority_to_submit', true)
    );

    // Assert — processing states are separately identifiable (not one generic status)
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->where('submission.request_status', 'RECEIVED')
        ->where('submission.engagement_authorization', 'NOT_VERIFIED')
        ->where('submission.diagnostic_authorization', 'NOT_VERIFIED')
        ->where('submission.processing_delivery_status', 'NOT_STARTED')
    );

    // Assert — diagnostic-related fields are present for Diagnostic-led entry
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->where('submission.data.architecture_entry_path', 'DIAGNOSTIC_LED')
        ->where('submission.data.diagnostic_reference', 'VD-000001')
        ->where('submission.diagnostic_authorization', 'NOT_VERIFIED')
    );

    // Assert — submission reference and case reference are stable identifiers
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->where('submission_reference', 'VA-000100')
        ->where('submission.case_reference', 'CASE-000100')
    );
});

it('exposes diagnostic authorization state as NOT_APPLICABLE when diagnostic reference is absent', function () {
    // Arrange
    $user = User::factory()->create();

    $submission = FormSubmission::query()->create([
        'form_type' => 'venture_architecture_request',
        'status' => 'pending',
        'case_reference' => 'CASE-000200',
        'data' => diagnosticLedArchitecturePayload([
            'diagnostic_reference' => null,
        ]),
        'request_status' => 'RECEIVED',
        'engagement_authorization' => 'NOT_VERIFIED',
        'diagnostic_authorization' => 'NOT_APPLICABLE',
        'processing_delivery_status' => 'NOT_STARTED',
    ]);
    $submission->submission_reference = 'VA-000200';
    $submission->save();

    $this->withoutVite();

    // Act
    $response = $this->actingAs($user)
        ->get("/admin/submissions/{$submission->id}")
        ->assertOk();

    // Assert — diagnostic authorization state is included even when not applicable
    $response->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
        ->where('submission.diagnostic_authorization', 'NOT_APPLICABLE')
        ->where('submission.data.diagnostic_reference', null)
    );
});
