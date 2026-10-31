const MAX_ALASAN = 500;

const $ = (selector, context = document) => context.querySelector(selector);
const $$ = (selector, context = document) => [...context.querySelectorAll(selector)];

document.addEventListener('DOMContentLoaded', () => {
    initPeriodDropdown();
    initMapelFilter();
    initModals();
    initRevisiForm();
});

/* Dropdown periode */
function initPeriodDropdown() {
    const toggle = $('[data-period-toggle]');
    const menu = $('[data-period-menu]');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });
    document.addEventListener('click', () => menu.classList.add('hidden'));
    menu.addEventListener('click', (e) => e.stopPropagation());
}

/* Filter mata pelajaran (Menunggu / Semua) */
function initMapelFilter() {
    const buttons = $$('[data-filter]');
    const items = $$('[data-mapel-item]');
    if (!buttons.length) return;

    const setActive = (active) => {
        buttons.forEach((b) => {
            const on = b === active;
            b.classList.toggle('bg-white', on);
            b.classList.toggle('shadow-sm', on);
            b.classList.toggle('text-slate-900', on);
            b.classList.toggle('text-slate-500', !on);
        });
    };

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            setActive(btn);
            const mode = btn.dataset.filter;
            items.forEach((item) => {
                const show = mode === 'semua' || item.dataset.perlu === '1';
                item.classList.toggle('hidden', !show);
            });
        });
    });

    setActive($('[data-filter="semua"]'));
}

/* Modal umum: class 'hidden' saat tutup, 'flex' saat buka */
function initModals() {
    const modals = $$('[data-modal]');

    const open = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        modal.querySelector('textarea, input:not([type="checkbox"])')?.focus();
    };

    const close = (modal) => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    };

    $$('[data-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => open(btn.dataset.modalOpen));
    });

    modals.forEach((modal) => {
        $$('[data-modal-close]', modal).forEach((btn) => {
            btn.addEventListener('click', () => close(modal));
        });
        modal.addEventListener('click', (e) => {
            if (e.target === modal) close(modal);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        modals.forEach((m) => {
            if (m.classList.contains('flex')) close(m);
        });
    });
}

/* Form minta revisi: validasi alasan, counter, pencarian siswa */
function initRevisiForm() {
    const form = $('#form-revisi');
    if (!form) return;

    const textarea = $('[data-revisi-alasan]', form);
    const counter = $('[data-revisi-count]', form);
    const errorEl = $('[data-revisi-error]', form);
    const submitBtn = $('[data-revisi-submit]', form);
    const search = $('[data-siswa-search]', form);
    const options = $$('[data-siswa-option]', form);

    const validate = ({ showError = false } = {}) => {
        const value = textarea.value;
        const isEmpty = value.trim().length === 0;
        const isTooLong = value.length > MAX_ALASAN;
        const showErr = (showError && isEmpty) || isTooLong;

        counter.textContent = `${value.length}/${MAX_ALASAN}`;
        counter.classList.toggle('text-red-600', isTooLong);

        errorEl.classList.toggle('hidden', !showErr);
        textarea.classList.toggle('border-red-500', showErr);
        textarea.classList.toggle('border-slate-300', !showErr);

        const valid = !isEmpty && !isTooLong;
        submitBtn.disabled = !valid;
        return valid;
    };

    textarea.addEventListener('input', () => validate());
    textarea.addEventListener('blur', () => validate({ showError: true }));

    search?.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        options.forEach((opt) => {
            opt.classList.toggle('hidden', !opt.dataset.nama.toLowerCase().includes(q));
        });
    });

    form.addEventListener('submit', (e) => {
        if (!validate({ showError: true })) {
            e.preventDefault();
            textarea.focus();
            return;
        }
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
    });

    validate();
}