<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SchemaBuilder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', function (SchemaBuilder $table) {
            $table->boolean('current_team')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('team_user', function (SchemaBuilder $table) {
            $table->dropColumn('current_team');
        });
    }
};
