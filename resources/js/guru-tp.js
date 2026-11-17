import Alpine from 'alpinejs';

const MAKS_DESKRIPSI = 300;

/**
 * Kelola Tujuan Pembelajaran (Guru 06–18).
 * Aturan desain: setengah TP pertama (floor n/2) masuk STS; satu TP ditandai SAS (TP terakhir); maks. 10 TP;
 * TP yang sudah dinilai tidak dapat dihapus; TP STS terkunci saat nilai STS dikunci.
 * TODO API: POST/PUT/DELETE /api/guru/rombel/{id}/tp, PUT /api/guru/rombel/{id}/tp/urutan, POST /api/guru/tp/salin
 */
Alpine.data('tpPage', ({ rombel, aktif, stsTerkunci, maks, tampil, modal, bukaRombel }) => ({
    rombel,
    aktifId: aktif,
    stsTerkunci,
    maks,
    tampil: tampil === 'sts' ? 'sts' : 'semua',
    semester: 'Ganjil · 2026/2027',
    pilihRombel: bukaRombel,
    cariRombel: '',
    form: { id: null, deskripsi: '', sas: false },
    error: {},
    seret: { dari: null, ke: null },
    salin: { tujuan: [], mode: 'tambah' },
    tidakDihapus: null,
    maksDeskripsi: MAKS_DESKRIPSI,

    init() {
        if (modal === 'tidak-dihapus') this.tidakDihapus = this.tp[2] ?? null;
        // Modal anak baru memasang listener setelah komponen ini selesai init.
        this.$nextTick(() => {
            if (modal === 'tambah' || modal === 'tambah-error') {
                this.bukaTambah();
                if (modal === 'tambah-error') {
                    this.form.sas = true;
                    this.validasi();
                }
            }
            if (modal === 'salin') this.bukaSalin(true);
        });
    },

    get r() {
        return this.rombel.find((x) => x.id === this.aktifId);
    },
    get tp() {
        return this.r.tp;
    },
    get jumlahSts() {
        return Math.floor(this.tp.length / 2);
    },
    isSts(i) {
        return i < this.jumlahSts;
    },
    get tpSas() {
        return this.tp.find((t) => t.sas);
    },
    get tampilTp() {
        return this.tampil === 'sts' ? this.tp.slice(0, this.jumlahSts) : this.tp;
    },
    get penuh() {
        return this.tp.length >= this.maks;
    },
    terkunci(i) {
        return this.stsTerkunci && this.isSts(i);
    },
    kode(i) {
        return `TP${i + 1}`;
    },
    get rombelTersaring() {
        const q = this.cariRombel.trim().toLowerCase();
        return this.rombel.filter((x) => !q || x.kelas.toLowerCase().includes(q));
    },

    gantiRombel(id) {
        this.aktifId = id;
        this.pilihRombel = false;
        this.tampil = 'semua';
        history.replaceState(null, '', `?rombel=${id}`);
    },

    // ---- Tambah / ubah (07, 08, 11) ----
    bukaTambah() {
        if (this.penuh) return;
        this.form = { id: null, deskripsi: '', sas: false };
        this.error = {};
        this.$dispatch('open-modal', 'tp');
    },

    bukaUbah(t) {
        this.form = { id: t.id, deskripsi: t.deskripsi, sas: t.sas };
        this.error = {};
        this.tidakDihapus = null;
        this.$dispatch('close-modal', 'tidak-dihapus');
        this.$dispatch('open-modal', 'tp');
    },

    get kodeForm() {
        const i = this.form.id ? this.tp.findIndex((t) => t.id === this.form.id) : this.tp.length;
        return this.kode(i);
    },

    get sasLain() {
        const t = this.tpSas;
        return t && t.id !== this.form.id ? t : null;
    },

    validasi() {
        const e = {};
        const d = this.form.deskripsi.trim();
        if (!d) e.deskripsi = 'Deskripsi TP wajib diisi.';
        else if (d.length > MAKS_DESKRIPSI) e.deskripsi = `Deskripsi maksimal ${MAKS_DESKRIPSI} karakter.`;
        else if (this.tp.some((t) => t.id !== this.form.id && t.deskripsi.toLowerCase() === d.toLowerCase())) e.deskripsi = 'TP dengan deskripsi yang sama sudah ada.';
        if (this.form.sas && this.sasLain) e.sas = `${this.kode(this.tp.indexOf(this.sasLain))} sudah ditandai sebagai TP terakhir.`;
        this.error = e;
        return !Object.keys(e).length;
    },

    simpan() {
        if (!this.validasi()) return;
        const d = this.form.deskripsi.trim();
        if (this.form.id) {
            Object.assign(this.tp.find((t) => t.id === this.form.id), { deskripsi: d, sas: this.form.sas });
            this.$dispatch('toast', { message: `${this.kodeForm} diperbarui`, type: 'success' });
        } else {
            this.tp.push({ id: `tp-${Date.now()}`, deskripsi: d, sas: this.form.sas, jumlah_nilai: 0 });
            this.$dispatch('toast', { message: `${this.kode(this.tp.length - 1)} ditambahkan`, type: 'success' });
        }
        this.$dispatch('close-modal', 'tp');
    },

    // ---- Hapus (15, 16) ----
    async hapus(t) {
        const i = this.tp.indexOf(t);
        if (t.jumlah_nilai > 0) {
            this.tidakDihapus = t;
            this.$dispatch('open-modal', 'tidak-dihapus');
            return;
        }
        const ok = await this.$store.confirm.ask({
            title: `Hapus ${this.kode(i)}?`,
            message: `"${t.deskripsi}" akan dihapus dari ${this.r.kelas}. Urutan TP sesudahnya ikut bergeser.`,
            confirmLabel: 'Hapus TP',
            tone: 'danger',
        });
        if (!ok) return;
        this.tp.splice(i, 1);
        this.$dispatch('toast', { message: `${this.kode(i)} dihapus`, type: 'success' });
    },

    // ---- Urutkan (12): seret atau tombol panah keyboard ----
    mulaiSeret(i, e) {
        if (this.terkunci(i)) return e.preventDefault();
        this.seret = { dari: i, ke: i };
        e.dataTransfer.effectAllowed = 'move';
    },
    lewat(i) {
        if (this.seret.dari !== null && !this.terkunci(i)) this.seret.ke = i;
    },
    lepas() {
        const { dari, ke } = this.seret;
        this.seret = { dari: null, ke: null };
        if (dari === null || ke === null || dari === ke) return;
        this.pindah(dari, ke);
    },
    geserKeyboard(i, arah) {
        const ke = i + arah;
        if (ke < 0 || ke >= this.tp.length || this.terkunci(ke) || this.terkunci(i)) return;
        this.pindah(i, ke);
        this.$nextTick(() => document.querySelector(`[data-geser="${ke}"]`)?.focus());
    },
    pindah(dari, ke) {
        const [t] = this.tp.splice(dari, 1);
        this.tp.splice(ke, 0, t);
        this.$dispatch('toast', { message: 'Urutan TP disimpan', type: 'success' });
    },

    // ---- Salin ke kelas lain (14) ----
    get tujuanSalin() {
        return this.rombel.filter((x) => x.id !== this.r.id);
    },
    bukaSalin(contoh = false) {
        if (!this.tp.length) return;
        const sefase = this.tujuanSalin.filter((x) => x.fase === this.r.fase);
        this.salin = { tujuan: contoh ? sefase.map((x) => x.id) : [], mode: 'tambah' };
        this.$dispatch('open-modal', 'salin');
    },
    melebihi(x) {
        return this.salin.mode === 'tambah' && x.tp.length + this.tp.length > this.maks;
    },
    toggleSalin(x) {
        if (x.fase !== this.r.fase) return;
        const i = this.salin.tujuan.indexOf(x.id);
        i === -1 ? this.salin.tujuan.push(x.id) : this.salin.tujuan.splice(i, 1);
    },
    get salinValid() {
        const t = this.tujuanSalin.filter((x) => this.salin.tujuan.includes(x.id));
        return t.length && t.every((x) => !this.melebihi(x));
    },
    jalankanSalin() {
        if (!this.salinValid) return;
        const tujuan = this.tujuanSalin.filter((x) => this.salin.tujuan.includes(x.id));
        tujuan.forEach((x) => {
            const salinan = this.tp.map((t, i) => ({ id: `tp-${x.id}-${Date.now()}-${i}`, deskripsi: t.deskripsi, sas: t.sas, jumlah_nilai: 0 }));
            if (this.salin.mode === 'ganti') x.tp = salinan;
            else {
                if (salinan.some((t) => t.sas)) x.tp.forEach((t) => (t.sas = false));
                x.tp.push(...salinan);
            }
        });
        this.$dispatch('close-modal', 'salin');
        this.$dispatch('toast', { message: `${this.tp.length} TP berhasil disalin ke ${tujuan.map((x) => x.kelas).join(' dan ')}`, type: 'success' });
    },
}));
