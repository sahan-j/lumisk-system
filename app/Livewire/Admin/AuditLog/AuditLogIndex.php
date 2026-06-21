<?php

namespace App\Livewire\Admin\AuditLog;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Audit Log')]
class AuditLogIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $event = '';

    #[Url]
    public string $user = '';

    #[Url]
    public string $model = '';

    #[Url]
    public string $from = '';

    #[Url(as: 'q')]
    public string $search = '';

    public function updating($name): void
    {
        if (in_array($name, ['event', 'user', 'model', 'from', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('event', 'user', 'model', 'from', 'search');
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->when($this->event, fn ($q) => $q->where('event', $this->event))
            ->when($this->user, fn ($q) => $q->where('user_id', $this->user))
            ->when($this->model, fn ($q) => $q->where('auditable_type', 'App\\Models\\' . $this->model))
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->search, fn ($q) => $q->where('auditable_label', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(30);

        return view('livewire.admin.audit-log.audit-log-index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'events' => AuditLog::EVENTS,
            'models' => AuditLog::MODELS,
        ]);
    }
}
