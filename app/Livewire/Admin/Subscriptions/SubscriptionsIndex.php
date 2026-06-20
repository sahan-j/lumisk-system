<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\Client;
use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Subscriptions')]
class SubscriptionsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $cycle = '';

    #[Url]
    public string $client = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'status', 'cycle', 'client'])) {
            $this->resetPage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('subscriptions.delete'), 403);

        if ($this->deleteId) {
            Subscription::find($this->deleteId)?->delete();
            $this->dispatch('toast', type: 'success', message: 'Subscription deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    protected function applyFilters($query)
    {
        return $query
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('subscription_number', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->cycle !== '', fn ($q) => $q->where('billing_cycle', $this->cycle))
            ->when($this->client !== '', fn ($q) => $q->where('client_id', $this->client));
    }

    public function render()
    {
        $subscriptions = $this->applyFilters(
            Subscription::with(['client', 'plan'])
        )->latest()->paginate(20);

        // Revenue stats from active subscriptions only.
        $active = Subscription::where('status', 'active')->get();

        return view('livewire.admin.subscriptions.subscriptions-index', [
            'subscriptions' => $subscriptions,
            'stats' => [
                'active' => $active->count(),
                'mrr' => round($active->sum('monthly_value'), 2),
                'arr' => round($active->sum('yearly_value'), 2),
                'past_due' => Subscription::where('status', 'past_due')->count(),
            ],
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'statuses' => Subscription::STATUSES,
            'cycles' => Subscription::BILLING_CYCLES,
        ]);
    }
}
