@props(['doc', 'heading', 'number', 'recipientLabel' => 'Bill To', 'secondDateLabel' => 'Due Date', 'secondDate' => null])

<div class="card mx-auto max-w-3xl overflow-hidden">
    <div class="bg-ink-900 p-8 text-white">
        <div class="flex items-start justify-between">
            <x-brand size="lg" mono />
            <div class="text-right">
                <p class="text-2xl font-semibold tracking-wide text-gradient-brand">{{ $heading }}</p>
                <p class="mt-1 text-sm text-gray-300">{{ $number }}</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <div class="grid grid-cols-2 gap-6">
            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">From</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ company_settings()->name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ company_settings()->email }}</p>
                <p class="whitespace-pre-line text-sm text-gray-500 dark:text-gray-400">{{ company_settings()->address }}</p>
            </div>
            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $recipientLabel }}</p>
                <p class="font-medium text-gray-900 dark:text-white">{{ $doc->client?->name }}</p>
                @if ($doc->client?->company_name)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $doc->client->company_name }}</p>@endif
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $doc->client?->email }}</p>
            </div>
        </div>

        <div class="mt-6 flex gap-8 text-sm">
            <div><span class="text-gray-400">Issue Date:</span> <span class="text-gray-900 dark:text-white">{{ $doc->issue_date?->format('M d, Y') }}</span></div>
            <div><span class="text-gray-400">{{ $secondDateLabel }}:</span> <span class="text-gray-900 dark:text-white">{{ $secondDate?->format('M d, Y') ?? '—' }}</span></div>
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
                @foreach ($doc->items as $item)
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
                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="text-gray-900 dark:text-white">{{ money($doc->subtotal) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Tax ({{ rtrim(rtrim(number_format($doc->tax_rate, 2), '0'), '.') }}%)</span><span class="text-gray-900 dark:text-white">{{ money($doc->tax_amount) }}</span></div>
                @if ($doc->discount_amount > 0)
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Discount</span><span class="text-gray-900 dark:text-white">− {{ money($doc->discount_amount) }}</span></div>
                @endif
                <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-ink-600"><span class="font-semibold text-gray-900 dark:text-white">Total</span><span class="text-lg font-semibold text-brand-purple">{{ money($doc->total) }}</span></div>
            </div>
        </div>

        @if ($doc->notes || $doc->terms)
            <div class="mt-8 grid grid-cols-1 gap-4 border-t border-gray-200 pt-6 text-sm dark:border-ink-600 sm:grid-cols-2">
                @if ($doc->notes)<div><p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Notes</p><p class="whitespace-pre-line text-gray-500 dark:text-gray-400">{{ $doc->notes }}</p></div>@endif
                @if ($doc->terms)<div><p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Terms</p><p class="whitespace-pre-line text-gray-500 dark:text-gray-400">{{ $doc->terms }}</p></div>@endif
            </div>
        @endif
    </div>
</div>
