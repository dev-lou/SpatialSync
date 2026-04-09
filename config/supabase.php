<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supabase Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Supabase connection details here. These values will be
    | used to connect to your Supabase project for database operations
    | and real-time features.
    |
    */

    'url' => env('SUPABASE_URL', ''),
    'service_key' => env('SUPABASE_SERVICE_KEY', ''),
    'anon_key' => env('SUPABASE_ANON_KEY', ''),
];
