<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$sb = app(App\Services\SupabaseClient::class);

$updates = [
    // Pools — deep blue water
    'Swimming Pool'       => ['default_color' => '#0099CC'],
    'Large Swimming Pool' => ['default_color' => '#0077AA'],
    // Beds — off-white mattress (frame is dark in JS)
    'Double Bed'    => ['default_color' => '#F1F5F9'],
    'Single Bed'    => ['default_color' => '#F8FAFC'],
    // Sofas — mid-grey cushions (arms are dark in JS)
    'Modern Sofa'   => ['default_color' => '#94A3B8'],
    'L-Shape Sofa'  => ['default_color' => '#94A3B8'],
    // Tables/chairs
    'Dining Table'  => ['default_color' => '#D4A97A'],
    'Coffee Table'  => ['default_color' => '#F8FAFC'],
    'Lounge Chair'  => ['default_color' => '#F1F5F9'],
    'Wood Chair'    => ['default_color' => '#D4A97A'],
    // Kitchen
    'Kitchen Counter'   => ['default_color' => '#F1F5F9'],
    'Kitchen Island'    => ['default_color' => '#F8FAFC'],
    'Kitchen Counter L' => ['default_color' => '#F1F5F9'],
];

foreach ($updates as $name => $data) {
    $r = $sb->update('part_presets', $data, ['name' => $name]);
    echo ($r ? '✓' : '✗') . " $name\n";
}
echo "\nDone.\n";
