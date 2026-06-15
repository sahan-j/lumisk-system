<?php

namespace App\Livewire\Admin\Reports;

use App\Livewire\Concerns\WithDateRange;
use App\Models\Client;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Client Report')]
class ClientReport extends Component
{
    use WithDateRange;

    /** @return \Illuminate\Support\Collection<int, array> */
    protected function clientStats()
    {
        [$from, $to] = $this->dateRange();

        return Client::with('invoices')->get()->map(function ($client) use ($from, $to) {
            $invoicesInRange = $client->invoices->whereBetween('issue_date', [$from, $to]);
            $totalInvoiced = (float) $invoicesInRange->sum('total');
            $totalPaid = (float) Payment::whereHas('invoice', fn ($q) => $q->where('client_id', $client->id))
                ->whereBetween('payment_date', [$from, $to])->sum('amount');

            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'invoice_count' => $invoicesInRange->count(),
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'outstanding' => max(0, $totalInvoiced - $totalPaid),
            ];
        })->sortByDesc('total_paid')->values();
    }

    public function export()
    {
        $stats = $this->clientStats();

        return response()->streamDownload(function () use ($stats) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Client', 'Email', 'Invoices', 'Total Invoiced', 'Total Paid', 'Outstanding']);
            foreach ($stats as $s) {
                fputcsv($handle, [
                    $s['name'], $s['email'], $s['invoice_count'],
                    number_format($s['total_invoiced'], 2, '.', ''),
                    number_format($s['total_paid'], 2, '.', ''),
                    number_format($s['outstanding'], 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'client-report-' . $this->rangeSuffix() . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();
        $clientStats = $this->clientStats();

        return view('livewire.admin.reports.client-report', [
            'from' => $from,
            'to' => $to,
            'clientStats' => $clientStats,
            'topClient' => $clientStats->first(),
            'totalClients' => $clientStats->count(),
            'totalInvoiced' => $clientStats->sum('total_invoiced'),
            'totalPaid' => $clientStats->sum('total_paid'),
            'totalOutstanding' => $clientStats->sum('outstanding'),
            'paidMax' => $clientStats->max('total_paid') ?: 1,
        ]);
    }
}
