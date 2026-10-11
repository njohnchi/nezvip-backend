<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\VentureDiagnostic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CaseDiscoveryService
{
    /**
     * Group every submission linked to an email address by its case reference.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forEmail(string $email): array
    {
        $normalized = strtolower(trim($email));

        if ($normalized === '') {
            return [];
        }

        $cases = $this->groupByCaseReference(
            $this->submissionsForEmail($normalized),
            $this->diagnosticsForEmail($normalized),
        );

        usort($cases, fn (array $a, array $b) => strcmp((string) $b['last_activity_at'], (string) $a['last_activity_at']));

        return $cases;
    }

    /**
     * List every record sharing a case reference, for the admin review screens.
     *
     * @return array<int, array<string, mixed>>
     */
    public function relatedRecords(string $caseReference, ?int $excludeSubmissionId = null, ?int $excludeDiagnosticId = null): array
    {
        $records = [];

        $submissions = FormSubmission::query()
            ->where('case_reference', $caseReference)
            ->when($excludeSubmissionId, fn ($query) => $query->whereKeyNot($excludeSubmissionId))
            ->latest()
            ->get();

        foreach ($submissions as $submission) {
            $records[] = [
                'kind' => 'submission',
                'id' => $submission->id,
                'label' => FormSubmission::labelFor($submission->form_type),
                'reference' => $submission->reference,
                'status' => $submission->status,
                'created_at' => $submission->created_at?->toIso8601String(),
            ];
        }

        $diagnostics = VentureDiagnostic::query()
            ->where('case_reference', $caseReference)
            ->when($excludeDiagnosticId, fn ($query) => $query->whereKeyNot($excludeDiagnosticId))
            ->latest()
            ->get();

        foreach ($diagnostics as $diagnostic) {
            $records[] = [
                'kind' => 'diagnostic',
                'id' => $diagnostic->id,
                'label' => 'Venture Diagnostic',
                'reference' => $diagnostic->reference,
                'status' => $diagnostic->status,
                'created_at' => $diagnostic->created_at?->toIso8601String(),
            ];
        }

        return $records;
    }

    /**
     * @return Collection<int, FormSubmission>
     */
    private function submissionsForEmail(string $email): Collection
    {
        return FormSubmission::query()
            ->whereNotNull('case_reference')
            ->whereRaw($this->emailJsonExpression('data').' = ?', [$email])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<int, VentureDiagnostic>
     */
    private function diagnosticsForEmail(string $email): Collection
    {
        return VentureDiagnostic::query()
            ->whereNotNull('case_reference')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->orderBy('created_at')
            ->get();
    }

    private function emailJsonExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "LOWER(json_extract({$column}, '$.email'))",
            default => "LOWER(json_unquote(json_extract({$column}, '$.email')))",
        };
    }

    /**
     * @param  Collection<int, FormSubmission>  $submissions
     * @param  Collection<int, VentureDiagnostic>  $diagnostics
     * @return array<int, array<string, mixed>>
     */
    private function groupByCaseReference(Collection $submissions, Collection $diagnostics): array
    {
        $cases = [];

        foreach ($submissions as $submission) {
            $reference = (string) $submission->case_reference;
            $cases[$reference] ??= $this->emptyCase($reference);

            $cases[$reference]['services'][] = [
                'type' => 'form_submission',
                'form_type' => $submission->form_type,
                'label' => FormSubmission::labelFor($submission->form_type),
                'reference' => $submission->reference,
                'status' => $submission->status,
                'submitted_at' => $submission->created_at?->toIso8601String(),
            ];

            $cases[$reference]['display_name'] = $this->displayName($cases[$reference]['display_name'], $submission->data ?? []);
            $cases[$reference]['last_activity_at'] = $this->latestTimestamp(
                $cases[$reference]['last_activity_at'],
                $submission->created_at?->toIso8601String(),
            );
        }

        foreach ($diagnostics as $diagnostic) {
            $reference = (string) $diagnostic->case_reference;
            $cases[$reference] ??= $this->emptyCase($reference);

            $cases[$reference]['services'][] = [
                'type' => 'venture_diagnostic',
                'form_type' => 'venture_diagnostic',
                'label' => 'Venture Diagnostic',
                'reference' => $diagnostic->reference,
                'status' => $diagnostic->status,
                'submitted_at' => $diagnostic->created_at?->toIso8601String(),
            ];

            $cases[$reference]['display_name'] = $this->displayName($cases[$reference]['display_name'], [
                'venture_name' => $diagnostic->venture_name,
            ]);
            $cases[$reference]['last_activity_at'] = $this->latestTimestamp(
                $cases[$reference]['last_activity_at'],
                $diagnostic->created_at?->toIso8601String(),
            );
        }

        return array_values($cases);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyCase(string $reference): array
    {
        return [
            'case_reference' => $reference,
            'display_name' => null,
            'services' => [],
            'last_activity_at' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function displayName(?string $current, array $data): ?string
    {
        if ($current !== null) {
            return $current;
        }

        foreach (['venture_name', 'institution_name', 'company_name', 'organization_name', 'organization'] as $key) {
            $value = $data[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function latestTimestamp(?string $current, ?string $candidate): ?string
    {
        if ($candidate === null) {
            return $current;
        }

        if ($current === null) {
            return $candidate;
        }

        return $candidate > $current ? $candidate : $current;
    }
}
