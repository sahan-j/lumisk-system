@props(['from', 'to'])

{{-- Renders inside a Livewire report component; binds to period/dateFrom/dateTo. --}}
<div class="mb-6 flex flex-wrap items-center gap-3">
    <select wire:model.live="period" class="form-input-base w-auto">
        @foreach (['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year', 'last_year' => 'Last Year', 'custom' => 'Custom Range'] as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
    </select>

    <div x-data x-show="$wire.period === 'custom'" x-cloak class="flex flex-wrap items-center gap-2">
        <input type="date" wire:model.live="dateFrom" class="form-input-base w-auto">
        <span class="text-sm text-gray-400">to</span>
        <input type="date" wire:model.live="dateTo" class="form-input-base w-auto">
    </div>

    <span class="text-xs text-gray-400">{{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}</span>
</div>
