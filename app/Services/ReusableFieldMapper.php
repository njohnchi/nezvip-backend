<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\VentureDiagnostic;

class ReusableFieldMapper
{
    /**
     * Shared fields that may be reused across NezVIP intake forms.
     *
     * @var array<int, string>
     */
    private const SHARED_FIELDS = [
        'full_name',
        'email',
        'venture_name',
        'venture_description',
        'target_market',
        'industry_category',
        'category',
        'organization',
        'organization_name',
        'location',
    ];

    /**
     * Build the reusable field set for a case from its earliest known values.
     *
     * @return array<string, mixed>
     */
    public function forCaseReference(string $caseReference): array
    {
        $fields = [];

        $submissions = FormSubmission::query()
            ->where('case_reference', $caseReference)
            ->orderBy('created_at')
            ->get();

        foreach ($submissions as $submission) {
            $fields = $this->merge($fields, $submission->data ?? []);
        }

        $diagnostics = VentureDiagnostic::query()
            ->where('case_reference', $caseReference)
            ->orderBy('created_at')
            ->get();

        foreach ($diagnostics as $diagnostic) {
            $fields = $this->merge($fields, $this->diagnosticFields($diagnostic));
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function merge(array $fields, array $source): array
    {
        foreach (self::SHARED_FIELDS as $key) {
            if (isset($fields[$key]) && $fields[$key] !== '') {
                continue;
            }

            $value = $source[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                $fields[$key] = trim($value);
            }
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    private function diagnosticFields(VentureDiagnostic $diagnostic): array
    {
        return [
            'full_name' => $diagnostic->full_name,
            'email' => $diagnostic->email,
            'venture_name' => $diagnostic->venture_name,
            'venture_description' => $diagnostic->venture_description,
            'industry_category' => $diagnostic->industry_category,
            'organization_name' => $diagnostic->organization_name,
            'location' => $diagnostic->location,
        ];
    }
}
