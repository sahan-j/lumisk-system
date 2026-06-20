<?php

namespace App\Livewire\Portal\CreditNotes;

use App\Models\CreditNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
#[Title('Credit Notes')]
class CreditNotesIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $creditNotes = CreditNote::with('invoice')
            ->where('client_id', Auth::guard('client')->id())
            ->where('status', '!=', 'draft')
            ->latest()
            ->paginate(15);

        return view('livewire.portal.credit-notes.credit-notes-index', [
            'creditNotes' => $creditNotes,
        ]);
    }
}
