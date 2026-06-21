<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('LKR')->after('status');
            $table->decimal('exchange_rate', 10, 4)->default(1)->after('currency_code');
            $table->decimal('total_lkr', 12, 2)->default(0)->after('total');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('LKR')->after('status');
            $table->decimal('exchange_rate', 10, 4)->default(1)->after('currency_code');
            $table->decimal('total_lkr', 12, 2)->default(0)->after('total');
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('LKR')->after('status');
            $table->decimal('exchange_rate', 10, 4)->default(1)->after('currency_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate', 'total_lkr']);
        });
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate', 'total_lkr']);
        });
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate']);
        });
    }
};
