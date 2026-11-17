import Alpine from 'alpinejs';

const jam = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');
const valid = (v) => v === '' || (/^\d{1,3}$/.test(String(v)) && +v >= 0 && +v <= 100);

/**
 * Input Nilai (Guru 19–29). Satu nilai per TP; nilai rapor = rata-rata TP yang tampil.
 * Status paket: draf | gagal (simpan otomatis gagal) | terkirim | revisi | ditutup | terkunci.
 * TODO API: PUT /api/guru/rombel/{id}/nilai (simpan otomatis per sel), POST /api/guru/paket/{id}/kirim
 */
Alpine.data('inputNilai', ({ rombel, aktif, peserta, singkat, periode, state, revisi, modal, bukaRombel }) => ({
    rombel,
    r: aktif,
    peserta: peserta.map((p) => ({ ...p, nilai: p.nilai.map((v) => (v === null ? '' : String(v))) })),
    singkat,
    periode,
    status: state === 'gagal' ? 'draf' : state,
    gagalSimpan: state === 'gagal',
    simpan: state === 'gagal' ? 'gagal' : 'tersimpan',
    waktuSimpan: '10.42',
    kotor: [],
    filterKelas: 'semua',
    pilihRombel: bukaRombel,
    cariRombel: '',
    fokus: { i: 0, j: 0 },
    revisi,
    sel: revisi && state === 'revisi' ? `${revisi.nisn}-${revisi.tp}` : null,
    tempel: { baris: [], ringkas: { valid: 0, salah: 0, kosong: 0 } },
    kirimKelas: null,
    pengirimanKelas: Object.fromEntries((aktif.kelas_asal ?? []).map((k) => [k.kelas, k.status])),
    timer: null,

    init() {
        if (state === 'gagal') {
            this.kotor = ['7-3', '8-1'];
            this.$nextTick(() => this.$dispatch('toast', { message: `Koneksi terputus. ${this.kotor.length} perubahan belum tersimpan.`, type: 'danger', action: 'Coba lagi', timeout: 8000 }));
        }
        if (state === 'revisi') {
            const p = this.peserta.find((x) => x.nisn === revisi.nisn);
            if (p) p.nilai[revisi.tp] = String(revisi.nilai);
        }
        this.$nextTick(() => {
            if (modal === 'tempel') this.pratinjauTempel(this.contohTempel(), 0, 0);
            if (modal === 'kirim') this.mintaKirim();
        });
    },

    // ---- Kolom & status ----
    get jumlahTp() { return this.r.tp.length; },
    get jumlahSts() { return Math.floor(this.jumlahTp / 2); },
    get kolom() {
        const n = this.periode === 'sts' ? this.jumlahSts : this.jumlahTp;
        return Array.from({ length: n }, (_, j) => j);
    },
    get idxSas() { return this.periode === 'semester' ? this.r.tp.findIndex((t) => t.sas) : -1; },
    get bacaSaja() { return ['terkirim', 'ditutup', 'terkunci'].includes(this.status); },
    bisaUbah(p) { return !this.bacaSaja && !(this.r.campuran && this.pengirimanKelas[p.kelas] !== 'draf'); },
    get tampil() {
        return this.filterKelas === 'semua' ? this.peserta : this.peserta.filter((p) => p.kelas === this.filterKelas);
    },
    valid,
    lengkap(p) { return this.kolom.every((j) => p.nilai[j] !== '' && valid(p.nilai[j])); },
    get jumlahLengkap() { return this.peserta.filter((p) => this.lengkap(p)).length; },
    rapor(p) {
        if (!this.lengkap(p)) return null;
        return Math.round(this.kolom.reduce((s, j) => s + +p.nilai[j], 0) / this.kolom.length);
    },
    kunci(p, j) { return `${this.peserta.indexOf(p)}-${j}`; },
    belumTersimpan(p, j) { return this.kotor.includes(this.kunci(p, j)); },
    diminta(p, j) { return this.sel === `${p.nisn}-${j}`; },
    get rombelTersaring() {
        const q = this.cariRombel.trim().toLowerCase();
        return this.rombel.filter((x) => !q || x.kelas.toLowerCase().includes(q));
    },
    labelStatusRombel(x) {
        return x.campuran ? 'Campuran' : (x.jumlah_tp ? `${x.jumlah_tp} TP` : 'Belum ada TP');
    },

    // ---- Simpan otomatis ----
    ubah(p, j, nilai) {
        p.nilai[j] = nilai.trim();
        if (this.diminta(p, j)) this.sel = null;
        const k = this.kunci(p, j);
        if (!this.kotor.includes(k)) this.kotor.push(k);
        if (!valid(p.nilai[j])) return;
        this.simpan = 'menyimpan';
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.jalankanSimpan(), 700);
    },
    jalankanSimpan() {
        if (this.gagalSimpan) {
            this.simpan = 'gagal';
            return;
        }
        this.kotor = this.kotor.filter((k) => {
            const [i, j] = k.split('-').map(Number);
            return !valid(this.peserta[i].nilai[j]);
        });
        this.simpan = 'tersimpan';
        this.waktuSimpan = jam();
    },
    cobaLagi() {
        this.gagalSimpan = false;
        this.simpan = 'menyimpan';
        setTimeout(() => {
            this.jalankanSimpan();
            this.$dispatch('toast', { message: `Tersimpan otomatis · ${this.waktuSimpan}`, type: 'success' });
        }, 600);
    },

    // ---- Tempel dari Excel (22) ----
    tanganiTempel(e) {
        if (this.bacaSaja) return;
        const teks = e.clipboardData?.getData('text/plain') ?? '';
        if (!teks.includes('\t') && !teks.includes('\n')) return; // satu nilai: biarkan input biasa
        e.preventDefault();
        const baris = teks.replace(/\r/g, '').split('\n').filter((b, i, a) => b !== '' || i < a.length - 1).map((b) => b.split('\t'));
        this.pratinjauTempel(baris, this.fokus.i, this.fokus.j);
    },
    contohTempel() {
        const ubah = { 2: { 5: '' }, 4: { 3: '105' }, 5: { 1: '' }, 6: { 2: '8o' }, 7: { 3: '', 5: '' } };
        return this.peserta.slice(0, 17).map((p, i) => p.nilai.slice(0, 6).map((v, j) => (ubah[i]?.[j] ?? (v || '80'))));
    },
    pratinjauTempel(matriks, i0, j0) {
        const ringkas = { valid: 0, salah: 0, kosong: 0 };
        const baris = matriks.slice(0, this.tampil.length - i0).map((sel, di) => {
            const p = this.tampil[i0 + di];
            return {
                nama: p.nama, p,
                sel: sel.slice(0, this.kolom.length - j0).map((v, dj) => {
                    const t = v.trim();
                    const jenis = t === '' ? 'kosong' : (valid(t) ? 'valid' : 'salah');
                    ringkas[jenis]++;
                    return { j: this.kolom[j0 + dj], nilai: t, jenis };
                }),
            };
        });
        this.tempel = { baris, ringkas, j0 };
        this.$dispatch('open-modal', 'tempel');
    },
    terapkanTempel() {
        let n = 0;
        this.tempel.baris.forEach((b) => b.sel.forEach((s) => {
            if (s.jenis === 'valid' && this.bisaUbah(b.p)) {
                this.ubah(b.p, s.j, s.nilai);
                n++;
            }
        }));
        this.$dispatch('close-modal', 'tempel');
        this.$dispatch('toast', { message: `${n} nilai diterapkan dari Excel`, type: 'success' });
    },
    tempelUlang() {
        this.$dispatch('close-modal', 'tempel');
        this.$dispatch('toast', { message: 'Salin sel dari Excel, klik sel awal, lalu tekan Ctrl+V', type: 'info' });
    },

    // ---- Kirim (24, 25) ----
    get barisKirim() {
        return this.kirimKelas ? this.peserta.filter((p) => p.kelas === this.kirimKelas) : this.peserta;
    },
    get masalah() {
        const m = [];
        this.barisKirim.forEach((p) => this.kolom.forEach((j) => {
            const v = p.nilai[j];
            if (v === '') m.push({ tp: `TP${j + 1}`, nama: p.nama, ket: 'kosong' });
            else if (!valid(v)) m.push({ tp: `TP${j + 1}`, nama: p.nama, ket: `tidak valid: ${v}` });
        }));
        return m;
    },
    mintaKirim(kelas = null) {
        if (this.kotor.length && this.simpan === 'gagal') return;
        this.kirimKelas = kelas;
        this.$dispatch('open-modal', 'kirim');
    },
    kembaliMengisi() {
        this.$dispatch('close-modal', 'kirim');
        const m = this.barisKirim.find((p) => !this.lengkap(p));
        if (!m) return;
        const j = this.kolom.find((x) => m.nilai[x] === '' || !valid(m.nilai[x]));
        this.$nextTick(() => document.querySelector(`[data-sel="${this.peserta.indexOf(m)}-${j}"]`)?.focus());
    },
    kirim() {
        if (this.masalah.length) return;
        this.$dispatch('close-modal', 'kirim');
        if (this.kirimKelas) {
            this.pengirimanKelas[this.kirimKelas] = 'terkirim';
            this.$dispatch('toast', { message: `Nilai ${this.r.kelas} kelas ${this.kirimKelas} berhasil dikirim`, type: 'info' });
            return;
        }
        this.status = 'terkirim';
        this.$dispatch('toast', { message: `Nilai Matematika ${this.r.kelas} berhasil dikirim`, type: 'info' });
    },
}));
