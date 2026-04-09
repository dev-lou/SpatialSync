<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SchemaBuilder;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_members', function (SchemaBuilder $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['admin', 'editor', 'viewer'])->default('viewer');
            $table->timestamps();
            $table->unique(['blueprint_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_members');
    }
};
