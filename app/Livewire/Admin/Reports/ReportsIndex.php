<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Reports & Analytics')]
class ReportsIndex extends Component
{
    public function render()
    {
        return view('livewire.admin.reports.reports-index');
    }
}
