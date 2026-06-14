<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // NOTE: MySQL TEXT columns cannot carry a default value, so these are
    // nullable and the default template text lives in code as
    // Company::DEFAULT_WHATSAPP_INVOICE / ::DEFAULT_WHATSAPP_ESTIMATE.
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('whatsapp_message_invoice')->nullable();
            $table->text('whatsapp_message_estimate')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_message_invoice', 'whatsapp_message_estimate']);
        });
    }
};
