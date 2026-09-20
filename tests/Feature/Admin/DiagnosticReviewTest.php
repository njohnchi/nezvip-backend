<?php

use App\Models\User;
use App\Models\VentureDiagnostic;

it('allows an authorized admin to update the diagnostic assessment without a viability score', function () {
    $user = User::factory()->create();

    $diagnostic = VentureDiagnostic::query()->create(ventureDiagnosticPayload());

    $this->actingAs($user)
        ->put("/admin/diagnostics/{$diagnostic->id}", [
            'status' => 'reviewing',
            'admin_notes' => 'Waiting on scope confirmation.',
            'recommended_action' => 'Proceed to architecture scoping.',
        ])
        ->assertRedirect();

    expect($diagnostic->refresh())
        ->status->toBe('reviewing')
        ->admin_notes->toBe('Waiting on scope confirmation.')
        ->recommended_action->toBe('Proceed to architecture scoping.');
});

it('ignores a lingering viability score field sent to the diagnostic review endpoint', function () {
    $user = User::factory()->create();

    $diagnostic = VentureDiagnostic::query()->create(ventureDiagnosticPayload());

    $this->actingAs($user)
        ->put("/admin/diagnostics/{$diagnostic->id}", [
            'status' => 'approved',
            'viability_score' => 42,
            'recommended_action' => 'Approval recorded without a numeric score.',
        ])
        ->assertRedirect();

    expect($diagnostic->refresh()->toArray())->not->toHaveKey('viability_score');
});

it('exposes the diagnostic reference for client-side linkage', function () {
    $diagnostic = VentureDiagnostic::query()->create(ventureDiagnosticPayload());

    expect($diagnostic->reference)->toBe('VD-'.str_pad((string) $diagnostic->id, 6, '0', STR_PAD_LEFT));
});

it('allows an authenticated user to view the diagnostic review page after the schema change', function () {
    $user = User::factory()->create();

    $diagnostic = VentureDiagnostic::query()->create(ventureDiagnosticPayload());

    $this->withoutVite();

    $this->actingAs($user)
        ->get("/admin/diagnostics/{$diagnostic->id}")
        ->assertOk()
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('admin/diagnostics/Show')
            ->missing('diagnostic.viability_score')
        );
});
