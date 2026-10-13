<?php

namespace App\Services;

use App\Models\FormDraft;
use Illuminate\Database\Eloquent\Builder;

class FormDraftService
{
    /**
     * Retire the in-progress drafts that a valid service submission supersedes.
     *
     * A draft is only completed when it belongs to the same person (normalised
     * email) and service form type. It is matched when it is unattached (no case
     * reference) or belongs to one of the supplied case references, so a
     * separate undertaking's draft for the same service is never discarded.
     *
     * @param  array<int, string|null>  $caseReferences
     * @return int the number of drafts completed
     */
    public function completeForSubmission(string $email, string $formType, array $caseReferences = []): int
    {
        $email = strtolower(trim($email));

        if ($email === '') {
            return 0;
        }

        $references = $this->normaliseReferences($caseReferences);

        return FormDraft::query()
            ->where('status', 'draft')
            ->where('form_type', $formType)
            ->where('email', $email)
            ->where(function (Builder $query) use ($references): void {
                $query->whereNull('case_reference');

                if ($references !== []) {
                    $query->orWhereIn('case_reference', $references);
                }
            })
            ->update(['status' => 'completed']);
    }

    /**
     * Uppercase, trim and de-duplicate the supplied case references.
     *
     * @param  array<int, string|null>  $caseReferences
     * @return array<int, string>
     */
    private function normaliseReferences(array $caseReferences): array
    {
        $references = array_map(
            static fn (?string $reference): ?string => $reference === null ? null : strtoupper(trim($reference)),
            $caseReferences,
        );

        $references = array_filter(
            $references,
            static fn (?string $reference): bool => $reference !== null && $reference !== '',
        );

        return array_values(array_unique($references));
    }
}
