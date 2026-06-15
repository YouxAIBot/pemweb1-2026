<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_level_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_level_id')->constrained('learning_levels')->cascadeOnDelete();
            $table->string('status')->default('locked');
            $table->unsignedInteger('best_score')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'learning_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_level_progress');
    }
};
