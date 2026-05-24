<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\SupabaseClient;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;

$supabase = app(SupabaseClient::class);

echo "Fetching users...\n";
$users = $supabase->select('users', ['id', 'name', 'email', 'is_admin'], []);
if (empty($users)) {
    exit("No users found in Supabase.\n");
}

$adminUser = null;
foreach ($users as $u) {
    if (isset($u['is_admin']) && $u['is_admin'] == 1) {
        $adminUser = $u;
        break;
    }
}

if (! $adminUser) {
    $adminUser = $users[0];
    echo 'No admin user found. Using first user: '.$adminUser['email']."\n";
} else {
    echo 'Found admin user: '.$adminUser['email']."\n";
}

$userId = $adminUser['id'];

// Create build
$buildId = Str::uuid()->toString();
$buildData = [
    'id' => $buildId,
    'name' => 'Modern House Demo',
    'description' => 'A pre-built modern house for demonstration.',
    'created_by' => $userId,
    'current_floor' => 1,
    'roof_visible' => true,
    'canvas_json' => json_encode(['version' => '1.0', 'parts' => []]),
];

echo "Creating build...\n";
$build = $supabase->insert('builds', $buildData);
if (! $build) {
    exit("Failed to create build.\n");
}
echo "Build created: $buildId\n";

// Fetch presets to map names to preset_ids
$presetsData = $supabase->select('part_presets', ['id', 'name', 'type', 'variant', 'default_width', 'default_height', 'default_depth', 'default_color'], ['is_active' => 'true']);
$presetsMap = [];
foreach ($presetsData as $p) {
    $presetsMap[$p['name']] = $p;
}

function createPart($type, $presetName, $x, $y, $z, $rotationY = 0, $w = null, $h = null, $d = null)
{
    global $presetsMap, $buildId, $userId;
    if (! isset($presetsMap[$presetName])) {
        echo "Warning: Preset $presetName not found.\n";

        return null;
    }
    $preset = $presetsMap[$presetName];

    return [
        'id' => Str::uuid()->toString(),
        'build_id' => $buildId,
        'type' => $type,
        'variant' => $preset['variant'],
        'position_x' => $x,
        'position_y' => $y,
        'position_z' => $z,
        'rotation_y' => $rotationY,
        'width' => $w ?? $preset['default_width'],
        'height' => $h ?? $preset['default_height'],
        'depth' => $d ?? $preset['default_depth'],
        'color' => $preset['default_color'],
        'floor_number' => 1,
    ];
}

$parts = [];

// Modern House Layout (Centered around 0,0,0)
// Floor (10x10)
$parts[] = createPart('floor', 'Concrete Floor', 0, 0, 0, 0, 10, 0.05, 10);
// Front Deck (4x1x10)
$parts[] = createPart('landscape', 'Outdoor Deck', 0, 0.05, 6, 0, 8, 0.1, 4);

// Walls (perimeter)
// Back Wall
$parts[] = createPart('wall', 'Wall', 0, 1.5, -4.9, 0, 10, 3, 0.2);
// Left Wall
$parts[] = createPart('wall', 'Wall', -4.9, 1.5, 0, 90, 10, 3, 0.2);
// Right Wall
$parts[] = createPart('wall', 'Wall', 4.9, 1.5, 0, 90, 10, 3, 0.2);
// Front Wall (with door and windows)
// Door
$parts[] = createPart('door', 'Sliding Glass Door', 0, 1.2, 4.9, 0, 1.8, 2.4, 0.1);
// Left front wall
$parts[] = createPart('wall', 'Half Glass Wall', -2.5, 1.5, 4.9, 0, 3, 3, 0.2);
// Right front wall
$parts[] = createPart('wall', 'Half Glass Wall', 2.5, 1.5, 4.9, 0, 3, 3, 0.2);

// Roof
$parts[] = createPart('roof', 'Flat Roof Large', 0, 3.1, 0, 0, 10.4, 0.2, 10.4);

// Furniture
// Living Area
$parts[] = createPart('furniture', 'L-Shape Sofa', -2, 0.45, 1, 90);
$parts[] = createPart('furniture', 'Coffee Table', -0.5, 0.22, 1, 0);
$parts[] = createPart('furniture', 'TV Unit', 3, 0.3, 1, -90);
$parts[] = createPart('furniture', 'Lounge Chair', -1, 0.45, -1, 45);

// Kitchen
$parts[] = createPart('fixture', 'Kitchen Counter L', -3, 0.45, -3, 0);
$parts[] = createPart('fixture', 'Kitchen Island', -1, 0.45, -3, 0);

// Bedroom (partitioned)
$parts[] = createPart('wall', 'Wall', 2.5, 1.5, -1, 0, 5, 3, 0.2);
$parts[] = createPart('door', 'Glass Door', 0.5, 1.2, -1, 0);
$parts[] = createPart('furniture', 'Double Bed', 3, 0.3, -3, 0);

// Landscape
$parts[] = createPart('landscape', 'Pine Tree', -6, 2, -5, 0);
$parts[] = createPart('landscape', 'Round Bush', 6, 0.6, 5, 0);
$parts[] = createPart('landscape', 'Round Bush', -6, 0.6, 5, 0);

$parts = array_filter($parts); // Remove nulls

echo 'Inserting '.count($parts)." parts...\n";
$successCount = 0;
foreach ($parts as $part) {
    $res = $supabase->insert('build_parts', $part);
    if ($res) {
        $successCount++;
    } else {
        echo 'Failed to insert part: '.$part['type']."\n";
    }
}

echo "Done! The Modern House Demo build has been generated with $successCount parts.\n";
