<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$supabase = app(\App\Services\SupabaseClient::class);

echo "--- Deduping part_presets ---\n";

$presets = $supabase->select('part_presets', ['*']);
echo "Found " . count($presets) . " total presets.\n";

$seen = [];
$toDelete = [];
$kept = 0;

foreach ($presets as $p) {
    // We dedupe by type and variant
    $key = $p['type'] . '|' . $p['variant'];
    
    if (isset($seen[$key])) {
        $toDelete[] = $p['id'];
    } else {
        $seen[$key] = true;
        $kept++;
    }
}

echo "Will keep $kept unique presets and delete " . count($toDelete) . " duplicates.\n";

foreach ($toDelete as $id) {
    echo "Deleting duplicate: $id\n";
    $success = $supabase->delete('part_presets', ['id' => $id]);
    if (!$success) {
        echo "FAILED to delete $id\n";
    }
}

echo "Cleanup complete.\n";
