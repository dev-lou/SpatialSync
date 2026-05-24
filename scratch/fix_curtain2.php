<?php

use App\Services\SupabaseClient;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$sb = app(SupabaseClient::class);

// Fix curtain wall: width=1 so it matches 1 grid cell like all other walls
$r = $sb->update('part_presets', ['default_width' => 1], ['name' => 'Curtain Wall Panel']);
echo $r ? "Curtain Wall Panel → width=1\n" : "FAILED\n";

// Verify all wall presets
$walls = $sb->select('part_presets', ['name', 'default_width', 'default_depth', 'variant'], ['type' => 'wall']);
echo "\nAll wall presets:\n";
foreach ($walls as $w) {
    echo "  {$w['name']}: w={$w['default_width']} d={$w['default_depth']} v={$w['variant']}\n";
}
