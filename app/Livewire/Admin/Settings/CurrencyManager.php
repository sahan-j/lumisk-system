<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Currency;
use Livewire\Component;

class CurrencyManager extends Component
{
    /** @var array<string, float|string> keyed by currency id */
    public array $rates = [];

    public function mount(): void
    {
        foreach (Currency::all() as $currency) {
            $this->rates[$currency->id] = (float) $currency->exchange_rate;
        }
    }

    public function updateRate(int $id): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('settings.edit'), 403);

        $currency = Currency::findOrFail($id);
        if ($currency->is_default) {
            return; // LKR base rate is fixed at 1.
        }

        $rate = (float) ($this->rates[$id] ?? 0);
        if ($rate < 0.0001) {
            $this->dispatch('toast', type: 'error', message: 'Exchange rate must be greater than zero.');

            return;
        }

        $currency->update(['exchange_rate' => $rate, 'updated_at_rate' => now()]);
        $this->dispatch('toast', type: 'success', message: "{$currency->code} rate updated.");
    }

    public function toggle(int $id): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('settings.edit'), 403);

        $currency = Currency::findOrFail($id);
        if ($currency->is_default) {
            return; // LKR can't be deactivated.
        }

        $currency->update(['is_active' => ! $currency->is_active]);
        $this->dispatch('toast', type: 'success', message: "{$currency->code} " . ($currency->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function render()
    {
        return view('livewire.admin.settings.currency-manager', [
            'currencies' => Currency::orderByDesc('is_default')->orderBy('code')->get(),
        ]);
    }
}
