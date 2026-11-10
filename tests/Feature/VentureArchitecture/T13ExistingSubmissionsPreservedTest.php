<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

it('preserves existing submission case reference, submission reference, and content after migration', function () {
    // Arrange: Simulate legacy state — table exists before venture_architecture migration columns
    Schema::table('form_submissions', function ($table) {
        $table->dropColumn([
            'undertaking_name', 'architecture_entry_path', 'architecture_request_type',
            'expected_architecture_outcome', 'authority_to_submit', 'authority_to_submit_at',
            'known_constraints_requirements', 'supporting_documents_links', 'diagnostic_reference',
            'case_reference', 'submission_reference', 'request_status', 'engagement_authorization',
            'diagnostic_authorization', 'processing_delivery_status',
        ]);
    });

    // Mark migration as not yet run so Artisan will execute it
    DB::table('migrations')->where('migration', '2026_11_05_120000_add_venture_architecture_columns_to_form_submissions_table.php')->delete();

    // Create a legacy submission with references stored in the data column
    $legacyData = [
        'full_name' => 'Legacy Founder',
        'email' => 'legacy@example.com',
        'undertaking_name' => 'Legacy Undertaking',
        'case_reference' => 'CASE-000001',
        'submission_reference' => 'VA-000001',
        'scope_description' => 'Original scope description.',
    ];

    $submission = FormSubmission::create([
        'form_type' => 'venture_architecture_request',
        'status' => 'pending',
        'data' => $legacyData,
    ]);

    $originalData = $submission->data;
    $originalId = $submission->id;

    // Act: Run the migration that adds venture_architecture columns
    Artisan::call('migrate', [
        '--path' => database_path('migrations/2026_11_05_120000_add_venture_architecture_columns_to_form_submissions_table.php'),
        '--force' => true,
    ]);

    // Assert: Existing data is preserved after migration
    $submission->refresh();

    expect($submission->id)->toBe($originalId);
    expect($submission->form_type)->toBe('venture_architecture_request');
    expect($submission->data)->toBe($originalData);
    expect($submission->data['case_reference'])->toBe('CASE-000001');
    expect($submission->data['submission_reference'])->toBe('VA-000001');
    expect($submission->data['scope_description'])->toBe('Original scope description.');
    // New columns added by migration should not alter existing data
    expect($submission->undertaking_name)->toBeNull();
    expect($submission->case_reference)->toBeNull();
    expect($submission->submission_reference)->toBeNull();
});

it('does not lose or alter existing submission data during migration', function () {
    // Arrange: Simulate legacy state — table exists before venture_architecture migration columns
    Schema::table('form_submissions', function ($table) {
        $table->dropColumn([
            'undertaking_name', 'architecture_entry_path', 'architecture_request_type',
            'expected_architecture_outcome', 'authority_to_submit', 'authority_to_submit_at',
            'known_constraints_requirements', 'supporting_documents_links', 'diagnostic_reference',
            'case_reference', 'submission_reference', 'request_status', 'engagement_authorization',
            'diagnostic_authorization', 'processing_delivery_status',
        ]);
    });

    // Mark migration as not yet run so Artisan will execute it
    DB::table('migrations')->where('migration', '2026_11_05_120000_add_venture_architecture_columns_to_form_submissions_table.php')->delete();

    $legacyData = [
        'full_name' => 'Legacy Founder',
        'email' => 'legacy@example.com',
        'case_reference' => 'CASE-000002',
        'submission_reference' => 'VA-000002',
    ];

    FormSubmission::create([
        'form_type' => 'venture_architecture_request',
        'status' => 'pending',
        'data' => $legacyData,
    ]);

    $rowCountBefore = FormSubmission::count();

    // Act: Run the migration
    Artisan::call('migrate', [
        '--path' => database_path('migrations/2026_11_05_120000_add_venture_architecture_columns_to_form_submissions_table.php'),
        '--force' => true,
    ]);

    // Assert: No rows lost, data intact (negative: data must not be null/empty/altered)
    expect(FormSubmission::count())->toBe($rowCountBefore);

    $submission = FormSubmission::query()->firstOrFail();
    expect($submission->data)->not->toBeEmpty();
    expect($submission->data['case_reference'])->not->toBeNull()->toBe('CASE-000002');
    expect($submission->data['submission_reference'])->not->toBeNull()->toBe('VA-000002');
    expect($submission->data['full_name'])->not->toBeNull()->toBe('Legacy Founder');
    expect($submission->data['email'])->not->toBeNull()->toBe('legacy@example.com');
});
