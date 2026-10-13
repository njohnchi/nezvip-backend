<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registry that mints permanent, service-neutral case references.
 *
 * Each row represents one undertaking; its auto-incrementing id becomes the
 * numeric part of a CASE-###### identifier. This is an identity allocator
 * only, not a case-governance record.
 */
class CaseReference extends Model
{
    protected $fillable = [];

    public function getReferenceAttribute(): string
    {
        return 'CASE-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
