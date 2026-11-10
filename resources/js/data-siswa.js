document.addEventListener('DOMContentLoaded', () => {
    // Filter dikirim saat dropdown berubah
    document.querySelectorAll('[data-auto-submit]').forEach((el) => {
        el.addEventListener('change', () => el.form?.submit());
    });

    // Pencarian dikirim saat tekan Enter atau tombol hapus (×) ditekan
    const searchInput = document.querySelector('[data-search]');
    if (searchInput) {
        searchInput.addEventListener('search', () => searchInput.form?.submit());
    }

    // Modal Impor Excel
    const modal = document.querySelector('[data-modal-impor]');
    const bukaBtn = document.querySelector('[data-buka-impor]');

    const bukaModal = () => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const tutupModal = () => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    bukaBtn?.addEventListener('click', bukaModal);
    document.querySelectorAll('[data-tutup-impor]').forEach((btn) => btn.addEventListener('click', tutupModal));

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) tutupModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') tutupModal();
    });

    // Pilihan mode impor: sorot kartu yang dipilih
    document.querySelectorAll('.ds-option input[type="radio"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.ds-option').forEach((opt) => opt.classList.remove('is-selected'));
            radio.closest('.ds-option')?.classList.add('is-selected');
        });
    });

    // Tombol simpan impor belum terhubung ke server
    document.querySelector('[data-simpan-impor]')?.addEventListener('click', () => {
        // Diisi setelah route impor dibuat
    });
});