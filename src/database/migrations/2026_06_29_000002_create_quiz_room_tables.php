<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_language_id')->nullable()->constrained('learning_languages')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 12)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft, waiting, playing, finished
            $table->unsignedInteger('current_question_order')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['learning_language_id', 'status']);
            $table->index(['owner_id', 'created_at']);
        });

        Schema::create('quiz_room_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_room_id')->constrained('quiz_rooms')->cascadeOnDelete();
            $table->unsignedInteger('question_order')->default(1);
            $table->text('question_text');
            $table->string('image_path')->nullable();
            $table->unsignedInteger('seconds_limit')->default(20);
            $table->unsignedInteger('points')->default(100);
            $table->timestamps();

            $table->index(['quiz_room_id', 'question_order']);
        });

        Schema::create('quiz_room_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_room_question_id')->constrained('quiz_room_questions')->cascadeOnDelete();
            $table->string('answer_text')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('quiz_room_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_room_id')->constrained('quiz_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedInteger('position')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_room_id', 'user_id']);
            $table->index(['quiz_room_id', 'score']);
        });

        Schema::create('quiz_room_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_room_id')->constrained('quiz_rooms')->cascadeOnDelete();
            $table->foreignId('quiz_room_question_id')->constrained('quiz_room_questions')->cascadeOnDelete();
            $table->foreignId('quiz_room_option_id')->nullable()->constrained('quiz_room_options')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('score_awarded')->default(0);
            $table->unsignedInteger('answer_time_ms')->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_room_question_id', 'user_id'], 'quiz_room_answers_question_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_room_answers');
        Schema::dropIfExists('quiz_room_members');
        Schema::dropIfExists('quiz_room_options');
        Schema::dropIfExists('quiz_room_questions');
        Schema::dropIfExists('quiz_rooms');
    }
};
