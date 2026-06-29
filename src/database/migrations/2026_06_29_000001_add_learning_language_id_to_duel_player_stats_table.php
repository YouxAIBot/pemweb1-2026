<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duel_player_stats', function (Blueprint $table) {
            if (! Schema::hasColumn('duel_player_stats', 'learning_language_id')) {
                $table->foreignId('learning_language_id')->nullable()->after('user_id')->constrained('learning_languages')->nullOnDelete();
            }
        });

        try {
            DB::statement('ALTER TABLE duel_player_stats DROP INDEX duel_player_stats_user_id_unique');
        } catch (\Throwable) {
            // The index may already have been removed on some installations.
        }

        try {
            DB::statement('UPDATE duel_player_stats dps LEFT JOIN user_learning_profiles ulp ON ulp.user_id = dps.user_id SET dps.learning_language_id = ulp.learning_language_id WHERE dps.learning_language_id IS NULL');
        } catch (\Throwable) {
            // Keep migration safe even if old data is incomplete.
        }

        Schema::table('duel_player_stats', function (Blueprint $table) {
            try {
                $table->unique(['user_id', 'learning_language_id'], 'duel_player_stats_user_language_unique');
            } catch (\Throwable) {
                // ignore duplicate attempt
            }
            try {
                $table->index(['learning_language_id', 'rating', 'wins'], 'duel_player_stats_language_rating_index');
            } catch (\Throwable) {
                // ignore duplicate attempt
            }
        });
    }

    public function down(): void
    {
        Schema::table('duel_player_stats', function (Blueprint $table) {
            try {
                $table->dropUnique('duel_player_stats_user_language_unique');
            } catch (\Throwable) {}
            try {
                $table->dropIndex('duel_player_stats_language_rating_index');
            } catch (\Throwable) {}
            if (Schema::hasColumn('duel_player_stats', 'learning_language_id')) {
                $table->dropConstrainedForeignId('learning_language_id');
            }
        });

        try {
            DB::statement('ALTER TABLE duel_player_stats ADD UNIQUE duel_player_stats_user_id_unique (user_id)');
        } catch (\Throwable) {}
    }
};
