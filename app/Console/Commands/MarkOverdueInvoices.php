<?php

namespace App\Console\Commands;

use App\Mail\OverdueReminderMail;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue';
    protected $description = 'Mark sent invoices as overdue if due date has passed, and email reminders';

    public function handle(): int
    {
        $company = Company::settings();

        // Find newly overdue (sent + due date passed) before bulk-updating status.
        $newlyOverdue = Invoice::with(['client', 'items', 'payments'])
            ->where('status', 'sent')
            ->where('due_date', '<', today())
            ->whereHas('client', fn ($q) => $q->whereNotNull('email'))
            ->get();

        // Bulk-mark all sent+past-due as overdue (no client filter — mark regardless).
        $updated = Invoice::where('status', 'sent')
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$updated} invoice(s) as overdue.");

        if ($company->overdue_reminders_enabled === false) {
            $this->info('Overdue reminder emails are disabled in Settings.');
            return Command::SUCCESS;
        }

        // Day-0 reminders: newly overdue invoices.
        foreach ($newlyOverdue as $invoice) {
            $this->sendReminder($invoice, $company, 0);
        }

        // Follow-up reminders: 3 days and 7 days after due date.
        $followUp = Invoice::with(['client', 'items', 'payments'])
            ->where('status', 'overdue')
            ->where(function ($q) {
                $q->whereDate('due_date', today()->subDays(3))
                  ->orWhereDate('due_date', today()->subDays(7));
            })
            ->whereHas('client', fn ($q) => $q->whereNotNull('email'))
            ->get();

        foreach ($followUp as $invoice) {
            $daysOverdue = (int) today()->diffInDays($invoice->due_date);
            $this->sendReminder($invoice, $company, $daysOverdue);
        }

        return Command::SUCCESS;
    }

    private function sendReminder(Invoice $invoice, Company $company, int $daysOverdue): void
    {
        $email = $invoice->client?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new OverdueReminderMail($invoice, $company, $daysOverdue));
            $label = $daysOverdue > 0 ? " ({$daysOverdue}d overdue)" : '';
            $this->info("Reminder{$label} sent to: {$email}");
        } catch (\Exception $e) {
            $this->error("Failed to send to {$email}: {$e->getMessage()}");
        }
    }
}
