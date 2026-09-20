<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\FormSubmission;
use App\Services\AcknowledgementEmailService;
use App\Services\TeamNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormSubmissionController extends Controller
{
    public function __construct(
        public AcknowledgementEmailService $acknowledgementEmailService,
        public TeamNotificationService $teamNotificationService,
    ) {}

    private function getValidationRules(string $formType): array
    {
        $rules = [
            'form_type' => 'required|string',
        ];

        switch ($formType) {
            case 'scope_review':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.venture_name' => 'required|string|max:255',
                    'data.venture_summary' => 'required|string',
                    'data.scope_questions' => 'required|string',
                ]);

            case 'venture_deconstruction':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.venture_description' => 'required|string',
                    'data.value_claim' => 'required|string',
                    'data.current_uncertainty' => 'required|string',
                    'data.target_market' => 'required|string|max:255',
                    'data.additional_context' => 'nullable|string',
                ]);

            case 'venture_orientation':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.venture_name' => 'required|string|max:255',
                    'data.venture_summary' => 'required|string',
                    'data.scope_questions' => 'required|string',
                ]);

            case 'venture_synthesis':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.venture_name' => 'required|string|max:255',
                    'data.category' => 'required|string|max:255',
                    'data.venture_description' => 'required|string',
                    'data.target_market' => 'required|string|max:255',
                    'data.offering' => 'required|string',
                    'data.economic_actor' => 'required|string',
                    'data.value_claim' => 'required|string',
                    'data.revenue_model' => 'required|string',
                    'data.pricing_logic' => 'required|string',
                    'data.delivery_process' => 'required|string',
                    'data.roles_dependencies' => 'required|string',
                    'data.control_points' => 'required|string',
                    'data.growth_mechanism' => 'required|string',
                    'data.founder_dependence' => 'required|string',
                    'data.demand_origin' => 'required|string',
                    'data.constraints' => 'required|string',
                    'data.proof_links' => 'nullable|string',
                    'data.unresolved_items' => 'nullable|string',
                    'data.confirm_factual' => 'required|boolean',
                ]);

            case 'venture_architecture_request':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.organization' => 'nullable|string|max:255',
                    'data.architecture_type' => 'required|string|max:255',
                    'data.undertaking_context' => 'required|string',
                    'data.scope_description' => 'required|string',
                    'data.diagnostic_reference' => 'nullable|string|max:255',
                    'data.notes' => 'nullable|string',
                ]);

            case 'program_brief_request':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.organization' => 'nullable|string|max:255',
                    'data.program_interest' => 'required|string|max:255',
                    'data.venture_context' => 'required|string',
                    'data.objectives' => 'required|string',
                    'data.timeline' => 'required|string|max:255',
                    'data.diagnostic_status' => 'nullable|string|max:255',
                    'data.notes' => 'nullable|string',
                ]);

            case 'retainer_request':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.organization' => 'nullable|string|max:255',
                    'data.retainer_type' => 'required|string|max:255',
                    'data.venture_context' => 'required|string',
                    'data.scope_description' => 'required|string',
                    'data.duration' => 'required|string|max:255',
                    'data.diagnostic_reference' => 'nullable|string|max:255',
                    'data.notes' => 'nullable|string',
                ]);

            case 'institutional_brief':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.institution_name' => 'required|string|max:255',
                    'data.institution_type' => 'required|string',
                    'data.country' => 'required|string|max:255',
                    'data.objective' => 'required|string',
                    'data.desired_outcomes' => 'required|string',
                    'data.timeline' => 'required|string',
                    'data.budget_range' => 'required|string',
                    'data.notes' => 'nullable|string',
                ]);

            case 'licensing_review':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.organization_name' => 'required|string|max:255',
                    'data.deployment_scope' => 'required|string',
                    'data.sector' => 'required|string',
                    'data.geography' => 'required|string',
                    'data.timeline' => 'required|string',
                    'data.notes' => 'nullable|string',
                ]);

            case 'investor_brief':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.investor_type' => 'required|string',
                    'data.ticket_size_range' => 'required|string',
                    'data.geography' => 'required|string',
                    'data.investment_thesis' => 'required|string',
                    'data.timeline' => 'required|string',
                    'data.notes' => 'nullable|string',
                ]);

            case 'production_partner':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.company_name' => 'required|string|max:255',
                    'data.location' => 'required|string',
                    'data.capability_description' => 'required|string',
                    'data.capacity_volume' => 'required|string',
                    'data.certifications' => 'nullable|string',
                    'data.reference_links' => 'nullable|string',
                    'data.notes' => 'nullable|string',
                ]);

            case 'distribution_partner':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.company_name' => 'required|string|max:255',
                    'data.territory' => 'required|string',
                    'data.channels' => 'required|string',
                    'data.logistics_capability' => 'required|string',
                    'data.references' => 'nullable|string',
                    'data.notes' => 'nullable|string',
                ]);

            case 'insights_subscribe':
                return array_merge($rules, [
                    'data.email' => 'required|email|max:255',
                    'data.interest_tags' => 'nullable|array',
                ]);

            case 'insights_request_report':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.organization_name' => 'nullable|string|max:255',
                    'data.report_title' => 'required|string|max:255',
                ]);

            case 'media_inquiry':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.media_outlet' => 'required|string|max:255',
                    'data.inquiry_type' => 'required|string',
                    'data.message' => 'required|string',
                ]);

            case 'career_application':
                return array_merge($rules, [
                    'data.full_name' => 'required|string|max:255',
                    'data.email' => 'required|email|max:255',
                    'data.location' => 'required|string',
                    'data.role_applied_for' => 'required|string',
                    'data.relevant_experience' => 'required|string',
                    'data.profile_link' => 'required|url',
                ]);

            default:
                return $rules;
        }
    }

    public function store(Request $request)
    {
        $formType = $request->input('form_type');
        $rules = array_merge($this->getValidationRules($formType), [
            'case_reference' => 'nullable|string|max:60',
            'operator_assisted' => 'sometimes|boolean',
            'assisted_intake_key' => 'sometimes|string',
        ]);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = (array) ($request->input('data', []) ?? []);

        $submission = FormSubmission::create([
            'form_type' => $formType,
            'data' => $data,
            'case_reference' => $this->resolveCaseReference($request, $data),
            'operator_assisted' => $this->isOperatorAssisted($request),
        ]);

        if ($submission->case_reference === null) {
            $submission->update(['case_reference' => $submission->reference]);
        }

        $reference = $submission->reference;

        $recipientEmail = (string) (($data['email'] ?? '') ?: '');

        if ($recipientEmail !== '') {
            $this->acknowledgementEmailService->send(
                $this->acknowledgementTemplateFor($formType),
                $recipientEmail,
                [
                    'name' => (string) ($data['full_name'] ?? $recipientEmail),
                    'reference' => $reference,
                    'submitted_at' => $submission->created_at->toDayDateTimeString(),
                    'app_name' => config('app.name'),
                ],
            );
        }

        $this->teamNotificationService->notify(
            'New '.FormSubmission::labelFor($formType).' submission ('.$reference.')',
            $this->teamNotificationMessage(FormSubmission::labelFor($formType), $reference, $data),
        );

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
            'data' => [
                'id' => $submission->id,
                'reference' => $reference,
                'case_reference' => $submission->case_reference,
            ],
        ], 201);
    }

    private function acknowledgementTemplateFor(string $formType): string
    {
        return match ($formType) {
            'venture_deconstruction' => EmailTemplate::VENTURE_DECONSTRUCTION_ACKNOWLEDGEMENT,
            'venture_synthesis' => EmailTemplate::VENTURE_SYNTHESIS_ACKNOWLEDGEMENT,
            default => EmailTemplate::FORM_SUBMISSION_ACKNOWLEDGEMENT,
        };
    }

    private function resolveCaseReference(Request $request, array $data): ?string
    {
        $candidate = (string) ($request->input('case_reference') ?? '')
            ?: (string) ($data['case_reference'] ?? '')
            ?: (string) ($data['diagnostic_reference'] ?? '')
            ?: (string) ($data['prior_reference'] ?? '');

        $candidate = strtoupper(trim($candidate));

        return $candidate !== '' ? $candidate : null;
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

    private function teamNotificationMessage(string $label, string $reference, array $data): string
    {
        $lines = [strtoupper($label), 'Reference: '.$reference];

        if (isset($data['full_name']) && $data['full_name'] !== '') {
            $lines[] = 'Name: '.$data['full_name'];
        }

        if (isset($data['email']) && $data['email'] !== '') {
            $lines[] = 'Email: '.$data['email'];
        }

        $link = (string) ($data['venture_name'] ?? $data['institution_name'] ?? $data['company_name'] ?? $data['organization_name'] ?? '');

        if ($link !== '') {
            $lines[] = 'Undertaking: '.$link;
        }

        $lines[] = '';
        $lines[] = 'A new submission is awaiting review in the admin submissions area.';

        return implode("\n", $lines);
    }
}
