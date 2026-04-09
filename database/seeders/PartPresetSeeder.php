<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartPresetSeeder extends Seeder
{
    public function run(): void
    {
        // Delete old presets first
        DB::table('part_presets')->truncate();

        $presets = [
            // ===== 1 UNIVERSAL WALL =====
            [
                'name' => 'Wall',
                'type' => 'wall',
                'variant' => 'standard',
                'default_width' => 1,
                'default_height' => 3,
                'default_depth' => 0.2,
                'default_color' => '#6B7280',
                'icon' => 'square',
            ],

            // ===== FLOORS (5 materials) =====
            [
                'name' => 'Tile Floor',
                'type' => 'floor',
                'variant' => 'tile',
                'default_width' => 1,
                'default_height' => 0.05,
                'default_depth' => 1,
                'default_color' => '#FFFFFF',
                'icon' => 'layers',
            ],
            [
                'name' => 'Hardwood Floor',
                'type' => 'floor',
                'variant' => 'hardwood',
                'default_width' => 1,
                'default_height' => 0.05,
                'default_depth' => 1,
                'default_color' => '#92400E',
                'icon' => 'layers',
            ],
            [
                'name' => 'Carpet Floor',
                'type' => 'floor',
                'variant' => 'carpet',
                'default_width' => 1,
                'default_height' => 0.05,
                'default_depth' => 1,
                'default_color' => '#9CA3AF',
                'icon' => 'layers',
            ],
            [
                'name' => 'Concrete Floor',
                'type' => 'floor',
                'variant' => 'concrete',
                'default_width' => 1,
                'default_height' => 0.05,
                'default_depth' => 1,
                'default_color' => '#6B7280',
                'icon' => 'layers',
            ],
            [
                'name' => 'Marble Floor',
                'type' => 'floor',
                'variant' => 'marble',
                'default_width' => 1,
                'default_height' => 0.05,
                'default_depth' => 1,
                'default_color' => '#F3F4F6',
                'icon' => 'layers',
            ],

            // ===== 1 ROOF =====
            [
                'name' => 'Flat Roof',
                'type' => 'roof',
                'variant' => 'flat',
                'default_width' => 4,
                'default_height' => 0.2,
                'default_depth' => 4,
                'default_color' => '#374151',
                'icon' => 'triangle',
            ],

            // ===== 1 DOOR =====
            [
                'name' => 'Door',
                'type' => 'door',
                'variant' => 'single',
                'default_width' => 1,
                'default_height' => 2.4,
                'default_depth' => 0.2,
                'default_color' => '#78350F',
                'icon' => 'door-open',
            ],

            // ===== 1 WINDOW =====
            [
                'name' => 'Window',
                'type' => 'window',
                'variant' => 'single',
                'default_width' => 0.8,
                'default_height' => 0.8,
                'default_depth' => 0.2,
                'default_color' => '#93C5FD',
                'icon' => 'layout-grid',
            ],

            // ===== 1 STAIRS =====
            [
                'name' => 'Stairs',
                'type' => 'stairs',
                'variant' => 'standard',
                'default_width' => 1,
                'default_height' => 2,
                'default_depth' => 2,
                'default_color' => '#F3F4F6',
                'icon' => 'trending-up',
            ],
        ];

        foreach ($presets as $preset) {
            DB::table('part_presets')->insert([
                'name' => $preset['name'],
                'type' => $preset['type'],
                'variant' => $preset['variant'],
                'default_width' => $preset['default_width'],
                'default_height' => $preset['default_height'],
                'default_depth' => $preset['default_depth'],
                'default_color' => $preset['default_color'],
                'icon' => $preset['icon'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
