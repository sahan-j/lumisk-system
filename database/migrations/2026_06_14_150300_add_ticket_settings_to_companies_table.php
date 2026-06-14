<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('ticket_prefix')->default('TKT')->after('overdue_reminders_enabled');
            $table->integer('ticket_next_number')->default(1)->after('ticket_prefix');
            $table->integer('sla_low_hours')->default(72)->after('ticket_next_number');
            $table->integer('sla_medium_hours')->default(24)->after('sla_low_hours');
            $table->integer('sla_high_hours')->default(4)->after('sla_medium_hours');
            $table->integer('sla_critical_hours')->default(1)->after('sla_high_hours');
            $table->boolean('ticket_notifications_enabled')->default(true)->after('sla_critical_hours');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_prefix', 'ticket_next_number',
                'sla_low_hours', 'sla_medium_hours', 'sla_high_hours', 'sla_critical_hours',
                'ticket_notifications_enabled',
            ]);
        });
    }
};
