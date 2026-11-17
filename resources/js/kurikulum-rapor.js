import Alpine from 'alpinejs';

const jam = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');

/**
 * 7.01 Format Rapor — pengaturan kop/identitas/komponen dengan pratinjau langsung.
 * TODO API: PUT /api/kurikulum/format-rapor (multipart untuk logo & tanda tangan)
 */
Alpine.data('formatRapor', ({ pengaturan, contoh }) => ({
    p: pengaturan,
    contoh,
    jenis: 'semester',
    kotor: false,
    tersimpan: null,
    ttdBerkas: null,
    komponenLabel: {
        capaian: 'Capaian kompetensi', kokurikuler: 'Kokurikuler', ekskul: 'Ekstrakurikuler',
        absen: 'Ketidakhadiran', catatan: 'Catatan wali kelas', qr: 'QR verifikasi',
    },

    init() {
        this.$watch('p', () => (this.kotor = true), { deep: true });
    },

    /** Dokumen pratinjau = contoh + pengaturan terkini. */
    get doc() {
        const d = this.contoh[this.jenis];
        return {
            ...d,
            kop: { baris1: this.p.baris1, baris2: this.p.baris2, sekolah: this.p.sekolah, alamat: this.p.alamat, kontak: this.p.kontak },
            ttd: { ...d.ttd, tempat: this.p.tempat, tanggal: this.jenis === 'sts' ? this.p.tanggal_sts : this.p.tanggal_semester, kepsek: this.p.kepsek, nip_kepsek: this.p.nip_kepsek },
            komponen: this.p.komponen,
        };
    },

    pilihBerkas(e, jenis) {
        const f = e.target.files[0];
        e.target.value = '';
        if (!f) return;
        if (!/\.png$/i.test(f.name)) {
            this.$dispatch('toast', { message: 'Berkas harus PNG', type: 'danger' });
            return;
        }
        if (jenis === 'logo') this.p.logo = f.name;
        else this.ttdBerkas = f.name;
        this.kotor = true;
    },

    simpan() {
        if (!this.p.sekolah.trim() || !this.p.kepsek.trim()) {
            this.$dispatch('toast', { message: 'Nama sekolah dan kepala sekolah wajib diisi', type: 'danger' });
            return;
        }
        this.tersimpan = jam();
        this.$nextTick(() => (this.kotor = false));
        this.$dispatch('toast', { message: 'Format rapor disimpan', type: 'success' });
    },
}));

/**
 * 7.02 Cetak & Ekspor Rapor.
 * TODO API: GET /api/rapor/{id}/pdf, POST /api/rapor/kelas/{kelas}/pdf (gabungan/ZIP)
 */
