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
     * Build the reusable field set for a case from its latest valid values.
     *
     * Submissions and diagnostics are replayed in chronological order so the
     * most recently confirmed value of each shared field wins. The underlying
     * records are never modified, preserving full submission history and
     * provenance for earlier engagements.
     *
     * @return array<string, mixed>
     */
    public function forCaseReference(string $caseReference): array
    {
        $sources = [];

        foreach (FormSubmission::query()
            ->where('case_reference', $caseReference)
            ->orderBy('created_at')
            ->get() as $submission) {
            $sources[] = [
                'at' => $submission->created_at?->getTimestamp() ?? 0,
                'fields' => $submission->data ?? [],
            ];
        }

        foreach (VentureDiagnostic::query()
            ->where('case_reference', $caseReference)
            ->orderBy('created_at')
            ->get() as $diagnostic) {
            $sources[] = [
                'at' => $diagnostic->created_at?->getTimestamp() ?? 0,
                'fields' => $this->diagnosticFields($diagnostic),
            ];
        }

        usort($sources, fn (array $a, array $b): int => $a['at'] <=> $b['at']);

        $fields = [];

        foreach ($sources as $source) {
            $fields = $this->merge($fields, $source['fields']);
        }

        return $fields;
    }

    /**
     * Overlay a source's shared fields, letting the latest valid value win.
     *
     * A later source only replaces an earlier value when it carries a
     * non-empty value, so a blank answer never erases previously confirmed
     * information.
     *
     * @param  array<string, mixed>  $fields
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    private function merge(array $fields, array $source): array
    {
        foreach (self::SHARED_FIELDS as $key) {
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
