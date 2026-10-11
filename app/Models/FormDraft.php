<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'draft_reference',
        'form_type',
        'email',
        'case_reference',
        'data',
        'current_step',
        'status',
        'last_saved_at',
    ];

    protected $casts = [
        'data' => 'array',
        'last_saved_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'draft_reference';
    }
}