Alpine.data('cetakRapor', ({ daftarKelas, data, dokumen, bukaKelas }) => ({
    daftarKelas,
    data,
    dokumen,
    pilihKelas: bukaKelas,
    cari: '',
    page: 1,
    perPage: 9,
    aktif: data.siswa.find((s) => s.nama === 'Citra Lestari')?.nis ?? data.siswa[0]?.nis,
    jenis: 'sts',

    init() {
        this.$watch('cari', () => (this.page = 1));
    },

    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        return this.data.siswa.filter((s) => !q || s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
    },
    get halaman() { return Math.max(1, Math.ceil(this.tersaring.length / this.perPage)); },
    get barisHalaman() { return this.tersaring.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
    get rentang() {
        if (!this.tersaring.length) return '0 dari 0';
        const a = (this.page - 1) * this.perPage + 1;
        return `${a}–${a + this.barisHalaman.length - 1} dari ${this.tersaring.length}`;
    },
    get siswaAktif() { return this.data.siswa.find((s) => s.nis === this.aktif); },
    get doc() { return this.dokumen[this.aktif]?.[this.jenis]; },

    inisial(n) { return n.split(' ').slice(0, 2).map((k) => k[0]).join(''); },

    unduh(cakupan) {
        if (!this.data.terkunci) {
            this.$dispatch('toast', { message: `Nilai ${this.data.kelas} belum dikunci — rapor final belum dapat diunduh`, type: 'warning' });
            return;
        }
        const pesan = {
            siswa: `Rapor ${this.siswaAktif.nama} diunduh`,
            kelas: `${this.data.siswa.length} PDF rapor ${this.data.kelas} berhasil dibuat`,
            semua: 'PDF rapor semua kelas terkunci sedang dibuat',
        }[cakupan];
        this.$dispatch('toast', { message: pesan, type: cakupan === 'siswa' ? 'success' : 'info' });
    },

    cetak() {
        if (!this.data.terkunci) {
            this.$dispatch('toast', { message: 'Hanya kelas terkunci yang dapat dicetak final', type: 'warning' });
            return;
        }
        window.print();
    },
}));

/**
 * 7.03–7.04 Terbitkan Rapor.
 * TODO API: POST /api/rapor/terbitkan { kelas[], jenis, notifikasi }, POST /api/rapor/tarik { kelas, jenis }
 */
Alpine.data('terbitkanRapor', ({ kelas, bukaModal }) => ({
    kelas,
    jenis: 'sts',
    tingkat: 'Kelas X',
    cari: '',
    dipilih: [],
    target: [],
    notifikasi: true,

    init() {
        this.dipilih = this.kelas.filter((k) => ['X-G', 'X-H', 'X-J'].includes(k.kelas)).map((k) => k.kelas);
        if (bukaModal) this.target = ['X-B'];
    },

    get tampil() {
        const q = this.cari.trim().toLowerCase();
        return this.kelas.filter((k) => `Kelas ${k.tingkat}` === this.tingkat && (!q || k.kelas.toLowerCase().includes(q) || (k.wali ?? '').toLowerCase().includes(q)));
    },

    bisa(k) { return k.kunci === 'terkunci' && k.pdf && !k.terbit; },

    toggle(k) {
        if (!this.bisa(k)) return;
        const i = this.dipilih.indexOf(k.kelas);
        i === -1 ? this.dipilih.push(k.kelas) : this.dipilih.splice(i, 1);
    },

    get kelasTarget() { return this.kelas.filter((k) => this.target.includes(k.kelas)); },
    get jumlahSiswa() { return this.kelasTarget.reduce((n, k) => n + k.jumlah, 0); },
    get tanpaOrtu() { return this.kelasTarget.flatMap((k) => k.tanpa_ortu); },
    get labelJenis() { return this.jenis === 'sts' ? 'Rapor STS' : 'Rapor Semester'; },

    minta(daftar) {
        this.target = daftar;
        this.notifikasi = true;
        this.$dispatch('open-modal', 'terbitkan');
    },

    terbitkan() {
        const waktu = `12 Okt 2026, ${jam()}`;
        this.kelasTarget.forEach((k) => (k.terbit = waktu));
        this.dipilih = this.dipilih.filter((x) => !this.target.includes(x));
        this.$dispatch('close-modal', 'terbitkan');
        this.$dispatch('toast', { message: `${this.labelJenis} ${this.target.join(', ')} berhasil diterbitkan`, type: 'success' });
    },

    async tarik(k) {
        const ok = await this.$store.confirm.ask({
            title: `Tarik terbitan ${this.labelJenis} ${k.kelas}?`,
            message: 'Rapor tidak lagi terlihat di portal siswa dan orang tua sampai diterbitkan kembali.',
            confirmLabel: 'Tarik terbitan',
            tone: 'danger',
        });
        if (!ok) return;
        k.terbit = null;
        this.$dispatch('toast', { message: `Terbitan ${this.labelJenis} ${k.kelas} ditarik`, type: 'warning' });
    },
}));

/** 7.05 Arsip & Versi Rapor. TODO API: GET /api/rapor/{id}/versi/{v}/pdf */
Alpine.data('arsipRapor', ({ arsip, dokumen }) => ({
    arsip,
    dokumen,
    tahun: '2026/2027',
    semester: 'Ganjil',
    jenisFilter: 'Semua',
    kelas: 'X-B',
    cari: '',
    buka: 'xb-2526004',
    page: 1,
    perPage: 6,
    pratinjau: null,

    init() {
        ['cari', 'jenisFilter', 'kelas'].forEach((k) => this.$watch(k, () => (this.page = 1)));
    },

    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        const jenis = { 'Rapor STS': 'sts', 'Rapor Semester': 'semester' }[this.jenisFilter];
        return this.arsip.filter((r) => (!q || r.nama.toLowerCase().includes(q)) && (!jenis || r.jenis === jenis) && r.kelas === this.kelas);
    },
    get halaman() { return Math.max(1, Math.ceil(this.tersaring.length / this.perPage)); },
    get barisHalaman() { return this.tersaring.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
    get rentang() {
        if (!this.tersaring.length) return '0 dari 0';
        const a = (this.page - 1) * this.perPage + 1;
        return `${a}–${a + this.barisHalaman.length - 1} dari ${this.tersaring.length}`;
    },

    lihat(r) {
        this.pratinjau = r;
        this.$dispatch('open-modal', 'pratinjau');
    },

    unduh(r, v) {
        this.$dispatch('toast', { message: `Rapor ${r.nama} ${v ?? r.versi[0].label} diunduh`, type: 'success' });
    },
}));
