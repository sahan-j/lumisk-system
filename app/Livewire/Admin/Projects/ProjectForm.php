<?php

namespace App\Livewire\Admin\Projects;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Project')]
class ProjectForm extends Component
{
    public ?Project $project = null;

    public string $name = '';
    public ?string $description = null;
    public ?int $client_id = null;
    public string $status = 'planning';
    public string $priority = 'medium';
    public ?string $start_date = null;
    public ?string $due_date = null;
    public ?float $budget = null;
    public ?string $notes = null;

    /** @var array<int> */
    public array $invoice_ids = [];

    public function mount(?Project $project = null): void
    {
        if ($project && $project->exists) {
            $this->project = $project->load('invoices');
            $this->name = $project->name;
            $this->description = $project->description;
            $this->client_id = $project->client_id;
            $this->status = $project->status;
            $this->priority = $project->priority;
            $this->start_date = $project->start_date?->format('Y-m-d');
            $this->due_date = $project->due_date?->format('Y-m-d');
            $this->budget = $project->budget !== null ? (float) $project->budget : null;
            $this->notes = $project->notes;
            $this->invoice_ids = $project->invoices->pluck('id')->all();
        } else {
            $this->client_id = (int) request('client') ?: null;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'status' => ['required', 'in:' . implode(',', Project::STATUSES)],
            'priority' => ['required', 'in:' . implode(',', Project::PRIORITIES)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'invoice_ids' => ['nullable', 'array'],
            'invoice_ids.*' => ['exists:invoices,id'],
        ]);

        $project = $this->project ?? new Project();
        $isNew = ! $project->exists;
        $wasCompleted = $project->getOriginal('status') === 'completed';

        $project->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'client_id' => $validated['client_id'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Stamp completion time when moving into completed state.
        if ($project->status === 'completed' && ! $project->completed_at) {
            $project->completed_at = now();
        } elseif ($project->status !== 'completed') {
            $project->completed_at = null;
        }

        $project->save();
        $project->invoices()->sync($this->invoice_ids);

        if ($isNew) {
            ActivityLog::log('project_created', "Project \"{$project->name}\" created",
                ['subject_type' => 'Project', 'subject_id' => $project->id,
                 'subject_label' => $project->name, 'client_id' => $project->client_id]);
        }
        if ($project->status === 'completed' && ! $wasCompleted) {
            ActivityLog::log('project_completed', "Project \"{$project->name}\" completed",
                ['subject_type' => 'Project', 'subject_id' => $project->id,
                 'subject_label' => $project->name, 'client_id' => $project->client_id]);
        }

        $this->dispatch('toast', type: 'success', message: 'Project saved.');

        return $this->redirect(route('admin.projects.show', $project), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.projects.project-form', [
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company_name']),
            'invoices' => Invoice::with('client')->latest()->take(100)->get(),
        ]);
    }
}
