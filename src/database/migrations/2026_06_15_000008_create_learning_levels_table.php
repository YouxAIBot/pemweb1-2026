<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_part_id')->constrained('learning_parts')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('type')->default('multiple_choice');
            $table->string('short_label')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('xp_reward')->default(10);
            $table->unsignedInteger('passing_score')->default(70);
            $table->unsignedInteger('position_x')->default(50);
            $table->unsignedInteger('position_y')->default(50);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['learning_part_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_levels');
    }
};
