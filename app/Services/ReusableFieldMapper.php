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
        $fields = [];

        foreach ($this->sourcesForCaseReference($caseReference) as $source) {
            $fields = $this->merge($fields, $source['fields']);
        }

        return $fields;
    }

    /**
     * Detect shared fields whose confirmed value changed between submissions.
     *
     * A later non-empty value that differs from the previously confirmed value
     * is treated as a contradiction: both source records are preserved and the
     * current value is flagged for internal clarification rather than silently
     * replacing the earlier one. Materiality is not guessed — every change to a
     * previously confirmed shared field is surfaced for an operator to confirm.
     *
     * @return array<int, array<string, mixed>>
     */
    public function contradictionsForCaseReference(string $caseReference): array
    {
        $confirmed = [];
        $contradictions = [];

        foreach ($this->sourcesForCaseReference($caseReference) as $source) {
            foreach (self::SHARED_FIELDS as $key) {
                $value = $source['fields'][$key] ?? null;

                if (! is_string($value) || trim($value) === '') {
                    continue;
                }

                $value = trim($value);
                $previous = $confirmed[$key] ?? null;

                if ($previous !== null && strcasecmp($previous['value'], $value) !== 0) {
                    $contradictions[$key] = [
                        'field' => $key,
                        'previous' => $previous['value'],
                        'previous_reference' => $previous['reference'],
                        'previous_at' => $previous['at'],
                        'current' => $value,
                        'current_reference' => $source['reference'],
                        'current_at' => $source['at'],
                    ];
                }

                $confirmed[$key] = [
                    'value' => $value,
                    'reference' => $source['reference'],
                    'at' => $source['at'],
                ];
            }
        }

        return array_values($contradictions);
    }

    /**
     * Replay every submission and diagnostic for a case in chronological order.
     *
     * Records sharing a timestamp keep their insertion order (submissions then
     * diagnostics, each by id), so the most recently created record wins ties.
     *
     * @return array<int, array{sort: int, sequence: int, reference: string, at: string|null, fields: array<string, mixed>}>
     */
    private function sourcesForCaseReference(string $caseReference): array
    {
        $sources = [];
        $sequence = 0;

        foreach (FormSubmission::query()
            ->where('case_reference', $caseReference)
            ->orderBy('created_at')
            ->get() as $submission) {
            $sources[] = [
                'sort' => $submission->created_at?->getTimestamp() ?? 0,
                'sequence' => $sequence++,
                'reference' => $submission->reference,
                'at' => $submission->created_at?->toIso8601String(),
                'fields' => $submission->data ?? [],
            ];
        }

        foreach (VentureDiagnostic::query()
            ->where('case_reference', $caseReference)
            ->orderBy('created_at')
            ->get() as $diagnostic) {
            $sources[] = [
                'sort' => $diagnostic->created_at?->getTimestamp() ?? 0,
                'sequence' => $sequence++,
                'reference' => $diagnostic->reference,
                'at' => $diagnostic->created_at?->toIso8601String(),
                'fields' => $this->diagnosticFields($diagnostic),
            ];
        }

        usort($sources, fn (array $a, array $b): int => $a['sort'] <=> $b['sort'] ?: $a['sequence'] <=> $b['sequence']);

        return $sources;
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
