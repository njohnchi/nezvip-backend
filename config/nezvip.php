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
];
