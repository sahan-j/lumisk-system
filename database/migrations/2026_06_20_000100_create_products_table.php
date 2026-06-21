<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('unit', 30)->default('unit');
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('currency_code', 3)->default('LKR');
            $table->boolean('track_inventory')->default(false);
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->integer('low_stock_threshold')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
