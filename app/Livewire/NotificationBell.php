<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public string $guard = 'web'; // 'web' = admin, 'client' = portal
    public int $unreadCount = 0;
    public bool $showDropdown = false;
    public array $notifications = [];

    public function mount(string $guard = 'web'): void
    {
        $this->guard = $guard;
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth($this->guard)->user();
        if (! $user) {
            return;
        }

        $this->unreadCount = $user->unreadNotifications()->count();
        $this->notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? '',
                'message' => $n->data['message'] ?? '',
                'icon' => $n->data['icon'] ?? '',
                'color' => $n->data['color'] ?? '#6d5cff',
                'url' => $n->data['url'] ?? '#',
                'read' => ! is_null($n->read_at),
                'time' => $n->created_at->diffForHumans(),
            ])->toArray();
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = ! $this->showDropdown;
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAllRead(): void
    {
        auth($this->guard)->user()?->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function goToNotification(string $id)
    {
        $user = auth($this->guard)->user();
        $notification = $user?->notifications()->find($id);

        if (! $notification) {
            return null;
        }

        $url = $notification->data['url'] ?? null;
        $notification->markAsRead();

        if ($url) {
            return $this->redirect($url, navigate: true);
        }

        $this->loadNotifications();

        return null;
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
