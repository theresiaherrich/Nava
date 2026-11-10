document.addEventListener('DOMContentLoaded', () => {
    // Pencarian dikirim saat tekan Enter atau saat tombol hapus (×) ditekan.
    const searchForm = document.querySelector('[data-search-form]');
    const searchInput = document.querySelector('[data-search]');

    if (searchForm && searchInput) {
        searchInput.addEventListener('search', () => searchForm.submit());
    }

    // Tab Rapor Semester yang belum terbit: tampilkan tooltip, jangan pindah halaman.
    document.querySelectorAll('[data-tab-disabled]').forEach((tab) => {
        const tampilkanTooltip = () => {
            tab.classList.add('is-tip');
            setTimeout(() => tab.classList.remove('is-tip'), 1800);
        };

        tab.addEventListener('click', (e) => {
            e.preventDefault();
            tampilkanTooltip();
        });

        tab.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                tampilkanTooltip();
            }
        });
    });
});