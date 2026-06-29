<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\ManagesNotifications;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Notifications')]
class NotificationsIndex extends Component
{
    use ManagesNotifications;

    protected function guardName(): string
    {
        return 'web';
    }

    public function render()
    {
        return view('livewire.notifications-page', $this->notificationsData());
    }
}
