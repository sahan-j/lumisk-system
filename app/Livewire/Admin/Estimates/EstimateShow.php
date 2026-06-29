<?php

namespace App\Livewire\Admin\Estimates;

use App\Models\ActivityLog;
use App\Models\Estimate;
use App\Models\InvoiceTemplate;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Estimate')]
class EstimateShow extends Component
{
    public Estimate $estimate;

    public bool $showTemplateModal = false;
    public string $templateName = '';

    public function mount(Estimate $estimate): void
    {
        $this->estimate = $estimate->load('items', 'client');
    }

    public function setStatus(string $status): void
    {
        if (! in_array($status, Estimate::STATUSES)) {
            return;
        }

        $this->estimate->update(['status' => $status]);
        $this->estimate->refresh();

        if ($status === 'sent') {
            ActivityLog::log('estimate_sent',
                "Estimate {$this->estimate->estimate_number} sent",
                ['subject_type' => 'Estimate', 'subject_id' => $this->estimate->id,
                 'subject_label' => $this->estimate->estimate_number, 'client_id' => $this->estimate->client_id]);

            $this->estimate->client?->notify(new \App\Notifications\Client\EstimateSentNotification($this->estimate));
        }

        $this->dispatch('toast', type: 'success', message: 'Status updated to ' . ucfirst($status) . '.');
    }

    public function openSendEmail(): void
    {
        $this->dispatch('open-send-email', type: 'estimate', id: $this->estimate->id);
    }

    public function saveAsTemplate(): void
    {
        $this->validate(['templateName' => ['required', 'string', 'max:255']]);

        $this->estimate->loadMissing('items');

        DB::transaction(function () {
            $template = InvoiceTemplate::create([
                'name' => $this->templateName,
                'description' => "Saved from estimate {$this->estimate->estimate_number}",
                'type' => 'estimate',
                'tax_rate' => $this->estimate->tax_rate,
                'discount_amount' => $this->estimate->discount_amount,
                'notes' => $this->estimate->notes,
                'terms' => $this->estimate->terms,
                'currency_code' => $this->estimate->currency_code ?: 'LKR',
                'created_by' => auth()->user()?->name,
            ]);

            foreach ($this->estimate->items as $item) {
                $template->items()->create([
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                    'sort_order' => $item->order,
                ]);
            }
        });

        $this->showTemplateModal = false;
        $name = $this->templateName;
        $this->templateName = '';
        $this->dispatch('toast', type: 'success', message: "Saved as template “{$name}”.");
    }

    #[On('email-sent')]
    public function refreshAfterSend(): void
    {
        $this->estimate->refresh();
    }

    public function render()
    {
        return view('livewire.admin.estimates.estimate-show');
    }
}
