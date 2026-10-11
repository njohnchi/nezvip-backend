<?php

namespace App\Services;

use App\Mail\CaseMagicLinkMail;
use App\Models\CaseVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CaseVerificationService
{
    /**
     * Issue a short-lived magic link, invalidating any outstanding links for the email.
     */
    public function issue(string $email): void
    {
        $email = strtolower(trim($email));

        CaseVerification::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $token = Str::random(64);
        $ttl = (int) config('nezvip.case_link_ttl_minutes');

        CaseVerification::create([
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes($ttl),
        ]);

        Mail::to($email)->queue(new CaseMagicLinkMail($this->linkFor($token), $ttl));
    }

    /**
     * Resolve a usable verification from a raw magic-link token.
     */
    public function resolve(string $token): ?CaseVerification
    {
        if ($token === '') {
            return null;
        }

        $verification = CaseVerification::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();

        return $verification && $verification->isUsable() ? $verification : null;
    }

    private function linkFor(string $token): string
    {
        return rtrim((string) config('nezvip.frontend_url'), '/')
            .config('nezvip.case_link_route')
            .'?token='.$token;
    }
}
