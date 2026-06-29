<?php

namespace App\Notifications\Admin;

use App\Models\Lead;
use App\Notifications\BaseDatabaseNotification;

class NewLeadNotification extends BaseDatabaseNotification
{
    public function __construct(public Lead $lead) {}

    public function toDatabase(object $notifiable): array
    {
        $where = $this->lead->company_name ? " from {$this->lead->company_name}" : '';

        return [
            'title' => 'New Lead Added',
            'message' => "{$this->lead->name}{$where} added to the pipeline",
            'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            'color' => '#6d5cff',
            'url' => route('admin.pipeline.index'),
            'type' => 'new_lead',
        ];
    }
}
