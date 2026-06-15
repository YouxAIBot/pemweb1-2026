<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_learning_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('learning_language_id')->nullable()->constrained('learning_languages')->nullOnDelete();
            $table->foreignId('current_part_id')->nullable()->constrained('learning_parts')->nullOnDelete();
            $table->foreignId('current_level_id')->nullable()->constrained('learning_levels')->nullOnDelete();
            $table->string('ability_level')->nullable();
            $table->unsignedInteger('start_level_number')->default(1);
            $table->unsignedInteger('start_part_number')->default(1);
            $table->unsignedInteger('total_xp')->default(0);
            $table->unsignedInteger('streak')->default(0);
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_learning_profiles');
    }
};
