<?php

namespace App\Livewire\Admin\Reports;

use App\Livewire\Concerns\WithDateRange;
use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Tax Report')]
class TaxReport extends Component
{
    use WithDateRange, WithPagination;

    public function export()
    {
        [$from, $to] = $this->dateRange();

        return response()->streamDownload(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice No', 'Client', 'Date', 'Subtotal', 'Tax Rate', 'Tax Amount', 'Total']);

            Invoice::with('client')
                ->whereBetween('issue_date', [$from, $to])
                ->where('tax_amount', '>', 0)
                ->orderBy('issue_date')
                ->chunk(100, function ($invoices) use ($handle) {
                    foreach ($invoices as $inv) {
                        fputcsv($handle, [
                            $inv->invoice_number,
                            $inv->client?->name ?? '—',
                            $inv->issue_date?->format('Y-m-d'),
                            number_format((float) $inv->subtotal, 2, '.', ''),
                            rtrim(rtrim(number_format((float) $inv->tax_rate, 2), '0'), '.') . '%',
                            number_format((float) $inv->tax_amount, 2, '.', ''),
                            number_format((float) $inv->total, 2, '.', ''),
                        ]);
                    }
                });

            fclose($handle);
        }, 'tax-report-' . $this->rangeSuffix() . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();

        $taxByRate = Invoice::whereBetween('issue_date', [$from, $to])
            ->where('status', '!=', 'draft')
            ->where('tax_rate', '>', 0)
            ->selectRaw('tax_rate, SUM(tax_amount) as total_tax, SUM(subtotal) as total_subtotal, SUM(total) as total_amount, COUNT(*) as invoice_count')
            ->groupBy('tax_rate')
            ->orderBy('tax_rate')
            ->get();

        $totalTaxCollected = (float) $taxByRate->sum('total_tax');
        $totalTaxableAmount = (float) $taxByRate->sum('total_subtotal');

        // Rolling last-12-months tax collected.
        $monthly = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $monthly->push([
                'label' => $month->format('M'),
                'value' => round((float) Invoice::where('status', '!=', 'draft')
                    ->whereYear('issue_date', $month->year)->whereMonth('issue_date', $month->month)
                    ->sum('tax_amount'), 2),
            ]);
        }

        $taxInvoices = Invoice::with('client')
            ->whereBetween('issue_date', [$from, $to])
            ->where('tax_amount', '>', 0)
            ->orderByDesc('issue_date')
            ->paginate(20);

        return view('livewire.admin.reports.tax-report', [
            'from' => $from,
            'to' => $to,
            'taxByRate' => $taxByRate,
            'totalTaxCollected' => $totalTaxCollected,
            'totalTaxableAmount' => $totalTaxableAmount,
            'effectiveRate' => $totalTaxableAmount > 0 ? round($totalTaxCollected / $totalTaxableAmount * 100, 1) : 0,
            'chartLabels' => $monthly->pluck('label'),
            'chartValues' => $monthly->pluck('value'),
            'taxInvoices' => $taxInvoices,
        ]);
    }
}
