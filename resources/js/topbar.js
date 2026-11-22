
/* ==========================================================
   TOPBAR SIAKAD
   Dropdown, pencarian, dan interaksi menu.
   ========================================================== */

document.addEventListener('DOMContentLoaded', () => {
    const topbar = document.querySelector('[data-topbar]');

    if (!topbar) return;

    // Dropdown tahun ajaran, notifikasi, dan profil
    const dropdowns = topbar.querySelectorAll('[data-tb-dd]');

    const closeDropdowns = (except = null) => {
        dropdowns.forEach((dropdown) => {
            if (dropdown === except) return;

            const button = dropdown.querySelector('[data-tb-dd-toggle]');
            const menu = dropdown.querySelector('[data-tb-dd-menu]');

            if (menu) menu.hidden = true;
            if (button) button.setAttribute('aria-expanded', 'false');
        });
    };

    dropdowns.forEach((dropdown) => {
        const button = dropdown.querySelector('[data-tb-dd-toggle]');
        const menu = dropdown.querySelector('[data-tb-dd-menu]');

        if (!button || !menu) return;

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            const willOpen = menu.hidden;

            closeDropdowns(dropdown);

            menu.hidden = !willOpen;
            button.setAttribute('aria-expanded', String(willOpen));
        });
    });

    document.addEventListener('click', (event) => {
        if (!topbar.contains(event.target)) {
            closeDropdowns();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdowns();
        }
    });

    // Pemilihan tahun ajaran
    topbar.querySelectorAll('[data-tb-year]').forEach((button) => {
        button.addEventListener('click', () => {
            const label = topbar.querySelector('[data-tb-year-label]');

            if (label) {
                label.textContent = button.dataset.tbYear;
            }

            topbar.querySelectorAll('[data-tb-year]').forEach((item) => {
                item.classList.remove('is-selected');
                item.setAttribute('aria-selected', 'false');
            });

            button.classList.add('is-selected');
            button.setAttribute('aria-selected', 'true');

            closeDropdowns();
        });
    });

    // Tombol pencarian pada layar kecil
    const search = topbar.querySelector('[data-tb-search]');
    const searchToggle = topbar.querySelector('[data-tb-search-toggle]');
    const searchInput = topbar.querySelector('[data-tb-search-input]');

    if (searchToggle && search && searchInput) {
        searchToggle.addEventListener('click', () => {
            search.classList.toggle('is-search-open');

            if (search.classList.contains('is-search-open')) {
                searchInput.focus();
            }
        });
    }

    // Pintasan Ctrl + K / Cmd + K
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchInput?.focus();
        }
    });

    // Tandai semua notifikasi sebagai dibaca (tampilan)
    const readAll = topbar.querySelector('[data-tb-read-all]');

    if (readAll) {
        readAll.addEventListener('click', () => {
            topbar.querySelectorAll('.tb-notif__item.is-new').forEach((item) => {
                item.classList.remove('is-new');
            });

            topbar.querySelector('[data-tb-dot]')?.remove();
            readAll.remove();
        });
    }
});
