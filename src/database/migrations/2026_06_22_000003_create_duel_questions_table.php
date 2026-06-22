<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_session_id')->constrained('duel_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('question_order');
            $table->string('question_type')->default('multiple_choice');
            $table->string('prompt')->nullable();
            $table->text('question_text');
            $table->json('options');
            $table->string('correct_answer');
            $table->text('explanation')->nullable();
            $table->string('difficulty')->default('normal');
            $table->string('source')->default('local_generator');
            $table->timestamps();

            $table->unique(['duel_session_id', 'question_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_questions');
    }
};
