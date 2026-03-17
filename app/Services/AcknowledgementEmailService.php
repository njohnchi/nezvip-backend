<?php

namespace App\Services;

use App\Mail\TemplateAcknowledgementMail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AcknowledgementEmailService
{
    /**
     * @param  array<string, mixed>  $variables
     */
    public function send(string $templateKey, string $recipientEmail, array $variables): bool
    {
        if ($recipientEmail === '') {
            return false;
        }

        EmailTemplate::ensureDefaults();

        $template = EmailTemplate::query()
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return false;
        }

        $subject = $this->replaceVariables($template->subject, $variables);
        $body = $this->replaceVariables($template->body, $variables);

        try {
            Mail::to($recipientEmail)->queue(new TemplateAcknowledgementMail($subject, $body));
        } catch (\Throwable $exception) {
            Log::warning('Acknowledgement email dispatch failed.', [
                'template_key' => $templateKey,
                'recipient_email' => $recipientEmail,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $token = '{{'.$key.'}}';
            $content = str_replace($token, (string) $value, $content);
        }

        return preg_replace('/{{\s*[a-zA-Z0-9_]+\s*}}/', '', $content) ?? $content;
    }
}
