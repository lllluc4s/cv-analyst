<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Resend API Key
    |--------------------------------------------------------------------------
    |
    | This key is used to authenticate with the Resend API. You can obtain
    | your API key from your Resend dashboard.
    |
    */

    'api_key' => env('RESEND_API_KEY'),
    
    /*
    |--------------------------------------------------------------------------
    | Marketing Configuration
    |--------------------------------------------------------------------------
    |
    | Custom configuration for marketing features
    |
    */
    
    'enabled' => env('RESEND_ENABLED', false),
];
