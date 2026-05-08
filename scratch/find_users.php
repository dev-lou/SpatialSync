<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\SupabaseClient;
$supabase = app(SupabaseClient::class);

$parts = $supabase->select('build_parts', ['*'], []);
print_r(array_slice($parts, 0, 1));
