<?php

namespace App\Livewire\Admin\Templates;

use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Invoice Templates')]
class TemplatesIndex extends Component
{
    use WithPagination;

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            InvoiceTemplate::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Template deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $templates = InvoiceTemplate::with('items')
            ->withCount('items')
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        $mostUsed = InvoiceTemplate::where('is_active', true)
            ->orderByDesc('usage_count')
            ->first();

        return view('livewire.admin.templates.templates-index', [
            'templates' => $templates,
            'totalTemplates' => InvoiceTemplate::where('is_active', true)->count(),
            'mostUsed' => $mostUsed,
            'totalItems' => InvoiceTemplateItem::whereHas('template', fn ($q) => $q->where('is_active', true))->count(),
        ]);
    }
}
