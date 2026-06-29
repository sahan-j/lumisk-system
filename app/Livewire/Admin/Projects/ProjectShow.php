<?php

namespace App\Livewire\Admin\Projects;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Project')]
class ProjectShow extends Component
{
    public Project $project;

    // Inline add-task form
    public bool $showTaskForm = false;
    public string $taskTitle = '';
    public string $taskPriority = 'medium';
    public ?string $taskDueDate = null;

    // Link-invoice modal
    public bool $showLinkInvoice = false;
    public ?int $linkInvoiceId = null;

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function addTask(): void
    {
        $validated = $this->validate([
            'taskTitle' => ['required', 'string', 'max:255'],
            'taskPriority' => ['required', 'in:' . implode(',', Task::PRIORITIES)],
            'taskDueDate' => ['nullable', 'date'],
        ]);

        $maxOrder = (int) $this->project->tasks()->max('sort_order');

        $this->project->tasks()->create([
            'title' => $validated['taskTitle'],
            'priority' => $validated['taskPriority'],
            'due_date' => $validated['taskDueDate'] ?: null,
            'status' => 'todo',
            'sort_order' => $maxOrder + 1,
        ]);

        $this->reset('taskTitle', 'taskPriority', 'taskDueDate', 'showTaskForm');
        $this->dispatch('toast', type: 'success', message: 'Task added.');
    }

    public function toggleTask(int $taskId): void
    {
        $task = $this->project->tasks()->findOrFail($taskId);
        if ($task->status === 'done') {
            $task->markTodo();
        } else {
            $task->markDone();
            ActivityLog::log('task_completed',
                "Task \"{$task->title}\" completed in {$this->project->name}",
                ['subject_type' => 'Project', 'subject_id' => $this->project->id,
                 'subject_label' => $this->project->name, 'client_id' => $this->project->client_id]);
        }
    }

    public function deleteTask(int $taskId): void
    {
        $this->project->tasks()->findOrFail($taskId)->delete();
        $this->dispatch('toast', type: 'success', message: 'Task deleted.');
    }

    public function updateStatus(string $status): void
    {
        if (! in_array($status, Project::STATUSES, true)) {
            return;
        }

        $this->project->status = $status;
        if ($status === 'completed' && ! $this->project->completed_at) {
            $this->project->completed_at = now();
        } elseif ($status !== 'completed') {
            $this->project->completed_at = null;
        }
        $this->project->save();

        if ($status === 'completed') {
            ActivityLog::log('project_completed', "Project \"{$this->project->name}\" completed",
                ['subject_type' => 'Project', 'subject_id' => $this->project->id,
                 'subject_label' => $this->project->name, 'client_id' => $this->project->client_id]);
        }

        // Keep the client informed of project status changes.
        if ($this->project->client_id) {
            $this->project->client?->notify(new \App\Notifications\Client\ProjectUpdateNotification($this->project));
        }

        $this->dispatch('toast', type: 'success', message: 'Status updated.');
    }

    public function linkInvoice(): void
    {
        $this->validate(['linkInvoiceId' => ['required', 'exists:invoices,id']]);

        $this->project->invoices()->syncWithoutDetaching([$this->linkInvoiceId]);
        $this->reset('linkInvoiceId', 'showLinkInvoice');
        $this->dispatch('toast', type: 'success', message: 'Invoice linked.');
    }

    public function unlinkInvoice(int $invoiceId): void
    {
        $this->project->invoices()->detach($invoiceId);
        $this->dispatch('toast', type: 'success', message: 'Invoice unlinked.');
    }

    public function render()
    {
        $this->project->load(['client', 'tasks', 'invoices.client']);

        // Invoices available to link: not already linked. Prefer same client.
        $linkedIds = $this->project->invoices->pluck('id');
        $availableInvoices = Invoice::with('client')
            ->whereNotIn('id', $linkedIds)
            ->when($this->project->client_id, fn ($q) => $q->where('client_id', $this->project->client_id))
            ->latest()
            ->take(100)
            ->get();

        return view('livewire.admin.projects.project-show', [
            'availableInvoices' => $availableInvoices,
            'invoicedTotal' => $this->project->invoices->sum('total'),
        ]);
    }
}
