import Alpine from 'alpinejs';
import { api, toast } from './siakad-api';

const jam = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' });
const valid = value => value === '' || (/^\d{1,3}(?:[.,]\d{1,2})?$/.test(value) && Number(value.replace(',', '.')) <= 100);
const angka = value => Number(value.replace(',', '.'));

Alpine.data('inputNilai', ({ rombel, aktif, grid, periode, paket }) => {
    const tp = grid.tp;
    const endpoint = '/guru/rombel/' + aktif.id;
    const idPeriode = grid.periode.id_periode;
    const peserta = grid.peserta.map(p => ({
        id: p.id_siswa_rombel,
        nisn: p.siswa.nis,
        nama: p.siswa.nama,
        kelas: p.kelas.nama,
        nilai: tp.map(t => String(p.nilai[t.id_tp]?.nilai ?? '')),
        edit: tp.map(t => p.nilai[t.id_tp]?.dapat_diedit ?? false),
        asal: tp.map(t => p.nilai[t.id_tp]?.id_periode ?? null),
    }));
    const kelasAsal = paket.map(p => ({
        id: p.kelas.id_kelas,
        kelas: p.kelas.nama,
        jumlah: p.kelengkapan.jumlah_siswa,
        status: p.status,
        koreksi: p.koreksi,
        revisi: p.riwayat.findLast(r => r.aksi === 'minta_revisi' || r.aksi === 'buka_koreksi'),
    }));
    const status = grid.periode.status_efektif;
    return {
    rombel,
    r: { ...aktif, tp, campuran: kelasAsal.length > 1, kelas_asal: kelasAsal },
    peserta,
    singkat: tp.map(t => t.deskripsi),
    periode,
    simpan: 'tersimpan',
    waktuSimpan: null,
    kotor: [],
    sedangSimpan: null,
    sedangKirim: false,
    filterKelas: 'semua',
    pilihRombel: false,
    cariRombel: '',
    fokus: { i: 0, j: 0 },
    revisi: {
        oleh: kelasAsal[0]?.revisi?.user?.nama ?? '',
        waktu: kelasAsal[0]?.revisi?.created_at?.slice(0, 10) ?? '',
        catatan: kelasAsal[0]?.revisi?.catatan ?? '',
    },
    tempel: { baris: [], ringkas: { valid: 0, salah: 0, kosong: 0 } },
    kirimKelas: null,
    pengirimanKelas: Object.fromEntries(kelasAsal.map(k => [k.kelas, k.status])),
    timer: null,

    get status() {
        if (this.r.campuran) return status === 'dibuka' ? 'draf' : status === 'dikunci' ? 'terkunci' : 'ditutup';
        const k = kelasAsal[0];
        if (k?.status === 'dikunci') return 'terkunci';
        if (k?.status === 'perlu_revisi' && this.dapatKirim(k)) return 'revisi';
        if (['terkirim', 'disetujui'].includes(k?.status)) return k.status;
        return status === 'dibuka' ? 'draf' : 'ditutup';
    },
    dapatKirim(k) {
        return (status === 'dibuka' || k.koreksi) && ['draf', 'perlu_revisi'].includes(k.status);
    },

    // ---- Kolom & status ----
    get jumlahTp() { return this.r.tp.length; },
    get kolom() {
        return this.r.tp.map((_, j) => j);
    },
    get idxSas() { return this.periode === 'semester' ? this.r.tp.findIndex(t => t.tp_sas) : -1; },
    get bacaSaja() { return !this.peserta.some(p => this.bisaUbah(p)); },
    bisaUbah(p, j) { return j === undefined ? p.edit.some(Boolean) : p.edit[j]; },
    get tampil() {
        return this.filterKelas === 'semua' ? this.peserta : this.peserta.filter((p) => p.kelas === this.filterKelas);
    },
    valid,
    get kolomWajib() {
        return this.periode === 'semester' ? this.kolom : this.kolom.filter(j =>
            tp[j].hanya_sts || this.peserta.some(p => p.nilai[j] !== '' && (p.asal[j] === idPeriode || p.edit[j])));
    },
    lengkap(p) {
        return this.kolomWajib.length > 0 && this.kolomWajib.every(j => this.nilaiPeriode(p, j) && valid(p.nilai[j]));
    },
    get jumlahLengkap() { return this.peserta.filter(p => this.lengkap(p)).length; },
    nilaiPeriode(p, j) {
        return p.nilai[j] !== '' && (this.periode === 'semester' || p.asal[j] === idPeriode || p.edit[j]);
    },
    rapor(p) {
        const values = this.kolom.filter(j => this.nilaiPeriode(p, j) && valid(p.nilai[j])).map(j => angka(p.nilai[j]));
        return values.length ? Math.round(values.reduce((sum, n) => sum + n, 0) / values.length * 100) / 100 : null;
    },
    kunci(p, j) { return `${this.peserta.indexOf(p)}-${j}`; },
    belumTersimpan(p, j) { return this.kotor.includes(this.kunci(p, j)); },
    diminta() { return false; },
    get rombelTersaring() {
        const q = this.cariRombel.trim().toLowerCase();
        return this.rombel.filter((x) => !q || x.kelas.toLowerCase().includes(q));
    },
    labelStatusRombel(x) {
        return x.campuran ? 'Campuran' : (x.jumlah_tp ? `${x.jumlah_tp} TP` : 'Belum ada TP');
    },

    // ---- Simpan otomatis ----
    ubah(p, j, nilai) {
        if (!this.bisaUbah(p, j) || p.nilai[j] === nilai.trim()) return;
        p.nilai[j] = nilai.trim();
        const key = this.kunci(p, j);
        if (!this.kotor.includes(key)) this.kotor.push(key);
        this.simpan = valid(p.nilai[j]) ? 'menyimpan' : 'gagal';
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.jalankanSimpan(), 400);
    },
    async jalankanSimpan() {
        if (this.sedangSimpan) return this.sedangSimpan;
        const changes = this.kotor.map(key => {
            const [i, j] = key.split('-').map(Number);
            return { key, p: this.peserta[i], j, nilai: this.peserta[i].nilai[j] };
        });
        const perubahan = changes.filter(item => valid(item.nilai)).slice(0, 1000);
        if (!perubahan.length) return !this.kotor.length;
        this.simpan = 'menyimpan';
        this.sedangSimpan = (async () => {
            try {
                await api(endpoint + '/nilai', 'PUT', {
                    id_periode: idPeriode,
                    items: perubahan.map(({ p, j, nilai }) => ({
                        id_siswa_rombel: p.id,
                        id_tp: tp[j].id_tp,
                        nilai: nilai === '' ? null : angka(nilai),
                    })),
                });
                this.kotor = this.kotor.filter(key => !perubahan.some(item => {
                    if (item.key !== key || item.nilai !== item.p.nilai[item.j]) return false;
                    item.p.asal[item.j] = item.nilai === '' ? null : idPeriode;
                    return true;
                }));
                this.waktuSimpan = jam();
                this.simpan = this.kotor.length ? 'gagal' : 'tersimpan';
                if (this.kotor.some(key => {
                    const [i, j] = key.split('-').map(Number);
                    return valid(this.peserta[i].nilai[j]);
                })) {
                    this.timer = setTimeout(() => this.jalankanSimpan(), 0);
                }
                return !this.kotor.length;
            } catch (error) {
                this.simpan = 'gagal';
                toast(error.message, 'danger');
                return false;
            } finally {
                this.sedangSimpan = null;
            }
        })();
        return this.sedangSimpan;
    },
    cobaLagi() { return this.jalankanSimpan(); },

    // ---- Tempel dari Excel (22) ----
    tanganiTempel(e) {
        if (this.bacaSaja) return;
        const teks = e.clipboardData?.getData('text/plain') ?? '';
        if (!teks.includes('\t') && !teks.includes('\n')) return; // satu nilai: biarkan input biasa
        e.preventDefault();
        const baris = teks.replace(/\r/g, '').split('\n').filter((b, i, a) => b !== '' || i < a.length - 1).map((b) => b.split('\t'));
        this.pratinjauTempel(baris, this.fokus.i, this.fokus.j);
    },
    pratinjauTempel(matriks, i0, j0) {
        const ringkas = { valid: 0, salah: 0, kosong: 0 };
        const baris = matriks.slice(0, this.tampil.length - i0).map((sel, di) => {
            const p = this.tampil[i0 + di];
            return {
                nama: p.nama, p,
                sel: sel.slice(0, this.kolom.length - j0).map((v, dj) => {
                    const t = v.trim();
                    const jenis = t === '' ? 'kosong' : (valid(t) && this.bisaUbah(p, this.kolom[j0 + dj]) ? 'valid' : 'salah');
                    ringkas[jenis]++;
                    return { j: this.kolom[j0 + dj], nilai: t, jenis };
                }),
            };
        });
        this.tempel = { baris, ringkas, j0 };
        this.$dispatch('open-modal', 'tempel');
    },
    async terapkanTempel() {
        const validCells = [];
        this.tempel.baris.forEach((b) => b.sel.forEach((s) => {
            if (s.jenis === 'valid' && this.bisaUbah(b.p, s.j)) {
                validCells.push({ p: b.p, j: s.j, nilai: s.nilai });
            }
        }));
        if (!validCells.length) return;
        try {
            for (let i = 0; i < validCells.length; i += 1000) {
                await api(endpoint + '/nilai/pratinjau', 'POST', {
                    id_periode: idPeriode,
                    items: validCells.slice(i, i + 1000).map(({ p, j, nilai }) => ({
                        id_siswa_rombel: p.id, id_tp: tp[j].id_tp, nilai: angka(nilai),
                    })),
                });
            }
        } catch (error) {
            toast(error.message, 'danger');
            return;
        }
        validCells.forEach(({ p, j, nilai }) => this.ubah(p, j, nilai));
        this.$dispatch('close-modal', 'tempel');
        toast(`${validCells.length} nilai diterapkan dari Excel`);
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
        this.barisKirim.forEach((p) => this.kolomWajib.forEach((j) => {
            const v = p.nilai[j];
            if (!this.nilaiPeriode(p, j)) m.push({ tp: `TP${j + 1}`, nama: p.nama, ket: 'kosong' });
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
    async kirim() {
        if (this.masalah.length || this.sedangKirim) return;
        const kelas = kelasAsal.find(k => k.kelas === this.kirimKelas) ?? kelasAsal[0];
        if (!kelas) return;
        this.sedangKirim = true;
        try {
            clearTimeout(this.timer);
            if (!await this.jalankanSimpan()) return;
            await api(endpoint + '/paket/kirim', 'POST', { id_periode: idPeriode, id_kelas: kelas.id });
            window.location.reload();
        } catch (error) {
            toast(error.message, 'danger');
        } finally {
            this.sedangKirim = false;
        }
    },
    };
});
