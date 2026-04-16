<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$supabase = app(\App\Services\SupabaseClient::class);

echo "--- Testing Supabase Connection ---\n";
if ($supabase->ping()) {
    echo "SUCCESS: Connected to Supabase.\n";
} else {
    echo "ERROR: Could not connect to Supabase. Check your .env credentials.\n";
    exit(1);
}

echo "\n--- Fetching One Build ---\n";
$builds = $supabase->select('builds', ['*']);
if (!empty($builds)) {
    echo "Found " . count($builds) . " builds.\n";
    print_r($builds[0]);
} else {
    echo "No builds found in the 'builds' table.\n";
}

echo "\n--- Fetching One Part Preset ---\n";
$presets = $supabase->select('part_presets', ['*']);
if (!empty($presets)) {
    echo "Found " . count($presets) . " presets.\n";
    print_r($presets[0]);
} else {
    echo "No presets found in 'part_presets'.\n";
}
