<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('invoices:mark-overdue')->dailyAt('08:00');
Schedule::command('tickets:check-sla')->everyFifteenMinutes();
Schedule::command('activity:clean')->monthly();
Schedule::command('subscriptions:process-billing')->dailyAt('07:00');
Schedule::command('subscriptions:send-reminders')->dailyAt('08:30');
Schedule::command('inventory:check-low-stock')->dailyAt('09:00');
Schedule::command('invoices:generate-recurring')->dailyAt('07:30');
Schedule::command('db:backup')->dailyAt('02:00');
Schedule::command('audit:clean')->monthly();
Schedule::command('notifications:clean')->monthly();
