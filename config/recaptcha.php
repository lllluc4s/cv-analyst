<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA v3 Configuration
    |--------------------------------------------------------------------------
    |
    | This config file is used to setup reCAPTCHA v3 integration.
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),
];
