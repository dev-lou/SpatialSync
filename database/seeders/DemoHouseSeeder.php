<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates one finished house so the editor — and any demo — opens on a real
 * design instead of an empty grid.
 *
 * The build is owned by the first registered user, or by the account named in
 * DEMO_OWNER_EMAIL when that variable is set:
 *
 *   php artisan db:seed --class=DemoHouseSeeder
 *
 * Safe to run twice: it replaces its own build rather than adding a duplicate.
 */
class DemoHouseSeeder extends Seeder
{
    private const BUILD_NAME = 'Demo House — Riverside Cottage';

    public function run(): void
    {
        $ownerEmail = (string) (env('DEMO_OWNER_EMAIL') ?? '');
        $owner = $ownerEmail !== ''
            ? DB::table('users')->where('email', $ownerEmail)->first()
            : DB::table('users')->orderBy('created_at')->first();

        if ($owner === null) {
            $this->command?->warn('No user found — register an account in the app first, or set DEMO_OWNER_EMAIL.');

            return;
        }

        $ownerId = $owner->id;

        // Replace any previous copy of this demo build.
        $existing = DB::table('builds')->where('name', self::BUILD_NAME)->where('created_by', $ownerId)->pluck('id');
        if ($existing->isNotEmpty()) {
            DB::table('build_parts')->whereIn('build_id', $existing->all())->delete();
            DB::table('builds')->whereIn('id', $existing->all())->delete();
        }

        $buildId = Str::uuid()->toString();
        $now = now();

        DB::table('builds')->insert([
            'id' => $buildId,
            'team_id' => null,
            'name' => self::BUILD_NAME,
            'description' => 'An 8m x 6m two-room cottage: floor, four walls, a partition, a door, a window and a flat roof.',
            'canvas_json' => json_encode(['version' => '1.0', 'parts' => []]),
            'created_by' => $ownerId,
            'current_floor' => 1,
            'roof_visible' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Coordinates are metres. Y is the centre of the part, so a 3m wall sits
        // at y = 1.5 and the floor slab at y = 0.025.
        $parts = [
            // Foundation slab
            ['floor', 'tile', 4.0, 0.025, 3.0, 8.0, 0.05, 6.0, 0, '#FFFFFF'],
            // Outer walls (8m wide faces, then 6m side faces rotated 90°)
            ['wall', 'standard', 4.0, 1.5, 0.0, 8.0, 3.0, 0.2, 0, '#6B7280'],
            ['wall', 'standard', 4.0, 1.5, 6.0, 8.0, 3.0, 0.2, 0, '#6B7280'],
            ['wall', 'standard', 0.0, 1.5, 3.0, 6.0, 3.0, 0.2, 90, '#6B7280'],
            ['wall', 'standard', 8.0, 1.5, 3.0, 6.0, 3.0, 0.2, 90, '#6B7280'],
            // Interior partition splitting the plan in two
            ['wall', 'standard', 4.5, 1.5, 1.5, 3.0, 3.0, 0.2, 90, '#9CA3AF'],
            // Door on the front wall, window on the back wall
            ['door', 'single', 2.0, 1.2, 0.0, 1.0, 2.4, 0.2, 0, '#78350F'],
            ['window', 'single', 6.0, 1.5, 6.0, 1.2, 1.2, 0.2, 0, '#93C5FD'],
            // Flat roof, just above the 3m walls
            ['roof', 'flat', 4.0, 3.1, 3.0, 8.0, 0.2, 6.0, 0, '#374151'],
        ];

        $rows = [];
        foreach ($parts as $index => [$type, $variant, $x, $y, $z, $width, $height, $depth, $rotation, $color]) {
            $rows[] = [
                'id' => Str::uuid()->toString(),
                'build_id' => $buildId,
                'type' => $type,
                'variant' => $variant,
                'position_x' => $x,
                'position_y' => $y,
                'position_z' => $z,
                'width' => $width,
                'height' => $height,
                'depth' => $depth,
                'rotation_y' => $rotation,
                'color' => $color,
                'material' => 'default',
                'floor_number' => 1,
                'z_index' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('build_parts')->insert($rows);

        $this->command?->info(sprintf(
            'Demo house created for %s with %d parts. Open /builds/%s to view it.',
            $owner->name,
            count($rows),
            $buildId
        ));
    }
}
