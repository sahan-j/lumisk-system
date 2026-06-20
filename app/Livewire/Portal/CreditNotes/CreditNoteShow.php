<?php

namespace App\Livewire\Portal\CreditNotes;

use App\Models\CreditNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Credit Note')]
class CreditNoteShow extends Component
{
    public CreditNote $creditNote;

    public function mount(CreditNote $creditNote): void
    {
        // Clients may only view their own (issued) credit notes.
        abort_unless(
            $creditNote->client_id === Auth::guard('client')->id() && $creditNote->status !== 'draft',
            403
        );

        $this->creditNote = $creditNote->load('items', 'invoice', 'applications.invoice');
    }

    public function render()
    {
        return view('livewire.portal.credit-notes.credit-note-show');
    }
}
