<?php

namespace App\Livewire\Admin;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class AttachmentsManager extends Component
{
    use WithFileUploads;

    public string $modelType = '';
    public int $modelId = 0;

    public array $uploadedFiles = [];

    public function mount(string $modelType, int $modelId): void
    {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
    }

    public function updatedUploadedFiles(): void
    {
        $this->uploadFiles();
    }

    public function uploadFiles(): void
    {
        if (empty($this->uploadedFiles)) {
            return;
        }

        $this->validate([
            'uploadedFiles.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar,txt'],
        ]);

        $folder = 'attachments/' . strtolower(class_basename($this->modelType)) . '/' . $this->modelId;
        $count = 0;

        foreach ($this->uploadedFiles as $file) {
            $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $storedName, 'public');

            Attachment::create([
                'attachable_type' => $this->modelType,
                'attachable_id' => $this->modelId,
                'filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedName,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->user()->name ?? 'Admin',
            ]);
            $count++;
        }

        $this->uploadedFiles = [];
        $this->dispatch('attachments-changed');
        $this->dispatch('toast', type: 'success', message: $count . ' ' . Str::plural('file', $count) . ' uploaded.');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = Attachment::where('id', $attachmentId)
            ->where('attachable_type', $this->modelType)
            ->where('attachable_id', $this->modelId)
            ->first();

        if ($attachment) {
            Storage::disk('public')->delete($attachment->path);
            $attachment->delete();
            $this->dispatch('attachments-changed');
            $this->dispatch('toast', type: 'success', message: 'Attachment deleted.');
        }
    }

    public function render()
    {
        $record = $this->modelType::find($this->modelId);

        return view('livewire.admin.attachments-manager', [
            'attachments' => $record ? $record->attachments()->get() : collect(),
        ]);
    }
}
