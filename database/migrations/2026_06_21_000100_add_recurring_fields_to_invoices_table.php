<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('terms');
            $table->enum('recurring_cycle', ['weekly', 'monthly', 'quarterly', 'semi_annual', 'annual'])->nullable()->after('is_recurring');
            $table->date('recurring_next_date')->nullable()->after('recurring_cycle');
            $table->date('recurring_end_date')->nullable()->after('recurring_next_date');
            $table->unsignedBigInteger('recurring_parent_id')->nullable()->after('recurring_end_date');
            $table->integer('recurring_count')->default(0)->after('recurring_parent_id');

            $table->foreign('recurring_parent_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['recurring_parent_id']);
            $table->dropColumn([
                'is_recurring',
                'recurring_cycle',
                'recurring_next_date',
                'recurring_end_date',
                'recurring_parent_id',
                'recurring_count',
            ]);
        });
    }
};
