<?php

namespace App\Livewire\Portal\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
#[Title('Invoices')]
class InvoicesIndex extends Component
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
        $invoices = Invoice::query()
            ->with('payments')
            ->where('client_id', Auth::guard('client')->id())
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.portal.invoices.invoices-index', [
            'invoices' => $invoices,
            'statuses' => Invoice::STATUSES,
        ]);
    }
}
