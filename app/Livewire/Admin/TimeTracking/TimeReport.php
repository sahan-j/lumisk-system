<?php

namespace App\Livewire\Admin\TimeTracking;

use App\Livewire\Concerns\WithDateRange;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Time Report')]
class TimeReport extends Component
{
    use WithDateRange;

    #[Url]
    public string $projectId = '';

    #[Url]
    public string $clientId = '';

    #[Url]
    public string $userId = '';

    #[Url]
    public string $billable = '';

    public ?int $billClientId = null;

    /** Base query for the current filter set (without ordering). */
    private function baseQuery()
    {
        [$from, $to] = $this->dateRange();

        return TimeEntry::with(['project', 'client'])
            ->whereNotNull('duration_minutes')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($this->projectId, fn ($q) => $q->where('project_id', $this->projectId))
            ->when($this->clientId, fn ($q) => $q->where('client_id', $this->clientId))
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->when($this->billable === 'billable', fn ($q) => $q->where('is_billable', true))
            ->when($this->billable === 'non_billable', fn ($q) => $q->where('is_billable', false));
    }

    public function export()
    {
        $entries = $this->baseQuery()->orderByDesc('date')->get();

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Staff', 'Description', 'Project', 'Client', 'Hours', 'Rate', 'Billable', 'Amount', 'Billed']);
            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->date?->format('Y-m-d'),
                    $e->user_name,
                    $e->description,
                    $e->project?->name ?? '—',
                    $e->client?->name ?? '—',
                    $e->duration_hours,
                    number_format((float) ($e->hourly_rate ?? 0), 2, '.', ''),
                    $e->is_billable ? 'Yes' : 'No',
                    number_format($e->billable_amount, 2, '.', ''),
                    $e->is_billed ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        }, 'time-report-' . $this->rangeSuffix() . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function billEntries()
    {
        abort_unless((bool) auth()->user()?->hasPermission('invoices.create'), 403);

        $this->validate(['billClientId' => ['required', 'exists:clients,id']]);

        $entries = $this->baseQuery()
            ->where('is_billable', true)
            ->where('is_billed', false)
            ->where('client_id', $this->billClientId)
            ->get();

        if ($entries->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No unbilled billable entries for that client in this range.');

            return;
        }

        $invoice = DB::transaction(function () use ($entries) {
            $client = Client::find($this->billClientId);

            $invoice = Invoice::create([
                'invoice_number' => DocumentNumberService::nextInvoiceNumber(),
                'client_id' => $this->billClientId,
                'status' => 'draft',
                'currency_code' => $client?->default_currency ?: 'LKR',
                'exchange_rate' => 1,
                'issue_date' => today(),
                'due_date' => today()->addDays(14),
                'tax_rate' => 0,
                'discount_amount' => 0,
                'notes' => 'Time-based invoice for ' . $entries->count() . ' time entr' . ($entries->count() === 1 ? 'y' : 'ies') . '.',
            ]);

            // One line per project + description grouping.
            $grouped = $entries->groupBy(fn ($e) => ($e->project?->name ?? 'General') . '|' . ($e->description ?? 'Time tracking'));

            $order = 0;
            foreach ($grouped as $key => $group) {
                [$project, $desc] = explode('|', $key, 2);
                $hours = round($group->sum('duration_minutes') / 60, 2);
                $rate = (float) ($group->first()->hourly_rate ?? 0);

                $invoice->items()->create([
                    'name' => $project . ' — ' . $desc,
                    'description' => $hours . ' hours @ ' . number_format($rate, 2) . '/hr',
                    'quantity' => $hours,
                    'unit_price' => $rate,
                    'total' => round($hours * $rate, 2),
                    'order' => $order++,
                ]);
            }

            $invoice->load('items');
            $invoice->recalculateTotals();
            $invoice->save();

            $entries->each(fn ($e) => $e->update(['is_billed' => true, 'billed_invoice_id' => $invoice->id]));

            ActivityLog::log('invoice_created',
                "Invoice {$invoice->invoice_number} created from time entries",
                ['subject_type' => 'Invoice', 'subject_id' => $invoice->id,
                 'subject_label' => $invoice->invoice_number, 'client_id' => $this->billClientId]);

            return $invoice;
        });

        $this->dispatch('toast', type: 'success', message: "Invoice {$invoice->invoice_number} created from time entries.");

        return $this->redirect(route('admin.invoices.show', $invoice), navigate: true);
    }

    public function render()
    {
        [$from, $to] = $this->dateRange();
        $entries = $this->baseQuery()->orderByDesc('date')->orderByDesc('started_at')->get();

        $byProject = $entries->groupBy(fn ($e) => $e->project?->name ?? 'No project')
            ->map(fn ($e) => [
                'minutes' => (int) $e->sum('duration_minutes'),
                'billable' => (float) $e->where('is_billable', true)->sum('billable_amount'),
                'count' => $e->count(),
            ])->sortByDesc('minutes');

        $byClient = $entries->groupBy(fn ($e) => $e->client?->name ?? 'No client')
            ->map(fn ($e) => [
                'minutes' => (int) $e->sum('duration_minutes'),
                'billable' => (float) $e->where('is_billable', true)->sum('billable_amount'),
                'unbilled' => (float) $e->where('is_billable', true)->where('is_billed', false)->sum('billable_amount'),
            ])->sortByDesc('minutes');

        $byDate = $entries->groupBy(fn ($e) => $e->date->format('Y-m-d'))
            ->map(fn ($e) => [
                'minutes' => (int) $e->sum('duration_minutes'),
                'count' => $e->count(),
            ])->sortKeysDesc();

        $unbilledByClient = $entries->where('is_billable', true)->where('is_billed', false)
            ->groupBy('client_id');

        return view('livewire.admin.time-tracking.time-report', [
            'entries' => $entries,
            'from' => $from,
            'to' => $to,
            'totalMinutes' => (int) $entries->sum('duration_minutes'),
            'billableMinutes' => (int) $entries->where('is_billable', true)->sum('duration_minutes'),
            'totalBillableAmount' => (float) $entries->sum('billable_amount'),
            'unbilledAmount' => (float) $entries->where('is_billable', true)->where('is_billed', false)->sum('billable_amount'),
            'byProject' => $byProject,
            'byClient' => $byClient,
            'byDate' => $byDate,
            'projectMax' => $byProject->max('minutes') ?: 1,
            'hasUnbilled' => $entries->where('is_billable', true)->where('is_billed', false)->isNotEmpty(),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
