<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\ActivityLog;
use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Subscription')]
class SubscriptionShow extends Component
{
    public Subscription $subscription;

    public bool $confirmingCancel = false;
    public ?string $cancellationReason = null;

    public function mount(Subscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function generateInvoiceNow()
    {
        abort_unless((bool) auth()->user()?->hasPermission('subscriptions.edit'), 403);

        $invoice = $this->subscription->generateInvoice();

        ActivityLog::log('subscription_billed',
            "Invoice {$invoice->invoice_number} generated for {$this->subscription->name}",
            ['subject_type' => 'Subscription', 'subject_id' => $this->subscription->id,
             'subject_label' => $this->subscription->subscription_number, 'client_id' => $this->subscription->client_id]);

        $this->dispatch('toast', type: 'success', message: "Invoice {$invoice->invoice_number} generated!");

        return $this->redirect(route('admin.invoices.show', $invoice), navigate: true);
    }

    public function pause(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('subscriptions.edit'), 403);

        $this->subscription->update(['status' => 'paused']);
        $this->subscription->refresh();
        $this->dispatch('toast', type: 'success', message: 'Subscription paused.');
    }

    public function resume(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('subscriptions.edit'), 403);

        $data = ['status' => 'active'];

        // If the next billing date is in the past, roll it forward from today.
        if ($this->subscription->next_billing_date && $this->subscription->next_billing_date->isPast()) {
            $data['next_billing_date'] = $this->subscription->calculateNextBillingDate(today());
        }

        $this->subscription->update($data);
        $this->subscription->refresh();
        $this->dispatch('toast', type: 'success', message: 'Subscription resumed.');
    }

    public function cancel(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('subscriptions.edit'), 403);

        $this->subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $this->cancellationReason,
        ]);
        $this->subscription->refresh();

        ActivityLog::log('subscription_cancelled',
            "Subscription {$this->subscription->subscription_number} cancelled",
            ['subject_type' => 'Subscription', 'subject_id' => $this->subscription->id,
             'subject_label' => $this->subscription->subscription_number, 'client_id' => $this->subscription->client_id]);

        $this->confirmingCancel = false;
        $this->dispatch('toast', type: 'success', message: 'Subscription cancelled.');
    }

    public function render()
    {
        $this->subscription->load(['client', 'plan']);
        $invoices = $this->subscription->invoices()->latest('invoices.created_at')->get();

        // Next 6 billing dates from the current next-billing date.
        $forecast = [];
        if ($this->subscription->next_billing_date && in_array($this->subscription->status, ['active', 'trial'])) {
            $date = $this->subscription->next_billing_date->copy();
            for ($i = 0; $i < 6; $i++) {
                $forecast[] = $date->copy();
                $date = $this->subscription->calculateNextBillingDate($date);
            }
        }

        return view('livewire.admin.subscriptions.subscription-show', [
            'invoices' => $invoices,
            'forecast' => $forecast,
        ]);
    }
}
