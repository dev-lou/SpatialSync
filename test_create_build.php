<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use App\Services\SupabaseClient;
use Illuminate\Support\Str;

// Test creating a build via Supabase
$supabase = app(SupabaseClient::class);

$testBuild = [
    'id' => Str::uuid(),
    'name' => 'Test Build from API',
    'description' => 'This is a test build created via direct API call',
    'created_by' => '33333333-3333-3333-3333-333333333333', // User ID from earlier tests
    'current_floor' => 1,
    'roof_visible' => true,
    'canvas_json' => json_encode(['version' => '1.0', 'parts' => []]),
];

echo "Attempting to create test build...\n";
$result = $supabase->insert('builds', $testBuild);

if ($result) {
    echo 'SUCCESS: Build created with ID: '.$result['id']."\n";

    // Verify it was created
    $verification = $supabase->select('builds', ['*'], ['id' => $result['id']]);
    if (! empty($verification)) {
        echo "VERIFICATION: Build found in database\n";
        echo 'Name: '.$verification[0]['name']."\n";
        echo 'Description: '.$verification[0]['description']."\n";
    } else {
        echo "ERROR: Build not found after creation\n";
    }
} else {
    echo "FAILED: Build creation returned null/false\n";
    // Let's also try to get any error from the supabase client
    // Since our insert method doesn't return error info directly, let's check if we can debug it
}
