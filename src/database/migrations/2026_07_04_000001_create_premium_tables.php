<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedInteger('duration_days')->default(30);
            $table->json('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('premium_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('premium_package_id')->nullable()->constrained('premium_packages')->nullOnDelete();
            $table->string('payment_code')->unique();
            $table->string('payment_method')->default('manual_bank_transfer');
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('payment_proof')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('note')->nullable();
            $table->string('gateway')->nullable();
            $table->string('gateway_order_id')->nullable()->unique();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_status')->nullable();
            $table->json('gateway_response')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('snap_redirect_url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'payment_status']);
            $table->index(['payment_status', 'created_at']);
        });

        Schema::create('user_premiums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('premium_package_id')->nullable()->constrained('premium_packages')->nullOnDelete();
            $table->foreignId('premium_payment_id')->nullable()->constrained('premium_payments')->nullOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_premiums');
        Schema::dropIfExists('premium_payments');
        Schema::dropIfExists('premium_packages');
    }
};
