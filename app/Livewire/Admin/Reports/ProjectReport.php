<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Expense;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Project Financials')]
class ProjectReport extends Component
{
    /** @return \Illuminate\Support\Collection<int, array> */
    protected function projectStats()
    {
        // Total expenses per project in one grouped query.
        $expensesByProject = Expense::whereNotNull('project_id')
            ->selectRaw('project_id, SUM(amount) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        return Project::with(['client', 'tasks', 'invoices.payments'])
            ->get()
            ->map(function ($project) use ($expensesByProject) {
                $totalInvoiced = (float) $project->invoices->sum('total');
                $totalPaid = (float) $project->invoices->flatMap->payments->sum('amount');
                $totalExpenses = (float) ($expensesByProject[$project->id] ?? 0);

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client' => $project->client?->name ?? '—',
                    'status' => $project->status,
                    'status_color' => $project->statusColor(),
                    'status_label' => $project->statusLabel(),
                    'completion' => $project->completion_percentage,
                    'total_tasks' => $project->tasks->count(),
                    'done_tasks' => $project->tasks->where('status', 'done')->count(),
                    'total_invoiced' => $totalInvoiced,
                    'total_paid' => $totalPaid,
                    'total_expenses' => $totalExpenses,
                    'profit' => round($totalPaid - $totalExpenses, 2),
                ];
            });
    }

    public function export()
    {
        $stats = $this->projectStats();

        return response()->streamDownload(function () use ($stats) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Project', 'Client', 'Status', 'Completion %', 'Tasks', 'Invoiced', 'Paid', 'Expenses', 'Profit']);
            foreach ($stats as $s) {
                fputcsv($handle, [
                    $s['name'], $s['client'], $s['status_label'], $s['completion'],
                    "{$s['done_tasks']}/{$s['total_tasks']}",
                    number_format($s['total_invoiced'], 2, '.', ''),
                    number_format($s['total_paid'], 2, '.', ''),
                    number_format($s['total_expenses'], 2, '.', ''),
                    number_format($s['profit'], 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'project-financials-' . today()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        $projectStats = $this->projectStats();

        return view('livewire.admin.reports.project-report', [
            'projectStats' => $projectStats,
            'totalProjects' => $projectStats->count(),
            'totalInvoiced' => $projectStats->sum('total_invoiced'),
            'totalPaid' => $projectStats->sum('total_paid'),
            'totalExpenses' => $projectStats->sum('total_expenses'),
            'totalProfit' => $projectStats->sum('profit'),
        ]);
    }
}
