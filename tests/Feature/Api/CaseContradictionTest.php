<?php

use App\Models\FormSubmission;
use App\Models\User;
use App\Models\VentureDiagnostic;
use App\Services\ReusableFieldMapper;

function contradictionSubmission(string $caseReference, array $data, string $createdAt): FormSubmission
{
    $submission = FormSubmission::query()->create([
        'form_type' => 'venture_synthesis',
        'status' => 'pending',
        'case_reference' => $caseReference,
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'venture_name' => 'Pilot Venture',
            ...$data,
        ],
    ]);

    $submission->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

    return $submission;
}

it('flags a shared field that changed between submissions', function () {
    contradictionSubmission('CASE-000001', ['venture_name' => 'Acme'], '2026-01-01 10:00:00');
    contradictionSubmission('CASE-000001', ['venture_name' => 'Acme Corp'], '2026-02-01 10:00:00');

    $contradictions = app(ReusableFieldMapper::class)->contradictionsForCaseReference('CASE-000001');

    expect($contradictions)->toHaveCount(1);
    expect($contradictions[0]['field'])->toBe('venture_name');
    expect($contradictions[0]['previous'])->toBe('Acme');
    expect($contradictions[0]['current'])->toBe('Acme Corp');
    expect($contradictions[0]['previous_reference'])->toStartWith('SYN-');
    expect($contradictions[0]['current_reference'])->toStartWith('SYN-');
});

it('does not flag identical values', function () {
    contradictionSubmission('CASE-000002', ['venture_name' => 'Acme'], '2026-01-01 10:00:00');
    contradictionSubmission('CASE-000002', ['venture_name' => 'Acme'], '2026-02-01 10:00:00');

    expect(app(ReusableFieldMapper::class)->contradictionsForCaseReference('CASE-000002'))->toBe([]);
});

it('does not flag a blank later value', function () {
    contradictionSubmission('CASE-000003', ['venture_name' => 'Acme'], '2026-01-01 10:00:00');
    contradictionSubmission('CASE-000003', ['venture_name' => '   '], '2026-02-01 10:00:00');

    expect(app(ReusableFieldMapper::class)->contradictionsForCaseReference('CASE-000003'))->toBe([]);
});

it('ignores case and whitespace only differences', function () {
    contradictionSubmission('CASE-000004', ['venture_name' => 'Acme'], '2026-01-01 10:00:00');
    contradictionSubmission('CASE-000004', ['venture_name' => '  acme  '], '2026-02-01 10:00:00');

    expect(app(ReusableFieldMapper::class)->contradictionsForCaseReference('CASE-000004'))->toBe([]);
});

it('flags a change between a submission and a diagnostic', function () {
    contradictionSubmission('CASE-000005', ['venture_name' => 'Acme'], '2026-01-01 10:00:00');

    $diagnostic = VentureDiagnostic::query()->create(ventureDiagnosticPayload([
        'full_name' => 'Founder Name',
        'email' => 'founder@example.com',
        'venture_name' => 'Acme Corp',
        'case_reference' => 'CASE-000005',
    ]));
    $diagnostic->forceFill(['created_at' => '2026-02-01 10:00:00', 'updated_at' => '2026-02-01 10:00:00'])->save();

    $contradictions = app(ReusableFieldMapper::class)->contradictionsForCaseReference('CASE-000005');

    expect($contradictions)->toHaveCount(1);
    expect($contradictions[0]['field'])->toBe('venture_name');
    expect($contradictions[0]['current'])->toBe('Acme Corp');
    expect($contradictions[0]['current_reference'])->toStartWith('VD-');
});

it('exposes contradictions on the admin submission review page', function () {
    $user = User::factory()->create();

    $submission = contradictionSubmission('CASE-000006', ['venture_name' => 'Acme'], '2026-01-01 10:00:00');
    contradictionSubmission('CASE-000006', ['venture_name' => 'Acme Corp'], '2026-02-01 10:00:00');

    $this->withoutVite();

    $this->actingAs($user)
        ->get("/admin/submissions/{$submission->id}")
        ->assertOk()
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('admin/submissions/Show')
            ->has('contradictions', 1)
            ->where('contradictions.0.field', 'venture_name')
            ->where('contradictions.0.current', 'Acme Corp')
        );
});

it('exposes contradictions on the admin diagnostic review page', function () {
    $user = User::factory()->create();

    contradictionSubmission('CASE-000007', ['venture_name' => 'Acme'], '2026-01-01 10:00:00');

    $diagnostic = VentureDiagnostic::query()->create(ventureDiagnosticPayload([
        'full_name' => 'Founder Name',
        'email' => 'founder@example.com',
        'venture_name' => 'Acme Corp',
        'case_reference' => 'CASE-000007',
    ]));
    $diagnostic->forceFill(['created_at' => '2026-02-01 10:00:00', 'updated_at' => '2026-02-01 10:00:00'])->save();

    $this->withoutVite();

    $this->actingAs($user)
        ->get("/admin/diagnostics/{$diagnostic->id}")
        ->assertOk()
        ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
            ->component('admin/diagnostics/Show')
            ->has('contradictions', 1)
            ->where('contradictions.0.field', 'venture_name')
        );
});
