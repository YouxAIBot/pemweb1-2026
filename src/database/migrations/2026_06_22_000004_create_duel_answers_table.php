<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_session_id')->constrained('duel_sessions')->cascadeOnDelete();
            $table->foreignId('duel_question_id')->constrained('duel_questions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('selected_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('score_awarded')->default(0);
            $table->unsignedInteger('answer_time_ms')->default(10000);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['duel_session_id', 'duel_question_id', 'user_id'], 'duel_answer_unique');
            $table->index(['duel_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_answers');
    }
};
