<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('duel_player_stats')) {
            return;
        }

        if (! Schema::hasColumn('duel_player_stats', 'learning_language_id')) {
            Schema::table('duel_player_stats', function ($table) {
                $table->foreignId('learning_language_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('learning_languages')
                    ->nullOnDelete();
            });
        }

        DB::statement('
            UPDATE duel_player_stats dps
            LEFT JOIN user_learning_profiles ulp ON ulp.user_id = dps.user_id
            SET dps.learning_language_id = ulp.learning_language_id
            WHERE dps.learning_language_id IS NULL
                AND ulp.learning_language_id IS NOT NULL
        ');

        if (! $this->indexExists('duel_player_stats', 'duel_player_stats_user_id_lookup_index')) {
            DB::statement('ALTER TABLE duel_player_stats ADD INDEX duel_player_stats_user_id_lookup_index (user_id)');
        }

        if ($this->indexExists('duel_player_stats', 'duel_player_stats_user_id_unique')) {
            try {
                DB::statement('ALTER TABLE duel_player_stats DROP INDEX duel_player_stats_user_id_unique');
            } catch (\Throwable) {
                DB::statement('ALTER TABLE duel_player_stats DROP FOREIGN KEY duel_player_stats_user_id_foreign');
                DB::statement('ALTER TABLE duel_player_stats DROP INDEX duel_player_stats_user_id_unique');
                DB::statement('ALTER TABLE duel_player_stats ADD CONSTRAINT duel_player_stats_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
            }
        }

        if (! $this->indexExists('duel_player_stats', 'duel_player_stats_user_language_unique')) {
            DB::statement('ALTER TABLE duel_player_stats ADD UNIQUE duel_player_stats_user_language_unique (user_id, learning_language_id)');
        }

        if (! $this->indexExists('duel_player_stats', 'duel_player_stats_language_rating_index')) {
            DB::statement('ALTER TABLE duel_player_stats ADD INDEX duel_player_stats_language_rating_index (learning_language_id, rating, wins)');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('duel_player_stats')) {
            return;
        }

        if ($this->indexExists('duel_player_stats', 'duel_player_stats_user_language_unique')) {
            DB::statement('ALTER TABLE duel_player_stats DROP INDEX duel_player_stats_user_language_unique');
        }

        if ($this->indexExists('duel_player_stats', 'duel_player_stats_language_rating_index')) {
            DB::statement('ALTER TABLE duel_player_stats DROP INDEX duel_player_stats_language_rating_index');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return ! empty(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]));
    }
};
