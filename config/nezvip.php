<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Team notification
    |--------------------------------------------------------------------------
    |
    | Notify the NezVIP operating inbox whenever a client submission or
    | engagement intake is received through the public web routes.
    |
    */

    'team_notification_enabled' => env('NEZVIP_TEAM_NOTIFICATION_ENABLED', true),

    'team_notification_email' => env('NEZVIP_TEAM_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS', 'info@nezvip.com')),

    /*
    |--------------------------------------------------------------------------
    | Assisted intake
    |--------------------------------------------------------------------------
    |
    | An authorized NezVIP officer may capture the same approved intake fields
    | by phone, video, in person or from a paper form and submit them through
    | the same public route. Records are marked operator-assisted only when the
    | assisted intake key below is supplied by the submitting officer.
    |
    */

    'assisted_intake_key' => env('NEZVIP_ASSISTED_INTAKE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Case continuity
    |--------------------------------------------------------------------------
    |
    | Returning clients verify ownership of an email address through a
    | short-lived magic link before any prior undertaking data is shown.
    | The link points at the public frontend continuation route.
    |
    */

    'frontend_url' => env('NEZVIP_FRONTEND_URL', 'http://localhost:8080'),

    'case_link_ttl_minutes' => (int) env('NEZVIP_CASE_LINK_TTL_MINUTES', 30),

    'case_link_route' => env('NEZVIP_CASE_LINK_ROUTE', '/continue-case'),
];
