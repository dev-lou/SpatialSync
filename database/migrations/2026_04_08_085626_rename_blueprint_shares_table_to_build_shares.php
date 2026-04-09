<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('blueprint_shares', 'build_shares');

        // Update foreign key reference from blueprint_id to build_id
        Schema::table('build_shares', function (Blueprint $table) {
            $table->renameColumn('blueprint_id', 'build_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('build_shares', function (Blueprint $table) {
            $table->renameColumn('build_id', 'blueprint_id');
        });

        Schema::rename('build_shares', 'blueprint_shares');
    }
};
