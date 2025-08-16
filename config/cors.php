<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'auth/*', 'companies/*', 'files/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        // Development URLs
        'http://localhost:5174', 
        'http://localhost:5175', 
        'http://localhost:5173',
        'http://localhost:5176',
        'http://localhost:5179',
        'http://localhost:3000',
        'http://127.0.0.1:5174', 
        'http://127.0.0.1:5175', 
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5176',
        'http://127.0.0.1:5179',
        
        // External services
        'https://github.com',
        'https://5615-177-24-17-25.ngrok-free.app',
        
        // Dynamic URLs from environment variables
        env('APP_URL'), // Backend URL
        env('APP_FRONTEND_URL'), // Frontend URL
        env('FRONTEND_URL'), // Alternative Frontend URL
        env('PRODUCTION_API_URL'), // Production API URL
        env('PRODUCTION_FRONTEND_URL'), // Production Frontend URL
        
        // Legacy support - to be removed in future versions
        'http://' . env('PRODUCTION_DOMAIN', 'lucas-cv.iwork.pt'),
        'https://' . env('PRODUCTION_DOMAIN', 'lucas-cv.iwork.pt'),
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'Origin',
        'X-Auth-Token',
        'X-API-Key',
        'ngrok-skip-browser-warning'
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
