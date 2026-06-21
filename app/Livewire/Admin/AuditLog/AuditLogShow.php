<?php

namespace App\Livewire\Admin\AuditLog;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Audit Log Detail')]
class AuditLogShow extends Component
{
    public AuditLog $auditLog;

    public function mount(AuditLog $auditLog): void
    {
        $this->auditLog = $auditLog;
    }

    public function render()
    {
        return view('livewire.admin.audit-log.audit-log-show');
    }
}
