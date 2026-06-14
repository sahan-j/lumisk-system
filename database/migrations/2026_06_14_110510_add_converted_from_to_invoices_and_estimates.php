<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('converted_from')->nullable()->after('status');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->string('converted_from')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('converted_from');
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('converted_from');
        });
    }
};
