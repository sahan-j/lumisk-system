<div>
    <a href="{{ route('admin.expenses.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Back to expenses
    </a>

    <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">{{ $expense ? 'Edit Expense' : 'New Expense' }}</h2>

    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left column --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="form-label">Title <span class="text-red-500">*</span></label>
                        <input wire:model="title" type="text" class="form-input-base" placeholder="e.g. Adobe Creative Cloud subscription">
                        @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea wire:model="description" rows="2" class="form-input-base"></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input wire:model="amount" type="number" step="0.01" min="0.01" class="form-input-base" placeholder="0.00">
                        @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Expense Date <span class="text-red-500">*</span></label>
                        <input wire:model="expense_date" type="date" class="form-input-base">
                        @error('expense_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Category</label>
                        <select wire:model="category_id" class="form-input-base">
                            <option value="">— No category —</option>
                            @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                        @error('category_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Payment Method <span class="text-red-500">*</span></label>
                        <select wire:model="payment_method" class="form-input-base">
                            @foreach ($methods as $m)<option value="{{ $m }}">{{ ucwords(str_replace('_', ' ', $m)) }}</option>@endforeach
                        </select>
                        @error('payment_method') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Reference Number</label>
                        <input wire:model="reference_number" type="text" class="form-input-base" placeholder="Invoice / receipt number">
                        @error('reference_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" rows="2" class="form-input-base"></textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            {{-- Link to client/project --}}
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Link &amp; Billing</h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Client</label>
                        <select wire:model.live="client_id" class="form-input-base">
                            <option value="">— None —</option>
                            @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}@if($c->company_name) ({{ $c->company_name }})@endif</option>@endforeach
                        </select>
                        @error('client_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Project</label>
                        <select wire:model="project_id" class="form-input-base">
                            <option value="">— None —</option>
                            @foreach ($projects as $p)
                                @if (! $client_id || $p->client_id == $client_id)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('project_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-3 dark:border-ink-600">
                        <input wire:model="is_billable" type="checkbox" class="mt-0.5 rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                        <span class="text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-200">Mark as billable</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Can be added to a client invoice later.</span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Receipt upload --}}
            <div class="card p-6">
                <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Receipt</h3>

                @if ($existingReceipt && ! $receipt)
                    <div class="mb-3 flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-ink-600">
                        <a href="{{ Storage::url($existingReceipt) }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-medium text-gold hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            View current receipt
                        </a>
                        <button type="button" wire:click="removeReceipt" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                    </div>
                @endif

                <label class="form-label">{{ $existingReceipt ? 'Replace receipt' : 'Upload receipt' }}</label>
                <input wire:model="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf"
                       class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-brand-purple/10 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-purple hover:file:bg-brand-purple/20 dark:text-gray-400">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG or PDF up to 5MB.</p>
                @error('receipt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                <div wire:loading wire:target="receipt" class="mt-2 text-xs text-gray-400">Uploading…</div>

                @if ($receipt && in_array(strtolower($receipt->getClientOriginalExtension()), ['jpg', 'jpeg', 'png']))
                    <img src="{{ $receipt->temporaryUrl() }}" alt="Preview" class="mt-3 max-h-48 rounded-lg border border-gray-200 dark:border-ink-600">
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 lg:col-span-3">
            <a href="{{ route('admin.expenses.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <span wire:loading.remove wire:target="save">Save Expense</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
