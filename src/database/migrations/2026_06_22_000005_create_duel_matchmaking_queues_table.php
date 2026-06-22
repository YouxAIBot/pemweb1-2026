<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_matchmaking_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learning_language_id')->nullable()->constrained('learning_languages')->nullOnDelete();
            $table->foreignId('duel_session_id')->nullable()->constrained('duel_sessions')->nullOnDelete();
            $table->string('difficulty')->default('normal');
            $table->string('status')->default('waiting');
            $table->timestamp('matched_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'learning_language_id', 'difficulty', 'created_at'], 'duel_queue_match_index');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_matchmaking_queues');
    }
};
