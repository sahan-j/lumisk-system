<?php

namespace App\Livewire\Admin\Estimates;

use App\Models\Estimate;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Estimates')]
class EstimatesIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public array $selected = [];
    public bool $selectAll = false;
    public array $pageIds = [];

    public function updating($name): void
    {
        if (in_array($name, ['search', 'status', 'from', 'to'])) {
            $this->resetPage();
            $this->reset('selected', 'selectAll');
        }
    }

    public function updatedSelectAll($value): void
    {
        $this->selected = $value ? array_map('strval', $this->pageIds) : [];
    }

    public function bulkMarkSent(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Estimate::whereIn('id', $this->selected)->where('status', 'draft')->update(['status' => 'sent']);
        $this->reset('selected', 'selectAll');
        $this->dispatch('toast', type: 'success', message: "{$count} marked as sent");
    }

    public function bulkDelete(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('estimates.delete'), 403);

        if (empty($this->selected)) {
            return;
        }

        $count = Estimate::whereIn('id', $this->selected)->count();
        Estimate::whereIn('id', $this->selected)->delete();
        $this->reset('selected', 'selectAll');
        $this->dispatch('toast', type: 'success', message: "{$count} items deleted");
    }

    public function bulkExportCsv()
    {
        if (empty($this->selected)) {
            return;
        }

        $records = Estimate::with('client')->whereIn('id', $this->selected)->latest()->get();
        $filename = 'estimates-export-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Number', 'Client', 'Issue Date', 'Expiry Date', 'Status', 'Total']);
            foreach ($records as $r) {
                fputcsv($out, [
                    $r->estimate_number,
                    $r->client?->name,
                    $r->issue_date?->format('Y-m-d'),
                    $r->expiry_date?->format('Y-m-d'),
                    $r->status,
                    number_format((float) $r->total, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function duplicate(int $id): void
    {
        $original = Estimate::with('items')->findOrFail($id);

        DB::transaction(function () use ($original) {
            $copy = $original->replicate(['estimate_number', 'client_note']);
            $copy->estimate_number = DocumentNumberService::nextEstimateNumber();
            $copy->status = 'draft';
            $copy->issue_date = now();
            $copy->expiry_date = now()->addDays((int) company_settings()->estimate_expiry_days);
            $copy->client_note = null;
            $copy->save();

            foreach ($original->items as $item) {
                $copy->items()->create($item->only(['name', 'description', 'quantity', 'unit_price', 'total', 'order']));
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Estimate duplicated.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('estimates.delete'), 403);

        if ($this->deleteId) {
            Estimate::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Estimate deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $estimates = Estimate::query()
            ->with('client')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('estimate_number', 'like', "%{$this->search}%")
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->from, fn ($q) => $q->whereDate('issue_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('issue_date', '<=', $this->to))
            ->latest()
            ->paginate(15);

        $this->pageIds = $estimates->pluck('id')->all();

        return view('livewire.admin.estimates.estimates-index', [
            'estimates' => $estimates,
            'statuses' => Estimate::STATUSES,
        ]);
    }
}
