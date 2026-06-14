<div>
    {{-- Action bar --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <a href="{{ route('admin.estimates.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Estimates
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $estimate->estimate_number }}</h2>
                <x-status-badge :color="$estimate->statusColor()" :label="$estimate->status" />
            </div>
            @if ($estimate->converted_from)
                <p class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-brand-purple">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4" /></svg>
                    Converted from {{ $estimate->converted_from }}
                </p>
            @endif
        </div>
        <div class="flex flex-wrap items-start gap-2">
            <a href="{{ route('admin.estimates.edit', $estimate) }}" class="btn-secondary !py-1.5 text-sm">Edit</a>
            <a href="{{ route('admin.estimates.pdf', $estimate) }}" class="btn-secondary !py-1.5 text-sm">Download PDF</a>
            <button wire:click="$dispatch('open-duplicate', { type: 'estimate', id: {{ $estimate->id }} })" class="btn-secondary !py-1.5 text-sm">Duplicate</button>
            <button wire:click="$dispatch('open-convert', { direction: 'estimate_to_invoice', id: {{ $estimate->id }} })" class="btn-primary !py-1.5 text-sm">Convert to Invoice</button>
            <div>
                <button wire:click="openSendEmail" class="btn-primary !py-1.5 text-sm">
                    <svg class="mr-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Send via Email
                </button>
                @php $lastSent = \App\Models\EmailLog::where('type','estimate')->where('reference_id',$estimate->id)->sent()->latest('sent_at')->first(); @endphp
                @if($lastSent)
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Last sent {{ $lastSent->sent_at->diffForHumans() }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Client response note --}}
    @if ($estimate->client_note)
        <div @class([
            'card mb-6 border-l-4 p-4',
            'border-green-500' => $estimate->status === 'accepted',
            'border-red-500' => $estimate->status === 'rejected',
            'border-gold' => ! in_array($estimate->status, ['accepted', 'rejected']),
        ])>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Client note ({{ ucfirst($estimate->status) }})</p>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">{{ $estimate->client_note }}</p>
        </div>
    @endif

    {{-- Status management --}}
    <div class="card mb-6 flex flex-wrap items-center gap-3 p-4">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Change status:</span>
        @php $actions = ['draft' => 'Draft', 'sent' => 'Mark as Sent', 'accepted' => 'Mark Accepted', 'rejected' => 'Mark Rejected', 'expired' => 'Mark Expired']; @endphp
        @foreach ($actions as $value => $label)
            @if ($estimate->status !== $value)
                <button wire:click="setStatus('{{ $value }}')"
                    @class([
                        'btn !py-1.5 text-sm',
                        'bg-green-600 text-white hover:bg-green-700' => $value === 'accepted',
                        'status-btn-sent' => $value === 'sent',
                        'btn-secondary' => ! in_array($value, ['accepted', 'sent']),
                    ])>{{ $label }}</button>
            @endif
        @endforeach
    </div>

    {{-- Document preview --}}
    <div class="card mx-auto max-w-3xl overflow-hidden">
        <div class="bg-ink-900 p-8 text-white">
            <div class="flex items-start justify-between">
                <x-brand size="lg" mono />
                <div class="text-right">
                    <p class="text-2xl font-semibold tracking-wide text-gold">ESTIMATE</p>
                    <p class="mt-1 text-sm text-gray-300">{{ $estimate->estimate_number }}</p>
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
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Prepared For</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $estimate->client?->name }}</p>
                    @if ($estimate->client?->company_name)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $estimate->client->company_name }}</p>@endif
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $estimate->client?->email }}</p>
                </div>
            </div>

            <div class="mt-6 flex gap-8 text-sm">
                <div><span class="text-gray-400">Issue Date:</span> <span class="text-gray-900 dark:text-white">{{ $estimate->issue_date?->format('M d, Y') }}</span></div>
                <div><span class="text-gray-400">Expiry Date:</span> <span class="text-gray-900 dark:text-white">{{ $estimate->expiry_date?->format('M d, Y') ?? '—' }}</span></div>
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
                    @foreach ($estimate->items as $item)
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
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="text-gray-900 dark:text-white">{{ money($estimate->subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Tax ({{ rtrim(rtrim(number_format($estimate->tax_rate, 2), '0'), '.') }}%)</span><span class="text-gray-900 dark:text-white">{{ money($estimate->tax_amount) }}</span></div>
                    @if ($estimate->discount_amount > 0)
                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Discount</span><span class="text-gray-900 dark:text-white">− {{ money($estimate->discount_amount) }}</span></div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-ink-600"><span class="font-semibold text-gray-900 dark:text-white">Total</span><span class="text-lg font-semibold text-gold">{{ money($estimate->total) }}</span></div>
                </div>
            </div>

            @if ($estimate->notes || $estimate->terms)
                <div class="mt-8 grid grid-cols-1 gap-4 border-t border-gray-200 pt-6 text-sm dark:border-ink-600 sm:grid-cols-2">
                    @if ($estimate->notes)<div><p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Notes</p><p class="whitespace-pre-line text-gray-500 dark:text-gray-400">{{ $estimate->notes }}</p></div>@endif
                    @if ($estimate->terms)<div><p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Terms</p><p class="whitespace-pre-line text-gray-500 dark:text-gray-400">{{ $estimate->terms }}</p></div>@endif
                </div>
            @endif
        </div>
    </div>
</div>
