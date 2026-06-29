<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Shared "all notifications" page behaviour for admin + portal.
 * The consuming component supplies the auth guard via guardName().
 */
trait ManagesNotifications
{
    use WithPagination;

    #[Url]
    public string $filter = 'all'; // all | unread | read

    abstract protected function guardName(): string;

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'unread', 'read'], true) ? $filter : 'all';
        $this->resetPage();
    }

    public function markRead(string $id): void
    {
        auth($this->guardName())->user()?->notifications()->find($id)?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth($this->guardName())->user()?->unreadNotifications->markAsRead();
        $this->dispatch('toast', type: 'success', message: 'All notifications marked as read.');
    }

    public function clearRead(): void
    {
        auth($this->guardName())->user()?->notifications()->whereNotNull('read_at')->delete();
        $this->resetPage();
        $this->dispatch('toast', type: 'success', message: 'Read notifications cleared.');
    }

    protected function notificationsData()
    {
        $user = auth($this->guardName())->user();

        $query = $user->notifications()
            ->when($this->filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($this->filter === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->latest();

        return [
            'notifications' => $query->paginate(30),
            'unreadCount' => $user->unreadNotifications()->count(),
        ];
    }
}
