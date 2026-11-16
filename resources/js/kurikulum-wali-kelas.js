import Alpine from 'alpinejs';

/** Penetapan Wali Kelas (Kurikulum 4.10). TODO API: PUT /api/kurikulum/wali-kelas (batch) */
Alpine.data('waliKelas', ({ kelas, guru, perubahan }) => ({
    // '' = belum ada wali (nilai option "Pilih wali kelas").
    kelas: kelas.map((k) => ({ ...k, wali: k.wali ?? '', awal: k.wali ?? '' })),
    guru,
    semester: 'Ganjil 2026/2027',
    tingkat: 'Kelas XI',
    cari: '',

    init() {
        Object.entries(perubahan).forEach(([nama, wali]) => {
            const k = this.kelas.find((x) => x.kelas === nama);
            if (k) k.wali = wali;
        });
    },

    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        return this.kelas.filter((k) => `Kelas ${k.tingkat}` === this.tingkat
            && (!q || k.kelas.toLowerCase().includes(q) || (k.wali ?? '').toLowerCase().includes(q)));
    },

    get tanpaWali() {
        return this.kelas.filter((k) => `Kelas ${k.tingkat}` === this.tingkat && !k.wali);
    },

    get diubah() {
        return this.kelas.filter((k) => k.wali !== k.awal);
    },

    /** Guru yang sudah menjadi wali kelas lain (satu guru hanya untuk satu kelas). */
    waliDi(nama, kecuali) {
        return this.kelas.find((k) => k.wali === nama && k.kelas !== kecuali)?.kelas ?? null;
    },

    batalkan() {
        this.kelas.forEach((k) => (k.wali = k.awal));
    },

    simpan() {
        const jumlah = this.diubah.length;
        this.kelas.forEach((k) => (k.awal = k.wali));
        this.$dispatch('toast', { message: `Wali kelas untuk ${jumlah} kelas disimpan`, type: 'success' });
    },
}));
