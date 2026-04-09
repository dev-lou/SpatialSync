<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('build_parts', function (Blueprint $table) {
            $table->string('color_front')->nullable()->after('color');
            $table->string('color_back')->nullable()->after('color_front');
            $table->string('material')->default('default')->after('color_back');
        });
    }

    public function down(): void
    {
        Schema::table('build_parts', function (Blueprint $table) {
            $table->dropColumn(['color_front', 'color_back', 'material']);
        });
    }
};
