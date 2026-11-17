import Alpine from 'alpinejs';

/**
 * Halaman login (Operator/Kurikulum 1.01, Siswa 1.01–1.02).
 * Form tetap POST ke route login.attempt (AuthController). Mode demo hanya mengisi nama pengguna;
 * saat FRONTEND_PREVIEW aktif, "Masuk" dengan peran demo langsung membuka halaman peran tersebut.
 */
Alpine.data('loginPage', ({ demo, peranAwal, usernameAwal, preview }) => ({
    demo,
    peran: peranAwal,
    username: usernameAwal ?? demo.find((d) => d.peran === peranAwal)?.username ?? '',
    password: '',
    lihat: false,
    memuat: false,
    bantuan: false,

    pilih(d) {
        this.peran = d.peran;
        this.username = d.username;
        this.password = 'demo1234';
        this.$nextTick(() => this.$refs.masuk.focus());
    },
    kirim(e) {
        const d = this.demo.find((x) => x.peran === this.peran && x.username === this.username);
        if (preview && d) {
            e.preventDefault();
            this.memuat = true;
            location.href = d.url;
            return;
        }
        this.memuat = true;
    },
}));
