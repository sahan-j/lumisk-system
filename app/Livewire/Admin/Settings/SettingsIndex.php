<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Settings')]
class SettingsIndex extends Component
{
    use WithFileUploads;

    public Company $company;

    // Company info
    public string $name = '';
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $website = null;
    public $logo = null; // new upload

    // PDF appearance
    public int $pdf_font_size = 10;

    // Bank details
    public string $bank_name = '';
    public string $bank_account_name = '';
    public string $bank_account_number = '';
    public string $bank_branch = '';

    // Invoice defaults
    public string $invoice_prefix = 'INV';
    public int $invoice_next_number = 1;
    public ?string $default_terms = null;
    public ?string $default_notes = null;
    public $default_tax_rate = 0;
    public string $currency = 'LKR';

    // Estimate defaults
    public string $estimate_prefix = 'EST';
    public int $estimate_next_number = 1;
    public int $estimate_expiry_days = 30;

    // Email templates
    public string $reply_to_email = '';
    public string $invoice_email_subject = '';
    public string $invoice_email_body = '';
    public string $estimate_email_subject = '';
    public string $estimate_email_body = '';

    // WhatsApp templates
    public string $whatsapp_message_invoice = '';
    public string $whatsapp_message_estimate = '';

    public function mount(): void
    {
        $this->company = Company::settings();
        $this->fill($this->company->only([
            'name', 'email', 'phone', 'address', 'website',
            'invoice_prefix', 'invoice_next_number', 'default_terms', 'default_notes',
            'default_tax_rate', 'currency', 'estimate_prefix', 'estimate_next_number', 'estimate_expiry_days',
        ]));
        $this->pdf_font_size = (int) ($this->company->pdf_font_size ?: 10);

        // Bank details are nullable columns — coalesce to empty string for string-typed props
        $this->bank_name           = $this->company->bank_name ?? '';
        $this->bank_account_name   = $this->company->bank_account_name ?? '';
        $this->bank_account_number = $this->company->bank_account_number ?? '';
        $this->bank_branch         = $this->company->bank_branch ?? '';

        // Email template fields are nullable columns — assign with defaults to avoid type errors
        $this->reply_to_email         = $this->company->reply_to_email ?? '';
        $this->invoice_email_subject  = $this->company->invoice_email_subject  ?: 'Invoice {invoice_number} from Lumisk Technology';
        $this->invoice_email_body     = $this->company->invoice_email_body     ?: "Dear {client_name},\n\nPlease find attached your invoice {invoice_number} for {total}.\n\nKind regards,\nLumisk Technology";
        $this->estimate_email_subject = $this->company->estimate_email_subject ?: 'Estimate {estimate_number} from Lumisk Technology';
        $this->estimate_email_body    = $this->company->estimate_email_body    ?: "Dear {client_name},\n\nPlease find attached your estimate {estimate_number} for {total}.\n\nKind regards,\nLumisk Technology";

        // WhatsApp templates — fall back to the code defaults when null
        $this->whatsapp_message_invoice  = $this->company->whatsapp_message_invoice  ?: Company::DEFAULT_WHATSAPP_INVOICE;
        $this->whatsapp_message_estimate = $this->company->whatsapp_message_estimate ?: Company::DEFAULT_WHATSAPP_ESTIMATE;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'pdf_font_size' => ['required', 'integer', 'min:8', 'max:14'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'invoice_next_number' => ['required', 'integer', 'min:1'],
            'default_terms' => ['nullable', 'string'],
            'default_notes' => ['nullable', 'string'],
            'default_tax_rate' => ['numeric', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'max:8'],
            'estimate_prefix' => ['required', 'string', 'max:10'],
            'estimate_next_number' => ['required', 'integer', 'min:1'],
            'estimate_expiry_days' => ['required', 'integer', 'min:1'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'invoice_email_subject' => ['required', 'string', 'max:500'],
            'invoice_email_body' => ['required', 'string'],
            'estimate_email_subject' => ['required', 'string', 'max:500'],
            'estimate_email_body' => ['required', 'string'],
            'whatsapp_message_invoice' => ['required', 'string'],
            'whatsapp_message_estimate' => ['required', 'string'],
        ]);

        if ($this->logo) {
            // Remove previous logo if any.
            if ($this->company->logo) {
                Storage::disk('public')->delete($this->company->logo);
            }
            $validated['logo'] = $this->logo->store('logos', 'public');
        } else {
            unset($validated['logo']);
        }

        $this->company->update($validated);
        $this->company->refresh();
        $this->logo = null;

        $this->dispatch('toast', type: 'success', message: 'Settings saved.');
    }

    public function removeLogo(): void
    {
        if ($this->company->logo) {
            Storage::disk('public')->delete($this->company->logo);
            $this->company->update(['logo' => null]);
            $this->company->refresh();
        }
        $this->dispatch('toast', type: 'success', message: 'Logo removed.');
    }

    public function render()
    {
        return view('livewire.admin.settings.settings-index');
    }
}
