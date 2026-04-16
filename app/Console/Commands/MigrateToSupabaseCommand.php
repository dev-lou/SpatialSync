<?php

/**
 * Data Migration Script
 * Run this to export your existing SQLite data and get SQL to insert into Supabase
 *
 * Usage: php artisan migrate:to-supabase
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrateToSupabaseCommand extends Command
{
    protected $signature = 'migrate:to-supabase';

    protected $description = 'Export SQLite data and generate Supabase INSERT statements';

    public function handle()
    {
        $this->info('Starting data migration from SQLite to Supabase...');

        // Generate SQL for each table
        $this->generateUsersSql();
        $this->generateTeamsSql();
        $this->generateBuildsSql();
        $this->generateBuildPartsSql();

        $this->info('Done! Check database/migrations/supabase_migration_data.sql for the SQL statements.');

        return 0;
    }

    protected function generateUsersSql()
    {
        $users = DB::table('users')->get();

        if ($users->isEmpty()) {
            $this->line('No users to migrate.');

            return;
        }

        $sql = "-- Users data migration\n";
        foreach ($users as $user) {
            $sql .= 'INSERT INTO users (id, name, email, password, is_admin, created_at, updated_at) VALUES (';
            $sql .= "'{$user->id}', ";
            $sql .= "'".addslashes($user->name)."', ";
            $sql .= "'".addslashes($user->email)."', ";
            $sql .= "'".addslashes($user->password)."', ";
            $sql .= ($user->is_admin ? 'true' : 'false').', ';
            $sql .= "'{$user->created_at}', ";
            $sql .= "'{$user->updated_at}');\n";
        }

        File::append('database/migrations/supabase_migration_data.sql', $sql);
        $this->info("Generated SQL for {$users->count()} users.");
    }

    protected function generateTeamsSql()
    {
        $teams = DB::table('teams')->get();

        if ($teams->isEmpty()) {
            $this->line('No teams to migrate.');

            return;
        }

        $sql = "\n-- Teams data migration\n";
        foreach ($teams as $team) {
            $sql .= 'INSERT INTO teams (id, name, created_at, updated_at) VALUES (';
            $sql .= "'{$team->id}', ";
            $sql .= "'".addslashes($team->name)."', ";
            $sql .= "'{$team->created_at}', ";
            $sql .= "'{$team->updated_at}');\n";
        }

        File::append('database/migrations/supabase_migration_data.sql', $sql);
        $this->info("Generated SQL for {$teams->count()} teams.");
    }

    protected function generateBuildsSql()
    {
        $builds = DB::table('builds')->get();

        if ($builds->isEmpty()) {
            $this->line('No builds to migrate.');

            return;
        }

        $sql = "\n-- Builds data migration\n";
        foreach ($builds as $build) {
            $sql .= 'INSERT INTO builds (id, team_id, name, description, canvas_json, created_by, current_floor, roof_visible, created_at, updated_at) VALUES (';
            $sql .= "'{$build->id}', ";
            $sql .= ($build->team_id ? "'{$build->team_id}'" : 'NULL').', ';
            $sql .= "'".addslashes($build->name)."', ";
            $sql .= ($build->description ? "'".addslashes($build->description)."'" : 'NULL').', ';
            $sql .= ($build->canvas_json ? "'".addslashes($build->canvas_json)."'" : 'NULL').', ';
            $sql .= ($build->created_by ? "'{$build->created_by}'" : 'NULL').', ';
            $sql .= "{$build->current_floor}, ";
            $sql .= ($build->roof_visible ? 'true' : 'false').', ';
            $sql .= "'{$build->created_at}', ";
            $sql .= "'{$build->updated_at}');\n";
        }

        File::append('database/migrations/supabase_migration_data.sql', $sql);
        $this->info("Generated SQL for {$builds->count()} builds.");
    }

    protected function generateBuildPartsSql()
    {
        $parts = DB::table('build_parts')->get();

        if ($parts->isEmpty()) {
            $this->line('No build parts to migrate.');

            return;
        }

        $sql = "\n-- Build Parts data migration\n";
        foreach ($parts as $part) {
            $sql .= 'INSERT INTO build_parts (id, build_id, type, variant, position_x, position_y, position_z, width, height, depth, rotation_y, color, color_front, color_back, material, floor_number, z_index, created_at, updated_at) VALUES (';
            $sql .= "'{$part->id}', ";
            $sql .= "'{$part->build_id}', ";
            $sql .= "'{$part->type}', ";
            $sql .= ($part->variant ? "'{$part->variant}'" : 'NULL').', ';
            $sql .= "{$part->position_x}, ";
            $sql .= "{$part->position_y}, ";
            $sql .= "{$part->position_z}, ";
            $sql .= "{$part->width}, ";
            $sql .= "{$part->height}, ";
            $sql .= "{$part->depth}, ";
            $sql .= "{$part->rotation_y}, ";
            $sql .= ($part->color ? "'{$part->color}'" : 'NULL').', ';
            $sql .= ($part->color_front ? "'{$part->color_front}'" : 'NULL').', ';
            $sql .= ($part->color_back ? "'{$part->color_back}'" : 'NULL').', ';
            $sql .= ($part->material ? "'{$part->material}'" : "'default'").', ';
            $sql .= "{$part->floor_number}, ";
            $sql .= "{$part->z_index}, ";
            $sql .= "'{$part->created_at}', ";
            $sql .= "'{$part->updated_at}');\n";
        }

        File::append('database/migrations/supabase_migration_data.sql', $sql);
        $this->info("Generated SQL for {$parts->count()} build parts.");
    }
}
