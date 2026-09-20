<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'form_type',
        'status',
        'data',
        'admin_notes',
        'case_reference',
        'operator_assisted',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'data' => 'array',
        'operator_assisted' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getReferenceAttribute(): string
    {
        return self::prefixFor($this->form_type).'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Reference prefix used for display and acknowledgement references.
     */
    public static function prefixFor(string $formType): string
    {
        return self::referencePrefixes()[$formType] ?? 'FS';
    }

    /**
     * @return array<string, string>
     */
    public static function referencePrefixes(): array
    {
        return [
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
            'venture_deconstruction' => 'VDS',
            'venture_orientation' => 'VO',
            'venture_synthesis' => 'SYN',
            'venture_architecture_request' => 'VA',
            'program_brief_request' => 'PBR',
            'retainer_request' => 'RTR',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
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
            'venture_deconstruction' => 'Venture Deconstruction',
            'venture_orientation' => 'Strategic Venture Orientation',
            'venture_synthesis' => 'Venture Synthesis',
            'venture_architecture_request' => 'Venture Architecture Request',
            'program_brief_request' => 'Program Brief Request',
            'retainer_request' => 'Retainer Request',
        ];
    }

    public static function labelFor(string $formType): string
    {
        return self::labels()[$formType]
            ?? str($formType)->replace('_', ' ')->title()->toString();
    }
}
