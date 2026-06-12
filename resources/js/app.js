import './bootstrap';

// NOTE: Livewire 3 ships and starts its own Alpine instance.
// Do NOT import/start Alpine here or it will initialise twice.
// Any custom Alpine components are registered via the `alpine:init`
// event so they are available to Livewire's bundled Alpine.

import Chart from 'chart.js/auto';
window.Chart = Chart;
