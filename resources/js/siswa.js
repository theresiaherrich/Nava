import Alpine from 'alpinejs';

/**
 * Portal Siswa (Siswa 3.01–5.02). Hanya rapor/nilai resmi yang sudah diterbitkan.
 * TODO API: GET /api/siswa/rapor/{jenis}/pdf, GET /api/siswa/nilai?periode=
 */

/** 3.01 / 3.04 / 3.05 Rapor Saya. */
Alpine.data('raporSaya', ({ rapor, dokumen, jenis }) => ({
    rapor,
    dokumen,
    jenis,
    lembar: 1,

    init() {
        this.$watch('jenis', () => {
            this.lembar = 1;
            const url = new URL(location.href);
            url.searchParams.set('jenis', this.jenis);
            history.replaceState(null, '', url);
        });
    },

    get r() { return this.rapor[this.jenis]; },
    get doc() { return this.dokumen[this.jenis]; },
    get jumlahLembar() { return this.jenis === 'sts' ? 1 : 2; },
    get judul() { return `${this.r.label} ${this.r.periode}`; },

    keLembar(n) {
        this.lembar = n;
        const el = this.$refs.area.querySelector(`[data-halaman="${n}"]`);
        if (el) this.$refs.area.scrollTo({ top: el.offsetTop - 24, behavior: 'smooth' });
    },
    pantauLembar(area) {
        if (this.jenis !== 'semester') return;
        const h2 = area.querySelector('[data-halaman="2"]');
        this.lembar = h2 && area.scrollTop >= h2.offsetTop - area.clientHeight / 2 ? 2 : 1;
    },
    unduh() {
        this.$dispatch('toast', { message: `${this.judul} diunduh (PDF)`, type: 'success' });
    },
}));

/** 4.01 Nilai per Mapel: cari + paginasi 9 kartu. */
Alpine.data('nilaiSiswa', ({ nilai }) => ({
    nilai,
    cari: '',
    page: 1,
    perPage: 9,

    init() { this.$watch('cari', () => (this.page = 1)); },

    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        return this.nilai.filter((m) => !q || m.mapel.toLowerCase().includes(q));
    },
    get halaman() { return Math.max(1, Math.ceil(this.tersaring.length / this.perPage)); },
    get kartu() { return this.tersaring.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
}));

/** 5.01 / 5.02 Riwayat Rapor. */
Alpine.data('riwayatSiswa', () => ({
    tahun: 'Semua',
    unduh(label) {
        this.$dispatch('toast', { message: `${label} diunduh (PDF)`, type: 'success' });
    },
}));
