<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_daily_mission_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dashboard_daily_mission_id')->constrained('dashboard_daily_missions')->cascadeOnDelete();
            $table->date('mission_date');
            $table->unsignedInteger('progress_value')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'dashboard_daily_mission_id', 'mission_date'], 'user_daily_mission_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_mission_progress');
    }
};
