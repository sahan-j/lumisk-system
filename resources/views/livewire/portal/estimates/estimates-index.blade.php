<div>
    <div class="mb-4 flex justify-end">
        <select wire:model.live="status" class="form-input-base sm:w-52">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
        </select>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">Estimate #</th>
                        <th class="px-5 py-3">Issue Date</th>
                        <th class="px-5 py-3">Expiry Date</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($estimates as $estimate)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('portal.estimates.show', $estimate) }}" class="font-medium text-gray-900 hover:text-brand-purple dark:text-white">{{ $estimate->estimate_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $estimate->issue_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $estimate->expiry_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ money($estimate->total) }}</td>
                            <td class="px-5 py-3"><x-status-badge :color="$estimate->statusColor()" :label="$estimate->status" /></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('portal.estimates.show', $estimate) }}" class="text-sm font-medium text-brand-purple hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">No estimates yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $estimates->links() }}</div>
</div>
