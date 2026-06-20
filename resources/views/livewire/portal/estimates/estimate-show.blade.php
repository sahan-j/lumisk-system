<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('portal.estimates.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Estimates
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $estimate->estimate_number }}</h2>
                <x-status-badge :color="$estimate->statusColor()" :label="$estimate->status" />
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('portal.estimates.pdf', $estimate) }}" class="btn-secondary">Download PDF</a>
            @if ($estimate->status === 'sent')
                <button wire:click="openResponse('accepted')" class="btn !bg-green-600 !text-white hover:!bg-green-700">Accept</button>
                <button wire:click="openResponse('rejected')" class="btn-danger">Reject</button>
            @endif
        </div>
    </div>

    {{-- Response banner --}}
    @if (in_array($estimate->status, ['accepted', 'rejected']))
        <div @class([
            'card mb-6 border-l-4 p-4',
            'border-green-500' => $estimate->status === 'accepted',
            'border-red-500' => $estimate->status === 'rejected',
        ])>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                You {{ $estimate->status }} this estimate.
            </p>
            @if ($estimate->client_note)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">“{{ $estimate->client_note }}”</p>
            @endif
        </div>
    @endif

    <x-document-preview :doc="$estimate" heading="ESTIMATE" :number="$estimate->estimate_number"
                        recipient-label="Prepared For" second-date-label="Expiry Date" :second-date="$estimate->expiry_date" />

    <x-portal-notes-attachments :doc="$estimate" />

    {{-- Accept / reject modal --}}
    @if ($showResponse)
        <x-app-modal :title="$responseAction === 'accepted' ? 'Accept estimate' : 'Reject estimate'" close="$set('showResponse', false)">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                {{ $responseAction === 'accepted'
                    ? 'Confirm that you accept this estimate. You may add an optional note.'
                    : 'Let us know why you are declining (optional).' }}
            </p>
            <form wire:submit="submitResponse">
                <label class="form-label">Note (optional)</label>
                <textarea wire:model="client_note" rows="3" class="form-input-base" placeholder="Add a note…"></textarea>
                @error('client_note') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showResponse', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" @class([
                        'btn',
                        '!bg-green-600 !text-white hover:!bg-green-700' => $responseAction === 'accepted',
                        'btn-danger' => $responseAction === 'rejected',
                    ])>
                        {{ $responseAction === 'accepted' ? 'Accept Estimate' : 'Reject Estimate' }}
                    </button>
                </div>
            </form>
        </x-app-modal>
    @endif
</div>
