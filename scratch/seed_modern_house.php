<?php

use App\Services\SupabaseClient;
use Illuminate\Contracts\Console\Kernel;

/**
 * Modern House 2026 Preset Seeder
 * Seeds new presets needed to build a modern 2026 architectural house
 * as seen in the pascal/editor reference.
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$supabase = app(SupabaseClient::class);

// New presets to add for modern house building
$newPresets = [

    // === WALLS ===
    [
        'name' => 'Half Glass Wall',
        'type' => 'wall',
        'variant' => 'half_glass',
        'default_width' => 1,
        'default_height' => 1.5,
        'default_depth' => 0.1,
        'default_color' => '#AADDFF',
        'icon' => 'square',
        'is_active' => true,
    ],
    [
        'name' => 'Curtain Wall Panel',
        'type' => 'wall',
        'variant' => 'glass',
        'default_width' => 2,
        'default_height' => 3,
        'default_depth' => 0.08,
        'default_color' => '#CCDDFF',
        'icon' => 'panels-left-bottom',
        'is_active' => true,
    ],

    // === DOORS ===
    [
        'name' => 'Sliding Glass Door',
        'type' => 'door',
        'variant' => 'sliding_glass',
        'default_width' => 1.8,
        'default_height' => 2.4,
        'default_depth' => 0.1,
        'default_color' => '#AADDFF',
        'icon' => 'panel-right-open',
        'is_active' => true,
    ],
    [
        'name' => 'Glass Door',
        'type' => 'door',
        'variant' => 'glass',
        'default_width' => 1,
        'default_height' => 2.4,
        'default_depth' => 0.1,
        'default_color' => '#AADDFF',
        'icon' => 'door-open',
        'is_active' => true,
    ],

    // === WINDOWS ===
    [
        'name' => 'Large Picture Window',
        'type' => 'window',
        'variant' => 'pane',
        'default_width' => 2,
        'default_height' => 1.8,
        'default_depth' => 0.12,
        'default_color' => '#AADDFF',
        'icon' => 'maximize',
        'is_active' => true,
    ],
    [
        'name' => 'Floor-to-Ceiling Window',
        'type' => 'window',
        'variant' => 'pane',
        'default_width' => 1.2,
        'default_height' => 2.8,
        'default_depth' => 0.1,
        'default_color' => '#AADDFF',
        'icon' => 'app-window',
        'is_active' => true,
    ],
    [
        'name' => 'Corner Window',
        'type' => 'window',
        'variant' => 'double_hung',
        'default_width' => 1.5,
        'default_height' => 1.2,
        'default_depth' => 0.12,
        'default_color' => '#AADDFF',
        'icon' => 'app-window',
        'is_active' => true,
    ],

    // === ROOFS ===
    [
        'name' => 'Flat Roof Large',
        'type' => 'roof',
        'variant' => 'flat',
        'default_width' => 6,
        'default_height' => 0.2,
        'default_depth' => 6,
        'default_color' => '#D1D5DB',
        'icon' => 'minus',
        'is_active' => true,
    ],
    [
        'name' => 'Roof Overhang',
        'type' => 'roof',
        'variant' => 'flat',
        'default_width' => 3,
        'default_height' => 0.15,
        'default_depth' => 1,
        'default_color' => '#D1D5DB',
        'icon' => 'triangle',
        'is_active' => true,
    ],

    // === STRUCTURAL ===
    [
        'name' => 'Steel I-Beam',
        'type' => 'structural',
        'variant' => 'beam',
        'default_width' => 4,
        'default_height' => 0.3,
        'default_depth' => 0.2,
        'default_color' => '#94A3B8',
        'icon' => 'minus',
        'is_active' => true,
    ],
    [
        'name' => 'Modern Column',
        'type' => 'structural',
        'variant' => 'column_square',
        'default_width' => 0.3,
        'default_height' => 3,
        'default_depth' => 0.3,
        'default_color' => '#F1F5F9',
        'icon' => 'pillar',
        'is_active' => true,
    ],

    // === FURNITURE (Modern 2026) ===
    [
        'name' => 'Coffee Table',
        'type' => 'furniture',
        'variant' => 'table',
        'default_width' => 1.2,
        'default_height' => 0.45,
        'default_depth' => 0.6,
        'default_color' => '#E2E8F0',
        'icon' => 'square',
        'is_active' => true,
    ],
    [
        'name' => 'Lounge Chair',
        'type' => 'furniture',
        'variant' => 'chair',
        'default_width' => 0.8,
        'default_height' => 0.9,
        'default_depth' => 0.9,
        'default_color' => '#CBD5E1',
        'icon' => 'armchair',
        'is_active' => true,
    ],
    [
        'name' => 'TV Unit',
        'type' => 'furniture',
        'variant' => 'bookshelf',
        'default_width' => 2,
        'default_height' => 0.6,
        'default_depth' => 0.4,
        'default_color' => '#1E293B',
        'icon' => 'tv',
        'is_active' => true,
    ],
    [
        'name' => 'Single Bed',
        'type' => 'furniture',
        'variant' => 'bed',
        'default_width' => 1.2,
        'default_height' => 0.6,
        'default_depth' => 2,
        'default_color' => '#E2E8F0',
        'icon' => 'bed',
        'is_active' => true,
    ],
    [
        'name' => 'L-Shape Sofa',
        'type' => 'furniture',
        'variant' => 'sofa',
        'default_width' => 3,
        'default_height' => 0.9,
        'default_depth' => 1.5,
        'default_color' => '#334155',
        'icon' => 'sofa',
        'is_active' => true,
    ],

    // === FIXTURES ===
    [
        'name' => 'Kitchen Island',
        'type' => 'fixture',
        'variant' => 'kitchen_counter',
        'default_width' => 2,
        'default_height' => 0.9,
        'default_depth' => 1,
        'default_color' => '#E2E8F0',
        'icon' => 'cooking-pot',
        'is_active' => true,
    ],
    [
        'name' => 'Kitchen Counter L',
        'type' => 'fixture',
        'variant' => 'kitchen_counter',
        'default_width' => 3,
        'default_height' => 0.9,
        'default_depth' => 0.6,
        'default_color' => '#E2E8F0',
        'icon' => 'cooking-pot',
        'is_active' => true,
    ],

    // === LANDSCAPE ===
    [
        'name' => 'Outdoor Deck',
        'type' => 'landscape',
        'variant' => 'pool',
        'default_width' => 4,
        'default_height' => 0.1,
        'default_depth' => 3,
        'default_color' => '#A0522D',
        'icon' => 'square',
        'is_active' => true,
    ],
    [
        'name' => 'Pergola',
        'type' => 'landscape',
        'variant' => 'fence',
        'default_width' => 3,
        'default_height' => 2.4,
        'default_depth' => 3,
        'default_color' => '#92400E',
        'icon' => 'grid',
        'is_active' => true,
    ],
    [
        'name' => 'Large Swimming Pool',
        'type' => 'landscape',
        'variant' => 'pool',
        'default_width' => 8,
        'default_height' => 0.3,
        'default_depth' => 4,
        'default_color' => '#BAE6FD',
        'icon' => 'waves',
        'is_active' => true,
    ],
    [
        'name' => 'Round Bush',
        'type' => 'landscape',
        'variant' => 'bush',
        'default_width' => 1.5,
        'default_height' => 1.2,
        'default_depth' => 1.5,
        'default_color' => '#15803D',
        'icon' => 'trees',
        'is_active' => true,
    ],
];

$added = 0;
$skipped = 0;

foreach ($newPresets as $preset) {
    // Check if already exists (same name + type)
    $existing = $supabase->select('part_presets', ['id'], [
        'name' => $preset['name'],
        'type' => $preset['type'],
    ]);

    if (! empty($existing)) {
        echo "  SKIP (exists): {$preset['name']}\n";
        $skipped++;

        continue;
    }

    $result = $supabase->insert('part_presets', $preset);
    if ($result) {
        echo "  + Added: {$preset['name']} [{$preset['type']}/{$preset['variant']}]\n";
        $added++;
    } else {
        echo "  ERROR: Failed to add {$preset['name']}\n";
    }
}

echo "\n=== Done: {$added} added, {$skipped} skipped ===\n";
echo 'Total presets now: '.count($supabase->select('part_presets', ['id'], ['is_active' => 'true']))."\n";
