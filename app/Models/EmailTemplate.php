<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EmailTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\EmailTemplateFactory> */
    use HasFactory;

    public const FORM_SUBMISSION_ACKNOWLEDGEMENT = 'form_submission_acknowledgement';

    public const VENTURE_DIAGNOSTIC_ACKNOWLEDGEMENT = 'venture_diagnostic_acknowledgement';

    public const CAREER_APPLICATION_ACKNOWLEDGEMENT = 'career_application_acknowledgement';

    protected $fillable = [
        'key',
        'name',
        'description',
        'subject',
        'body',
        'is_active',
        'available_variables',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'available_variables' => 'array',
        ];
    }

    public static function ensureDefaults(): void
    {
        foreach (self::defaultTemplates() as $template) {
            self::query()->firstOrCreate(
                ['key' => $template['key']],
                $template,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function defaultTemplates(): array
    {
        return [
            [
                'key' => self::FORM_SUBMISSION_ACKNOWLEDGEMENT,
                'name' => 'Form Submission Acknowledgement',
                'description' => 'Sent after a general form submission is received.',
                'subject' => 'We received your submission ({{reference}})',
                'body' => "Hello {{name}},\n\nThank you for your {{form_type_label}} submission to {{app_name}}.\n\nReference: {{reference}}\nSubmitted: {{submitted_at}}\n\nOur team will review your request and follow up if additional details are needed.\n\nRegards,\n{{app_name}} Team",
                'is_active' => true,
                'available_variables' => [
                    'name',
                    'reference',
                    'form_type',
                    'form_type_label',
                    'submitted_at',
                    'app_name',
                ],
            ],
            [
                'key' => self::VENTURE_DIAGNOSTIC_ACKNOWLEDGEMENT,
                'name' => 'Venture Diagnostic Acknowledgement',
                'description' => 'Sent after a venture diagnostic intake form is submitted.',
                'subject' => 'Venture diagnostic received ({{reference}})',
                'body' => "Hello {{name}},\n\nWe have received your venture diagnostic for \"{{venture_name}}\".\n\nReference: {{reference}}\nSubmitted: {{submitted_at}}\n\nThank you for the detailed information. Our review team will assess your submission and update you on next steps.\n\nRegards,\n{{app_name}} Team",
                'is_active' => true,
                'available_variables' => [
                    'name',
                    'reference',
                    'venture_name',
                    'intake_type',
                    'submitted_at',
                    'app_name',
                ],
            ],
            [
                'key' => self::CAREER_APPLICATION_ACKNOWLEDGEMENT,
                'name' => 'Career Application Acknowledgement',
                'description' => 'Sent after a candidate submits a career application.',
                'subject' => 'Application received for {{role_title}} ({{reference}})',
                'body' => "Hello {{name}},\n\nThank you for applying to {{role_title}} at {{venture_name}}.\n\nReference: {{reference}}\nSubmitted: {{submitted_at}}\n\nOur hiring team will review your application. Only shortlisted candidates will be contacted.\n\nRegards,\n{{app_name}} Team",
                'is_active' => true,
                'available_variables' => [
                    'name',
                    'reference',
                    'role_title',
                    'venture_name',
                    'submitted_at',
                    'app_name',
                ],
            ],
        ];
    }

    public static function defaultTemplateForKey(string $key): ?array
    {
        return Collection::make(self::defaultTemplates())
            ->firstWhere('key', $key);
    }
}
