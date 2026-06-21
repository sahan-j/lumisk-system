<?php

namespace App\Console\Commands;

use App\Helpers\EmailTemplateHelper;
use App\Mail\InvoiceMail;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\EmailLog;
use App\Models\Invoice;
use App\Services\DocumentNumberService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'invoices:generate-recurring';
    protected $description = 'Auto-generate recurring invoices that are due today';

    public function handle(): int
    {
        $company = Company::settings();
        $today = today();

        $recurringInvoices = Invoice::with(['client', 'items'])
            ->where('is_recurring', true)
            ->where('recurring_next_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('recurring_end_date')
                    ->orWhere('recurring_end_date', '>=', $today);
            })
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->get();

        $count = 0;

        foreach ($recurringInvoices as $template) {
            try {
                DB::transaction(function () use ($template, $today, $company) {
                    $newNumber = DocumentNumberService::nextInvoiceNumber();

                    $newInvoice = Invoice::create([
                        'invoice_number' => $newNumber,
                        'client_id' => $template->client_id,
                        'status' => 'draft',
                        'currency_code' => $template->currency_code,
                        'exchange_rate' => $template->exchange_rate,
                        'issue_date' => $today,
                        'due_date' => $today->copy()->addDays(14),
                        'subtotal' => $template->subtotal,
                        'tax_rate' => $template->tax_rate,
                        'tax_amount' => $template->tax_amount,
                        'discount_amount' => $template->discount_amount,
                        'total' => $template->total,
                        'total_lkr' => $template->total_lkr,
                        'notes' => $template->notes,
                        'terms' => $template->terms,
                        'recurring_parent_id' => $template->id,
                        'is_recurring' => false,
                    ]);

                    foreach ($template->items as $order => $item) {
                        $newInvoice->items()->create([
                            'name' => $item->name,
                            'description' => $item->description,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total' => $item->total,
                            'order' => $order,
                        ]);
                    }

                    if ($template->client?->email) {
                        $defaultSubject = $company->invoice_email_subject
                            ?: 'Invoice {invoice_number} from {company_name}';
                        $defaultBody = $company->invoice_email_body
                            ?: "Dear {client_name},\n\nPlease find attached your invoice {invoice_number} for {total}.\n\nKind regards,\n{company_name}";

                        $subject = EmailTemplateHelper::forInvoice($defaultSubject, $newInvoice->load('client'), $company);
                        $body    = EmailTemplateHelper::forInvoice($defaultBody, $newInvoice, $company);

                        Mail::to($template->client->email)->send(
                            new InvoiceMail($newInvoice, $company, $subject, $body)
                        );

                        EmailLog::create([
                            'type' => 'invoice',
                            'reference_id' => $newInvoice->id,
                            'to_email' => $template->client->email,
                            'subject' => $subject,
                            'message' => $body,
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                        $newInvoice->update(['status' => 'sent']);
                    }

                    $template->update([
                        'recurring_next_date' => $template->calculateNextRecurringDate(),
                        'recurring_count' => $template->recurring_count + 1,
                    ]);

                    ActivityLog::log(
                        'invoice_created',
                        "Recurring invoice {$newNumber} auto-generated for {$template->client->name}",
                        [
                            'subject_type' => 'Invoice',
                            'subject_id' => $newInvoice->id,
                            'subject_label' => $newNumber,
                            'client_id' => $template->client_id,
                        ]
                    );
                });

                $count++;
                $this->info("Generated: {$template->invoice_number} → new invoice for {$template->client->name}");

            } catch (\Exception $e) {
                $this->error("Failed for {$template->invoice_number}: {$e->getMessage()}");
                Log::error('Recurring invoice generation failed', [
                    'invoice_id' => $template->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done. Generated {$count} recurring invoice(s).");

        return Command::SUCCESS;
    }
}
