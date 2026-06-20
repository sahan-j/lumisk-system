@props(['record'])

@php
    $modelType = get_class($record);
    $notesCount = $record->notes()->count();
    $attachmentsCount = $record->attachments()->count();
@endphp

<div x-data="{ tab: 'notes' }" class="card mt-6 p-6">
    {{-- Tab headers --}}
    <div class="mb-4 flex gap-1 border-b border-gray-200 dark:border-ink-600">
        <button @click="tab = 'notes'" type="button"
                :class="tab === 'notes' ? 'border-brand-purple text-brand-purple' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="flex items-center gap-1.5 border-b-2 px-4 py-2.5 text-sm font-medium">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Notes
            @if ($notesCount > 0)<span class="rounded-full bg-brand-purple px-2 py-0.5 text-[10px] font-semibold text-white">{{ $notesCount }}</span>@endif
        </button>
        <button @click="tab = 'attachments'" type="button"
                :class="tab === 'attachments' ? 'border-brand-purple text-brand-purple' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                class="flex items-center gap-1.5 border-b-2 px-4 py-2.5 text-sm font-medium">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
            Attachments
            @if ($attachmentsCount > 0)<span class="rounded-full bg-brand-purple px-2 py-0.5 text-[10px] font-semibold text-white">{{ $attachmentsCount }}</span>@endif
        </button>
    </div>

    <div x-show="tab === 'notes'">
        <livewire:admin.notes-manager :model-type="$modelType" :model-id="$record->id" :key="'notes-' . $modelType . '-' . $record->id" />
    </div>
    <div x-show="tab === 'attachments'" x-cloak>
        <livewire:admin.attachments-manager :model-type="$modelType" :model-id="$record->id" :key="'att-' . $modelType . '-' . $record->id" />
    </div>
</div>
