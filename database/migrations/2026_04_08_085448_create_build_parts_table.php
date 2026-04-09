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
        Schema::create('build_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained()->onDelete('cascade');
            $table->string('type'); // wall, floor, roof, door, window, stairs
            $table->string('variant'); // specific variant name
            $table->float('position_x')->default(0);
            $table->float('position_y')->default(0);
            $table->float('position_z')->default(0);
            $table->float('width')->default(1);
            $table->float('height')->default(1);
            $table->float('depth')->default(1);
            $table->integer('rotation_y')->default(0);
            $table->string('color')->nullable();
            $table->integer('floor_number')->default(1);
            $table->integer('z_index')->default(0);
            $table->timestamps();

            $table->index(['build_id', 'floor_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('build_parts');
    }
};
