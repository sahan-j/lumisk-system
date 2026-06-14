<div>
    {{-- Action bar --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Invoices
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</h2>
                <x-status-badge :color="$invoice->statusColor()" :label="$invoice->status" />
            </div>
            @if ($invoice->converted_from)
                <p class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-brand-purple">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4" /></svg>
                    Converted from {{ $invoice->converted_from }}
                </p>
            @endif
        </div>
        <div class="flex flex-wrap items-start gap-2">
            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn-secondary !py-1.5 text-sm">Edit</a>
            <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn-secondary !py-1.5 text-sm">Download PDF</a>
            <button wire:click="$dispatch('open-duplicate', { type: 'invoice', id: {{ $invoice->id }} })" class="btn-secondary !py-1.5 text-sm">Duplicate</button>
            <button wire:click="$dispatch('open-convert', { direction: 'invoice_to_estimate', id: {{ $invoice->id }} })" class="btn-secondary !py-1.5 text-sm">Convert to Estimate</button>
            <button wire:click="$dispatch('open-whatsapp', { type: 'invoice', id: {{ $invoice->id }} })"
                    class="btn !py-1.5 text-sm font-medium text-white" style="background:#25d366;">
                <svg class="mr-1 inline h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.892c0 2.096.549 4.142 1.595 5.945L0 24l6.305-1.654a11.962 11.962 0 005.71 1.447h.005c6.582 0 11.946-5.334 11.949-11.893a11.821 11.821 0 00-3.45-8.351"/></svg>
                WhatsApp
            </button>
            @if (! in_array($invoice->status, ['cancelled']))
                <button wire:click="$dispatch('open-record-payment', { invoiceId: {{ $invoice->id }} })" class="btn-primary !py-1.5 text-sm">
                    <svg class="mr-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Record Payment
                </button>
            @endif
            <div>
                <button wire:click="openSendEmail" class="btn-primary !py-1.5 text-sm">
                    <svg class="mr-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Send via Email
                </button>
                @php $lastSent = \App\Models\EmailLog::where('type','invoice')->where('reference_id',$invoice->id)->sent()->latest('sent_at')->first(); @endphp
                @if($lastSent)
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Last sent {{ $lastSent->sent_at->diffForHumans() }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Status management --}}
    <div class="card mb-6 flex flex-wrap items-center gap-3 p-4">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Change status:</span>
        @php $actions = ['draft' => 'Draft', 'sent' => 'Mark as Sent', 'paid' => 'Mark as Paid', 'overdue' => 'Mark Overdue', 'cancelled' => 'Cancel']; @endphp
        @foreach ($actions as $value => $label)
            @if ($invoice->status !== $value)
                <button wire:click="setStatus('{{ $value }}')"
                    @class([
                        'btn !py-1.5 text-sm',
                        'bg-green-600 text-white hover:bg-green-700' => $value === 'paid',
                        'status-btn-sent' => $value === 'sent',
                        'btn-secondary' => ! in_array($value, ['paid', 'sent']),
                    ])>{{ $label }}</button>
            @endif
        @endforeach
    </div>

    {{-- Payment summary --}}
    <x-payment-summary :invoice="$invoice" />

    {{-- Document preview --}}
    <div class="card mx-auto max-w-3xl overflow-hidden">
        <div class="bg-ink-900 p-8 text-white">
            <div class="flex items-start justify-between">
                <x-brand size="lg" mono />
                <div class="text-right">
                    <p class="text-2xl font-semibold tracking-wide text-gold">INVOICE</p>
                    <p class="mt-1 text-sm text-gray-300">{{ $invoice->invoice_number }}</p>
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
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Bill To</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $invoice->client?->name }}</p>
                    @if ($invoice->client?->company_name)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->client->company_name }}</p>@endif
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->client?->email }}</p>
                </div>
            </div>

            <div class="mt-6 flex gap-8 text-sm">
                <div><span class="text-gray-400">Issue Date:</span> <span class="text-gray-900 dark:text-white">{{ $invoice->issue_date?->format('M d, Y') }}</span></div>
                <div><span class="text-gray-400">Due Date:</span> <span class="text-gray-900 dark:text-white">{{ $invoice->due_date?->format('M d, Y') ?? '—' }}</span></div>
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
                    @foreach ($invoice->items as $item)
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
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="text-gray-900 dark:text-white">{{ money($invoice->subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Tax ({{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}%)</span><span class="text-gray-900 dark:text-white">{{ money($invoice->tax_amount) }}</span></div>
                    @if ($invoice->discount_amount > 0)
                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Discount</span><span class="text-gray-900 dark:text-white">− {{ money($invoice->discount_amount) }}</span></div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-ink-600"><span class="font-semibold text-gray-900 dark:text-white">Total</span><span class="text-lg font-semibold text-gold">{{ money($invoice->total) }}</span></div>
                </div>
            </div>

            @if ($invoice->notes || $invoice->terms)
                <div class="mt-8 grid grid-cols-1 gap-4 border-t border-gray-200 pt-6 text-sm dark:border-ink-600 sm:grid-cols-2">
                    @if ($invoice->notes)<div><p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Notes</p><p class="whitespace-pre-line text-gray-500 dark:text-gray-400">{{ $invoice->notes }}</p></div>@endif
                    @if ($invoice->terms)<div><p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Terms</p><p class="whitespace-pre-line text-gray-500 dark:text-gray-400">{{ $invoice->terms }}</p></div>@endif
                </div>
            @endif
        </div>
    </div>
</div>
