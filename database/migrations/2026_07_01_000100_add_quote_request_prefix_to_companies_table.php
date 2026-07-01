<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('quote_request_prefix')->default('QR')->after('estimate_next_number');
            $table->unsignedInteger('quote_request_next_number')->default(1)->after('quote_request_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['quote_request_prefix', 'quote_request_next_number']);
        });
    }
};
