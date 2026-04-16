<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$supabase = app(\App\Services\SupabaseClient::class);

echo "--- Testing Supabase Delete ---\n";

// Use a known existing build ID from previous test results
$buildId = "11111111-1111-1111-1111-000000000001"; 

$url = config('supabase.url') . "/rest/v1/builds?id=eq." . $buildId;
$headers = [
    'apikey' => config('supabase.service_key'),
    'Authorization' => "Bearer " . config('supabase.service_key'),
    'Content-Type' => 'application/json',
];

echo "Sending DELETE to $url...\n";
$response = \Illuminate\Support\Facades\Http::withHeaders($headers)->delete($url);

if ($response->successful()) {
    echo "SUCCESS! Delete operation returned code " . $response->status() . "\n";
} else {
    echo "FAILED! Error Code: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";
}
