<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Lumisk Technology');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->string('estimate_prefix')->default('EST');
            $table->unsignedInteger('invoice_next_number')->default(1);
            $table->unsignedInteger('estimate_next_number')->default(1);
            $table->unsignedInteger('estimate_expiry_days')->default(30);
            $table->text('default_terms')->nullable();
            $table->text('default_notes')->nullable();
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->string('currency', 8)->default('LKR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
