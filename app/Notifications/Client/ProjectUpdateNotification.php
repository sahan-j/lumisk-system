<?php

namespace App\Notifications\Client;

use App\Models\Project;
use App\Notifications\BaseDatabaseNotification;

class ProjectUpdateNotification extends BaseDatabaseNotification
{
    public function __construct(public Project $project) {}

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Project Update',
            'message' => "Project '{$this->project->name}' status changed to " . $this->project->statusLabel(),
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'color' => '#6d5cff',
            'url' => route('portal.projects.show', $this->project),
            'type' => 'project_update',
        ];
    }
}
