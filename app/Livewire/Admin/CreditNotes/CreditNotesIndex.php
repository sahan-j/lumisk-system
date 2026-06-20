<?php

namespace App\Livewire\Admin\CreditNotes;

use App\Models\Client;
use App\Models\CreditNote;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Credit Notes')]
class CreditNotesIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $client = '';

    public bool $confirmingVoid = false;
    public ?int $voidId = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'status', 'client'])) {
            $this->resetPage();
        }
    }

    public function confirmVoid(int $id): void
    {
        $this->voidId = $id;
        $this->confirmingVoid = true;
    }

    public function voidCreditNote(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('credit-notes.edit'), 403);

        $cn = CreditNote::find($this->voidId);
        if ($cn && (float) $cn->amount_applied <= 0 && $cn->status !== 'void') {
            $cn->update(['status' => 'void']);
            $this->dispatch('toast', type: 'success', message: 'Credit note voided.');
        } elseif ($cn) {
            $this->dispatch('toast', type: 'error', message: 'Cannot void a credit note that has been applied.');
        }

        $this->confirmingVoid = false;
        $this->voidId = null;
    }

    public function render()
    {
        $creditNotes = CreditNote::with(['client', 'invoice'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('credit_note_number', 'like', "%{$this->search}%")
                        ->orWhere('reason', 'like', "%{$this->search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->client !== '', fn ($q) => $q->where('client_id', $this->client))
            ->latest()
            ->paginate(20);

        // Issued (non-void) credit notes drive the remaining-balance figure.
        $issued = CreditNote::where('status', 'issued')->get();

        return view('livewire.admin.credit-notes.credit-notes-index', [
            'creditNotes' => $creditNotes,
            'stats' => [
                'total_issued' => CreditNote::where('status', '!=', 'void')->sum('total'),
                'total_applied' => CreditNote::sum('amount_applied'),
                'total_remaining' => round($issued->sum('amount_remaining'), 2),
                'count_this_month' => CreditNote::whereMonth('issue_date', now()->month)
                    ->whereYear('issue_date', now()->year)->count(),
            ],
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'statuses' => CreditNote::STATUSES,
        ]);
    }
}
