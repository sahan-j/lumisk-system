<?php

namespace App\Livewire\Portal\Tickets;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.portal')]
#[Title('Support Tickets')]
class TicketsIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $clientId = Auth::guard('client')->id();

        $tickets = Ticket::where('client_id', $clientId)
            ->withCount('messages')
            ->latest()
            ->paginate(15);

        return view('livewire.portal.tickets.tickets-index', [
            'tickets' => $tickets,
            'openCount' => Ticket::where('client_id', $clientId)->where('status', 'open')->count(),
            'inProgressCount' => Ticket::where('client_id', $clientId)->where('status', 'in_progress')->count(),
            'resolvedCount' => Ticket::where('client_id', $clientId)->whereIn('status', ['resolved', 'closed'])->count(),
        ]);
    }
}
