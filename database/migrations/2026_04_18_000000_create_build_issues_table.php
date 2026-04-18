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
        Schema::create('build_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('build_id')->constrained('builds')->onDelete('cascade');
            $table->foreignUuid('part_id')->nullable()->constrained('build_parts')->onDelete('set null');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->float('position_x')->nullable();
            $table->float('position_y')->nullable();
            $table->float('position_z')->nullable();
            $table->timestamps();

            $table->index(['build_id', 'status']);
            $table->index(['build_id', 'part_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('build_issues');
    }
};
