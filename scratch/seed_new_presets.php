<?php

use App\Services\SupabaseClient;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$supabase = app(SupabaseClient::class);

$newPresets = [
    // Structural
    ['name' => 'Round Column', 'type' => 'structural', 'variant' => 'column_round', 'default_width' => 0.5, 'default_height' => 3, 'default_depth' => 0.5, 'default_color' => '#E5E7EB', 'is_active' => 'true'],
    ['name' => 'Square Column', 'type' => 'structural', 'variant' => 'column_square', 'default_width' => 0.5, 'default_height' => 3, 'default_depth' => 0.5, 'default_color' => '#E5E7EB', 'is_active' => 'true'],
    ['name' => 'Concrete Beam', 'type' => 'structural', 'variant' => 'beam', 'default_width' => 3, 'default_height' => 0.4, 'default_depth' => 0.4, 'default_color' => '#E5E7EB', 'is_active' => 'true'],
    ['name' => 'Arch Pillar', 'type' => 'structural', 'variant' => 'arch', 'default_width' => 3, 'default_height' => 3, 'default_depth' => 0.5, 'default_color' => '#E5E7EB', 'is_active' => 'true'],

    // Furniture
    ['name' => 'Dining Table', 'type' => 'furniture', 'variant' => 'table', 'default_width' => 2, 'default_height' => 1, 'default_depth' => 1, 'default_color' => '#8B5A2B', 'is_active' => 'true'],
    ['name' => 'Wood Chair', 'type' => 'furniture', 'variant' => 'chair', 'default_width' => 0.5, 'default_height' => 1, 'default_depth' => 0.5, 'default_color' => '#8B5A2B', 'is_active' => 'true'],
    ['name' => 'Modern Sofa', 'type' => 'furniture', 'variant' => 'sofa', 'default_width' => 2.5, 'default_height' => 0.8, 'default_depth' => 1, 'default_color' => '#4B5563', 'is_active' => 'true'],
    ['name' => 'Double Bed', 'type' => 'furniture', 'variant' => 'bed', 'default_width' => 2, 'default_height' => 0.8, 'default_depth' => 2, 'default_color' => '#D1D5DB', 'is_active' => 'true'],
    ['name' => 'Bookshelf', 'type' => 'furniture', 'variant' => 'bookshelf', 'default_width' => 1.5, 'default_height' => 2, 'default_depth' => 0.4, 'default_color' => '#5C4033', 'is_active' => 'true'],

    // Fixtures
    ['name' => 'Toilet', 'type' => 'fixture', 'variant' => 'toilet', 'default_width' => 0.5, 'default_height' => 1, 'default_depth' => 0.6, 'default_color' => '#FFFFFF', 'is_active' => 'true'],
    ['name' => 'Bathroom Sink', 'type' => 'fixture', 'variant' => 'sink', 'default_width' => 0.8, 'default_height' => 0.8, 'default_depth' => 0.5, 'default_color' => '#FFFFFF', 'is_active' => 'true'],
    ['name' => 'Kitchen Counter', 'type' => 'fixture', 'variant' => 'kitchen_counter', 'default_width' => 2, 'default_height' => 0.9, 'default_depth' => 0.6, 'default_color' => '#9CA3AF', 'is_active' => 'true'],
    ['name' => 'Bathtub', 'type' => 'fixture', 'variant' => 'bathtub', 'default_width' => 2, 'default_height' => 0.6, 'default_depth' => 1, 'default_color' => '#FFFFFF', 'is_active' => 'true'],

    // Landscape
    ['name' => 'Pine Tree', 'type' => 'landscape', 'variant' => 'tree', 'default_width' => 2, 'default_height' => 4, 'default_depth' => 2, 'default_color' => '#2E8B57', 'is_active' => 'true'],
    ['name' => 'Swimming Pool', 'type' => 'landscape', 'variant' => 'pool', 'default_width' => 5, 'default_height' => 0.2, 'default_depth' => 3, 'default_color' => '#E0F6FF', 'is_active' => 'true'],
    ['name' => 'Wood Fence', 'type' => 'landscape', 'variant' => 'fence', 'default_width' => 2, 'default_height' => 1.5, 'default_depth' => 0.2, 'default_color' => '#A0522D', 'is_active' => 'true'],
    ['name' => 'Garden Bush', 'type' => 'landscape', 'variant' => 'bush', 'default_width' => 1, 'default_height' => 1, 'default_depth' => 1, 'default_color' => '#228B22', 'is_active' => 'true'],

    // Updated Existing Variants
    ['name' => 'Half Wall', 'type' => 'wall', 'variant' => 'half', 'default_width' => 1, 'default_height' => 3, 'default_depth' => 0.2, 'default_color' => '#E5E7EB', 'is_active' => 'true'],
    ['name' => 'Glass Wall', 'type' => 'wall', 'variant' => 'glass', 'default_width' => 1, 'default_height' => 3, 'default_depth' => 0.1, 'default_color' => '#AADDFF', 'is_active' => 'true'],

    ['name' => 'Double Door', 'type' => 'door', 'variant' => 'double', 'default_width' => 2, 'default_height' => 2.2, 'default_depth' => 0.2, 'default_color' => '#8B5A2B', 'is_active' => 'true'],
    ['name' => 'Arch Door', 'type' => 'door', 'variant' => 'arch', 'default_width' => 1.2, 'default_height' => 2.4, 'default_depth' => 0.2, 'default_color' => '#5C4033', 'is_active' => 'true'],

    ['name' => 'Double Hung Window', 'type' => 'window', 'variant' => 'double_hung', 'default_width' => 1, 'default_height' => 1.5, 'default_depth' => 0.2, 'default_color' => '#FFFFFF', 'is_active' => 'true'],
    ['name' => 'Multi-Pane Window', 'type' => 'window', 'variant' => 'pane', 'default_width' => 1.5, 'default_height' => 1.5, 'default_depth' => 0.2, 'default_color' => '#FFFFFF', 'is_active' => 'true'],

    ['name' => 'Hip Roof', 'type' => 'roof', 'variant' => 'hip', 'default_width' => 1, 'default_height' => 2, 'default_depth' => 1, 'default_color' => '#4B5563', 'is_active' => 'true'],
    ['name' => 'Shed Roof', 'type' => 'roof', 'variant' => 'shed', 'default_width' => 1, 'default_height' => 1.5, 'default_depth' => 1, 'default_color' => '#4B5563', 'is_active' => 'true'],
];

echo "Starting seed...\n";
$successCount = 0;
foreach ($newPresets as $preset) {
    $preset['id'] = Str::uuid()->toString();
    try {
        $result = $supabase->insert('part_presets', $preset);
        if ($result) {
            echo "Inserted: {$preset['name']}\n";
            $successCount++;
        } else {
            echo "Failed to insert {$preset['name']} - check laravel log for Supabase error.\n";
        }
    } catch (Exception $e) {
        echo "Failed to insert {$preset['name']}: ".$e->getMessage()."\n";
    }
}

echo "Completed! Inserted $successCount presets.\n";
