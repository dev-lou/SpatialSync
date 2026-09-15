<?php

namespace Database\Seeders;

use App\Models\Build;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Local development accounts only. These are created with factory-random
        // passwords and are not meant to be run against the demo instance — use
        // the app's register flow for accounts real people sign in with.
        $admin = User::factory()->create([
            'name' => 'Local Admin',
            'email' => 'admin@spatialsync.test',
            'is_admin' => true,
        ]);

        // Create demo user (editor)
        $user = User::factory()->create([
            'name' => 'Local Editor',
            'email' => 'editor@spatialsync.test',
            'is_admin' => false,
        ]);

        // Create a default team
        $team = Team::create([
            'name' => 'SpatialSync Team',
            'owner_id' => $admin->id,
        ]);

        // Add users to team
        $team->members()->attach($admin->id, ['role' => 'owner']);
        $team->members()->attach($user->id, ['role' => 'member']);

        // Update current team
        $admin->update(['current_team_id' => $team->id]);
        $user->update(['current_team_id' => $team->id]);

        // Create sample builds for demo user
        $builds = [
            [
                'name' => 'Modern House',
                'description' => 'Contemporary 2-story home with open floor plan',
            ],
            [
                'name' => 'Beach Villa',
                'description' => 'Luxury beachfront property with multiple floors',
            ],
            [
                'name' => 'Cozy Cottage',
                'description' => 'Rustic 1-story home with garden',
            ],
        ];

        foreach ($builds as $buildData) {
            $build = Build::create([
                'team_id' => $team->id,
                'name' => $buildData['name'],
                'description' => $buildData['description'],
                'created_by' => $user->id,
                'current_floor' => 1,
                'roof_visible' => true,
                'canvas_json' => [
                    'version' => '1.0',
                    'objects' => [],
                ],
            ]);

            // Add creator as editor
            $build->members()->attach($user->id, ['role' => 'editor']);
        }

        // Run part presets seeder
        $this->call([
            PartPresetSeeder::class,
        ]);
    }
}
