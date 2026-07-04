<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('placement')->default('level_entry');
            $table->string('video_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('target_url')->nullable();
            $table->unsignedInteger('duration_seconds')->default(15);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['placement', 'is_active']);
        });

        Schema::create('ad_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ad_id')->nullable()->constrained('ads')->nullOnDelete();
            $table->string('placement');
            $table->string('context_type')->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->timestamp('shown_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'placement', 'created_at']);
        });

        Schema::table('learning_levels', function (Blueprint $table) {
            if (! Schema::hasColumn('learning_levels', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('passing_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('learning_levels', function (Blueprint $table) {
            if (Schema::hasColumn('learning_levels', 'is_premium')) {
                $table->dropColumn('is_premium');
            }
        });

        Schema::dropIfExists('ad_impressions');
        Schema::dropIfExists('ads');
    }
};
