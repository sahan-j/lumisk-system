<div>
    @php $fmt = fn ($m) => sprintf('%dh %02dm', intdiv((int) $m, 60), (int) $m % 60); @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.time.index') }}" wire:navigate class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Time Tracking
            </a>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Time Report</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}</p>
        </div>
        <button wire:click="export" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
        </button>
    </div>

    {{-- Filters --}}
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
        <select wire:model.live="period" class="form-input-base">
            @foreach (\App\Livewire\Concerns\WithDateRange::PERIODS as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
        </select>
        @if ($period === 'custom')
            <input wire:model.live="dateFrom" type="date" class="form-input-base">
            <input wire:model.live="dateTo" type="date" class="form-input-base">
        @endif
        <select wire:model.live="projectId" class="form-input-base">
            <option value="">All projects</option>
            @foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
        </select>
        <select wire:model.live="clientId" class="form-input-base">
            <option value="">All clients</option>
            @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <select wire:model.live="userId" class="form-input-base">
            <option value="">All staff</option>
            @foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
        </select>
        <select wire:model.live="billable" class="form-input-base">
            <option value="">All time</option>
            <option value="billable">Billable</option>
            <option value="non_billable">Non-billable</option>
        </select>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Total Hours</span><p class="mt-2 font-mono text-2xl font-semibold text-gray-900 dark:text-white">{{ $fmt($totalMinutes) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Billable Hours</span><p class="mt-2 font-mono text-2xl font-semibold text-brand-purple">{{ $fmt($billableMinutes) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Billable Amount</span><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ money($totalBillableAmount) }}</p></div>
        <div class="card p-5"><span class="text-sm text-gray-500 dark:text-gray-400">Unbilled</span><p class="mt-2 text-2xl font-semibold {{ $unbilledAmount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">{{ money($unbilledAmount) }}</p></div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- By project --}}
        <div class="card p-5">
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Hours by Project</h3>
            @forelse ($byProject as $name => $data)
                <div class="mb-3">
                    <div class="mb-1 flex items-center justify-between text-xs">
                        <span class="text-gray-700 dark:text-gray-300">{{ $name }}</span>
                        <span class="font-mono text-gray-500 dark:text-gray-400">{{ $fmt($data['minutes']) }} @if ($data['billable'] > 0)· {{ money($data['billable']) }}@endif</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-700">
                        <div class="h-full rounded-full bg-brand-purple" style="width: {{ max(2, round($data['minutes'] / $projectMax * 100)) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-gray-400">No data for this range.</p>
            @endforelse
        </div>

        {{-- By client --}}
        <div class="card p-5">
            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">By Client</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wider text-gray-400 dark:border-ink-600">
                        <th class="py-2">Client</th><th class="py-2 text-right">Hours</th><th class="py-2 text-right">Billable</th><th class="py-2 text-right">Unbilled</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                        @forelse ($byClient as $name => $data)
                            <tr>
                                <td class="py-2 text-gray-700 dark:text-gray-300">{{ $name }}</td>
                                <td class="py-2 text-right font-mono text-gray-500 dark:text-gray-400">{{ $fmt($data['minutes']) }}</td>
                                <td class="py-2 text-right text-gray-900 dark:text-white">{{ money($data['billable']) }}</td>
                                <td class="py-2 text-right {{ $data['unbilled'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">{{ money($data['unbilled']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Entries table --}}
    <div class="card mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead><tr class="table-head">
                    <th class="px-4 py-3">Date</th><th class="px-4 py-3">Description</th><th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3">Client</th><th class="px-4 py-3 text-right">Duration</th><th class="px-4 py-3 text-right">Rate</th>
                    <th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Billed</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $entry->date->format('M d') }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-900 dark:text-white">{{ $entry->description ?? '—' }}<span class="ml-1 text-xs text-gray-400">· {{ $entry->user_name }}</span></td>
                            <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $entry->project?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400">{{ $entry->client?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-sm text-gray-900 dark:text-white">{{ $entry->duration_formatted }}</td>
                            <td class="px-4 py-2.5 text-right text-sm text-gray-500 dark:text-gray-400">{{ $entry->hourly_rate ? money($entry->hourly_rate) : '—' }}</td>
                            <td class="px-4 py-2.5 text-right text-sm font-medium {{ $entry->is_billable ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">{{ $entry->is_billable ? money($entry->billable_amount) : 'n/b' }}</td>
                            <td class="px-4 py-2.5">@if ($entry->is_billed)<span class="text-xs text-green-600 dark:text-green-400">✓</span>@else<span class="text-xs text-gray-300 dark:text-gray-600">—</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400">No time entries match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bill unbilled time --}}
    @if ($hasUnbilled)
        <div class="card mt-6 border border-brand-purple/20 bg-brand-purple/5 p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Create Invoice from Unbilled Time</h3>
            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Bills all unbilled billable entries for the selected client within the current filters.</p>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label class="form-label">Client</label>
                    <select wire:model="billClientId" class="form-input-base">
                        <option value="">Select client…</option>
                        @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    @error('billClientId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <button wire:click="billEntries" class="btn-primary">
                    <svg class="mr-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Create Invoice
                </button>
            </div>
        </div>
    @endif
</div>
