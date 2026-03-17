<?php

use App\Mail\TemplateAcknowledgementMail;
use App\Models\Career;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;

it('sends acknowledgement email for general form submissions', function () {
    Mail::fake();

    $payload = [
        'form_type' => 'scope_review',
        'data' => [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'venture_name' => 'Growth Labs',
            'venture_summary' => 'A growing venture.',
            'scope_questions' => 'How do we improve distribution?',
        ],
    ];

    $this->postJson('/api/v1/form-submission', $payload)
        ->assertCreated()
        ->assertJsonPath('success', true);

    Mail::assertQueued(TemplateAcknowledgementMail::class, function (TemplateAcknowledgementMail $mail) {
        return $mail->hasTo('john@example.com');
    });
});

it('sends acknowledgement email for venture diagnostic submissions', function () {
    Mail::fake();

    $this->postJson('/api/v1/venture-diagnostic', ventureDiagnosticApiPayload())
        ->assertCreated()
        ->assertJsonPath('success', true);

    Mail::assertQueued(TemplateAcknowledgementMail::class, function (TemplateAcknowledgementMail $mail) {
        return $mail->hasTo('founder@example.com');
    });
});

it('sends acknowledgement email for career applications', function () {
    Mail::fake();

    $career = Career::factory()->active()->create();

    $this->postJson("/api/v1/careers/{$career->id}/apply", [
        'full_name' => 'Applicant Name',
        'email' => 'applicant@example.com',
        'location' => 'Lagos, Nigeria',
        'relevant_experience' => 'Five years of operational experience.',
        'profile_link' => 'https://linkedin.com/in/applicant',
    ])->assertCreated();

    Mail::assertQueued(TemplateAcknowledgementMail::class, function (TemplateAcknowledgementMail $mail) {
        return $mail->hasTo('applicant@example.com');
    });
});

it('does not send form acknowledgement when the template is inactive', function () {
    Mail::fake();

    EmailTemplate::ensureDefaults();

    EmailTemplate::query()
        ->where('key', EmailTemplate::FORM_SUBMISSION_ACKNOWLEDGEMENT)
        ->update(['is_active' => false]);

    $payload = [
        'form_type' => 'scope_review',
        'data' => [
            'full_name' => 'Muted User',
            'email' => 'muted@example.com',
            'venture_name' => 'Growth Labs',
            'venture_summary' => 'A growing venture.',
            'scope_questions' => 'How do we improve distribution?',
        ],
    ];

    $this->postJson('/api/v1/form-submission', $payload)
        ->assertCreated();

    Mail::assertNothingSent();
});

function ventureDiagnosticApiPayload(): array
{
    return [
        'intake_type' => 'founder',
        'full_name' => 'Founding Team',
        'email' => 'founder@example.com',
        'location' => 'Lagos, Nigeria',
        'organization_name' => 'NezVIP Labs',
        'organization_type' => 'Startup',
        'role_title' => 'Founder',
        'venture_name' => 'Pilot Venture',
        'industry_category' => 'Technology',
        'venture_description' => 'Testing diagnostic payload.',
        'what_is_sold' => 'Software subscriptions',
        'primary_customer_type' => 'SMB',
        'core_value_proposition' => 'Operational intelligence.',
        'revenue_model' => 'Subscription',
        'pricing_logic' => 'Tiered model',
        'payment_flow' => 'Online monthly payments',
        'revenue_risk_carrier' => 'The business',
        'delivery_process' => 'Delivered through a web app',
        'who_performs_work' => 'Internal team',
        'operational_dependencies' => 'Cloud infrastructure',
        'scale_bottleneck' => 'Reviewer bandwidth',
        'claimed_bottlenecks' => 'Customer acquisition',
        'assets_ip_licenses' => 'Trademark',
        'competitive_positioning' => 'Focused African market coverage',
        'growth_mechanism' => 'Content and partnerships',
        'founder_role' => 'Leads execution and strategy',
        'replaceability_assumptions' => 'Process documentation in progress',
        'customer_awareness' => 'Customers understand key outcomes',
        'demand_origin' => 'Inbound referrals',
        'demand_responsibility' => 'Commercial team',
        'demand_ownership' => 'Sales function',
        'underperformance_impact' => 'Revenue slowdown',
        'new_seller_benefits' => 'Clear positioning',
        'demand_dynamics' => 'Demand tracks founder content',
        'capital_intensity' => 'Moderate',
        'regulatory_constraints' => 'Standard compliance',
        'known_risks' => 'Execution risk',
        'additional_context' => 'None',
        'known_unknowns' => 'CAC variance',
        'declared_exclusions' => 'No trade secrets included',
        'budget_range' => '10,000-25,000 USD',
        'timeline' => '6 weeks',
        'proof_links' => ['https://example.com/deck'],
        'sensitive_info_warning' => false,
        'consent_packet_only' => true,
        'consent_no_trade_secrets' => true,
        'consent_diagnosis_outcomes' => true,
    ];
}
