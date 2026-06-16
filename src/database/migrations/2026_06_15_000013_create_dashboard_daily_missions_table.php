<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_daily_missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('mission_type')->default('questions_answered');
            $table->unsignedInteger('target')->default(1);
            $table->unsignedInteger('default_progress')->default(0);
            $table->string('unit_label')->default('soal');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_daily_missions');
    }
};
