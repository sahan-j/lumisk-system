<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Invoice Aging')]
class InvoiceAgingReport extends Component
{
    /** Bucket an invoice by how far past its due date it is. */
    public static function bucketFor(Invoice $invoice): string
    {
        if (! $invoice->due_date || $invoice->due_date->gte(today())) {
            return 'current';
        }
        $days = (int) today()->diffInDays($invoice->due_date);

        return match (true) {
            $days <= 30 => '1_30',
            $days <= 60 => '31_60',
            $days <= 90 => '61_90',
            default => '90_plus',
        };
    }

    /** @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection> */
    protected function buckets()
    {
        $unpaid = Invoice::with('client')
            ->whereIn('status', ['sent', 'overdue'])
            ->orderBy('due_date')
            ->get();

        return collect(['current' => collect(), '1_30' => collect(), '31_60' => collect(), '61_90' => collect(), '90_plus' => collect()])
            ->map(fn ($_, $key) => $unpaid->filter(fn ($inv) => static::bucketFor($inv) === $key)->values());
    }

    public function export()
    {
        $buckets = $this->buckets();
        $labels = ['current' => 'Current', '1_30' => '1-30 days', '31_60' => '31-60 days', '61_90' => '61-90 days', '90_plus' => '90+ days'];

        return response()->streamDownload(function () use ($buckets, $labels) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice No', 'Client', 'Issue Date', 'Due Date', 'Days Overdue', 'Amount', 'Bucket']);
            foreach ($buckets as $key => $invoices) {
                foreach ($invoices as $inv) {
                    $days = $inv->due_date && $inv->due_date->lt(today()) ? (int) today()->diffInDays($inv->due_date) : 0;
                    fputcsv($handle, [
                        $inv->invoice_number,
                        $inv->client?->name ?? '—',
                        $inv->issue_date?->format('Y-m-d'),
                        $inv->due_date?->format('Y-m-d'),
                        $days,
                        number_format((float) $inv->total, 2, '.', ''),
                        $labels[$key],
                    ]);
                }
            }
            fclose($handle);
        }, 'invoice-aging-' . today()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        $buckets = $this->buckets();

        $summary = $buckets->map(fn ($invoices) => [
            'count' => $invoices->count(),
            'total' => (float) $invoices->sum('total'),
        ]);

        return view('livewire.admin.reports.invoice-aging-report', [
            'buckets' => $buckets,
            'summary' => $summary,
        ]);
    }
}
