<?php

namespace App\Livewire\Portal\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.portal')]
#[Title('Projects')]
class ProjectsIndex extends Component
{
    public function render()
    {
        $projects = Project::query()
            ->where('client_id', Auth::guard('client')->id())
            ->where('status', '!=', 'cancelled')
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            ])
            ->with('tasks')
            ->latest()
            ->get();

        return view('livewire.portal.projects.projects-index', [
            'projects' => $projects,
        ]);
    }
}
