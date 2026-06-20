<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\SubscriptionPlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Subscription Plans')]
class PlansIndex extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public ?string $description = null;
    public ?float $amount = null;
    public string $billing_cycle = 'monthly';
    public ?int $trial_days = 0;
    public bool $is_active = true;
    public array $features = [];
    public string $newFeature = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    protected function guard(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('subscriptions.manage_plans'), 403);
    }

    public function openCreate(): void
    {
        $this->guard();
        $this->reset(['editingId', 'name', 'description', 'amount', 'billing_cycle', 'trial_days', 'is_active', 'features', 'newFeature']);
        $this->billing_cycle = 'monthly';
        $this->is_active = true;
        $this->trial_days = 0;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $this->guard();
        $plan = SubscriptionPlan::findOrFail($id);
        $this->editingId = $plan->id;
        $this->name = $plan->name;
        $this->description = $plan->description;
        $this->amount = (float) $plan->amount;
        $this->billing_cycle = $plan->billing_cycle;
        $this->trial_days = (int) $plan->trial_days;
        $this->is_active = (bool) $plan->is_active;
        $this->features = $plan->features ?? [];
        $this->newFeature = '';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function addFeature(): void
    {
        $value = trim($this->newFeature);
        if ($value !== '') {
            $this->features[] = $value;
            $this->newFeature = '';
        }
    }

    public function removeFeature(int $index): void
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function save(): void
    {
        $this->guard();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:' . implode(',', SubscriptionPlan::BILLING_CYCLES)],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'currency' => company_settings()->currency ?: 'LKR',
            'billing_cycle' => $validated['billing_cycle'],
            'billing_cycle_days' => SubscriptionPlan::cycleDays($validated['billing_cycle']),
            'trial_days' => (int) ($validated['trial_days'] ?? 0),
            'is_active' => $this->is_active,
            'features' => array_values(array_filter($this->features)),
        ];

        if ($this->editingId) {
            SubscriptionPlan::findOrFail($this->editingId)->update($data);
            $message = 'Plan updated!';
        } else {
            SubscriptionPlan::create($data);
            $message = 'Plan created!';
        }

        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        $this->guard();
        if ($this->deleteId) {
            SubscriptionPlan::find($this->deleteId)?->delete();
            $this->dispatch('toast', type: 'success', message: 'Plan deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $plans = SubscriptionPlan::withCount(['subscriptions as active_count' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return view('livewire.admin.subscriptions.plans-index', [
            'plans' => $plans,
            'cycles' => SubscriptionPlan::BILLING_CYCLES,
        ]);
    }
}
