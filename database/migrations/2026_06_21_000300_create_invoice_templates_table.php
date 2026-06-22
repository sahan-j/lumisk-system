<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['invoice', 'estimate', 'both'])->default('both');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('currency_code', 3)->default('LKR');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
