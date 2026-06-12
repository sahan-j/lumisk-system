<?php

namespace App\Livewire\Portal\Estimates;

use App\Models\Estimate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Estimate')]
class EstimateShow extends Component
{
    public Estimate $estimate;

    public bool $showResponse = false;
    public string $responseAction = '';   // 'accepted' | 'rejected'
    public string $client_note = '';

    public function mount(Estimate $estimate): void
    {
        abort_unless($estimate->client_id === Auth::guard('client')->id(), 403);

        $this->estimate = $estimate->load('items', 'client');
    }

    public function openResponse(string $action): void
    {
        if (! in_array($action, ['accepted', 'rejected']) || $this->estimate->status !== 'sent') {
            return;
        }

        $this->responseAction = $action;
        $this->client_note = '';
        $this->showResponse = true;
    }

    public function submitResponse(): void
    {
        // Only a "sent" estimate may be accepted or rejected.
        if ($this->estimate->status !== 'sent' || ! in_array($this->responseAction, ['accepted', 'rejected'])) {
            $this->showResponse = false;
            return;
        }

        $this->validate([
            'client_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->estimate->update([
            'status' => $this->responseAction,
            'client_note' => $this->client_note ?: null,
        ]);
        $this->estimate->refresh();

        $this->showResponse = false;
        $this->dispatch('toast', type: 'success', message: 'Estimate ' . $this->responseAction . '.');
    }

    public function render()
    {
        return view('livewire.portal.estimates.estimate-show');
    }
}
