<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('invoice_id')->constrained('products')->nullOnDelete();
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('estimate_id')->constrained('products')->nullOnDelete();
        });

        // Guards against deducting stock twice for the same invoice.
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('stock_deducted')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('stock_deducted');
        });
    }
};
