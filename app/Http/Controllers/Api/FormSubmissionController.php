<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\FormSubmission;
use App\Services\AcknowledgementEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormSubmissionController extends Controller
{
    public function __construct(public AcknowledgementEmailService $acknowledgementEmailService) {}

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
        $rules = $this->getValidationRules($formType);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $submission = FormSubmission::create([
            'form_type' => $formType,
            'data' => $request->input('data'),
        ]);

        $references = [
            'scope_review' => 'SR',
            'institutional_brief' => 'IB',
            'licensing_review' => 'LR',
            'investor_brief' => 'INV',
            'production_partner' => 'PP',
            'distribution_partner' => 'DP',
            'insights_subscribe' => 'IS',
            'insights_request_report' => 'IRR',
            'media_inquiry' => 'MI',
            'career_application' => 'CA',
        ];

        $prefix = $references[$formType] ?? 'FS';
        $reference = $prefix.'-'.str_pad($submission->id, 6, '0', STR_PAD_LEFT);
        $submissionData = $request->input('data', []);
        $recipientEmail = (string) ($submissionData['email'] ?? '');

        if ($recipientEmail !== '') {
            $this->acknowledgementEmailService->send(
                EmailTemplate::FORM_SUBMISSION_ACKNOWLEDGEMENT,
                $recipientEmail,
                [
                    'name' => (string) ($submissionData['full_name'] ?? $recipientEmail),
                    'reference' => $reference,
                    'form_type' => $formType,
                    'form_type_label' => $this->formTypeLabel($formType),
                    'submitted_at' => $submission->created_at->toDayDateTimeString(),
                    'app_name' => config('app.name'),
                ],
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
            'data' => [
                'id' => $submission->id,
                'reference' => $reference,
            ],
        ], 201);
    }

    private function formTypeLabel(string $formType): string
    {
        return match ($formType) {
            'scope_review' => 'Scope Review',
            'institutional_brief' => 'Institutional Brief',
            'licensing_review' => 'Licensing Review',
            'investor_brief' => 'Investor Brief',
            'production_partner' => 'Production Partner',
            'distribution_partner' => 'Distribution Partner',
            'insights_subscribe' => 'Insights Subscription',
            'insights_request_report' => 'Insight Report Request',
            'media_inquiry' => 'Media Inquiry',
            'career_application' => 'Career Application',
            default => str($formType)->replace('_', ' ')->title()->toString(),
        };
    }
}
