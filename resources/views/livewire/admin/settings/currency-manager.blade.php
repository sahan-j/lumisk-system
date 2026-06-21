<div>
    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-ink-600">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-ink-600">
            <thead class="bg-gray-50 dark:bg-ink-800">
                <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2 text-center">Symbol</th>
                    <th class="px-4 py-2 text-right">Rate (to LKR)</th>
                    <th class="px-4 py-2 text-center">Active</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-ink-700">
                @foreach ($currencies as $currency)
                    <tr wire:key="cur-{{ $currency->id }}">
                        <td class="px-4 py-2.5">
                            <span class="font-mono font-semibold text-brand-purple">{{ $currency->code }}</span>
                            @if ($currency->is_default)
                                <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">default</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                            {{ $currency->name }}
                            @if ($currency->updated_at_rate && ! $currency->is_default)
                                <span class="block text-[10px] text-gray-400">rate updated {{ $currency->updated_at_rate->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-center text-base font-semibold text-gray-900 dark:text-white">{{ $currency->symbol }}</td>
                        <td class="px-4 py-2.5">
                            @if ($currency->is_default)
                                <div class="text-right font-mono text-gray-400">1.0000</div>
                            @else
                                <div class="flex items-center justify-end gap-2">
                                    <input wire:model="rates.{{ $currency->id }}" type="number" step="0.0001" min="0"
                                           class="form-input-base !w-28 !py-1 text-right text-sm">
                                    <button wire:click="updateRate({{ $currency->id }})" type="button" class="btn-primary !py-1 !px-3 text-xs">Update</button>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            @if ($currency->is_default)
                                <span class="inline-flex h-5 w-9 items-center rounded-full bg-green-500 px-0.5"><span class="ml-auto h-4 w-4 rounded-full bg-white"></span></span>
                            @else
                                <button wire:click="toggle({{ $currency->id }})" type="button"
                                        class="inline-flex h-5 w-9 items-center rounded-full px-0.5 transition {{ $currency->is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-ink-600' }}">
                                    <span class="h-4 w-4 rounded-full bg-white transition {{ $currency->is_active ? 'ml-auto' : '' }}"></span>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-gray-400">
        Update exchange rates manually. Rates apply to <strong>new</strong> documents only — existing invoices and estimates keep the rate stored at creation time.
    </p>
</div>
