<div>
    <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-white">Credit Notes</h1>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-ink-600">
                <thead>
                    <tr class="table-head">
                        <th class="px-5 py-3">CN #</th>
                        <th class="px-5 py-3">Issue Date</th>
                        <th class="px-5 py-3">Reason</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($creditNotes as $cn)
                        <tr wire:key="cn-{{ $cn->id }}" class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('portal.credit-notes.show', $cn) }}" wire:navigate class="font-mono text-sm font-medium text-red-500 hover:underline">{{ $cn->credit_note_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $cn->issue_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400"><span class="block max-w-[220px] truncate">{{ $cn->reason }}</span></td>
                            <td class="px-5 py-3 text-right font-mono text-sm font-medium text-gray-900 dark:text-white">{{ money($cn->total) }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $cn->status_color }}">{{ $cn->status_label }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('portal.credit-notes.pdf', $cn) }}" class="text-sm font-medium text-brand-purple hover:underline">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">You have no credit notes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $creditNotes->links() }}</div>
</div>
