<?php

namespace App\Services;

use App\Models\CaseReference;

class CaseReferenceService
{
    /**
     * Mint a new permanent, service-neutral case reference (e.g. CASE-000123).
     */
    public function mint(): string
    {
        return CaseReference::query()->create([])->reference;
    }
}
