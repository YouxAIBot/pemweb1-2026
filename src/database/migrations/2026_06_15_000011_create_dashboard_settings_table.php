<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_text')->default('YoLearning');
            $table->string('welcome_text')->default('Selamat datang');
            $table->string('adventure_text')->default('Ayo mulai petualanganmu');
            $table->string('language_title')->default('Pilih bahasa untuk dipelajari');
            $table->string('ability_title')->default('Seberapa jauh Anda memahami bahasa ini?');
            $table->string('dashboard_title')->default('Petualangan belajar kamu');
            $table->string('dashboard_subtitle')->nullable();
            $table->string('part_button_label')->default('Buka Bagian');
            $table->string('theme')->default('discord_dark');
            $table->boolean('animations_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_settings');
    }
};
