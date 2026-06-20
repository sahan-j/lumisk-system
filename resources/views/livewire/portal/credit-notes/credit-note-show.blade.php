<div>
    <a href="{{ route('portal.credit-notes.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-purple dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Credit Notes
    </a>

    <div class="card overflow-hidden">
        <div class="bg-ink-900 p-8 text-white">
            <div class="flex items-start justify-between">
                <x-brand size="lg" mono />
                <div class="text-right">
                    <p class="text-2xl font-semibold tracking-wide text-red-400">CREDIT NOTE</p>
                    <p class="mt-1 text-sm text-gray-300">{{ $creditNote->credit_note_number }}</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex gap-8 text-sm">
                    <div><span class="text-gray-400">Issue Date:</span> <span class="text-gray-900 dark:text-white">{{ $creditNote->issue_date?->format('M d, Y') }}</span></div>
                    @if ($creditNote->invoice)
                        <div><span class="text-gray-400">Ref. Invoice:</span> <span class="font-medium text-gray-900 dark:text-white">{{ $creditNote->invoice->invoice_number }}</span></div>
                    @endif
                </div>
                <a href="{{ route('portal.credit-notes.pdf', $creditNote) }}" class="btn-secondary !py-1.5 text-sm">Download PDF</a>
            </div>

            <table class="mt-6 w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wider text-gray-400 dark:border-ink-600">
                        <th class="py-2">Item</th>
                        <th class="py-2 text-right">Qty</th>
                        <th class="py-2 text-right">Unit Price</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                    @foreach ($creditNote->items as $item)
                        <tr>
                            <td class="py-3">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                                @if ($item->description)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->description }}</p>@endif
                            </td>
                            <td class="py-3 text-right text-gray-700 dark:text-gray-300">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="py-3 text-right text-gray-700 dark:text-gray-300">{{ money($item->unit_price, false) }}</td>
                            <td class="py-3 text-right font-medium text-gray-900 dark:text-white">{{ money($item->total, false) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 flex justify-end">
                <div class="w-64 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="text-gray-900 dark:text-white">{{ money($creditNote->subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Tax ({{ rtrim(rtrim(number_format($creditNote->tax_rate, 2), '0'), '.') }}%)</span><span class="text-gray-900 dark:text-white">{{ money($creditNote->tax_amount) }}</span></div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-ink-600"><span class="font-semibold text-gray-900 dark:text-white">Credit Total</span><span class="text-lg font-semibold text-red-500">{{ money($creditNote->total) }}</span></div>
                </div>
            </div>

            <div class="mt-8 rounded-lg border border-red-200 border-l-[3px] border-l-red-500 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/10">
                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-red-500">Reason for Credit Note</p>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $creditNote->reason }}</p>
            </div>

            @if ($creditNote->applications->isNotEmpty())
                <div class="mt-6 border-t border-gray-200 pt-6 text-sm dark:border-ink-600">
                    <p class="mb-2 font-semibold text-gray-700 dark:text-gray-200">Applied To</p>
                    @foreach ($creditNote->applications as $app)
                        <p class="text-gray-500 dark:text-gray-400">{{ money($app->amount) }} applied to {{ $app->invoice?->invoice_number }} on {{ $app->applied_at?->format('M d, Y') }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
