{{-- Global toast stack. Listens for Livewire `toast` events:
     $this->dispatch('toast', type: 'success', message: 'Saved'); --}}
<div x-data="{
        toasts: [],
        add(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type: detail.type || 'success', message: detail.message || '' });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
     }"
     x-init="window.Livewire && window.Livewire.on('toast', (e) => add(Array.isArray(e) ? e[0] : e))"
     class="fixed top-4 right-4 z-[100] flex w-80 flex-col gap-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="flex items-start gap-3 rounded-lg border bg-white p-4 shadow-lg dark:bg-ink-800"
             :class="{
                'border-green-200 dark:border-green-900/50': toast.type === 'success',
                'border-red-200 dark:border-red-900/50': toast.type === 'error',
                'border-brand-purple/30 dark:border-brand-purple/40': toast.type === 'info'
             }">
            <div class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full text-white"
                 :class="{
                    'bg-green-500': toast.type === 'success',
                    'bg-red-500': toast.type === 'error',
                    'bg-brand-purple': toast.type === 'info'
                 }">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path x-show="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    <path x-show="toast.type === 'error'" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    <path x-show="toast.type === 'info'" stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01" />
                </svg>
            </div>
            <p class="flex-1 text-sm text-gray-700 dark:text-gray-200" x-text="toast.message"></p>
            <button @click="remove(toast.id)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
