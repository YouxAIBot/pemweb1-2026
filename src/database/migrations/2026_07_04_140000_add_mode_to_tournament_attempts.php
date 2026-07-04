<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tournament_attempts')) {
            return;
        }

        Schema::table('tournament_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('tournament_attempts', 'mode')) {
                $table->string('mode')->default('tournament')->after('learning_language_id')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tournament_attempts') || ! Schema::hasColumn('tournament_attempts', 'mode')) {
            return;
        }

        Schema::table('tournament_attempts', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
