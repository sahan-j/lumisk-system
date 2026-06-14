<?php

namespace App\Livewire\Admin\Settings;

use App\Models\ExpenseCategory;
use Livewire\Component;

class ExpenseCategoriesManager extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public string $color = '#6d5cff';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:20'],
        ];
    }

    public function edit(int $id): void
    {
        $category = ExpenseCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->color = $category->color;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'name', 'color']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            ExpenseCategory::findOrFail($this->editingId)->update($validated);
            $message = 'Category updated.';
        } else {
            ExpenseCategory::create($validated);
            $message = 'Category added.';
        }

        $this->cancelEdit();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function delete(int $id): void
    {
        $category = ExpenseCategory::withCount('expenses')->findOrFail($id);

        if ($category->expenses_count > 0) {
            $this->dispatch('toast', type: 'error', message: 'Cannot delete a category that has expenses.');

            return;
        }

        $category->delete();
        $this->dispatch('toast', type: 'success', message: 'Category deleted.');
    }

    public function render()
    {
        return view('livewire.admin.settings.expense-categories-manager', [
            'categories' => ExpenseCategory::withCount('expenses')->orderBy('name')->get(),
        ]);
    }
}
