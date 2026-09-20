<?php

use App\Mail\TemplateAcknowledgementMail;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

function operatorAssistedRequest(array $payload): array
{
    return [
        ...$payload,
        'operator_assisted' => true,
        'assisted_intake_key' => config('nezvip.assisted_intake_key'),
    ];
}

it('accepts a venture deconstruction intake with a deconstruction reference', function () {
    Mail::fake();

    config(['nezvip.team_notification_email' => 'ops@example.com']);

    $response = $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_deconstruction',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'venture_description' => 'A venture being explored.',
            'value_claim' => 'Customers receive daily operational insight.',
            'current_uncertainty' => 'Whether to expand before proof of repeat demand.',
            'target_market' => 'africa',
            'additional_context' => 'Two-person team at present.',
        ],
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    expect($response->json('data.reference'))->toStartWith('VDS-');
    expect($response->json('data.case_reference'))->toBe($response->json('data.reference'));

    $submission = FormSubmission::query()->latest('id')->firstOrFail();
    expect($submission->form_type)->toBe('venture_deconstruction');
    expect($submission->reference)->toBe('VDS-'.str_pad((string) $submission->id, 6, '0', STR_PAD_LEFT));
    expect($submission->case_reference)->toBe($submission->reference);

    Mail::assertQueued(TemplateAcknowledgementMail::class, function (TemplateAcknowledgementMail $mail) {
        if (! $mail->hasTo('founder@example.com')) {
            return false;
        }

        return str_contains($mail->content()->view, 'template-acknowledgement');
    });

    Mail::assertQueued(TemplateAcknowledgementMail::class, function (TemplateAcknowledgementMail $mail) {
        return $mail->hasTo('ops@example.com');
    });
});

it('links a deeper engagement to an existing case reference', function () {
    Mail::fake();

    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_synthesis',
        'case_reference' => 'VDS-000042',
        'data' => [
            'full_name' => 'Founder Name',
            'email' => 'founder@example.com',
            'venture_name' => 'Pilot Venture',
            'category' => 'Technology',
            'venture_description' => 'A factual description of the undertaking.',
            'target_market' => 'africa',
            'offering' => 'A software platform.',
            'economic_actor' => 'Small businesses',
            'value_claim' => 'Reduced operational overhead.',
            'revenue_model' => 'Subscription',
            'pricing_logic' => 'Monthly tiers',
            'delivery_process' => 'Delivered online.',
            'roles_dependencies' => 'Internal team',
            'control_points' => 'Proprietary dataset',
            'growth_mechanism' => 'Referrals',
            'founder_dependence' => 'Moderate',
            'demand_origin' => 'Sales team',
            'constraints' => 'Capital constrained',
            'confirm_factual' => true,
        ],
    ])
        ->assertCreated()
        ->assertJsonPath('data.case_reference', 'VDS-000042')
        ->assertJsonPath('data.reference', 'SYN-000001');

    $submission = FormSubmission::query()->where('form_type', 'venture_synthesis')->firstOrFail();
    expect($submission->case_reference)->toBe('VDS-000042');
});

it('rejects a venture deconstruction intake missing required fields', function () {
    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_deconstruction',
        'data' => [
            'full_name' => 'Incomplete',
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonValidationErrors([
            'data.email',
            'data.venture_description',
            'data.value_claim',
            'data.current_uncertainty',
            'data.target_market',
        ]);
});

it('accepts the remaining engagement intake forms with their reference prefixes', function () {
    Mail::fake();

    $payloads = [
        'venture_orientation' => [
            'data.full_name' => 'Founder Name', 'data.email' => 'founder@example.com',
            'data.venture_name' => 'Pilot Venture', 'data.venture_summary' => 'Operating today.',
            'data.scope_questions' => 'What pathway fits next?',
        ],
        'venture_architecture_request' => [
            'data.full_name' => 'Founder Name', 'data.email' => 'founder@example.com',
            'data.architecture_type' => 'full-architecture', 'data.undertaking_context' => 'Late stage.',
            'data.scope_description' => 'Complete design work.',
        ],
        'program_brief_request' => [
            'data.full_name' => 'Founder Name', 'data.email' => 'founder@example.com',
            'data.program_interest' => 'nezda', 'data.venture_context' => 'Operating venture.',
            'data.objectives' => 'Structured demand.', 'data.timeline' => 'flexible',
        ],
        'retainer_request' => [
            'data.full_name' => 'Founder Name', 'data.email' => 'founder@example.com',
            'data.retainer_type' => 'monthly', 'data.venture_context' => 'Operating venture.',
            'data.scope_description' => 'Ongoing intelligence.', 'data.duration' => '6 months',
        ],
    ];

    $expectedReferences = [
        'venture_orientation' => 'VO-',
        'venture_architecture_request' => 'VA-',
        'program_brief_request' => 'PBR-',
        'retainer_request' => 'RTR-',
    ];

    foreach ($payloads as $formType => $fields) {
        $data = [];
        foreach ($fields as $key => $value) {
            $data[str_replace('data.', '', $key)] = $value;
        }

        $this->postJson('/api/v1/form-submission', [
            'form_type' => $formType,
            'data' => $data,
        ])
            ->assertCreated()
            ->assertJsonPath('data.reference', fn (string $ref) => str_starts_with($ref, $expectedReferences[$formType]));
    }
});

it('marks a record as operator-assisted only with the assisted intake key', function () {
    Mail::fake();

    config(['nezvip.assisted_intake_key' => 'opt-in-verify']);

    $this->postJson('/api/v1/form-submission', operatorAssistedRequest([
        'form_type' => 'venture_orientation',
        'data' => [
            'full_name' => 'Captured By Officer',
            'email' => 'client@example.com',
            'venture_name' => 'Paper Intake Venture',
            'venture_summary' => 'Captured over the phone.',
            'scope_questions' => 'Asking about pathway clarity.',
        ],
    ]))->assertCreated();

    $submission = FormSubmission::query()->where('form_type', 'venture_orientation')->firstOrFail();
    expect($submission->operator_assisted)->toBeTrue();

    $this->postJson('/api/v1/form-submission', [
        'form_type' => 'venture_orientation',
        'operator_assisted' => true,
        'data' => [
            'full_name' => 'Self Submitted',
            'email' => 'self@example.com',
            'venture_name' => 'Self Intake Venture',
            'venture_summary' => 'Submitted directly.',
            'scope_questions' => 'No key supplied.',
        ],
    ])->assertCreated();

    $selfSubmitted = FormSubmission::query()
        ->where('form_type', 'venture_orientation')
        ->where('data->full_name', 'Self Submitted')
        ->firstOrFail();
    expect($selfSubmitted->operator_assisted)->toBeFalse();
});

it('stores the case reference and assisted marker on venture diagnostics', function () {
    Mail::fake();

    config(['nezvip.assisted_intake_key' => 'opt-in-verify']);

    $this->postJson('/api/v1/venture-diagnostic', operatorAssistedRequest([
        'case_reference' => 'VDS-000007',
        ...ventureDiagnosticApiPayload(),
    ]))
        ->assertCreated()
        ->assertJsonPath('data.reference', 'VD-000001')
        ->assertJsonPath('data.case_reference', 'VDS-000007');

    $diagnostic = \App\Models\VentureDiagnostic::query()->firstOrFail();
    expect($diagnostic->operator_assisted)->toBeTrue();
    expect($diagnostic->case_reference)->toBe('VDS-000007');
});
