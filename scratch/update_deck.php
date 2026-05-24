<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\PartPreset;
use Illuminate\Contracts\Console\Kernel;

PartPreset::where('name', 'Outdoor Deck')->update(['variant' => 'deck']);
echo 'Updated Outdoor Deck variant.';
