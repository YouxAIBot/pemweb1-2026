<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique();
            $table->string('page_name');
            $table->string('kicker')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('side_badge')->nullable();
            $table->string('side_title')->nullable();
            $table->text('side_description')->nullable();
            $table->json('side_points')->nullable();
            $table->string('identifier_label')->nullable();
            $table->string('name_label')->nullable();
            $table->string('email_label')->nullable();
            $table->string('password_label')->nullable();
            $table->string('captcha_label')->nullable();
            $table->string('submit_label')->nullable();
            $table->string('forgot_password_label')->nullable();
            $table->string('register_prompt')->nullable();
            $table->string('register_link_label')->nullable();
            $table->string('login_prompt')->nullable();
            $table->string('login_link_label')->nullable();
            $table->string('back_home_label')->nullable();
            $table->string('success_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_page_settings');
    }
};
