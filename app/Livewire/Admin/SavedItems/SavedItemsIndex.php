<?php

namespace App\Livewire\Admin\SavedItems;

use App\Models\SavedItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Saved Items')]
class SavedItemsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public $unit_price = 0;
    public string $unit = '';

    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $item = SavedItem::findOrFail($id);
        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->description = (string) $item->description;
        $this->unit_price = (float) $item->unit_price;
        $this->unit = (string) $item->unit;
        $this->showForm = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        SavedItem::updateOrCreate(['id' => $this->editingId], $validated);

        $this->showForm = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: 'Saved item ' . ($this->editingId ? 'updated.' : 'created.'));
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            SavedItem::findOrFail($this->deleteId)->delete();
            $this->dispatch('toast', type: 'success', message: 'Saved item deleted.');
        }
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'description', 'unit_price', 'unit']);
        $this->resetValidation();
    }

    public function render()
    {
        $items = SavedItem::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.saved-items.saved-items-index', compact('items'));
    }
}
