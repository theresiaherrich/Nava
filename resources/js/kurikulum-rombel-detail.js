import Alpine from 'alpinejs';
import { gabung, inisial, namaPendek, pemilihGuru, warnaAvatar } from './kurikulum-rombel.js';

/** Detail Rombel (Kurikulum 4.08–4.09). TODO API: POST/DELETE /api/kurikulum/rombel/{id}/peserta, PUT .../pengampu */
Alpine.data('rombelDetail', ({ rombel, calonPeserta, calon, bukaModal }) => gabung(pemilihGuru(calon), {
    rombel,
    calonPeserta,
    filterKelas: 'semua',
    cari: '',
    page: 1,
    perPage: 10,
    tambah: { kelas: rombel.kelas_asal[0], cari: '', dipilih: [] },
    keluarkanTarget: null,
    inisial,
    warnaAvatar,
    namaPendek,

    init() {
        ['filterKelas', 'cari'].forEach((k) => this.$watch(k, () => (this.page = 1)));
        if (bukaModal) {
            // Contoh pilihan sesuai desain 4.09.
            this.tambah.dipilih = this.calonPeserta.filter((s) => ['Dafa Ramadhan', 'Elisa Putri', 'Hana Safira'].includes(s.nama)).map((s) => s.nis);
        }
    },

    get opsiKelas() {
        return { semua: 'Semua', ...Object.fromEntries(this.rombel.kelas_asal.map((k) => [k, k])) };
    },

    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        return this.rombel.peserta.filter((s) => (this.filterKelas === 'semua' || s.kelas_asal === this.filterKelas)
            && (!q || s.nama.toLowerCase().includes(q) || s.nis.includes(q)));
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
        return `${awal}-${awal + this.barisHalaman.length - 1} dari ${this.tersaring.length}`;
    },

    jumlahKelas(k) {
        return this.rombel.peserta.filter((s) => s.kelas_asal === k).length;
    },

    warnaKelas(k) {
        return this.rombel.kelas_asal.indexOf(k) % 2 === 0 ? 'bg-violet-soft text-violet' : 'bg-info-soft text-info';
    },

    // ---- Ganti pengampu ----
    pilih(guru) {
        if (guru.penuh) return;
        this.rombel.guru = guru.nama;
        this.rombel.guru_status = guru.status;
        this.tutupPicker();
        this.$dispatch('toast', { message: `Pengampu ${this.rombel.nama} disimpan`, type: 'success' });
    },

    // ---- Keluarkan peserta ----
    konfirmasiKeluarkan(s) {
        this.keluarkanTarget = s;
        this.$dispatch('open-modal', 'keluarkan');
    },

    keluarkan() {
        const s = this.keluarkanTarget;
        this.rombel.peserta = this.rombel.peserta.filter((p) => p.nis !== s.nis);
        this.calonPeserta.forEach((c) => c.nis === s.nis && (c.terdaftar = false));
        this.page = Math.min(this.page, this.halaman);
        this.$dispatch('close-modal', 'keluarkan');
        this.$dispatch('toast', { message: `${s.nama} dikeluarkan dari ${this.rombel.nama}`, type: 'success' });
    },

    // ---- Tambah peserta (4.09) ----
    bukaTambah() {
        this.tambah = { kelas: this.rombel.kelas_asal[0], cari: '', dipilih: [] };
        this.$dispatch('open-modal', 'tambah-peserta');
    },

    get calonTampil() {
        const q = this.tambah.cari.trim().toLowerCase();
        return this.calonPeserta.filter((s) => s.kelas_asal === this.tambah.kelas
            && (!q || s.nama.toLowerCase().includes(q) || s.nis.includes(q)));
    },

    toggle(s) {
        if (s.terdaftar) return;
        const i = this.tambah.dipilih.indexOf(s.nis);
        i === -1 ? this.tambah.dipilih.push(s.nis) : this.tambah.dipilih.splice(i, 1);
    },

    simpanPeserta() {
        const baru = this.calonPeserta.filter((s) => this.tambah.dipilih.includes(s.nis));
        baru.forEach((s) => {
            s.terdaftar = true;
            this.rombel.peserta.push({ nis: s.nis, nama: s.nama, kelas_asal: s.kelas_asal });
        });
        this.$dispatch('close-modal', 'tambah-peserta');
        this.$dispatch('toast', { message: `${baru.length} siswa ditambahkan ke ${this.rombel.nama}`, type: 'success' });
        this.tambah.dipilih = [];
    },
}));
