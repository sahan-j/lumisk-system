<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('subscription_prefix', 20)->default('SUB');
            $table->integer('subscription_next_number')->default(1);
            $table->integer('subscription_invoice_due_days')->default(14);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['subscription_prefix', 'subscription_next_number', 'subscription_invoice_due_days']);
        });
    }
};
