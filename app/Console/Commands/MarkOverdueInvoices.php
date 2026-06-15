<?php

namespace App\Console\Commands;

use App\Mail\OverdueReminderMail;
use App\Models\ActivityLog;
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

        // Capture every invoice about to transition (for the activity log), then bulk-update.
        $transitioning = Invoice::where('status', 'sent')
            ->where('due_date', '<', today())
            ->get(['id', 'invoice_number', 'client_id']);

        $updated = Invoice::where('status', 'sent')
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        foreach ($transitioning as $inv) {
            ActivityLog::log('invoice_overdue',
                "Invoice {$inv->invoice_number} is now overdue",
                ['subject_type' => 'Invoice', 'subject_id' => $inv->id,
                 'subject_label' => $inv->invoice_number, 'client_id' => $inv->client_id,
                 'causer_name' => 'System']);
        }

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
