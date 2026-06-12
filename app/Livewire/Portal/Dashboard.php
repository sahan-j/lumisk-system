<?php

namespace App\Livewire\Portal;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $client = Auth::guard('client')->user();

        $totalInvoices = $client->invoices()->count();
        $unpaidAmount = $client->invoices()->whereIn('status', ['sent', 'overdue'])
            ->with('payments')
            ->get()
            ->sum('outstanding_balance');
        $paidAmount = Payment::whereHas('invoice', fn ($q) => $q->where('client_id', $client->id))->sum('amount');
        $pendingEstimates = $client->estimates()->where('status', 'sent')->count();

        $recentInvoices = $client->invoices()->latest()->take(5)->get();
        $recentEstimates = $client->estimates()->latest()->take(5)->get();

        return view('livewire.portal.dashboard', [
            'client' => $client,
            'totalInvoices' => $totalInvoices,
            'unpaidAmount' => $unpaidAmount,
            'paidAmount' => $paidAmount,
            'pendingEstimates' => $pendingEstimates,
            'recentInvoices' => $recentInvoices,
            'recentEstimates' => $recentEstimates,
        ]);
    }
}
