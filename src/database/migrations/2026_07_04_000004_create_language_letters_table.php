<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_language_id')->constrained('learning_languages')->cascadeOnDelete();
            $table->string('symbol', 40);
            $table->string('reading')->nullable();
            $table->string('example_word')->nullable();
            $table->string('example_translation')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('audio_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['learning_language_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_letters');
    }
};
