import Alpine from 'alpinejs';

/**
 * Data Kelas & Anggota Kelas (Operator 2.07–2.08).
 * TODO API: POST /api/master/kelas, POST /api/master/kelas/{id}/anggota, POST /api/master/anggota-kelas/pindah
 */
Alpine.data('kelasPage', ({ tingkat, kelas, tanpaKelas, bukaPindah }) => ({
    tingkat,
    kelas,
    tanpaKelas,
    aktif: Math.max(0, kelas.findIndex((k) => k.kelas.endsWith('-B'))),
    cari: '',
    page: 1,
    perPage: 10,
    pindah: { siswa: null, ke: '', alasan: '', error: '' },
    tambah: { dipilih: [] },
    kelasBaru: { nama: '', wali: '', error: '' },
    semester: 'Ganjil 2026/2027',

    init() {
        this.$watch('cari', () => (this.page = 1));
        this.$watch('aktif', () => { this.page = 1; this.cari = ''; });
        if (bukaPindah) {
            const s = this.kelasAktif.anggota.find((x) => x.nama === 'Bagas Pratama') ?? this.kelasAktif.anggota[0];
            this.bukaPindahkan(s, false);
            this.pindah.ke = this.kelas.find((k) => k.kelas.endsWith('-C'))?.kelas ?? '';
        }
    },

    get kelasAktif() {
        return this.kelas[this.aktif];
    },

    get totalSiswa() {
        return this.kelas.reduce((n, k) => n + k.anggota.length, 0);
    },

    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        return this.kelasAktif.anggota.filter((s) => !q || s.nama.toLowerCase().includes(q) || s.nis.includes(q));
    },

    get halaman() {
        return Math.max(1, Math.ceil(this.tersaring.length / this.perPage));
    },

    get barisHalaman() {
        return this.tersaring.slice((this.page - 1) * this.perPage, this.page * this.perPage);
    },

    get rentang() {
        if (!this.tersaring.length) return '0 dari 0';
        const awal = (this.page - 1) * this.perPage + 1;
        return `${awal}–${awal + this.barisHalaman.length - 1} dari ${this.tersaring.length}`;
    },

    inisial(nama) {
        return nama.split(' ').slice(0, 2).map((k) => k[0]).join('').toUpperCase();
    },

    // ---- Pindahkan siswa (2.08) ----
    bukaPindahkan(s, buka = true) {
        this.pindah = { siswa: s, ke: '', alasan: '', error: '' };
        if (buka) this.$dispatch('open-modal', 'pindah');
    },

    get tujuanPindah() {
        return this.kelas.filter((k) => k.kelas !== this.kelasAktif.kelas);
    },

    simpanPindah() {
        const { siswa, ke } = this.pindah;
        if (!ke) {
            this.pindah.error = 'Pilih kelas tujuan';
            return;
        }
        const tujuan = this.kelas.find((k) => k.kelas === ke);
        this.kelasAktif.anggota = this.kelasAktif.anggota.filter((x) => x.nis !== siswa.nis);
        tujuan.anggota.push(siswa);
        this.page = Math.min(this.page, this.halaman);
        this.$dispatch('close-modal', 'pindah');
        this.$dispatch('toast', { message: `${siswa.nama} dipindahkan ke ${ke}`, type: 'success' });
    },

    // ---- Tambah siswa ke kelas ----
    bukaTambah() {
        this.tambah.dipilih = [];
        this.$dispatch('open-modal', 'tambah-anggota');
    },

    toggleTambah(nis) {
        const i = this.tambah.dipilih.indexOf(nis);
        i === -1 ? this.tambah.dipilih.push(nis) : this.tambah.dipilih.splice(i, 1);
    },

    simpanTambah() {
        const baru = this.tanpaKelas.filter((s) => this.tambah.dipilih.includes(s.nis));
        this.kelasAktif.anggota.push(...baru);
        this.tanpaKelas = this.tanpaKelas.filter((s) => !this.tambah.dipilih.includes(s.nis));
        this.$dispatch('close-modal', 'tambah-anggota');
        this.$dispatch('toast', { message: `${baru.length} siswa ditambahkan ke ${this.kelasAktif.kelas}`, type: 'success' });
    },

    // ---- Tambah kelas ----
    bukaKelasBaru() {
        const huruf = String.fromCharCode(65 + this.kelas.length);
        this.kelasBaru = { nama: `${this.tingkat}-${huruf}`, wali: '', error: '' };
        this.$dispatch('open-modal', 'kelas-baru');
    },

    simpanKelasBaru() {
        const nama = this.kelasBaru.nama.trim().toUpperCase();
        if (!new RegExp(`^${this.tingkat}-[A-Z]$`).test(nama)) {
            this.kelasBaru.error = `Format nama kelas: ${this.tingkat}-A sampai ${this.tingkat}-Z`;
            return;
        }
        if (this.kelas.some((k) => k.kelas === nama)) {
            this.kelasBaru.error = 'Kelas sudah ada';
            return;
        }
        this.kelas.push({ id_kelas: nama.toLowerCase(), kelas: nama, wali: this.kelasBaru.wali.trim() || null, anggota: [] });
        this.aktif = this.kelas.length - 1;
        this.$dispatch('close-modal', 'kelas-baru');
        this.$dispatch('toast', { message: `Kelas ${nama} dibuat`, type: 'success' });
    },
}));
