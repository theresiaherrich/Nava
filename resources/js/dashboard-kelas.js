// Dashboard Kelas: filter status, cari mapel, dan pagination tabel di sisi browser.
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-mapel]');
    if (!root) return;

    const PER_PAGE = 11;
    const rows = [...root.querySelectorAll('tr[data-row]')];
    const emptyRow = root.querySelector('[data-empty]');
    const statusSelect = root.querySelector('[data-mapel-status]');
    const searchInput = root.querySelector('[data-mapel-search]');
    const info = root.querySelector('[data-mapel-info]');
    const pager = root.querySelector('[data-mapel-pager]');

    let page = 1;

    const arrow = (d) =>
        `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${d}"/></svg>`;

    const button = (label, target, { active = false, disabled = false, aria = '' } = {}) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.innerHTML = label;
        b.disabled = disabled;
        if (active) {
            b.classList.add('is-active');
            b.setAttribute('aria-current', 'page');
        }
        if (aria) b.setAttribute('aria-label', aria);
        b.addEventListener('click', () => {
            page = target;
            render();
        });
        return b;
    };

    const render = () => {
        const status = statusSelect.value;
        const q = searchInput.value.trim().toLowerCase();

        const matched = rows.filter(
            (r) => (!status || r.dataset.status === status) && (!q || r.dataset.search.includes(q)),
        );

        const lastPage = Math.max(1, Math.ceil(matched.length / PER_PAGE));
        page = Math.min(Math.max(1, page), lastPage);
        const start = (page - 1) * PER_PAGE;
        const visible = new Set(matched.slice(start, start + PER_PAGE));

        rows.forEach((r) => (r.hidden = !visible.has(r)));
        emptyRow.hidden = matched.length > 0;

        const filtered = status || q;
        info.textContent = filtered
            ? `Menampilkan ${visible.size} dari ${matched.length} mapel (difilter dari ${rows.length})`
            : `Menampilkan ${visible.size} dari ${rows.length} mapel`;

        pager.innerHTML = '';
        if (lastPage <= 1) return;
        pager.append(button(arrow('m15 18-6-6 6-6'), page - 1, { disabled: page === 1, aria: 'Halaman sebelumnya' }));
        for (let i = 1; i <= lastPage; i++) {
            pager.append(button(String(i), i, { active: i === page, aria: `Halaman ${i}` }));
        }
        pager.append(button(arrow('m9 18 6-6-6-6'), page + 1, { disabled: page === lastPage, aria: 'Halaman berikutnya' }));
    };

    statusSelect.addEventListener('change', () => {
        page = 1;
        render();
    });
    searchInput.addEventListener('input', () => {
        page = 1;
        render();
    });

    render();
});
