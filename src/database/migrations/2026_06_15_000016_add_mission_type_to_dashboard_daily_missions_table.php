<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_daily_missions', function (Blueprint $table) {
            if (! Schema::hasColumn('dashboard_daily_missions', 'mission_type')) {
                $table->string('mission_type')->default('questions_answered')->after('title');
            }
        });

        DB::table('dashboard_daily_missions')
            ->where('unit_label', 'menit')
            ->update([
                'mission_type' => 'study_minutes',
            ]);

        DB::table('dashboard_daily_missions')
            ->where('title', 'like', '%level%')
            ->update([
                'mission_type' => 'levels_completed',
            ]);
    }

    public function down(): void
    {
        Schema::table('dashboard_daily_missions', function (Blueprint $table) {
            if (Schema::hasColumn('dashboard_daily_missions', 'mission_type')) {
                $table->dropColumn('mission_type');
            }
        });
    }
};
