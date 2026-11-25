const MAX_CATATAN = 300;
const DEBOUNCE_MS = 800;

const $ = (selector, context = document) => context.querySelector(selector);
const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];

document.addEventListener('DOMContentLoaded', () => {
    if (!$('[data-total]')) return;

    initDropdowns();
    initCatatan();
    initTemplateChips();
});

/* Dropdown: tombol membuka/menutup panel, klik di luar menutup semua */
function initDropdowns() {
    const dropdowns = $$('[data-dropdown]');
    if (!dropdowns.length) return;

    dropdowns.forEach((dd) => {
        const toggle = $('[data-dropdown-toggle]', dd);
        const menu = $('[data-dropdown-menu]', dd);
        if (!toggle || !menu) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdowns.forEach((other) => {
                if (other !== dd) $('[data-dropdown-menu]', other)?.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
        });

        menu.addEventListener('click', (e) => e.stopPropagation());
    });

    document.addEventListener('click', () => {
        $$('[data-dropdown-menu]').forEach((m) => m.classList.add('hidden'));
    });
}

/* Catatan: counter, status baris, autosave, dan total lengkap */
function initCatatan() {
    const page = $('[data-total]');
    if (page.dataset.terkunci === '1') return;

    const textareas = $$('textarea[data-catatan-url]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const timers = new Map();
    let lastFocused = null;

    const updateRow = (textarea) => {
        const row = textarea.closest('[data-row]');
        const value = textarea.value;
        const filled = value.trim().length > 0;

        row.querySelector('[data-counter]').textContent = value.length;

        textarea.classList.toggle('border-orange-300', !filled);
        textarea.classList.toggle('border-slate-300', filled);

        const status = row.querySelector('[data-status]');
        if (status) {
            status.textContent = filled ? 'Lengkap' : 'Belum lengkap';
            status.classList.toggle('bg-green-100', filled);
            status.classList.toggle('text-green-700', filled);
            status.classList.toggle('bg-orange-100', !filled);
            status.classList.toggle('text-orange-700', !filled);
        }

        updateTotal();
    };

    const updateTotal = () => {
        const total = Number(page.dataset.total);
        const filled = textareas.filter((t) => t.value.trim().length > 0).length;

        const countEl = $('#pl-lengkap-count');
        if (countEl) countEl.textContent = `${filled}/${total}`;

        const badge = $('#pl-lengkap-badge');
        if (badge) {
            const selesai = total > 0 && filled === total;
            badge.classList.toggle('bg-orange-100', !selesai);
            badge.classList.toggle('text-orange-700', !selesai);
            badge.classList.toggle('bg-green-100', selesai);
            badge.classList.toggle('text-green-700', selesai);
        }
    };

    const saveCatatan = async (textarea) => {
        try {
            const res = await fetch(textarea.dataset.catatanUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ catatan: textarea.value }),
            });
            if (!res.ok) throw new Error('Gagal menyimpan');

            const data = await res.json();
            const waktu = $('#pl-waktu-simpan');
            if (waktu && data.waktu) waktu.textContent = data.waktu;
        } catch (err) {
            console.error(err);
        }
    };

    textareas.forEach((textarea) => {
        textarea.addEventListener('focus', () => { lastFocused = textarea; });

        textarea.addEventListener('input', () => {
            if (textarea.value.length > MAX_CATATAN) {
                textarea.value = textarea.value.slice(0, MAX_CATATAN);
            }
            updateRow(textarea);

            clearTimeout(timers.get(textarea));
            timers.set(textarea, setTimeout(() => saveCatatan(textarea), DEBOUNCE_MS));
        });
    });

    // Saat disimpan manual, batalkan autosave yang masih menunggu
    $('#form-pelengkap')?.addEventListener('submit', () => {
        textareas.forEach((t) => clearTimeout(timers.get(t)));
    });

    window.plLastFocused = () => lastFocused;
    updateTotal();
}

/* Klik template: sisipkan ke catatan yang terakhir difokus */
function initTemplateChips() {
    const chips = $$('[data-template]');
    if (!chips.length) return;

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const target = window.plLastFocused?.();
            if (!target) return;

            const current = target.value.trim();
            const next = current ? `${current} ${chip.dataset.template}` : chip.dataset.template;

            target.value = next.slice(0, MAX_CATATAN);
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.focus();
        });
    });
}