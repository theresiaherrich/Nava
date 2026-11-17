import Alpine from 'alpinejs';

const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const BULAN_PANJANG = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const tgl = (iso) => {
    const [y, m, d] = iso.split('-').map(Number);
    return { y, m, d };
};
export const tanggalPendek = (iso) => (iso ? `${tgl(iso).d} ${BULAN[tgl(iso).m - 1]} ${tgl(iso).y}` : '');
const rentang = (a, b) => {
    const x = tgl(a);
    const y = tgl(b);
    return `${x.d} ${BULAN_PANJANG[x.m - 1]}${x.y !== y.y ? ' ' + x.y : ''} – ${y.d} ${BULAN_PANJANG[y.m - 1]} ${y.y}`;
};

/**
 * Periode & Penguncian (Kurikulum 5.01–5.02, 6.03–6.04).
 * TODO API: PUT /api/kurikulum/periode/{id}, POST /api/kurikulum/periode/{id}/kunci { kelas[] },
 *           POST /api/kurikulum/periode/{id}/koreksi { kelas, mapel[], alasan, batas }
 */
Alpine.data('periodePage', ({ periode, kelas, mapelKelas, modal }) => ({
    periode,
    kelas,
    mapelKelas,
    semester: 'ganjil',
    tingkat: 'X',
    semua: false,
    jadwal: { jenis: 'sts', buka: '', tutup: '', perpanjangan: false, alasan: '', error: {} },
    kunci: { daftar: [] },
    koreksi: { kelas: null, mapel: [], cari: '', alasan: '', batas: '2026-10-30', error: '' },
    tanggalPendek,

    init() {
        this.$watch('tingkat', () => (this.semua = false));
        if (modal === 'jadwal') {
            this.siapkanJadwal('sts');
            Object.assign(this.jadwal, { tutup: '2026-10-30', perpanjangan: true, alasan: 'Perpanjangan karena kegiatan class meeting 19–21 Oktober.' });
        }
        if (modal === 'kunci') this.kunci.daftar = this.siap.map((k) => k.kelas);
        if (modal === 'koreksi') {
            this.siapkanKoreksi(this.kelas.find((k) => k.kelas === 'X-B'));
            this.koreksi.mapel = ['Matematika'];
            this.koreksi.alasan = 'Terdapat kesalahan input nilai TP3 atas nama Dimas Saputra (8 seharusnya 80).';
        }
    },

    get kelasTingkat() {
        return this.kelas.filter((k) => k.tingkat === this.tingkat);
    },

    get kelasTampil() {
        return this.semua ? this.kelasTingkat : this.kelasTingkat.slice(0, 6);
    },

    get siap() {
        return this.kelasTingkat.filter((k) => k.status === 'siap');
    },

    // ---- Ubah jadwal (5.02) ----
    siapkanJadwal(jenis) {
        const p = this.periode[jenis];
        this.jadwal = { jenis, buka: p.buka, tutup: p.tutup, perpanjangan: p.perpanjangan, alasan: '', error: {} };
    },

    ubahJadwal(jenis) {
        this.siapkanJadwal(jenis);
        this.$dispatch('open-modal', 'jadwal');
    },

    simpanJadwal() {
        const j = this.jadwal;
        const e = {};
        if (!j.buka) e.buka = 'Tanggal buka wajib diisi';
        if (!j.tutup) e.tutup = 'Tanggal tutup wajib diisi';
        else if (j.buka && j.tutup <= j.buka) e.tutup = 'Tanggal tutup harus setelah tanggal buka';
        if (!j.alasan.trim()) e.alasan = 'Alasan perubahan wajib diisi';
        j.error = e;
        if (Object.keys(e).length) return;

        Object.assign(this.periode[j.jenis], { buka: j.buka, tutup: j.tutup, perpanjangan: j.perpanjangan, rentang: rentang(j.buka, j.tutup) });
        this.$dispatch('close-modal', 'jadwal');
        this.$dispatch('toast', { message: `Jadwal ${this.periode[j.jenis].singkat} diperbarui · ${tanggalPendek(j.buka)} – ${tanggalPendek(j.tutup)}`, type: 'success' });
    },

    // ---- Kunci nilai (6.03) ----
    mintaKunci(daftar) {
        this.kunci.daftar = daftar;
        this.$dispatch('open-modal', 'kunci');
    },

    kunciNilai() {
        this.kunci.daftar.forEach((nama) => {
            const k = this.kelas.find((x) => x.kelas === nama);
            Object.assign(k, { status: 'terkunci', terkunci_sejak: '12 Okt 2026' });
        });
        this.$dispatch('close-modal', 'kunci');
        this.$dispatch('toast', { message: `Nilai Kelas ${this.kunci.daftar.join(', ')} berhasil dikunci`, type: 'success' });
    },

    // ---- Buka koreksi (6.04) ----
    siapkanKoreksi(k) {
        this.koreksi = { kelas: k, mapel: [], cari: '', alasan: '', batas: '2026-10-30', error: '' };
    },

    bukaKoreksi(k) {
        this.siapkanKoreksi(k);
        this.$dispatch('open-modal', 'koreksi');
    },

    get saranMapel() {
        const q = this.koreksi.cari.trim().toLowerCase();
        return this.mapelKelas.filter((m) => !this.koreksi.mapel.includes(m.mapel) && (!q || m.mapel.toLowerCase().includes(q))).slice(0, 6);
    },

    guruMapel(nama) {
        return this.mapelKelas.find((m) => m.mapel === nama)?.guru ?? '';
    },

    tambahMapel(m) {
        if (m && !this.koreksi.mapel.includes(m.mapel)) this.koreksi.mapel.push(m.mapel);
        this.koreksi.cari = '';
    },

    simpanKoreksi() {
        const c = this.koreksi;
        if (!c.mapel.length) {
            c.error = 'Pilih minimal satu mata pelajaran.';
            return;
        }
        if (!c.alasan.trim()) {
            c.error = 'Alasan koreksi wajib diisi.';
            return;
        }
        Object.assign(c.kelas, { status: 'koreksi', koreksi: `${c.mapel.join(', ')} · ${c.alasan.trim().slice(0, 40)}${c.alasan.trim().length > 40 ? '…' : ''}` });
        this.$dispatch('close-modal', 'koreksi');
        this.$dispatch('toast', { message: `Koreksi ${c.mapel.join(', ')} ${c.kelas.kelas} dibuka`, type: 'violet' });
    },

    tambahPeriode() {
        this.$dispatch('toast', { message: 'Form tambah periode belum ada di desain.', type: 'info' });
    },
}));
