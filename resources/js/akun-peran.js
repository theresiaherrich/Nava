document.addEventListener('DOMContentLoaded', () => {
    // Filter peran & status langsung dikirim saat dipilih
    document.querySelectorAll('[data-auto-submit]').forEach((el) => {
        el.addEventListener('change', () => el.form?.submit());
    });

    // Pencarian dikirim saat tekan Enter atau tombol hapus (×) ditekan
    const searchInput = document.querySelector('[data-search]');
    if (searchInput) {
        searchInput.addEventListener('search', () => searchInput.form?.submit());
    }

    // Modal Tambah Akun
    const modal = document.querySelector('[data-modal]');
    const bukaBtn = document.querySelector('[data-buka-modal]');

    const bukaModal = () => {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        modal.querySelector('input[name="nama"]')?.focus();
    };

    const tutupModal = () => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    bukaBtn?.addEventListener('click', bukaModal);
    document.querySelectorAll('[data-tutup-modal]').forEach((btn) => btn.addEventListener('click', tutupModal));

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) tutupModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') tutupModal();
    });

    // Tampilkan kolom NIP & status hanya jika peran Guru dipilih
    const checkGuru = document.querySelector('[data-peran-check="guru"]');
    const guruBox = document.querySelector('[data-guru-box]');

    const perbaruiGuruBox = () => {
        if (!guruBox) return;
        guruBox.hidden = !(checkGuru && checkGuru.checked);
    };

    checkGuru?.addEventListener('change', perbaruiGuruBox);
    perbaruiGuruBox();

    // Buat password otomatis
    document.querySelector('[data-buat-password]')?.addEventListener('click', () => {
        const input = document.getElementById('ak-password');
        if (!input) return;

        const karakter = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        const acak = new Uint32Array(10);
        crypto.getRandomValues(acak);
        input.value = Array.from(acak, (n) => karakter[n % karakter.length]).join('');
        input.type = 'text';
    });

    // Form belum terhubung ke server. Cegah submit agar halaman tidak reload.
    document.querySelector('[data-form-akun]')?.addEventListener('submit', (e) => {
        e.preventDefault();
    });

    // Tombol aksi di tabel (ubah, reset password, aktif/nonaktif) belum terhubung
});