<div>
    <a href="{{ route('admin.pipeline.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Pipeline
    </a>

    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ $lead ? 'Edit Lead' : 'New Lead' }}</h2>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left: contact + deal --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Contact</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Contact Name <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" class="form-input-base" placeholder="e.g. Nimal Perera">
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Company Name</label>
                            <input wire:model="company_name" type="text" class="form-input-base">
                            @error('company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input wire:model="email" type="email" class="form-input-base">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Phone</label>
                            <input wire:model="phone" type="text" class="form-input-base" placeholder="+94 ...">
                            @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Deal</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Deal Value ({{ company_settings()->currency ?: 'LKR' }})</label>
                            <input wire:model="value" type="number" step="0.01" min="0" class="form-input-base" placeholder="0.00">
                            @error('value') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Win Probability — <span class="font-semibold text-brand-purple" x-text="$wire.probability + '%'"></span></label>
                            <input wire:model.live="probability" type="range" min="0" max="100" step="5" class="mt-3 w-full accent-brand-purple">
                            @error('probability') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Expected Close Date</label>
                            <input wire:model="expected_close_date" type="date" class="form-input-base">
                            @error('expected_close_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Source</label>
                            <select wire:model="source" class="form-input-base">
                                @foreach ($sources as $s)<option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>@endforeach
                            </select>
                            @error('source') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" rows="4" class="form-input-base" placeholder="Anything useful about this lead…"></textarea>
                </div>
            </div>

            {{-- Right: pipeline placement --}}
            <div class="space-y-6">
                <div class="card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Pipeline</h3>
                    <div class="mb-4">
                        <label class="form-label">Stage <span class="text-red-500">*</span></label>
                        <select wire:model="stage_id" class="form-input-base">
                            @foreach ($stages as $stage)<option value="{{ $stage->id }}">{{ $stage->name }}</option>@endforeach
                        </select>
                        @error('stage_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Assigned To</label>
                        <select wire:model="assigned_to" class="form-input-base">
                            <option value="">Unassigned</option>
                            @foreach ($staff as $member)<option value="{{ $member->name }}">{{ $member->name }}</option>@endforeach
                        </select>
                        @error('assigned_to') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.pipeline.index') }}" wire:navigate class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="save">{{ $lead ? 'Update' : 'Create' }} Lead</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
