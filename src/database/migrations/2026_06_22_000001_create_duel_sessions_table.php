<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('learning_language_id')->nullable()->constrained('learning_languages')->nullOnDelete();
            $table->foreignId('player_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('player_two_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('difficulty')->default('normal');
            $table->unsignedTinyInteger('question_count')->default(10);
            $table->unsignedTinyInteger('seconds_per_question')->default(10);
            $table->string('status')->default('preparing');
            $table->json('settings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['learning_language_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_sessions');
    }
};
