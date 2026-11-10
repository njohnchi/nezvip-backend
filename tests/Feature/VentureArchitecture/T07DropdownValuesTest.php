<?php

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

function ventureArchitectureBasePayload(): array
{
    return [
        'form_type' => 'venture_architecture_request',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'undertaking_name' => 'Pilot Venture',
            'architecture_entry_path' => 'STANDALONE',
            'architecture_request_type' => 'FULL_MASTER',
            'undertaking_context' => 'Late stage venture.',
            'scope_description' => 'Complete design work.',
            'expected_architecture_outcome' => 'Target operating model.',
            'authority_to_submit' => true,
        ],
    ];
}

it('accepts every valid Entry Path value', function () {
    Mail::fake();
    config(['nezvip.team_notification_email' => 'ops@example.com']);

    $validEntryPaths = ['STANDALONE', 'DIAGNOSTIC_LED', 'UNDETERMINED'];

    foreach ($validEntryPaths as $entryPath) {
        $payload = ventureArchitectureBasePayload();
        $payload['data']['architecture_entry_path'] = $entryPath;

        $this->postJson('/api/v1/form-submission', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true);

        expect(FormSubmission::query()->latest('id')->firstOrFail()->data['architecture_entry_path'])
            ->toBe($entryPath);
    }
});

it('accepts every valid Request Type value', function () {
    Mail::fake();
    config(['nezvip.team_notification_email' => 'ops@example.com']);

    $validRequestTypes = [
        'FULL_MASTER',
        'SPECIFIC_SPECIALIST',
        'RE_ARCHITECTURE',
        'NEW_PROJECT',
        'UNDETERMINED_SCOPE',
    ];

    foreach ($validRequestTypes as $requestType) {
        $payload = ventureArchitectureBasePayload();
        $payload['data']['architecture_request_type'] = $requestType;

        $this->postJson('/api/v1/form-submission', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true);

        expect(FormSubmission::query()->latest('id')->firstOrFail()->data['architecture_request_type'])
            ->toBe($requestType);
    }
});

it('rejects an invalid Entry Path value with a validation error', function () {
    $payload = ventureArchitectureBasePayload();
    $payload['data']['architecture_entry_path'] = 'INVALID_PATH';

    $this->postJson('/api/v1/form-submission', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['data.architecture_entry_path']);
});

it('rejects an invalid Request Type value with a validation error', function () {
    $payload = ventureArchitectureBasePayload();
    $payload['data']['architecture_request_type'] = 'INVALID_TYPE';

    $this->postJson('/api/v1/form-submission', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors(['data.architecture_request_type']);
});
