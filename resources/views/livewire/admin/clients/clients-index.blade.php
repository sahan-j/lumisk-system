<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Clients</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage clients and portal access.</p>
        </div>
        <button wire:click="create" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            Add Client
        </button>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search clients…" class="form-input-base pl-10">
        </div>
        <select wire:model.live="portal" class="form-input-base sm:w-48">
            <option value="">All clients</option>
            <option value="enabled">Portal enabled</option>
            <option value="disabled">Portal disabled</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Portal</th>
                        <th class="px-5 py-3 text-center">Invoices</th>
                        <th class="px-5 py-3 text-center">Estimates</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.clients.show', $client) }}" class="font-medium text-gray-900 hover:text-gold dark:text-white">{{ $client->name }}</a>
                                @if ($client->company_name)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $client->company_name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $client->email }}</p>
                                @if ($client->phone)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $client->phone }}</p>@endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($client->portal_enabled)
                                    <x-status-badge color="green" label="Enabled" />
                                @else
                                    <x-status-badge color="gray" label="Disabled" />
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $client->invoices_count }}</td>
                            <td class="px-5 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $client->estimates_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="View">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                    <button wire:click="edit({{ $client->id }})" class="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-ink-700" title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $client->id }})" class="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30" title="Delete">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">No clients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>

    {{-- Create / Edit modal --}}
    @if ($showForm)
        <x-app-modal :title="$editingId ? 'Edit Client' : 'Add Client'" close="$set('showForm', false)" max-width="sm:max-w-xl">
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="form-input-base">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Company</label>
                        <input wire:model="company_name" type="text" class="form-input-base">
                    </div>
                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email" class="form-input-base">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input wire:model="phone" type="text" class="form-input-base">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Address</label>
                        <textarea wire:model="address" rows="2" class="form-input-base"></textarea>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-ink-600">
                    <label class="flex items-center gap-3">
                        <input wire:model.live="portal_enabled" type="checkbox" class="rounded border-gray-300 text-gold focus:ring-gold dark:border-ink-600 dark:bg-ink-800">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Enable client portal access</span>
                    </label>
                    @if ($portal_enabled)
                        <div class="mt-4">
                            <label class="form-label">Portal Password {{ $editingId ? '(leave blank to keep current)' : '' }}</label>
                            <div class="relative" x-data="{ show: false }">
                                <input x-ref="input" wire:model="password" type="password" class="form-input-base pr-10" placeholder="Minimum 6 characters">
                                <button type="button" tabindex="-1" @click="show = !show; $refs.input.type = show ? 'text' : 'password'"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-purple focus:outline-none"
                                        :aria-label="show ? 'Hide password' : 'Show password'">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showForm', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update' : 'Create' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </form>
        </x-app-modal>
    @endif

    {{-- Delete confirmation --}}
    @if ($confirmingDelete)
        <x-app-modal title="Delete client?" close="$set('confirmingDelete', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">This will soft-delete the client. Their invoices and estimates remain in the system. You can restore from the database if needed.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingDelete', false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </x-app-modal>
    @endif
</div>
