import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

/**
 * Toast global. Panggil dari mana saja:
 *   window.dispatchEvent(new CustomEvent('toast', { detail: { message: '...', type: 'success' } }))
 * atau di Alpine: $dispatch('toast', { message: '...', type: 'success' })
 * Tipe: success | info | warning | danger | violet. Opsional: action (label tombol kanan).
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

/**
 * Dialog konfirmasi global (lihat components/ui/confirm-dialog):
 *   if (await this.$store.confirm.ask({ title, message, confirmLabel: 'Hapus', tone: 'danger' })) { ... }
 */
Alpine.store('confirm', {
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Ya, lanjutkan',
    tone: 'primary',
    resolver: null,
    ask({ title, message = '', confirmLabel = 'Ya, lanjutkan', tone = 'primary' }) {
        Object.assign(this, { title, message, confirmLabel, tone, open: true });
        return new Promise((resolve) => (this.resolver = resolve));
    },
    jawab(ya) {
        this.open = false;
        this.resolver?.(ya);
        this.resolver = null;
    },
});

/** Kelas badge per nilai, satu sumber untuk seluruh halaman (selaras dengan components/ui/badge). */
const TONE = {
    success: 'bg-success-soft text-success',
    warning: 'bg-warning-soft text-warning',
    danger: 'bg-danger-soft text-danger',
    info: 'bg-info-soft text-info',
    violet: 'bg-violet-soft text-violet',
    neutral: 'bg-neutral-soft text-neutral',
    yellow: 'bg-amber-100 text-amber-700',
};

Alpine.store('ref', {
    tone: TONE,
    peran: {
        'Admin Kurikulum': 'success',
        'Admin Kesiswaan': 'success',
        Operator: 'neutral',
        Guru: 'info',
        'Wali Kelas': 'violet',
        'Guru BK': 'warning',
        Siswa: 'neutral',
        'Orang Tua': 'yellow',
    },
    badge(tone) {
        return `inline-flex items-center whitespace-nowrap rounded-md px-2 py-0.5 text-xs font-semibold ${TONE[tone] ?? TONE.neutral}`;
    },
    peranBadge(peran) {
        return this.badge(this.peran[peran] ?? 'neutral');
    },
});

/** $angka(2010) → "2.010" */
Alpine.magic('angka', () => (n) => Number(n ?? 0).toLocaleString('id-ID'));

/** $tanggal('2026-10-30') → "30 Oktober 2026"; $tanggal(iso, true) → "30 Okt 2026" */
Alpine.magic('tanggal', () => (iso, pendek = false) => {
    if (!iso) return '';
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString('id-ID', { day: 'numeric', month: pendek ? 'short' : 'long', year: 'numeric' }).replace('.', '');
});

window.Alpine = Alpine;
Alpine.plugin(focus);

// Dimulai setelah semua module script halaman dijalankan, supaya komponen
// Alpine.data() yang didaftarkan file JS halaman sudah tersedia.
document.addEventListener('DOMContentLoaded', () => Alpine.start());
