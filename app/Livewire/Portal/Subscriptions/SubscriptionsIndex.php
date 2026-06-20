<?php

namespace App\Livewire\Portal\Subscriptions;

use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('My Subscriptions')]
class SubscriptionsIndex extends Component
{
    public function render()
    {
        $subscriptions = Subscription::where('client_id', Auth::guard('client')->id())
            ->latest()
            ->get();

        return view('livewire.portal.subscriptions.subscriptions-index', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
