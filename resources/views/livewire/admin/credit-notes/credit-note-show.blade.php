<div>
    {{-- Action bar --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <a href="{{ route('admin.credit-notes.index') }}" wire:navigate class="mb-1 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gold dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Credit Notes
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-red-500">{{ $creditNote->credit_note_number }}</h2>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white" style="background-color: {{ $creditNote->status_color }}">{{ $creditNote->status_label }}</span>
            </div>
        </div>
        <div class="flex flex-wrap items-start gap-2">
            @permission('credit-notes.edit')
            @if ($creditNote->status === 'draft')
                <a href="{{ route('admin.credit-notes.edit', $creditNote) }}" wire:navigate class="btn-secondary !py-1.5 text-sm">Edit</a>
                <button wire:click="issue" class="btn-primary !py-1.5 text-sm">Issue Credit Note</button>
            @endif
            @endpermission
            <a href="{{ route('admin.credit-notes.pdf', $creditNote) }}" class="btn-secondary !py-1.5 text-sm">Download PDF</a>
            <button wire:click="sendToClient" class="btn-secondary !py-1.5 text-sm">
                <span wire:loading.remove wire:target="sendToClient">Send to Client</span>
                <span wire:loading wire:target="sendToClient">Sending…</span>
            </button>
            @permission('credit-notes.edit')
            @if ($creditNote->amount_applied <= 0 && $creditNote->status !== 'void')
                <button wire:click="$set('confirmingVoid', true)" class="btn-secondary !py-1.5 text-sm">Void</button>
            @endif
            @endpermission
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: document --}}
        <div class="space-y-6 lg:col-span-2">
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
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">From</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ company_settings()->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ company_settings()->email }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Credit To</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $creditNote->client?->name }}</p>
                            @if ($creditNote->client?->company_name)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $creditNote->client->company_name }}</p>@endif
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $creditNote->client?->email }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-8 text-sm">
                        <div><span class="text-gray-400">Issue Date:</span> <span class="text-gray-900 dark:text-white">{{ $creditNote->issue_date?->format('M d, Y') }}</span></div>
                        @if ($creditNote->invoice)
                            <div><span class="text-gray-400">Ref. Invoice:</span> <a href="{{ route('admin.invoices.show', $creditNote->invoice) }}" class="font-medium text-brand-purple hover:underline">{{ $creditNote->invoice->invoice_number }}</a></div>
                        @endif
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

                    {{-- Reason --}}
                    <div class="mt-8 rounded-lg border border-red-200 border-l-[3px] border-l-red-500 bg-red-50 p-4 dark:border-red-900/40 dark:bg-red-900/10">
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-red-500">Reason for Credit Note</p>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $creditNote->reason }}</p>
                        @if ($creditNote->invoice)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Issued against {{ $creditNote->invoice->invoice_number }} — {{ money($creditNote->invoice->total) }}</p>
                        @endif
                    </div>

                    @if ($creditNote->notes)
                        <div class="mt-6 border-t border-gray-200 pt-6 text-sm dark:border-ink-600">
                            <p class="mb-1 font-semibold text-gray-700 dark:text-gray-200">Notes</p>
                            <p class="whitespace-pre-line text-gray-500 dark:text-gray-400">{{ $creditNote->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Applied to --}}
            <div class="card p-5">
                <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Applied To</h3>
                @if ($creditNote->applications->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($creditNote->applications as $app)
                            <div wire:key="app-{{ $app->id }}" class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2 text-sm dark:border-ink-700">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span class="font-mono font-medium text-green-600 dark:text-green-400">{{ money($app->amount) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">applied to</span>
                                    @if ($app->invoice)
                                        <a href="{{ route('admin.invoices.show', $app->invoice) }}" class="font-mono text-brand-purple hover:underline">{{ $app->invoice->invoice_number }}</a>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400">{{ $app->applied_at?->format('M d, Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-4 text-center text-sm text-gray-400">Not applied to any invoice yet.</p>
                @endif
            </div>
        </div>

        {{-- Right: details + apply --}}
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Issue Date</dt><dd class="text-gray-900 dark:text-white">{{ $creditNote->issue_date?->format('M d, Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Credit Amount</dt><dd class="font-mono font-semibold text-gray-900 dark:text-white">{{ money($creditNote->total) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Amount Applied</dt><dd class="font-mono font-semibold text-green-600 dark:text-green-400">{{ money($creditNote->amount_applied) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Remaining</dt><dd class="font-mono font-semibold {{ $creditNote->amount_remaining > 0 ? 'text-brand-purple' : 'text-gray-400' }}">{{ money($creditNote->amount_remaining) }}</dd></div>
                </dl>
                @php $pct = (float) $creditNote->total > 0 ? min(100, round((float) $creditNote->amount_applied / (float) $creditNote->total * 100)) : 0; @endphp
                <div class="mt-4">
                    <div class="mb-1 flex justify-between text-xs text-gray-400"><span>Applied</span><span>{{ $pct }}%</span></div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-ink-800">
                        <div class="h-2 rounded-full bg-green-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Apply to invoice --}}
            @permission('credit-notes.edit')
            @if ($creditNote->status === 'issued' && $creditNote->amount_remaining > 0)
                <div class="card p-5">
                    <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Apply to Invoice</h3>
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Reduce an outstanding invoice using this credit.</p>
                    <div class="space-y-3">
                        <div>
                            <label class="form-label">Invoice</label>
                            <select wire:model="applyInvoiceId" class="form-input-base">
                                <option value="">Select an invoice…</option>
                                @foreach ($openInvoices as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->invoice_number }} — {{ money($inv->outstanding_balance) }} due</option>
                                @endforeach
                            </select>
                            @error('applyInvoiceId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Amount</label>
                            <input wire:model="applyAmount" type="number" step="0.01" min="0.01" max="{{ $creditNote->amount_remaining }}" class="form-input-base" placeholder="Max {{ money($creditNote->amount_remaining) }}">
                            @error('applyAmount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button wire:click="apply" class="btn-primary w-full justify-center">Apply Credit</button>
                        @if ($openInvoices->isEmpty())
                            <p class="text-center text-xs text-gray-400">This client has no invoices with an outstanding balance.</p>
                        @endif
                    </div>
                </div>
            @endif
            @endpermission

            {{-- Client --}}
            <div class="card p-5">
                <h3 class="mb-3 font-semibold text-gray-900 dark:text-white">Client</h3>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $creditNote->client?->name }}</p>
                @if ($creditNote->client?->email)<p class="text-sm text-gray-500 dark:text-gray-400">{{ $creditNote->client->email }}</p>@endif
                <a href="{{ route('admin.clients.show', $creditNote->client) }}" wire:navigate class="mt-2 inline-block text-sm font-medium text-brand-purple hover:underline">View client →</a>
            </div>
        </div>
    </div>

    {{-- Void confirmation --}}
    @if ($confirmingVoid)
        <x-app-modal title="Void credit note?" close="$set('confirmingVoid', false)">
            <p class="text-sm text-gray-600 dark:text-gray-300">Voiding marks <strong>{{ $creditNote->credit_note_number }}</strong> as cancelled. This can't be undone from the UI.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="$set('confirmingVoid', false)" class="btn-secondary">Cancel</button>
                <button wire:click="void" class="btn-danger">Void</button>
            </div>
        </x-app-modal>
    @endif
</div>
