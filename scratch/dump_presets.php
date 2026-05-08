<?php
/**
 * Dump all presets with their types, dimensions, colors, and variants.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$sb = app(App\Services\SupabaseClient::class);

$presets = $sb->select('part_presets', ['*'], ['is_active' => true]);

// Group by type
$byType = [];
foreach ($presets as $p) {
    $byType[$p['type']][] = $p;
}

foreach ($byType as $type => $items) {
    echo "\n=== {$type} (" . count($items) . " presets) ===\n";
    foreach ($items as $p) {
        echo "  {$p['name']}: w={$p['default_width']} h={$p['default_height']} d={$p['default_depth']} color={$p['default_color']} variant={$p['variant']}\n";
    }
}
