<?php

use App\Models\FormSubmission;
use App\Models\User;
use App\Models\VentureDiagnostic;

function createLinkedSubmission(string $caseReference, string $formType = 'venture_deconstruction'): FormSubmission
{
    return FormSubmission::query()->create([
        'form_type' => $formType,
        'status' => 'pending',
        'case_reference' => $caseReference,
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'venture_name' => 'Linked Venture',
        ],
    ]);
}

it('shows related records on the admin submission review page', function () {
    $user = User::factory()->create();

    $submission = createLinkedSubmission('VDS-000100');
    createLinkedSubmission('VDS-000100', 'venture_synthesis');

    VentureDiagnostic::query()->create(ventureDiagnosticPayload([
        'email' => 'founder@example.com',
        'case_reference' => 'VDS-000100',
    ]));

    $this->withoutVite();

    $this->actingAs($user)
        ->get("/admin/submissions/{$submission->id}")
        ->assertOk()
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('admin/submissions/Show')
            ->has('related', 2)
            ->has('related.0.kind')
            ->has('related.0.id')
        );
});

it('shows related records on the admin diagnostic review page', function () {
    $user = User::factory()->create();

    $diagnostic = VentureDiagnostic::query()->create(ventureDiagnosticPayload([
        'email' => 'founder@example.com',
        'case_reference' => 'VDS-000200',
    ]));

    createLinkedSubmission('VDS-000200');

    $this->withoutVite();

    $this->actingAs($user)
        ->get("/admin/diagnostics/{$diagnostic->id}")
        ->assertOk()
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('admin/diagnostics/Show')
            ->has('related', 1)
            ->has('related.0.kind')
            ->has('related.0.id')
        );
});
