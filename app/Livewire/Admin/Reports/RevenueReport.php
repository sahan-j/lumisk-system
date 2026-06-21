<?php

namespace App\Livewire\Admin\Reports;

use App\Livewire\Concerns\WithDateRange;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Revenue Report')]
class RevenueReport extends Component
{
    use WithDateRange, WithPagination;

    /** Revenue is recognised on the payment date (not row creation). */
    public function export()
    {
        [$from, $to] = $this->dateRange();

        return response()->streamDownload(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice Number', 'Client', 'Issue Date', 'Due Date', 'Status', 'Subtotal', 'Tax', 'Total', 'Amount Paid', 'Outstanding']);

            Invoice::with(['client', 'payments'])
                ->whereBetween('issue_date', [$from, $to])
                ->orderBy('issue_date')
                ->chunk(100, function ($invoices) use ($handle) {
                    foreach ($invoices as $inv) {
                        fputcsv($handle, [
                            $inv->invoice_number,
                            $inv->client?->name ?? '—',
                            $inv->issue_date?->format('Y-m-d'),
                            $inv->due_date?->format('Y-m-d'),
                            ucfirst($inv->status),
                            number_format((float) $inv->subtotal, 2, '.', ''),
                            number_format((float) $inv->tax_amount, 2, '.', ''),
                            number_format((float) $inv->total, 2, '.', ''),
                            number_format((float) $inv->total_paid, 2, '.', ''),
                            number_format((float) $inv->outstanding_balance, 2, '.', ''),
                        ]);
                    }
                });

            fclose($handle);
        }, 'revenue-report-' . $this->rangeSuffix() . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();

        $totalRevenue = Payment::whereBetween('payment_date', [$from, $to])->sum('amount');

        // Credit notes issued in the period (a deduction); net revenue subtracts what was applied.
        $creditNotesIssued = (float) CreditNote::where('status', '!=', 'void')
            ->whereBetween('issue_date', [$from, $to])->sum('total');
        $creditNotesApplied = (float) CreditNote::whereBetween('issue_date', [$from, $to])->sum('amount_applied');
        $netRevenue = round((float) $totalRevenue - $creditNotesApplied, 2);

        // Rolling last-12-months revenue trend (period-independent).
        $monthly = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $monthly->push([
                'label' => $month->format('M'),
                'value' => round((float) Payment::whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)->sum('amount'), 2),
            ]);
        }

        $revenueByClient = Payment::with('invoice.client')
            ->whereBetween('payment_date', [$from, $to])
            ->get()
            ->groupBy(fn ($p) => $p->invoice?->client?->name ?? '—')
            ->map(fn ($payments) => (float) $payments->sum('amount'))
            ->sortDesc()
            ->take(10);

        $invoiceStats = [
            'total' => Invoice::whereBetween('issue_date', [$from, $to])->count(),
            'paid' => Invoice::whereBetween('issue_date', [$from, $to])->where('status', 'paid')->count(),
            'outstanding' => Invoice::whereBetween('issue_date', [$from, $to])->whereIn('status', ['sent', 'overdue'])->sum('total'),
            'overdue' => Invoice::where('status', 'overdue')->count(),
        ];

        // Per-currency breakdown of invoices issued in the period (original + LKR equivalent).
        $currencyBreakdown = Invoice::whereBetween('issue_date', [$from, $to])
            ->get(['currency_code', 'total', 'total_lkr'])
            ->groupBy('currency_code')
            ->map(fn ($group, $code) => [
                'code' => $code,
                'count' => $group->count(),
                'total' => (float) $group->sum('total'),
                'lkr' => (float) $group->sum('total_lkr'),
            ])
            ->sortByDesc('lkr')
            ->values();

        $invoices = Invoice::with(['client', 'payments'])
            ->whereBetween('issue_date', [$from, $to])
            ->orderByDesc('issue_date')
            ->paginate(20);

        return view('livewire.admin.reports.revenue-report', [
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'creditNotesIssued' => $creditNotesIssued,
            'creditNotesApplied' => $creditNotesApplied,
            'netRevenue' => $netRevenue,
            'chartLabels' => $monthly->pluck('label'),
            'chartValues' => $monthly->pluck('value'),
            'revenueByClient' => $revenueByClient,
            'clientRevenueMax' => $revenueByClient->max() ?: 1,
            'invoiceStats' => $invoiceStats,
            'currencyBreakdown' => $currencyBreakdown,
            'invoices' => $invoices,
        ]);
    }
}
