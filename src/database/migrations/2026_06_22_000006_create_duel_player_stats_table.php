<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('rating')->default(1000);
            $table->string('rank_label')->default('Bronze');
            $table->unsignedInteger('matches')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->unsignedInteger('draws')->default(0);
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedInteger('best_score')->default(0);
            $table->timestamps();

            $table->index(['rating', 'wins']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_player_stats');
    }
};
