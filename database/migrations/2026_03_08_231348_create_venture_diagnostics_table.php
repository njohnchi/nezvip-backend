<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_diagnostics', function (Blueprint $table) {
            $table->id();

            // Intake metadata
            $table->string('intake_type');
            $table->string('status')->default('pending'); // pending, reviewing, approved, rejected, re-architect, kill
            $table->string('full_name');
            $table->string('email');
            $table->string('location');
            $table->string('organization_name')->nullable();
            $table->string('organization_type')->nullable();
            $table->string('role_title')->nullable();

            // Section A: Venture Identity
            $table->string('venture_name');
            $table->string('industry_category');
            $table->text('venture_description');

            // Section B: Offering & Value
            $table->text('what_is_sold');
            $table->string('primary_customer_type');
            $table->text('core_value_proposition');

            // Section C: Business Model
            $table->string('revenue_model');
            $table->text('pricing_logic');
            $table->text('payment_flow');
            $table->text('revenue_risk_carrier');

            // Section D: Operations
            $table->text('delivery_process');
            $table->text('who_performs_work');
            $table->text('operational_dependencies');
            $table->text('scale_bottleneck');

            // Section E: Control & Advantage
            $table->text('claimed_bottlenecks');
            $table->text('assets_ip_licenses')->nullable();
            $table->text('competitive_positioning');

            // Section F: Growth & Scale
            $table->text('growth_mechanism');
            $table->text('founder_role');
            $table->text('replaceability_assumptions');

            // Section G: Demand Context
            $table->text('customer_awareness');
            $table->text('demand_origin');
            $table->text('demand_responsibility');
            $table->text('demand_ownership');
            $table->text('underperformance_impact');
            $table->text('new_seller_benefits');
            $table->text('demand_dynamics');

            // Section H: Risk & Constraints
            $table->text('capital_intensity');
            $table->text('regulatory_constraints');
            $table->text('known_risks');

            // Section I: Notes & Clarifications
            $table->text('additional_context')->nullable();
            $table->text('known_unknowns')->nullable();
            $table->text('declared_exclusions');

            // Budget & Timeline
            $table->string('budget_range');
            $table->string('timeline');
            $table->json('proof_links');
            $table->boolean('sensitive_info_warning')->default(false);

            // Consents
            $table->boolean('consent_packet_only')->default(false);
            $table->boolean('consent_no_trade_secrets')->default(false);
            $table->boolean('consent_diagnosis_outcomes')->default(false);

            // Assessment fields
            $table->text('admin_notes')->nullable();
            $table->integer('viability_score')->nullable();
            $table->json('risk_assessment')->nullable();
            $table->text('recommended_action')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_diagnostics');
    }
};
