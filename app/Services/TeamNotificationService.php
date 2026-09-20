<?php

namespace App\Services;

use App\Mail\TemplateAcknowledgementMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TeamNotificationService
{
    /**
     * Notify the NezVIP operating inbox that a client intake has been received.
     */
    public function notify(string $subject, string $message): bool
    {
        if (! config('nezvip.team_notification_enabled')) {
            return false;
        }

        $recipient = (string) config('nezvip.team_notification_email');

        if ($recipient === '') {
            return false;
        }

        try {
            Mail::to($recipient)->queue(new TemplateAcknowledgementMail($subject, $message));
        } catch (\Throwable $exception) {
            Log::warning('Team notification dispatch failed.', [
                'recipient_email' => $recipient,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        return true;
    }
}
