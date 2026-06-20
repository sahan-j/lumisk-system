<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionInvoiceMail;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ProcessSubscriptionBilling extends Command
{
    protected $signature = 'subscriptions:process-billing';
    protected $description = 'Generate invoices for subscriptions due for billing';

    public function handle(): int
    {
        $company = Company::settings();

        // First, transition any subscriptions whose lifecycle has changed.
        $this->expireEnded();
        $this->activateTrials();

        // Active, auto-invoicing subscriptions due today (and not past their end date).
        $dueSubscriptions = Subscription::with(['client', 'plan'])
            ->where('status', 'active')
            ->where('auto_invoice', true)
            ->whereDate('next_billing_date', '<=', today())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            })
            ->get();

        $count = 0;
        foreach ($dueSubscriptions as $subscription) {
            try {
                DB::transaction(function () use ($subscription, $company) {
                    $invoice = $subscription->generateInvoice();

                    if ($subscription->auto_send_invoice && $subscription->client?->email) {
                        Mail::to($subscription->client->email)
                            ->send(new SubscriptionInvoiceMail($invoice, $company, $subscription));
                        $invoice->update(['status' => 'sent']);
                    }

                    ActivityLog::log('subscription_billed',
                        "Auto-invoice {$invoice->invoice_number} generated for {$subscription->name} ({$subscription->client?->name})",
                        ['subject_type' => 'Subscription', 'subject_id' => $subscription->id,
                         'subject_label' => $subscription->subscription_number,
                         'client_id' => $subscription->client_id, 'causer_name' => 'System']);
                });

                $count++;
                $this->info("Billed: {$subscription->subscription_number} — {$subscription->client?->name}");
            } catch (\Exception $e) {
                $this->error("Failed {$subscription->subscription_number}: {$e->getMessage()}");
                $subscription->update(['status' => 'past_due']);
            }
        }

        $this->info("Processed {$count} subscription billing(s).");

        return Command::SUCCESS;
    }

    /** Active subscriptions past their end date become expired. */
    private function expireEnded(): void
    {
        Subscription::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', today())
            ->update(['status' => 'expired']);
    }

    /** Trials whose trial window has ended become active and bill immediately. */
    private function activateTrials(): void
    {
        Subscription::where('status', 'trial')
            ->whereNotNull('trial_end_date')
            ->whereDate('trial_end_date', '<', today())
            ->update(['status' => 'active', 'next_billing_date' => today()]);
    }
}
