<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\Invoice;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public array $results = ['clients' => [], 'invoices' => [], 'estimates' => []];

    public bool $showResults = false;

    public function updatedQuery(): void
    {
        $q = trim($this->query);

        if (strlen($q) < 2) {
            $this->results = ['clients' => [], 'invoices' => [], 'estimates' => []];
            $this->showResults = false;

            return;
        }

        $this->results['clients'] = Client::query()
            ->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%"))
            ->limit(5)
            ->get()
            ->map(fn ($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'email' => $c->email,
            ])
            ->toArray();

        $this->results['invoices'] = Invoice::query()
            ->with('client')
            ->where(fn ($sub) => $sub
                ->where('invoice_number', 'like', "%{$q}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                ->orWhere('total', 'like', "%{$q}%"))
            ->limit(5)
            ->get()
            ->map(fn ($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'    => $inv->client?->name ?? '—',
                'total'          => (float) $inv->total,
                'status'         => $inv->status,
            ])
            ->toArray();

        $this->results['estimates'] = Estimate::query()
            ->with('client')
            ->where(fn ($sub) => $sub
                ->where('estimate_number', 'like', "%{$q}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                ->orWhere('total', 'like', "%{$q}%"))
            ->limit(5)
            ->get()
            ->map(fn ($est) => [
                'id'              => $est->id,
                'estimate_number' => $est->estimate_number,
                'client_name'     => $est->client?->name ?? '—',
                'total'           => (float) $est->total,
                'status'          => $est->status,
            ])
            ->toArray();

        $this->showResults = true;
    }

    public function clearSearch(): void
    {
        $this->reset('query', 'results', 'showResults');
    }

    public function render()
    {
        return view('livewire.admin.global-search');
    }
}
