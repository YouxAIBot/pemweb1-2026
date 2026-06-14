<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained()->cascadeOnDelete();
            $table->string('item_key')->nullable();
            $table->string('label')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->string('accent_text')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('image_path')->nullable();
            $table->longText('icon_svg')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_section_items');
    }
};
