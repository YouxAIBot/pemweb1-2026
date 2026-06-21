<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_language_id')->nullable()->constrained('learning_languages')->nullOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->index(['learning_language_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_attempts');
    }
};
