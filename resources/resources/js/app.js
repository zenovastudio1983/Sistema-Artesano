import './bootstrap';
import Chart from 'chart.js/auto';
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import TomSelect from 'tom-select';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

// Make Chart.js available globally for Blade components
window.Chart = Chart;

// Configure flatpickr defaults
flatpickr.localize(Spanish);
window.flatpickr = flatpickr;
window.flatpickrDefaults = {
    locale: 'es',
    dateFormat: 'd/m/Y',
    allowInput: true,
    disableMobile: false,
};

// Make TomSelect available globally
window.TomSelect = TomSelect;

// Register Alpine plugins and stores BEFORE Alpine starts.
// Livewire 3 bundles Alpine and fires 'alpine:init' before starting it.
// Do NOT import Alpine or call Alpine.start() here — Livewire controls that.
document.addEventListener('alpine:init', () => {
    Alpine.plugin(focus);
    Alpine.plugin(collapse);

    // Global notification store
    Alpine.store('notifications', {
        items: [],
        add(notification) {
            const id = Date.now();
            this.items.push({ id, ...notification });
            if (notification.timeout !== 0) {
                setTimeout(() => this.remove(id), notification.timeout ?? 4000);
            }
        },
        remove(id) {
            this.items = this.items.filter(n => n.id !== id);
        },
        success(message, title = 'Éxito') {
            this.add({ type: 'success', title, message });
        },
        error(message, title = 'Error') {
            this.add({ type: 'error', title, message, timeout: 6000 });
        },
        warning(message, title = 'Advertencia') {
            this.add({ type: 'warning', title, message, timeout: 5000 });
        },
        info(message, title = 'Información') {
            this.add({ type: 'info', title, message });
        },
    });

    // Confirmation dialog
    Alpine.data('confirm', () => ({
        open: false,
        message: '',
        onConfirm: null,
        show(message, callback) {
            this.message = message;
            this.onConfirm = callback;
            this.open = true;
        },
        confirm() {
            if (this.onConfirm) this.onConfirm();
            this.open = false;
        },
        cancel() {
            this.open = false;
            this.onConfirm = null;
        },
    }));

    // Dropdown menu
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; },
    }));

    // Sidebar state
    Alpine.data('sidebar', () => ({
        open: window.innerWidth >= 1024,
        toggle() { this.open = !this.open; },
        close() { if (window.innerWidth < 1024) this.open = false; },
    }));

    // Inline number formatter
    Alpine.magic('currency', () => (value, currency = null) => {
        const c = currency ?? document.documentElement.dataset.currency ?? 'PEN';
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: c,
            minimumFractionDigits: 2,
        }).format(value ?? 0);
    });

    Alpine.magic('number', () => (value, decimals = 2) => {
        return new Intl.NumberFormat('es-PE', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(value ?? 0);
    });
});

// Wire up Livewire events after Livewire initializes
document.addEventListener('livewire:init', () => {
    Livewire.on('notify', ({ type, message, title }) => {
        Alpine.store('notifications')[type ?? 'info'](message, title);
    });

    // Auto-initialize flatpickr on elements with data-flatpickr
    Livewire.hook('morph.updated', ({ el }) => {
        el.querySelectorAll('[data-flatpickr]').forEach(input => {
            if (!input._flatpickr) {
                flatpickr(input, {
                    ...window.flatpickrDefaults,
                    ...JSON.parse(input.dataset.flatpickr || '{}'),
                });
            }
        });

        el.querySelectorAll('[data-tomselect]').forEach(select => {
            if (!select.tomselect) {
                new TomSelect(select, {
                    create: false,
                    ...JSON.parse(select.dataset.tomselect || '{}'),
                });
            }
        });
    });
});
