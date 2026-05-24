<?php

use App\Services\SupabaseClient;
use Illuminate\Contracts\Console\Kernel;

/**
 * Fix curtain wall and half glass wall depth so they raycast reliably
 * and update any too-thin wall presets.
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$supabase = app(SupabaseClient::class);

// Get all presets that are wall type with very thin depth
$presets = $supabase->select('part_presets', ['*'], ['type' => 'wall']);

$fixed = 0;
foreach ($presets as $preset) {
    $updates = [];

    // Curtain wall / glass walls: bump depth to 0.15 for reliable raycasting
    if (($preset['variant'] === 'glass' || $preset['variant'] === 'half_glass') && $preset['default_depth'] < 0.12) {
        $updates['default_depth'] = 0.15;
        echo "  Fix depth: {$preset['name']} ({$preset['default_depth']} → 0.15)\n";
    }

    if (! empty($updates)) {
        $supabase->update('part_presets', $updates, ['id' => $preset['id']]);
        $fixed++;
    }
}

echo "\nFixed $fixed presets.\n";
