<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$supabase = app(App\Services\SupabaseClient::class);
$presets = $supabase->select('part_presets', ['*'], ['is_active' => 'true']);
echo "Total: " . count($presets) . "\n\n";

$byType = [];
foreach ($presets as $p) {
    $byType[$p['type']][] = $p;
}

foreach ($byType as $type => $items) {
    echo "=== {$type} (" . count($items) . ") ===\n";
    foreach ($items as $p) {
        echo "  {$p['name']} | variant={$p['variant']} | {$p['default_width']}x{$p['default_height']}x{$p['default_depth']} | color={$p['default_color']}\n";
    }
    echo "\n";
}
