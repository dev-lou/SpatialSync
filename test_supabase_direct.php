<?php

use App\Services\SupabaseClient;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the Supabase client directly
$supabase = app(SupabaseClient::class);

echo "Testing Supabase Connection...\n";
$pingResult = $supabase->ping();
echo 'Ping Result: '.($pingResult ? 'SUCCESS' : 'FAILED')."\n";

if ($pingResult) {
    echo "Testing Builds Table Access...\n";
    $builds = $supabase->select('builds', ['id', 'name'], []);
    echo 'Builds Retrieved: '.count($builds)."\n";

    if (! empty($builds)) {
        echo 'First Build: '.$builds[0]['name']."\n";
    }

    echo "Testing Users Table Access...\n";
    $users = $supabase->select('users', ['id', 'email', 'is_admin'], []);
    echo 'Users Retrieved: '.count($users)."\n";

    if (! empty($users)) {
        echo 'First User: '.$users[0]['email'].' (Admin: '.($users[0]['is_admin'] ? 'Yes' : 'No').")\n";
    }
} else {
    echo "Connection failed - cannot proceed with further tests\n";
}
