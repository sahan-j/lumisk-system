<?php

namespace App\Livewire\Portal\Subscriptions;

use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Subscription')]
class SubscriptionShow extends Component
{
    public Subscription $subscription;

    public bool $requestingCancel = false;
    public ?string $cancelReason = null;

    public function mount(Subscription $subscription): void
    {
        // Clients may only view their own subscriptions.
        abort_unless($subscription->client_id === Auth::guard('client')->id(), 403);

        $this->subscription = $subscription->load('client');
    }

    public function cancelRequest(): void
    {
        $company = company_settings();
        $to = $company->email ?: $company->reply_to_email;
        $client = $this->subscription->client;

        if ($to) {
            $body = "Cancellation request from {$client->name} ({$client->email}).\n\n"
                . "Subscription: {$this->subscription->subscription_number} — {$this->subscription->name}\n"
                . 'Reason: ' . ($this->cancelReason ?: 'Not provided');

            Mail::raw($body, function ($message) use ($to, $client) {
                $message->to($to)
                    ->subject("Subscription cancellation request — {$this->subscription->subscription_number}")
                    ->replyTo($client->email, $client->name);
            });
        }

        $this->requestingCancel = false;
        $this->cancelReason = null;
        $this->dispatch('toast', type: 'success', message: 'Cancellation request sent. We will be in touch shortly.');
    }

    public function render()
    {
        $invoices = $this->subscription->invoices()->latest('invoices.created_at')->get();

        return view('livewire.portal.subscriptions.subscription-show', [
            'invoices' => $invoices,
        ]);
    }
}
