import Alpine from 'alpinejs';

const PER_HALAMAN = 10;
const waktu = () => {
    const d = new Date();
    const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][d.getMonth()];
    return `${d.getDate()} ${bulan} ${d.getFullYear()}, ${d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.')}`;
};

/**
 * Cetak & Ekspor Rapor wali kelas (Wali 5.01, 5.03–5.07).
 * TODO API: GET /api/rapor/{id}/pdf, POST /api/wali/rapor/kelas/pdf → job id; GET /api/jobs/{id} untuk progres;
 *           GET /api/wali/rapor/riwayat, GET /api/wali/rapor/riwayat/{id}/pdf (unduh ulang)
 */
Alpine.data('cetakWali', ({ kelas, terkunci, siswa, dokumen, riwayat, awal }) => ({
    kelas,
    terkunci,
    siswa,
    dokumen,
    riwayat,
    tab: awal.tab,
    jenis: awal.jenis,
    lembar: 1,
    page: 1,
    aktif: siswa.find((s) => s.nama === 'Citra Lestari')?.nis ?? siswa[0]?.nis,
    cari: awal.cari,
    bukaCari: !!awal.cari,
    sorot: 0,
    // Proses PDF satu kelas: null | { selesai, total } | 'selesai'
    proses: null,
    _timer: null,

    init() {
        this.$watch('jenis', () => (this.lembar = 1));
        this.$watch('cari', () => { this.sorot = 0; this.bukaCari = !!this.cari.trim(); });
        if (awal.proses && this.terkunci) this.$nextTick(() => this.unduhKelas(16));
    },

    get siap() { return this.terkunci ? this.siswa.length : 0; },
    get jumlahHalaman() { return Math.ceil(this.siswa.length / PER_HALAMAN); },
    get barisHalaman() { return this.siswa.slice((this.page - 1) * PER_HALAMAN, this.page * PER_HALAMAN); },
    get rentang() {
        const a = (this.page - 1) * PER_HALAMAN + 1;
        return `${a}–${a + this.barisHalaman.length - 1} dari ${this.siswa.length}`;
    },
    get siswaAktif() { return this.siswa.find((s) => s.nis === this.aktif); },
    get doc() { return this.dokumen[this.aktif]?.[this.jenis]; },
    get jumlahLembar() { return this.jenis === 'sts' ? 1 : 2; },
    get labelJenis() { return this.jenis === 'sts' ? 'Rapor STS' : 'Rapor Semester'; },
    inisial(n) { return n.split(' ').slice(0, 2).map((k) => k[0]).join(''); },

    // ---- Cari siswa (5.04) ----
    get hasilCari() {
        const q = this.cari.trim().toLowerCase();
        return q ? this.siswa.filter((s) => s.nama.toLowerCase().includes(q) || s.nisn.includes(q)).slice(0, 8) : [];
    },
    /** Pecah nama menjadi [sebelum, cocok, sesudah] untuk menebalkan kata kunci tanpa x-html. */
    potong(nama) {
        const q = this.cari.trim();
        const i = nama.toLowerCase().indexOf(q.toLowerCase());
        return !q || i === -1 ? [nama, '', ''] : [nama.slice(0, i), nama.slice(i, i + q.length), nama.slice(i + q.length)];
    },
    navigasiCari(arah) {
        if (!this.hasilCari.length) return;
        this.bukaCari = true;
        this.sorot = (this.sorot + arah + this.hasilCari.length) % this.hasilCari.length;
    },
    pilih(s) {
        if (!s) return;
        this.aktif = s.nis;
        this.page = Math.floor(this.siswa.indexOf(s) / PER_HALAMAN) + 1;
        this.tab = 'pratinjau';
        this.cari = '';
        this.$nextTick(() => (this.bukaCari = false));
    },

    // ---- Pratinjau semester 2 lembar (5.06) ----
    keLembar(n) {
        this.lembar = n;
        const el = this.$refs.area.querySelector(`[data-halaman="${n}"]`);
        if (el) this.$refs.area.scrollTo({ top: el.offsetTop - 24, behavior: 'smooth' });
    },
    /** Sinkronkan penanda "Halaman n" dengan posisi gulir pratinjau. */
    pantauLembar(area) {
        if (this.jenis !== 'semester') return;
        const h2 = area.querySelector('[data-halaman="2"]');
        this.lembar = h2 && area.scrollTop >= h2.offsetTop - area.clientHeight / 2 ? 2 : 1;
    },

    // ---- Cetak & unduh ----
    catat(cakupan, versi = 'v1') {
        this.riwayat.unshift({ waktu: waktu(), jenis: this.labelJenis, cakupan, versi, oleh: 'Siti Rahmawati' });
    },
    cetak() {
        if (!this.terkunci) return;
        this.catat(this.siswaAktif.nama);
        window.print();
    },
    unduhSiswa() {
        if (!this.terkunci) return;
        this.catat(this.siswaAktif.nama);
        this.$dispatch('toast', { message: `${this.labelJenis} ${this.siswaAktif.nama} diunduh`, type: 'success' });
    },
    unduhKelas(mulai = 0) {
        if (!this.terkunci || (this.proses && this.proses !== 'selesai')) return;
        clearInterval(this._timer);
        this.proses = { selesai: mulai, total: this.siswa.length };
        this._timer = setInterval(() => {
            this.proses.selesai = Math.min(this.proses.total, this.proses.selesai + 2);
            if (this.proses.selesai >= this.proses.total) {
                clearInterval(this._timer);
                this.proses = 'selesai';
                this.catat('kelas');
            }
        }, 320);
    },
    batalkan() {
        clearInterval(this._timer);
        this.proses = null;
        this.$dispatch('toast', { message: 'Pembuatan PDF dibatalkan', type: 'warning' });
    },
    get persen() { return this.proses && this.proses !== 'selesai' ? Math.round(this.proses.selesai / this.proses.total * 100) : 100; },
    get sisaDetik() { return this.proses && this.proses !== 'selesai' ? Math.max(1, Math.ceil((this.proses.total - this.proses.selesai) / 2 * 0.32)) : 0; },
    get namaZip() { return `Rapor_${this.jenis === 'sts' ? 'STS' : 'Semester'}_${this.kelas}_Ganjil_2026.zip`; },
    ambil(bentuk) {
        this.proses = null;
        this.$dispatch('toast', { message: bentuk === 'zip' ? `${this.namaZip} diunduh` : `PDF gabungan ${this.labelJenis} ${this.kelas} diunduh`, type: 'success' });
    },
    unduhUlang(r) {
        this.$dispatch('toast', { message: `${r.jenis} · ${r.cakupan} (${r.versi}) diunduh ulang`, type: 'success' });
    },
}));
