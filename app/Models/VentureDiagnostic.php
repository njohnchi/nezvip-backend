<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureDiagnostic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Intake metadata
        'intake_type', 'status', 'full_name', 'email', 'location',
        'organization_name', 'organization_type', 'role_title',

        // Section A
        'venture_name', 'industry_category', 'venture_description',

        // Section B
        'what_is_sold', 'primary_customer_type', 'core_value_proposition',

        // Section C
        'revenue_model', 'pricing_logic', 'payment_flow', 'revenue_risk_carrier',

        // Section D
        'delivery_process', 'who_performs_work', 'operational_dependencies', 'scale_bottleneck',

        // Section E
        'claimed_bottlenecks', 'assets_ip_licenses', 'competitive_positioning',

        // Section F
        'growth_mechanism', 'founder_role', 'replaceability_assumptions',

        // Section G
        'customer_awareness', 'demand_origin', 'demand_responsibility',
        'demand_ownership', 'underperformance_impact', 'new_seller_benefits', 'demand_dynamics',

        // Section H
        'capital_intensity', 'regulatory_constraints', 'known_risks',

        // Section I
        'additional_context', 'known_unknowns', 'declared_exclusions',

        // Budget & Timeline
        'budget_range', 'timeline', 'proof_links', 'sensitive_info_warning',

        // Consents
        'consent_packet_only', 'consent_no_trade_secrets', 'consent_diagnosis_outcomes',

        // Assessment
        'admin_notes', 'viability_score', 'risk_assessment', 'recommended_action',
        'reviewed_at', 'reviewed_by',
    ];

    protected $casts = [
        'proof_links' => 'array',
        'risk_assessment' => 'array',
        'sensitive_info_warning' => 'boolean',
        'consent_packet_only' => 'boolean',
        'consent_no_trade_secrets' => 'boolean',
        'consent_diagnosis_outcomes' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
