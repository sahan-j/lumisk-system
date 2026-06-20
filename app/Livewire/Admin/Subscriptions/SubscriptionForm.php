<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Subscription')]
class SubscriptionForm extends Component
{
    public ?Subscription $subscription = null;

    public ?int $client_id = null;
    public ?int $plan_id = null;
    public string $name = '';
    public ?string $description = null;
    public ?float $amount = null;
    public string $billing_cycle = 'monthly';
    public string $start_date = '';
    public ?string $end_date = null;
    public ?int $trial_days = 0;
    public ?string $next_billing_date = null;
    public bool $auto_invoice = true;
    public bool $auto_send_invoice = true;
    public ?string $notes = null;
    public string $status = 'active';

    public function mount(?Subscription $subscription = null): void
    {
        if ($subscription && $subscription->exists) {
            $this->subscription = $subscription;
            $this->client_id = $subscription->client_id;
            $this->plan_id = $subscription->plan_id;
            $this->name = $subscription->name;
            $this->description = $subscription->description;
            $this->amount = (float) $subscription->amount;
            $this->billing_cycle = $subscription->billing_cycle;
            $this->start_date = $subscription->start_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $this->end_date = $subscription->end_date?->format('Y-m-d');
            $this->trial_days = 0;
            $this->next_billing_date = $subscription->next_billing_date?->format('Y-m-d');
            $this->auto_invoice = (bool) $subscription->auto_invoice;
            $this->auto_send_invoice = (bool) $subscription->auto_send_invoice;
            $this->notes = $subscription->notes;
            $this->status = $subscription->status;
        } else {
            $this->start_date = now()->format('Y-m-d');
            $this->client_id = (int) request('client') ?: null;
        }
    }

    /** Auto-fill fields from the chosen plan. */
    public function selectPlan(int $planId): void
    {
        $plan = SubscriptionPlan::find($planId);
        if (! $plan) {
            return;
        }

        $this->plan_id = $plan->id;
        $this->name = $plan->name;
        $this->amount = (float) $plan->amount;
        $this->billing_cycle = $plan->billing_cycle;
        $this->trial_days = (int) $plan->trial_days;
        if ($plan->description) {
            $this->description = $plan->description;
        }
    }

    public function clearPlan(): void
    {
        $this->plan_id = null;
    }

    protected function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:' . implode(',', Subscription::BILLING_CYCLES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'next_billing_date' => ['nullable', 'date'],
            'auto_invoice' => ['boolean'],
            'auto_send_invoice' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', Subscription::STATUSES)],
        ];
    }

    public function save()
    {
        abort_unless((bool) auth()->user()?->hasPermission($this->subscription ? 'subscriptions.edit' : 'subscriptions.create'), 403);

        $validated = $this->validate();

        $data = [
            'client_id' => $validated['client_id'],
            'plan_id' => $validated['plan_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'currency' => company_settings()->currency ?: 'LKR',
            'billing_cycle' => $validated['billing_cycle'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'auto_invoice' => $this->auto_invoice,
            'auto_send_invoice' => $this->auto_send_invoice,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($this->subscription) {
            $data['status'] = $validated['status'];
            $data['next_billing_date'] = $this->next_billing_date ?: $this->subscription->next_billing_date;
            $this->subscription->update($data);
            $sub = $this->subscription;
            $message = 'Subscription updated!';
        } else {
            $start = Carbon::parse($validated['start_date']);
            $trialDays = (int) ($validated['trial_days'] ?? 0);

            if ($trialDays > 0) {
                $data['status'] = 'trial';
                $data['trial_end_date'] = $start->copy()->addDays($trialDays);
                $data['next_billing_date'] = $start->copy()->addDays($trialDays + 1);
            } else {
                $data['status'] = 'active';
                $data['next_billing_date'] = $start->copy()->addDays(SubscriptionPlan::cycleDays($validated['billing_cycle']));
            }

            $data['subscription_number'] = Subscription::generateNumber();
            $data['created_by'] = auth()->user()?->name;

            $sub = Subscription::create($data);

            ActivityLog::log('subscription_created',
                "Subscription {$sub->subscription_number} created for {$sub->client?->name}",
                ['subject_type' => 'Subscription', 'subject_id' => $sub->id,
                 'subject_label' => $sub->subscription_number, 'client_id' => $sub->client_id]);

            $message = "Subscription {$sub->subscription_number} created!";
        }

        $this->dispatch('toast', type: 'success', message: $message);

        return $this->redirect(route('admin.subscriptions.show', $sub), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.subscriptions.subscription-form', [
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company_name']),
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('name')->get(),
            'cycles' => Subscription::BILLING_CYCLES,
            'statuses' => Subscription::STATUSES,
        ]);
    }
}
