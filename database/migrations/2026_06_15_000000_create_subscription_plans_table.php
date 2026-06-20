<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 8)->default('LKR');
            $table->enum('billing_cycle', ['weekly', 'monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
            $table->integer('billing_cycle_days')->default(30);
            $table->integer('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
