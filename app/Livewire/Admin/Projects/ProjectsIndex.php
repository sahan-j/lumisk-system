<?php

namespace App\Livewire\Admin\Projects;

use App\Models\Client;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Projects')]
class ProjectsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $client = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingClient(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        abort_unless((bool) auth()->user()?->hasPermission('projects.delete'), 403);

        if ($this->deleteId) {
            Project::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Project deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $projects = Project::query()
            ->with('client')
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            ])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->client, fn ($q) => $q->where('client_id', $this->client))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.projects.projects-index', [
            'projects' => $projects,
            'statuses' => Project::STATUSES,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
