<?php

namespace App\Livewire\Portal\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Layout('components.layouts.portal')]
#[Title('Project')]
class ProjectShow extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        // Ownership check — clients may only view their own projects.
        if ($project->client_id !== Auth::guard('client')->id()) {
            throw new NotFoundHttpException();
        }

        $this->project = $project->load(['tasks', 'invoices.payments']);
    }

    public function render()
    {
        return view('livewire.portal.projects.project-show');
    }
}
