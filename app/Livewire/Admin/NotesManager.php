<?php

namespace App\Livewire\Admin;

use App\Models\Note;
use Livewire\Component;

class NotesManager extends Component
{
    public string $modelType = '';
    public int $modelId = 0;

    public string $newNote = '';
    public bool $isInternal = true;
    public bool $isAdding = false;

    public function mount(string $modelType, int $modelId): void
    {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
    }

    public function addNote(): void
    {
        $this->validate([
            'newNote' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        Note::create([
            'notable_type' => $this->modelType,
            'notable_id' => $this->modelId,
            'content' => $this->newNote,
            'is_internal' => $this->isInternal,
            'author_type' => 'admin',
            'author_name' => auth()->user()->name ?? 'Admin',
        ]);

        $this->reset('newNote');
        $this->isAdding = false;
        $this->dispatch('notes-changed');
        $this->dispatch('toast', type: 'success', message: 'Note added.');
    }

    public function deleteNote(int $noteId): void
    {
        $note = Note::where('id', $noteId)
            ->where('notable_type', $this->modelType)
            ->where('notable_id', $this->modelId)
            ->first();

        if ($note) {
            $note->delete();
            $this->dispatch('notes-changed');
            $this->dispatch('toast', type: 'success', message: 'Note deleted.');
        }
    }

    public function render()
    {
        $record = $this->modelType::find($this->modelId);

        return view('livewire.admin.notes-manager', [
            // Access via the relation method — Invoice/Estimate/Project also have a `notes` column,
            // so the `notes` magic property would return that string, not the morphMany relation.
            'notes' => $record ? $record->notes()->get() : collect(),
        ]);
    }
}
