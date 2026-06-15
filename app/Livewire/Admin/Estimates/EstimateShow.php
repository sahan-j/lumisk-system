<?php

namespace App\Livewire\Admin\Estimates;

use App\Models\ActivityLog;
use App\Models\Estimate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Estimate')]
class EstimateShow extends Component
{
    public Estimate $estimate;

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
        }

        $this->dispatch('toast', type: 'success', message: 'Status updated to ' . ucfirst($status) . '.');
    }

    public function openSendEmail(): void
    {
        $this->dispatch('open-send-email', type: 'estimate', id: $this->estimate->id);
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
