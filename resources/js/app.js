import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

/**
 * Toast global. Panggil dari mana saja:
 *   window.dispatchEvent(new CustomEvent('toast', { detail: { message: '...', type: 'success' } }))
 * atau di Alpine: $dispatch('toast', { message: '...', type: 'success' })
 * Tipe: success | info | warning | danger. Opsional: action (label tombol kanan).
 */
Alpine.store('toasts', {
    items: [],
    push({ message, type = 'success', action = null, timeout = 4000 }) {
        const id = Date.now() + Math.random();
        this.items.push({ id, message, type, action });
        setTimeout(() => this.remove(id), timeout);
    },
    remove(id) {
        this.items = this.items.filter((t) => t.id !== id);
    },
});

window.addEventListener('toast', (e) => Alpine.store('toasts').push(e.detail ?? {}));

window.Alpine = Alpine;
Alpine.plugin(focus);

// Dimulai setelah semua module script halaman dijalankan, supaya komponen
// Alpine.data() yang didaftarkan file JS halaman sudah tersedia.
document.addEventListener('DOMContentLoaded', () => Alpine.start());
