<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PartPreset;
PartPreset::where('name', 'Outdoor Deck')->update(['variant' => 'deck']);
echo "Updated Outdoor Deck variant.";
