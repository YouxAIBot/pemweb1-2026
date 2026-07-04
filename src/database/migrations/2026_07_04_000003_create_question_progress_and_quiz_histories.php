<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_question_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_question_id')->constrained('learning_questions')->cascadeOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->string('selected_answer')->nullable();
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'learning_question_id']);
        });

        Schema::create('quiz_room_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_room_id')->nullable()->constrained('quiz_rooms')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('room_code', 12);
            $table->string('room_title');
            $table->unsignedInteger('final_position')->nullable();
            $table->unsignedInteger('final_score')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_room_id', 'user_id']);
            $table->index(['user_id', 'played_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_room_histories');
        Schema::dropIfExists('user_question_progress');
    }
};
