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

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
<<<<<<< HEAD
        'http://localhost:5173', 
        'http://localhost:5174', 
        'http://localhost:3000', 
        'http://127.0.0.1:5173', 
        'http://127.0.0.1:5174',
        'https://yazidtest.my.id', // Added inside the array
    ],
    
=======
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'https://yazidtest.my.id',
        'https://www.yazidtest.my.id',
        'http://yazidtest.my.id',
        'http://www.yazidtest.my.id',
    ],

>>>>>>> c821d0f9ce6aef283b6402d7687a51f5fd8b56ae
    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true,

];
