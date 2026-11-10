document.addEventListener('DOMContentLoaded', () => {
    // Filter tingkat langsung dikirim saat dipilih
    document.querySelectorAll('[data-auto-submit]').forEach((el) => {
        el.addEventListener('change', () => el.form?.submit());
    });

    // Pencarian dikirim saat tekan Enter atau tombol hapus (×) ditekan
    const searchInput = document.querySelector('[data-search]');
    if (searchInput) {
        searchInput.addEventListener('search', () => searchInput.form?.submit());
    }
});