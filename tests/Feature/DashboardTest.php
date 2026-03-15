<?php

use App\Models\Career;
use App\Models\CareerApplication;
use App\Models\FormSubmission;
use App\Models\Insight;
use App\Models\User;
use App\Models\VentureDiagnostic;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('account.email_verified', true)
        ->where('account.permissions_count', 0)
        ->where('account.insights_subscription_active', false)
        ->where('recent_activity', []),
    );
});

test('dashboard shows admin summaries analytics and recent activity for authorized users', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $permissions = [
        'view users',
        'view roles',
        'view insights',
        'manage careers',
        'view diagnostics',
        'view submissions',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Editor', 'web');
    Role::findOrCreate('Super Admin', 'web');

    $admin = User::factory()->create();
    $admin->givePermissionTo($permissions);

    User::factory()->create([
        'insights_subscribed_at' => now()->subDay(),
        'insights_subscription_expires_at' => now()->addMonth(),
    ]);

    Insight::query()->create([
        'title' => 'Published insight',
        'slug' => 'published-insight',
        'excerpt' => 'Published excerpt',
        'content' => 'Published content body',
        'preview_chars' => 240,
        'is_premium' => true,
        'is_published' => true,
        'published_at' => now()->subDay(),
        'author_id' => $admin->id,
    ]);

    Insight::query()->create([
        'title' => 'Draft insight',
        'slug' => 'draft-insight',
        'excerpt' => 'Draft excerpt',
        'content' => 'Draft content body',
        'preview_chars' => 240,
        'is_premium' => false,
        'is_published' => false,
        'published_at' => null,
        'author_id' => $admin->id,
    ]);

    $activeCareer = Career::factory()->active()->create();
    Career::factory()->inactive()->create();

    CareerApplication::query()->create([
        'career_id' => $activeCareer->id,
        'full_name' => 'Pending Applicant',
        'email' => 'pending-applicant@example.com',
        'location' => 'Lagos, Nigeria',
        'role_applied_for' => $activeCareer->role_title,
        'relevant_experience' => 'Relevant experience for the pending application.',
        'profile_link' => 'https://example.com/pending-applicant',
        'status' => 'pending',
    ]);

    CareerApplication::query()->create([
        'career_id' => $activeCareer->id,
        'full_name' => 'Shortlisted Applicant',
        'email' => 'shortlisted-applicant@example.com',
        'location' => 'Abuja, Nigeria',
        'role_applied_for' => $activeCareer->role_title,
        'relevant_experience' => 'Relevant experience for the shortlisted application.',
        'profile_link' => 'https://example.com/shortlisted-applicant',
        'status' => 'shortlisted',
    ]);

    FormSubmission::query()->create([
        'form_type' => 'scope_review',
        'status' => 'pending',
        'data' => ['full_name' => 'Pending Submitter', 'email' => 'pending@example.com'],
    ]);

    FormSubmission::query()->create([
        'form_type' => 'scope_review',
        'status' => 'closed',
        'data' => ['full_name' => 'Closed Submitter', 'email' => 'closed@example.com'],
    ]);

    FormSubmission::query()->create([
        'form_type' => 'media_inquiry',
        'status' => 'contacted',
        'data' => ['full_name' => 'Media Submitter', 'email' => 'media@example.com'],
    ]);

    VentureDiagnostic::query()->create(ventureDiagnosticPayload([
        'venture_name' => 'Pending Venture',
        'status' => 'pending',
        'viability_score' => null,
    ]));

    VentureDiagnostic::query()->create(ventureDiagnosticPayload([
        'venture_name' => 'Approved Venture',
        'status' => 'approved',
        'viability_score' => 88,
    ]));

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('account.permissions_count', 6)
        ->where('summaries.users.value', 2)
        ->where('summaries.roles.value', 2)
        ->where('summaries.insights.value', 1)
        ->where('summaries.careers.value', 1)
        ->where('summaries.review_queue.value', 3)
        ->where('pipelines.insights.total', 2)
        ->where('pipelines.insights.statuses.0.value', 1)
        ->where('pipelines.applications.total', 2)
        ->where('pipelines.applications.statuses.0.value', 1)
        ->where('pipelines.applications.statuses.2.value', 1)
        ->where('pipelines.diagnostics.total', 2)
        ->where('pipelines.diagnostics.statuses.0.value', 1)
        ->where('pipelines.diagnostics.statuses.2.value', 1)
        ->where('pipelines.submissions.total', 3)
        ->where('pipelines.submissions.statuses.0.value', 1)
        ->where('pipelines.submissions.statuses.2.value', 1)
        ->where('pipelines.submissions.statuses.3.value', 1)
        ->where('analytics.series.users.total', 2)
        ->where('analytics.series.insights.total', 2)
        ->where('analytics.series.applications.total', 2)
        ->where('analytics.series.diagnostics.total', 2)
        ->where('analytics.series.submissions.total', 3)
        ->where('analytics.top_form_types.0.form_type', 'scope_review')
        ->where('analytics.top_form_types.0.count', 2)
        ->has('recent_activity', 8),
    );
});

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
        'viability_score' => null,
        'risk_assessment' => null,
        'recommended_action' => null,
        'reviewed_at' => null,
        'reviewed_by' => null,
    ], $overrides);
}

