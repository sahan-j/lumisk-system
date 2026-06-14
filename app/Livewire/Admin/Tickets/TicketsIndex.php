<?php

namespace App\Livewire\Admin\Tickets;

use App\Models\Client;
use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Support Tickets')]
class TicketsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $client = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingClient(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $tickets = Ticket::query()
            ->with(['client', 'project'])
            ->withCount('messages')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('subject', 'like', "%{$this->search}%")
                        ->orWhere('ticket_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->client, fn ($q) => $q->where('client_id', $this->client))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.tickets.tickets-index', [
            'tickets' => $tickets,
            'statuses' => Ticket::STATUSES,
            'priorities' => Ticket::PRIORITIES,
            'types' => Ticket::TYPES,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'openCount' => Ticket::where('status', 'open')->count(),
            'inProgressCount' => Ticket::where('status', 'in_progress')->count(),
            'slaOverdueCount' => Ticket::where('is_overdue_sla', true)->whereNotIn('status', ['resolved', 'closed'])->count(),
            'resolvedTodayCount' => Ticket::whereDate('resolved_at', today())->count(),
        ]);
    }
}
