<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_level_id')->constrained('learning_levels')->cascadeOnDelete();
            $table->string('type')->default('multiple_choice');
            $table->string('instruction')->nullable();
            $table->longText('question_text');
            $table->string('audio_path')->nullable();
            $table->string('image_path')->nullable();
            $table->longText('correct_answer')->nullable();
            $table->longText('explanation')->nullable();
            $table->unsignedInteger('points')->default(10);
            $table->unsignedInteger('time_limit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_questions');
    }
};
