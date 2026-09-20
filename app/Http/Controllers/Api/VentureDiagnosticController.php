<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\VentureDiagnostic;
use App\Services\AcknowledgementEmailService;
use App\Services\TeamNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VentureDiagnosticController extends Controller
{
    public function __construct(
        public AcknowledgementEmailService $acknowledgementEmailService,
        public TeamNotificationService $teamNotificationService,
    ) {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Intake metadata
            'intake_type' => 'required|string',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'location' => 'required|string|max:255',
            'organization_name' => 'nullable|string|max:255',
            'organization_type' => 'nullable|string|max:255',
            'role_title' => 'nullable|string|max:255',

            // Section A
            'venture_name' => 'required|string|max:255',
            'industry_category' => 'required|string|max:255',
            'venture_description' => 'required|string',

            // Section B
            'what_is_sold' => 'required|string',
            'primary_customer_type' => 'required|string',
            'core_value_proposition' => 'required|string',

            // Section C
            'revenue_model' => 'required|string',
            'pricing_logic' => 'required|string',
            'payment_flow' => 'required|string',
            'revenue_risk_carrier' => 'required|string',

            // Section D
            'delivery_process' => 'required|string',
            'who_performs_work' => 'required|string',
            'operational_dependencies' => 'required|string',
            'scale_bottleneck' => 'required|string',

            // Section E
            'claimed_bottlenecks' => 'required|string',
            'assets_ip_licenses' => 'nullable|string',
            'competitive_positioning' => 'required|string',

            // Section F
            'growth_mechanism' => 'required|string',
            'founder_role' => 'required|string',
            'replaceability_assumptions' => 'required|string',

            // Section G
            'customer_awareness' => 'required|string',
            'demand_origin' => 'required|string',
            'demand_responsibility' => 'required|string',
            'demand_ownership' => 'required|string',
            'underperformance_impact' => 'required|string',
            'new_seller_benefits' => 'required|string',
            'demand_dynamics' => 'required|string',

            // Section H
            'capital_intensity' => 'required|string',
            'regulatory_constraints' => 'required|string',
            'known_risks' => 'required|string',

            // Section I
            'additional_context' => 'nullable|string',
            'known_unknowns' => 'nullable|string',
            'declared_exclusions' => 'required|string',

            // Budget & Timeline
            'budget_range' => 'required|string',
            'timeline' => 'required|string',
            'proof_links' => 'required|array|min:1',
            'proof_links.*' => 'url',
            'sensitive_info_warning' => 'boolean',

            // Consents
            'consent_packet_only' => 'required|accepted',
            'consent_no_trade_secrets' => 'required|accepted',
            'consent_diagnosis_outcomes' => 'required|accepted',

            // Case linkage & intake assistance
            'case_reference' => 'nullable|string|max:60',
            'operator_assisted' => 'sometimes|boolean',
            'assisted_intake_key' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $diagnostic = VentureDiagnostic::create($validator->validated());
        $diagnostic->forceFill([
            'case_reference' => $this->resolveCaseReference($request, $diagnostic),
            'operator_assisted' => $this->isOperatorAssisted($request),
        ])->save();

        $reference = $diagnostic->reference;

        $this->acknowledgementEmailService->send(
            EmailTemplate::VENTURE_DIAGNOSTIC_ACKNOWLEDGEMENT,
            $diagnostic->email,
            [
                'name' => $diagnostic->full_name,
                'reference' => $reference,
                'venture_name' => $diagnostic->venture_name,
                'intake_type' => $diagnostic->intake_type,
                'submitted_at' => $diagnostic->created_at->toDayDateTimeString(),
                'app_name' => config('app.name'),
            ],
        );

        $this->teamNotificationService->notify(
            'New Venture Diagnostic submission ('.$reference.')',
            $this->teamNotificationMessage($reference, $diagnostic),
        );

        return response()->json([
            'success' => true,
            'message' => 'Venture diagnostic submitted successfully',
            'data' => [
                'id' => $diagnostic->id,
                'reference' => $reference,
                'case_reference' => $diagnostic->case_reference,
            ],
        ], 201);
    }

    private function resolveCaseReference(Request $request, VentureDiagnostic $diagnostic): ?string
    {
        $candidate = (string) ($request->input('case_reference') ?? '');

        if ($candidate === '') {
            return $diagnostic->reference;
        }

        $candidate = strtoupper(trim($candidate));

        return $candidate !== '' ? $candidate : $diagnostic->reference;
    }

    private function isOperatorAssisted(Request $request): bool
    {
        if (! $request->boolean('operator_assisted')) {
            return false;
        }

        $key = (string) config('nezvip.assisted_intake_key');

        if ($key === '') {
            return false;
        }

        return hash_equals($key, (string) $request->input('assisted_intake_key', ''));
    }

    private function teamNotificationMessage(string $reference, VentureDiagnostic $diagnostic): string
    {
        return implode("\n", [
            'VENTURE DIAGNOSTIC',
            'Reference: '.$reference,
            'Name: '.$diagnostic->full_name,
            'Email: '.$diagnostic->email,
            'Venture: '.$diagnostic->venture_name,
            '',
            'A new diagnostic is awaiting review in the admin diagnostics area.',
        ]);
    }
}
