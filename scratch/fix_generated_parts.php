<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\SupabaseClient;

$supabase = app(SupabaseClient::class);
$buildId = 'ae26e67a-5fd3-4dfd-add8-a30077f1eb56';

// The API doesn't support bulk updates directly unless using proper endpoint
// So let's fetch all parts for this build and update them one by one.
$parts = $supabase->select('build_parts', ['id'], ['build_id' => $buildId]);

if(empty($parts)) {
    echo "No parts found.";
    exit;
}

$count = 0;
foreach($parts as $part) {
    $supabase->update('build_parts', ['floor_number' => 1], ['id' => $part['id']]);
    $count++;
}

echo "Updated floor numbers for $count parts.\n";
