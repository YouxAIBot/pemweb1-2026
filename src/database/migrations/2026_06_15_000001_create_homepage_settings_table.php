<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('YoLearning');
            $table->string('brand_text')->default('YoLearning');
            $table->string('brand_initial', 5)->default('Y');
            $table->string('brand_logo_path')->nullable();
            $table->string('meta_title')->default('YoLearning - Belajar Bahasa Interaktif');
            $table->text('meta_description')->nullable();
            $table->string('footer_left')->nullable();
            $table->string('footer_right')->nullable();
            $table->boolean('cursor_glow_enabled')->default(true);
            $table->unsignedTinyInteger('cursor_glow_size')->default(18);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
