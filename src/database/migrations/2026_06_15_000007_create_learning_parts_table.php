<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_language_id')->constrained('learning_languages')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('level_number')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['learning_language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_parts');
    }
};
