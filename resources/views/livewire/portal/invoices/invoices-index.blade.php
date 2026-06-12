<div>
    {{-- Filter --}}
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
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Issue Date</th>
                        <th class="px-5 py-3">Due Date</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3 text-right">Paid</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-ink-800">
                            <td class="px-5 py-3">
                                <a href="{{ route('portal.invoices.show', $invoice) }}" class="font-medium text-gray-900 hover:text-brand-purple dark:text-white">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->issue_date?->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">{{ money($invoice->total) }}</td>
                            <td class="px-5 py-3 text-right text-sm">
                                @if (in_array($invoice->status, ['sent', 'overdue', 'paid']))
                                    <span class="font-medium {{ $invoice->total_paid > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">{{ money($invoice->total_paid, false) }}</span>
                                    <span class="text-gray-400"> / {{ money($invoice->total, false) }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3"><x-status-badge :color="$invoice->statusColor()" :label="$invoice->status" /></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('portal.invoices.show', $invoice) }}" class="text-sm font-medium text-brand-purple hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
