<?php

namespace App\Livewire\Admin\Clients;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Client')]
class ClientShow extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function render()
    {
        return view('livewire.admin.clients.client-show', [
            'invoices' => $this->client->invoices()->latest()->get(),
            'estimates' => $this->client->estimates()->latest()->get(),
            'totalPaid' => $this->client->invoices()->where('status', 'paid')->sum('total'),
            'totalOutstanding' => $this->client->invoices()->whereIn('status', ['sent', 'overdue'])->sum('total'),
        ]);
    }
}
