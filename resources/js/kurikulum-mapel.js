import Alpine from 'alpinejs';

/** Mata Pelajaran (Kurikulum 4.03–4.04). TODO API: POST /api/kurikulum/mapel, PUT /api/kurikulum/mapel/{id} */
Alpine.data('mataPelajaran', ({ mapel, kelompok, bukaModal }) => ({
    mapel,
    kelompok,
    fase: 'F',
    cari: '',
    form: {},
    error: {},
    editId: null,

    init() {
        this.resetForm();
        if (bukaModal) {
            // Contoh isian sesuai desain 4.04.
            this.form = { kode: 'BA-05', nama: 'Bahasa Korea', fase: 'F', kelompok: 'bahasa_asing', aktif: true };
        }
    },

    resetForm() {
        this.form = { kode: '', nama: '', fase: this.fase, kelompok: 'wajib', aktif: true };
        this.error = {};
        this.editId = null;
    },

    daftar(kode) {
        const q = this.cari.trim().toLowerCase();
        return this.mapel.filter(
            (m) => m.fase === this.fase && m.kelompok === kode
                && (!q || m.nama.toLowerCase().includes(q) || m.kode.toLowerCase().includes(q)),
        );
    },

    tambah() {
        this.resetForm();
        this.$dispatch('open-modal', 'mapel');
    },

    ubah(m) {
        this.form = { kode: m.kode, nama: m.nama, fase: m.fase, kelompok: m.kelompok, aktif: m.aktif };
        this.error = {};
        this.editId = m.id_mapel;
        this.$dispatch('open-modal', 'mapel');
    },

    simpan() {
        const f = this.form;
        this.error = {};
        if (!f.kode.trim()) this.error.kode = 'Kode wajib diisi';
        if (!f.nama.trim()) this.error.nama = 'Nama wajib diisi';
        const bentrok = this.mapel.some(
            (m) => m.fase === f.fase && m.kode.toLowerCase() === f.kode.trim().toLowerCase() && m.id_mapel !== this.editId,
        );
        if (!this.error.kode && bentrok) this.error.kode = 'Kode sudah dipakai';
        if (Object.keys(this.error).length) return;

        if (this.editId) {
            Object.assign(this.mapel.find((m) => m.id_mapel === this.editId), { ...f, kode: f.kode.trim(), nama: f.nama.trim() });
        } else {
            this.mapel.push({
                id_mapel: `${f.fase}-${f.kode}`.toLowerCase(),
                ...f,
                kode: f.kode.trim(),
                nama: f.nama.trim(),
                jumlah_rombel: 0,
            });
        }
        this.fase = f.fase;
        this.$dispatch('close-modal', 'mapel');
        this.$dispatch('toast', { message: `Mapel ${f.nama.trim()} disimpan`, type: 'success' });
        this.editId = null;
    },
}));
