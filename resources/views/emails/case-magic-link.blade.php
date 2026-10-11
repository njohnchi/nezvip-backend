<div style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <p>Hello,</p>

    <p>Use the secure link below to continue your NezVIP undertaking. The link confirms you own this email address before any prior information is shown.</p>

    <p>
        <a href="{{ $magicLink }}" style="display: inline-block; padding: 12px 20px; background: #111827; color: #ffffff; text-decoration: none; border-radius: 4px;">
            Continue my undertaking
        </a>
    </p>

    <p>This link expires in {{ $expiresInMinutes }} minutes and can be used once.</p>

    <p>If you did not request this, you can safely ignore this email.</p>

    <p style="color: #6b7280; font-size: 13px;">{{ $magicLink }}</p>
</div>
