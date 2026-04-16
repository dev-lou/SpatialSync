<?php

use App\Services\SupabaseClient;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the Supabase client directly
$supabase = app(SupabaseClient::class);

// Check tables structure
$tables = ['users', 'builds', 'build_parts', 'build_members', 'part_presets', 'build_shares', 'build_messages', 'teams'];

foreach ($tables as $table) {
    echo "\nChecking table: $table\n";
    try {
        // Get first row to see structure
        $result = $supabase->select($table, ['*'], [], 1);
        if (! empty($result)) {
            echo 'Columns: '.implode(', ', array_keys($result[0]))."\n";
            echo 'Sample data: '.json_encode($result[0])."\n";
        } else {
            echo "Table exists but is empty or error occurred\n";
        }
    } catch (Exception $e) {
        echo 'Error accessing table: '.$e->getMessage()."\n";
    }
}
