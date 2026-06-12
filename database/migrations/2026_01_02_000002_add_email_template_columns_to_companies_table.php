<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('invoice_email_subject')->default('Invoice {invoice_number} from Lumisk Technology');
            $table->text('invoice_email_body')->nullable();
            $table->string('estimate_email_subject')->default('Estimate {estimate_number} from Lumisk Technology');
            $table->text('estimate_email_body')->nullable();
            $table->string('reply_to_email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_email_subject',
                'invoice_email_body',
                'estimate_email_subject',
                'estimate_email_body',
                'reply_to_email',
            ]);
        });
    }
};
