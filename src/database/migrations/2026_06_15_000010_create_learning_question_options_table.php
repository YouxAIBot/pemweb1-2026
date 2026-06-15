<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_question_id')->constrained('learning_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->string('audio_path')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_question_options');
    }
};
