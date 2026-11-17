import Alpine from 'alpinejs';

const jam = () => {
    const d = new Date();
    return `${d.getDate()} ${['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][d.getMonth()]} ${d.getFullYear()}, ${d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.')}`;
};
const MAKS_ALASAN = 500;
/** Nilai dianggap di luar rentang wajar bila ≥ 25 poin di bawah rata-rata TP tersebut. */
const AMBANG = 25;

/**
 * Verifikasi Nilai wali kelas (Wali 2.01–2.09).
 * TODO API: POST /api/wali/paket/{id}/setujui, POST /api/wali/paket/{id}/revisi { alasan, siswa[] }
 */
Alpine.data('verifikasiPage', ({ kelas, paket, aktif, state, modal, bukaPeriode }) => ({
    kelas,
    paket,
    aktifId: paket.some((p) => p.id === aktif) ? aktif : paket[0].id,
    state,
    filter: 'semua',
    periode: 'STS Ganjil 2026/2027',
    pilihPeriode: bukaPeriode,
    semuaBaris: false,
    catatan: '',
    revisi: { alasan: '', siswa: [], cari: '', error: '' },
    maksAlasan: MAKS_ALASAN,

    init() {
        this.$watch('aktifId', () => {
            this.semuaBaris = false;
            this.catatan = '';
        });
        this.$nextTick(() => {
            if (modal === 'revisi' || modal === 'revisi-error') {
                this.mintaRevisi();
                if (modal === 'revisi-error') this.revisi.error = 'Alasan revisi wajib diisi.';
            }
            if (modal === 'setujui') this.$dispatch('open-modal', 'setujui');
        });
    },

    get p() { return this.paket.find((x) => x.id === this.aktifId); },
    get daftar() {
        return this.filter === 'menunggu' ? this.paket.filter((x) => ['menunggu', 'dikirim_ulang'].includes(x.status)) : this.paket;
    },
    get jumlahDisetujui() { return this.paket.filter((x) => ['disetujui', 'terkunci'].includes(x.status)).length; },
    get bisaDiperiksa() { return ['menunggu', 'dikirim_ulang'].includes(this.p.status); },
    get adaKiriman() { return this.paket.some((x) => !['sedang_diisi', 'belum_tp'].includes(x.status)); },
    terkirim(x) { return !['sedang_diisi', 'belum_tp'].includes(x.status); },

    // ---- Statistik paket aktif ----
    get kolom() { return Array.from({ length: this.p.tp }, (_, j) => j); },
    get barisTampil() { return this.semuaBaris ? this.p.nilai : this.p.nilai.slice(0, 8); },
    rapor(b) { return b.nilai.length ? Math.round(b.nilai.reduce((s, v) => s + v, 0) / b.nilai.length) : null; },
    get rataTp() {
        return this.kolom.map((j) => this.p.nilai.reduce((s, b) => s + b.nilai[j], 0) / Math.max(1, this.p.nilai.length));
    },
    aneh(b, j) { return b.nilai[j] <= this.rataTp[j] - AMBANG; },
    get daftarAneh() {
        const a = [];
        this.p.nilai.forEach((b) => this.kolom.forEach((j) => this.aneh(b, j) && a.push({ nama: b.nama, tp: `TP${j + 1}`, nilai: b.nilai[j] })));
        return a;
    },
    get rataKelas() {
        const r = this.p.nilai.map((b) => this.rapor(b)).filter((x) => x !== null);
        return r.length ? (r.reduce((s, v) => s + v, 0) / r.length).toFixed(1).replace('.', ',') : '—';
    },
    get rentang() {
        const r = this.p.nilai.map((b) => this.rapor(b));
        return r.length ? `${Math.min(...r)}–${Math.max(...r)}` : '—';
    },

    labelStatus(s) {
        return { menunggu: 'Menunggu verifikasi', dikirim_ulang: 'Dikirim ulang', disetujui: 'Disetujui', revisi: 'Dikembalikan', sedang_diisi: 'Sedang diisi', belum_tp: 'Belum ada TP', terkunci: 'Terkunci' }[s];
    },
    toneStatus(s) {
        return { menunggu: 'info', dikirim_ulang: 'violet', disetujui: 'success', revisi: 'violet', sedang_diisi: 'warning', belum_tp: 'danger', terkunci: 'success' }[s];
    },

    // ---- Minta revisi (2.04, 2.05) ----
    mintaRevisi() {
        const aneh = [...new Set(this.daftarAneh.map((a) => a.nama))];
        this.revisi = { alasan: this.catatan, siswa: [], cari: '', error: '', saran: aneh };
        this.$dispatch('open-modal', 'revisi');
    },
    get siswaRevisi() {
        const q = this.revisi.cari.trim().toLowerCase();
        const saran = this.revisi.saran ?? [];
        const urut = [...this.p.nilai].sort((a, b) => (saran.includes(b.nama) ? 1 : 0) - (saran.includes(a.nama) ? 1 : 0));
        return urut.filter((b) => !q || b.nama.toLowerCase().includes(q)).slice(0, q ? 8 : 3);
    },
    ketSiswa(b) {
        const a = this.daftarAneh.filter((x) => x.nama === b.nama);
        return a.map((x) => `${x.tp} = ${x.nilai}`).join(', ');
    },
    toggleSiswa(nama) {
        const i = this.revisi.siswa.indexOf(nama);
        i === -1 ? this.revisi.siswa.push(nama) : this.revisi.siswa.splice(i, 1);
    },
    kirimRevisi() {
        const a = this.revisi.alasan.trim();
        if (!a) {
            this.revisi.error = 'Alasan revisi wajib diisi.';
            return;
        }
        this.p.status = 'revisi';
        this.p.riwayat.unshift({ jenis: 'revisi', judul: 'Dikembalikan untuk revisi', oleh: 'Siti Rahmawati (Wali Kelas)', waktu: jam(), catatan: a + (this.revisi.siswa.length ? ` (${this.revisi.siswa.join(', ')})` : '') });
        this.$dispatch('close-modal', 'revisi');
        this.$dispatch('toast', { message: `Nilai ${this.p.mapel} dikembalikan ke ${this.p.guru.replace(/,.*$/, '')}`, type: 'warning' });
    },

    // ---- Setujui (2.06) ----
    setujui() {
        this.p.status = 'disetujui';
        this.p.riwayat.unshift({ jenis: 'disetujui', judul: 'Disetujui', oleh: 'Siti Rahmawati (Wali Kelas)', waktu: jam(), catatan: this.catatan.trim() || null });
        this.$dispatch('close-modal', 'setujui');
        this.$dispatch('toast', { message: `Nilai ${this.p.mapel} ${this.kelas} disetujui`, type: 'success' });
    },
}));
