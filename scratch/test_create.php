<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Str;
$supabase = app(\App\Services\SupabaseClient::class);

echo "--- Simulating Build Creation ---\n";

// Mock user ID (must be a UUID existing in your users table, or just a valid UUID if no FK)
$userId = "33333333-3333-3333-3333-333333333333";

$buildData = [
    'id' => Str::uuid()->toString(),
    'team_id' => null,
    'name' => "Manual Test Build",
    'description' => "Created via test script",
    'created_by' => $userId,
    'current_floor' => 1,
    'roof_visible' => true,
    'canvas_json' => json_encode(['version' => '1.0', 'parts' => []]),
];

$url = config('supabase.url') . "/rest/v1/builds";
$headers = [
    'apikey' => config('supabase.service_key'),
    'Authorization' => "Bearer " . config('supabase.service_key'),
    'Content-Type' => 'application/json',
    'Prefer' => 'return=representation',
];

echo "Sending POST to $url...\n";
$response = \Illuminate\Support\Facades\Http::withHeaders($headers)->post($url, $buildData);

if ($response->successful()) {
    echo "SUCCESS! Build created.\n";
    print_r($response->json());
} else {
    echo "FAILED! Error Code: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";
}
