<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\SupabaseClient;
use Illuminate\Contracts\Console\Kernel;

$supabase = app(SupabaseClient::class);

$parts = $supabase->select('build_parts', ['*'], []);
print_r(array_slice($parts, 0, 1));
