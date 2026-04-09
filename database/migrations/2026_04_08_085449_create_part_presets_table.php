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
        Schema::create('part_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // wall, floor, roof, door, window, stairs
            $table->string('variant');
            $table->float('default_width')->default(1);
            $table->float('default_height')->default(1);
            $table->float('default_depth')->default(1);
            $table->string('default_color')->nullable();
            $table->string('icon'); // lucide icon name
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_presets');
    }
};
