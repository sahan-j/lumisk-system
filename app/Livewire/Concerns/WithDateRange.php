<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * Shared date-range filtering for report components.
 * Exposes `period`, `dateFrom`, `dateTo` (URL-bound) and resolves them to a
 * [Carbon $from, Carbon $to] window via dateRange().
 */
trait WithDateRange
{
    #[Url]
    public string $period = 'this_month';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public const PERIODS = [
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_quarter' => 'This Quarter',
        'this_year' => 'This Year',
        'last_year' => 'Last Year',
        'custom' => 'Custom Range',
    ];

    public function updatingPeriod(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingDateFrom(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatingDateTo(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function dateRange(): array
    {
        return match ($this->period) {
            'today' => [today(), today()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            'custom' => [
                ($this->dateFrom ? Carbon::parse($this->dateFrom) : now()->startOfMonth())->startOfDay(),
                ($this->dateTo ? Carbon::parse($this->dateTo) : now()->endOfMonth())->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    /** Suffix for export filenames, e.g. 2026-06-01-to-2026-06-30. */
    protected function rangeSuffix(): string
    {
        [$from, $to] = $this->dateRange();

        return $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d');
    }
}
