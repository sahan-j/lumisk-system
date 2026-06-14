<div>
    <a href="{{ route('admin.projects.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to projects
    </a>

    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ $project ? 'Edit Project' : 'New Project' }}</h2>

    <form wire:submit="save" class="space-y-6">
        <div class="card max-w-3xl p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="form-label">Project Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="form-input-base">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Client</label>
                    <select wire:model="client_id" class="form-input-base">
                        <option value="">— No client —</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}@if($c->company_name) ({{ $c->company_name }})@endif</option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Status <span class="text-red-500">*</span></label>
                        <select wire:model="status" class="form-input-base">
                            @foreach (\App\Models\Project::STATUSES as $s)
                                <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Priority <span class="text-red-500">*</span></label>
                        <select wire:model="priority" class="form-input-base">
                            @foreach (\App\Models\Project::PRIORITIES as $p)
                                <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Start Date</label>
                    <input wire:model="start_date" type="date" class="form-input-base">
                    @error('start_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Due Date</label>
                    <input wire:model="due_date" type="date" class="form-input-base">
                    @error('due_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Budget</label>
                    <input wire:model="budget" type="number" step="0.01" min="0" class="form-input-base" placeholder="0.00">
                    @error('budget') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Description</label>
                    <textarea wire:model="description" rows="3" class="form-input-base"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" rows="2" class="form-input-base"></textarea>
                </div>
            </div>
        </div>

        {{-- Linked invoices --}}
        <div class="card max-w-3xl p-6">
            <h3 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Linked Invoices</h3>
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Attach existing invoices to this project.</p>
            @if ($invoices->count())
                <div class="max-h-64 space-y-1 overflow-y-auto rounded-lg border border-gray-200 p-2 dark:border-ink-600">
                    @foreach ($invoices as $inv)
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-ink-700">
                            <input wire:model="invoice_ids" type="checkbox" value="{{ $inv->id }}" class="rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                            <span class="flex-1 text-sm text-gray-700 dark:text-gray-200">{{ $inv->invoice_number }}
                                <span class="text-xs text-gray-400">— {{ $inv->client?->name ?? '—' }}</span>
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ money($inv->total) }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">No invoices available.</p>
            @endif
        </div>

        <div class="flex max-w-3xl justify-end gap-3">
            <a href="{{ route('admin.projects.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="save">{{ $project ? 'Update Project' : 'Create Project' }}</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
