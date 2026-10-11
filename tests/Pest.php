<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Build a valid venture diagnostic payload for tests.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function ventureDiagnosticPayload(array $overrides = []): array
{
    return array_merge([
        'intake_type' => 'founder',
        'status' => 'pending',
        'full_name' => 'Jane Founder',
        'email' => fake()->unique()->safeEmail(),
        'location' => 'Lagos, Nigeria',
        'organization_name' => 'NezVIP Labs',
        'organization_type' => 'Startup',
        'role_title' => 'Founder',
        'venture_name' => 'Sample Venture',
        'industry_category' => 'Technology',
        'venture_description' => 'A venture diagnostic submission used for testing.',
        'what_is_sold' => 'Subscription software',
        'primary_customer_type' => 'SMB',
        'core_value_proposition' => 'Operational clarity and venture intelligence.',
        'revenue_model' => 'Subscription',
        'pricing_logic' => 'Tiered pricing based on usage.',
        'payment_flow' => 'Customers pay monthly online.',
        'revenue_risk_carrier' => 'The company bears the revenue risk.',
        'delivery_process' => 'Software delivery through a web application.',
        'who_performs_work' => 'Internal product and research teams.',
        'operational_dependencies' => 'Cloud hosting and analyst input.',
        'scale_bottleneck' => 'Analyst review capacity.',
        'claimed_bottlenecks' => 'Customer acquisition and onboarding.',
        'assets_ip_licenses' => 'Trademark and proprietary frameworks.',
        'competitive_positioning' => 'Focused on African venture intelligence.',
        'growth_mechanism' => 'Content, referrals, and partnerships.',
        'founder_role' => 'Leads strategy and partnerships.',
        'replaceability_assumptions' => 'Execution knowledge is being documented.',
        'customer_awareness' => 'Customers understand the outcome they need.',
        'demand_origin' => 'Inbound referrals and existing audience.',
        'demand_responsibility' => 'Marketing and partnerships.',
        'demand_ownership' => 'Owned by the commercial team.',
        'underperformance_impact' => 'Pipeline and revenue slow down.',
        'new_seller_benefits' => 'Clear positioning and repeatable process.',
        'demand_dynamics' => 'Demand increases with founder visibility.',
        'capital_intensity' => 'Moderate working capital needs.',
        'regulatory_constraints' => 'Standard data compliance obligations.',
        'known_risks' => 'Execution and retention risks.',
        'additional_context' => 'Additional notes for reviewers.',
        'known_unknowns' => 'Channel efficiency and CAC range.',
        'declared_exclusions' => 'No trade secrets included.',
        'budget_range' => '10,000-25,000 USD',
        'timeline' => '6 weeks',
        'proof_links' => ['https://example.com/deck'],
        'sensitive_info_warning' => false,
        'consent_packet_only' => true,
        'consent_no_trade_secrets' => true,
        'consent_diagnosis_outcomes' => true,
        'admin_notes' => null,
        'risk_assessment' => null,
        'recommended_action' => null,
        'reviewed_at' => null,
        'reviewed_by' => null,
    ], $overrides);
}
