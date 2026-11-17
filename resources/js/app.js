// Topbar (<x-topbar>): dropdown, pencarian, dan pintasan Ctrl/⌘ + K.
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.querySelector('[data-topbar]');
    if (!bar) return;

    /* ---------- Dropdown (tahun ajaran, notifikasi, profil) ---------- */
    const dropdowns = [...bar.querySelectorAll('[data-tb-dd]')];

    const setOpen = (dd, open) => {
        const btn = dd.querySelector('[data-tb-dd-toggle]');
        const menu = dd.querySelector('[data-tb-dd-menu]');
        if (!btn || !menu) return;
        menu.hidden = !open;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    const closeAll = (except) => dropdowns.forEach((dd) => dd !== except && setOpen(dd, false));

    dropdowns.forEach((dd) => {
        const toggleBtn = dd.querySelector('[data-tb-dd-toggle]');
        if (!toggleBtn) return;
        toggleBtn.addEventListener('click', () => {
            const open = dd.querySelector('[data-tb-dd-menu]').hidden;
            closeAll(dd);
            setOpen(dd, open);
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-tb-dd]')) closeAll();
    });

    /* ---------- Pilih tahun ajaran (dummy: hanya ganti label) ---------- */
    const yearLabel = bar.querySelector('[data-tb-year-label]');
    bar.querySelectorAll('[data-tb-year]').forEach((opt) => {
        opt.addEventListener('click', () => {
            bar.querySelectorAll('[data-tb-year]').forEach((o) => {
                o.classList.toggle('is-selected', o === opt);
                o.setAttribute('aria-selected', o === opt ? 'true' : 'false');
            });
            if (yearLabel) yearLabel.textContent = opt.dataset.tbYear;
            closeAll();
        });
    });

    /* ---------- Tandai semua notifikasi dibaca ---------- */
    const readAll = bar.querySelector('[data-tb-read-all]');
    if (readAll) {
        readAll.addEventListener('click', () => {
            bar.querySelectorAll('.tb-notif__item.is-new').forEach((n) => n.classList.remove('is-new'));
            bar.querySelector('[data-tb-dot]')?.remove();
            readAll.remove();
        });
    }

    /* ---------- Pencarian ---------- */
    const searchForm = bar.querySelector('[data-tb-search]');
    const searchInput = bar.querySelector('[data-tb-search-input]');
    const searchToggle = bar.querySelector('[data-tb-search-toggle]');
    const kbd = bar.querySelector('[data-tb-kbd]');

    const isMac = /Mac|iPhone|iPad/i.test(navigator.platform || navigator.userAgent);
    if (kbd && !isMac) kbd.textContent = 'Ctrl K';

    const openSearch = () => {
        if (searchForm) searchForm.classList.add('is-open');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    };

    searchToggle?.addEventListener('click', openSearch);
    searchInput?.addEventListener('blur', () => {
        if (!searchInput.value && searchForm) searchForm.classList.remove('is-open');
    });

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            closeAll();
            openSearch();
        }
        if (e.key === 'Escape') {
            closeAll();
            if (searchInput && document.activeElement === searchInput) {
                searchInput.blur();
            }
        }
    });
});
