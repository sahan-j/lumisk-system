<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Services\PublicTokenService;
use Livewire\Attributes\On;
use Livewire\Component;

class WhatsappModal extends Component
{
    public bool $show = false;
    public string $type = '';
    public ?int $referenceId = null;
    public string $phone = '';
    public string $message = '';
    public string $publicUrl = '';
    public string $whatsappUrl = '';

    #[On('open-whatsapp')]
    public function handleOpen(string $type, int $id): void
    {
        $company = Company::settings();
        $service = app(PublicTokenService::class);

        if ($type === 'invoice') {
            $record = Invoice::with('client')->findOrFail($id);
            $template = $company->whatsapp_message_invoice ?: Company::DEFAULT_WHATSAPP_INVOICE;
        } else {
            $record = Estimate::with('client')->findOrFail($id);
            $template = $company->whatsapp_message_estimate ?: Company::DEFAULT_WHATSAPP_ESTIMATE;
        }

        $this->type = $type;
        $this->referenceId = $id;
        $this->phone = $this->cleanPhone($record->client?->phone ?? '');
        $this->publicUrl = $service->getPublicUrl($type, $id);
        $this->message = $this->fillTemplate($template, $type, $record, $company, $this->publicUrl);

        $this->regenerateUrl();
        $this->show = true;
    }

    public function updatedPhone(): void
    {
        $this->regenerateUrl();
    }

    public function updatedMessage(): void
    {
        $this->regenerateUrl();
    }

    private function fillTemplate(string $template, string $type, $record, Company $company, string $link): string
    {
        $total = money($record->total, false);

        $common = [
            '{client_name}' => $record->client?->name ?? '',
            '{total}' => $total,
            '{link}' => $link,
            '{company_name}' => $company->name ?? '',
            '{company_phone}' => $company->phone ?? '',
        ];

        if ($type === 'invoice') {
            $specific = [
                '{invoice_number}' => $record->invoice_number,
                '{due_date}' => $record->due_date?->format('M d, Y') ?? '—',
            ];
        } else {
            $specific = [
                '{estimate_number}' => $record->estimate_number,
                '{expiry_date}' => $record->expiry_date?->format('M d, Y') ?? '—',
            ];
        }

        $map = array_merge($common, $specific);

        return str_replace(array_keys($map), array_values($map), $template);
    }

    private function regenerateUrl(): void
    {
        $clean = $this->cleanPhone($this->phone);
        $this->whatsappUrl = 'https://wa.me/' . $clean . '?text=' . rawurlencode($this->message);
    }

    /**
     * Normalise a Sri Lankan number to international format without "+".
     * "0773243784" → "94773243784"; "+94773243784" → "94773243784".
     */
    private function cleanPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '94' . substr($digits, 1);
        }

        if (! str_starts_with($digits, '94')) {
            return '94' . $digits;
        }

        return $digits;
    }

    public function render()
    {
        return view('livewire.admin.whatsapp-modal');
    }
}
