<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminderMail;
use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';
    protected $description = 'Send renewal reminders before subscription billing dates';

    public function handle(): int
    {
        $company = Company::settings();

        foreach ([7, 3] as $days) {
            $reminders = Subscription::with('client')
                ->where('status', 'active')
                ->whereDate('next_billing_date', today()->addDays($days))
                ->whereHas('client', fn ($q) => $q->whereNotNull('email'))
                ->get();

            foreach ($reminders as $sub) {
                try {
                    Mail::to($sub->client->email)
                        ->send(new SubscriptionReminderMail($sub, $company, $days));
                    $this->info("{$days}-day reminder sent: {$sub->client->name} ({$sub->subscription_number})");
                } catch (\Exception $e) {
                    $this->error("Failed {$sub->subscription_number}: {$e->getMessage()}");
                }
            }
        }

        return Command::SUCCESS;
    }
}
