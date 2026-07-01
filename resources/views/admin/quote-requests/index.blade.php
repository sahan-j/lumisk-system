<x-layouts.admin title="Quote Requests">
    <x-alert />

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Pending</p><p class="mt-1 text-2xl font-semibold text-amber-500">{{ $stats['pending'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Under Review</p><p class="mt-1 text-2xl font-semibold text-brand-purple">{{ $stats['reviewing'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Submitted Today</p><p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_today'] }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500 dark:text-gray-400">Total</p><p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p></div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @php $filters = ['' => 'All', 'pending' => 'Pending', 'reviewing' => 'Reviewing', 'converted' => 'Converted', 'declined' => 'Declined']; @endphp
        @foreach ($filters as $value => $label)
            <a href="{{ route('admin.quote-requests.index', array_filter(['status' => $value])) }}"
               class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ ($status ?? '') === $value ? 'bg-brand-purple text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-ink-700 dark:text-gray-300 dark:hover:bg-ink-600' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead class="bg-gray-50 dark:bg-ink-800">
                    <tr class="text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Budget</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Submitted</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800 {{ $req->status === 'pending' ? 'bg-amber-50/40 dark:bg-amber-900/5' : '' }}">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $req->request_number }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $req->client?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($req->title, 40) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $req->service_type_label }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $req->budget_range_label }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" style="background: {{ $req->status_color }}1a; color: {{ $req->status_color }};">{{ $req->status_label }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-400">{{ $req->created_at->diffForHumans() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('admin.quote-requests.show', $req) }}"
                                   class="rounded-lg px-3 py-1.5 text-xs font-medium {{ $req->status === 'pending' ? 'bg-brand-purple text-white hover:opacity-90' : 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-ink-600 dark:text-gray-300 dark:hover:bg-ink-700' }}">
                                    {{ $req->status === 'pending' ? 'Review' : 'View' }} →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400">No quote requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($requests->hasPages())
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
</x-layouts.admin>
