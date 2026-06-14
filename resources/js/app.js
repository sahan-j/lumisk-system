import './bootstrap';

// NOTE: Livewire 3 ships and starts its own Alpine instance.
// Do NOT import/start Alpine here or it will initialise twice.
// Any custom Alpine components are registered via the `alpine:init`
// event so they are available to Livewire's bundled Alpine.

import Chart from 'chart.js/auto';
window.Chart = Chart;

// Global search (Ctrl/Cmd+K) — admin navbar dropdown + keyboard navigation.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('globalSearch', () => ({
        open: false,
        activeIndex: -1,
        openSearch() {
            this.open = true;
            this.$nextTick(() => this.$refs.searchInput.focus());
        },
        closeSearch() {
            this.open = false;
            this.$refs.searchInput.blur();
        },
        moveDown() {
            const items = this.$el.querySelectorAll('.search-item');
            if (items.length === 0) return;
            this.activeIndex = Math.min(this.activeIndex + 1, items.length - 1);
            this.highlight(items);
        },
        moveUp() {
            const items = this.$el.querySelectorAll('.search-item');
            if (items.length === 0) return;
            this.activeIndex = Math.max(this.activeIndex - 1, 0);
            this.highlight(items);
        },
        highlight(items) {
            items.forEach((i) => i.classList.remove('active'));
            if (this.activeIndex >= 0) {
                items[this.activeIndex].classList.add('active');
                items[this.activeIndex].scrollIntoView({ block: 'nearest' });
            }
        },
        selectActive() {
            const items = this.$el.querySelectorAll('.search-item');
            if (this.activeIndex >= 0 && items[this.activeIndex]) {
                items[this.activeIndex].click();
            }
        },
    }));
});
