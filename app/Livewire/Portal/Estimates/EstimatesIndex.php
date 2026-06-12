<?php

namespace App\Livewire\Portal\Estimates;

use App\Models\Estimate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
#[Title('Estimates')]
class EstimatesIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $estimates = Estimate::query()
            ->where('client_id', Auth::guard('client')->id())
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.portal.estimates.estimates-index', [
            'estimates' => $estimates,
            'statuses' => Estimate::STATUSES,
        ]);
    }
}
