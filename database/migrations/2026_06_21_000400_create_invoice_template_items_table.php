<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('invoice_templates')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_template_items');
    }
};
