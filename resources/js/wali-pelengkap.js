import Alpine from 'alpinejs';

const MAKS = 300;
const PER_HALAMAN = 8;
const TEMPLAT = {
    'Aktif & bertanggung jawab': 'Ananda aktif dan bertanggung jawab dalam kegiatan belajar.',
    'Perlu meningkatkan kehadiran': 'Ananda perlu meningkatkan kehadiran dan ketepatan waktu.',
    'Pertahankan prestasi': 'Pertahankan prestasi belajar dan terus kembangkan potensi diri.',
};
const jam = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');

/**
 * Pelengkap Rapor wali kelas (Wali 3.01–3.03): catatan wali kelas per siswa dengan simpan otomatis.
 * Kehadiran (Presensi A1) dan ekstrakurikuler (Kesiswaan) hanya-baca.
 * TODO API: PUT /api/wali/pelengkap-rapor/{nis} { periode, catatan } (simpan otomatis + tombol Simpan)
 */
Alpine.data('pelengkapPage', ({ siswa, state }) => ({
    terkunci: state === 'terkunci',
    belumA1: state === 'belum-a1',
    periode: 'sts',
    // Catatan rapor semester terpisah dari STS; semester belum dimulai sehingga masih kosong.
    catatan: { sts: Object.fromEntries(siswa.map((s) => [s.nis, s.catatan])), semester: Object.fromEntries(siswa.map((s) => [s.nis, ''])) },
    siswa,
    status: 'Semua siswa',
    cari: '',
    halaman: 1,
    tersimpan: '10.42',
    menyimpan: false,
    terakhirFokus: null,
    maks: MAKS,
    _timer: null,

    // Hasil filter status dibekukan saat filter dipilih agar baris tidak hilang ketika catatan sedang diketik.
    _cocok: null,

    init() {
        ['status', 'periode'].forEach((k) => this.$watch(k, () => this.bekukan()));
        this.$watch('cari', () => (this.halaman = 1));
    },
    bekukan() {
        this.halaman = 1;
        this._cocok = this.status === 'Semua siswa' ? null
            : new Set(this.siswa.filter((s) => (this.status === 'Lengkap') === this.lengkap(s)).map((s) => s.nis));
    },

    get isi() { return this.catatan[this.periode]; },
    lengkap(s) { return this.isi[s.nis].trim() !== ''; },
    get jumlahLengkap() { return this.siswa.filter((s) => this.lengkap(s)).length; },
    get jumlahBelum() { return this.siswa.length - this.jumlahLengkap; },
    get ekskulDinilai() { return this.siswa.filter((s) => s.ekskul.length).length; },
    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        return this.siswa.filter((s) => (!this._cocok || this._cocok.has(s.nis))
            && (!q || s.nama.toLowerCase().includes(q) || s.nis.includes(q)));
    },
    get jumlahHalaman() { return Math.max(1, Math.ceil(this.tersaring.length / PER_HALAMAN)); },
    get baris() { return this.tersaring.slice((this.halaman - 1) * PER_HALAMAN, this.halaman * PER_HALAMAN); },
    nomor(s) { return this.siswa.indexOf(s) + 1; },

    ubah(nis, nilai) {
        this.catatan[this.periode][nis] = nilai.slice(0, MAKS);
        clearTimeout(this._timer);
        this.menyimpan = true;
        this._timer = setTimeout(() => {
            this.menyimpan = false;
            this.tersimpan = jam();
        }, 1200);
    },
    sisipkan(label) {
        if (this.terkunci) return;
        const nis = this.terakhirFokus ?? this.siswa.find((s) => !this.lengkap(s))?.nis;
        if (!nis) return;
        const kini = this.isi[nis].trim();
        this.ubah(nis, (kini ? `${kini} ` : '') + TEMPLAT[label]);
        this.$nextTick(() => document.getElementById(`catatan-${nis}`)?.focus());
    },
    simpan() {
        clearTimeout(this._timer);
        this.menyimpan = false;
        this.tersimpan = jam();
        this.$dispatch('toast', { message: `Catatan wali kelas tersimpan · ${this.jumlahLengkap}/${this.siswa.length} lengkap`, type: 'success' });
    },
    tampilkanBelum() {
        this.status = 'Belum lengkap';
        this.$nextTick(() => document.getElementById('daftar-peserta')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    },
}));
