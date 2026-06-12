<?php

namespace App\Livewire\Admin;

use App\Helpers\EmailTemplateHelper;
use App\Mail\EstimateMail;
use App\Mail\InvoiceMail;
use App\Models\Company;
use App\Models\EmailLog;
use App\Models\Estimate;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;

class SendEmailModal extends Component
{
    public bool $show = false;
    public string $type = '';
    public ?int $referenceId = null;
    public string $toEmail = '';
    public string $ccEmail = '';
    public string $subject = '';
    public string $message = '';
    public bool $isSending = false;

    #[On('open-send-email')]
    public function handleOpen(string $type, int $id): void
    {
        if ($type === 'invoice') {
            $this->openForInvoice($id);
        } else {
            $this->openForEstimate($id);
        }
    }

    public function openForInvoice(int $invoiceId): void
    {
        $invoice = Invoice::with('client', 'items')->findOrFail($invoiceId);
        $company = Company::settings();

        $defaultSubject = $company->invoice_email_subject
            ?: 'Invoice {invoice_number} from Lumisk Technology';
        $defaultBody    = $company->invoice_email_body
            ?: "Dear {client_name},\n\nPlease find attached your invoice {invoice_number} for {total}.\n\nKind regards,\nLumisk Technology";

        $this->type        = 'invoice';
        $this->referenceId = $invoiceId;
        $this->toEmail     = $invoice->client->email ?? '';
        $this->ccEmail     = '';
        $this->subject     = EmailTemplateHelper::forInvoice($defaultSubject, $invoice, $company);
        $this->message     = EmailTemplateHelper::forInvoice($defaultBody,   $invoice, $company);
        $this->show        = true;
        $this->resetValidation();
    }

    public function openForEstimate(int $estimateId): void
    {
        $estimate = Estimate::with('client', 'items')->findOrFail($estimateId);
        $company  = Company::settings();

        $defaultSubject = $company->estimate_email_subject
            ?: 'Estimate {estimate_number} from Lumisk Technology';
        $defaultBody    = $company->estimate_email_body
            ?: "Dear {client_name},\n\nPlease find attached your estimate {estimate_number} for {total}.\n\nKind regards,\nLumisk Technology";

        $this->type        = 'estimate';
        $this->referenceId = $estimateId;
        $this->toEmail     = $estimate->client->email ?? '';
        $this->ccEmail     = '';
        $this->subject     = EmailTemplateHelper::forEstimate($defaultSubject, $estimate, $company);
        $this->message     = EmailTemplateHelper::forEstimate($defaultBody,    $estimate, $company);
        $this->show        = true;
        $this->resetValidation();
    }

    public function send(): void
    {
        $this->validate([
            'toEmail' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'message' => ['required', 'string'],
        ]);

        $this->isSending = true;

        try {
            $company = Company::settings();
            $cc      = $this->ccEmail ?: null;

            if ($this->type === 'invoice') {
                $invoice = Invoice::with('client', 'items')->findOrFail($this->referenceId);

                Mail::to($this->toEmail)->send(
                    new InvoiceMail($invoice, $company, $this->subject, $this->message, $cc)
                );

                EmailLog::create([
                    'type'         => 'invoice',
                    'reference_id' => $invoice->id,
                    'to_email'     => $this->toEmail,
                    'cc_email'     => $cc,
                    'subject'      => $this->subject,
                    'message'      => $this->message,
                    'status'       => 'sent',
                    'sent_at'      => now(),
                ]);

                if ($invoice->status === 'draft') {
                    $invoice->update(['status' => 'sent']);
                }

            } else {
                $estimate = Estimate::with('client', 'items')->findOrFail($this->referenceId);

                Mail::to($this->toEmail)->send(
                    new EstimateMail($estimate, $company, $this->subject, $this->message, $cc)
                );

                EmailLog::create([
                    'type'         => 'estimate',
                    'reference_id' => $estimate->id,
                    'to_email'     => $this->toEmail,
                    'cc_email'     => $cc,
                    'subject'      => $this->subject,
                    'message'      => $this->message,
                    'status'       => 'sent',
                    'sent_at'      => now(),
                ]);

                if ($estimate->status === 'draft') {
                    $estimate->update(['status' => 'sent']);
                }
            }

            $this->show = false;
            $this->dispatch('email-sent');
            $this->dispatch('toast', type: 'success', message: 'Email sent successfully!');

        } catch (\Exception $e) {
            EmailLog::create([
                'type'          => $this->type,
                'reference_id'  => $this->referenceId,
                'to_email'      => $this->toEmail,
                'cc_email'      => $this->ccEmail ?: null,
                'subject'       => $this->subject,
                'message'       => $this->message,
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at'       => now(),
            ]);

            $this->addError('send_error', 'Failed to send email: ' . $e->getMessage());

        } finally {
            $this->isSending = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.send-email-modal');
    }
}
