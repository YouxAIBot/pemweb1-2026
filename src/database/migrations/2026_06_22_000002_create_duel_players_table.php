<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_session_id')->constrained('duel_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0);
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->unsignedTinyInteger('wrong_count')->default(0);
            $table->unsignedInteger('total_time_ms')->default(0);
            $table->string('result')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['duel_session_id', 'user_id']);
            $table->index(['user_id', 'result']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_players');
    }
};
