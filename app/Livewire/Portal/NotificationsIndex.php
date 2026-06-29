<?php

namespace App\Livewire\Portal;

use App\Livewire\Concerns\ManagesNotifications;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Notifications')]
class NotificationsIndex extends Component
{
    use ManagesNotifications;

    protected function guardName(): string
    {
        return 'client';
    }

    public function render()
    {
        return view('livewire.notifications-page', $this->notificationsData());
    }
}
