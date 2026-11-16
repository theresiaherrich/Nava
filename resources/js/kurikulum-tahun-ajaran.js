import Alpine from 'alpinejs';

/** Tahun Ajaran & Semester (Kurikulum 4.01–4.02). TODO API: POST /api/kurikulum/semester/{id}/aktifkan */
Alpine.data('tahunAjaran', ({ semester, bukaModal }) => ({
    semester,
    target: null,
    salinRombel: true,

    init() {
        if (bukaModal) {
            this.target = this.semester.find((s) => s.status === 'akan_datang') ?? null;
        }
    },

    get aktif() {
        return this.semester.find((s) => s.status === 'aktif');
    },

    get jumlahTahun() {
        return new Set(this.semester.map((s) => s.tahun_ajaran)).size;
    },

    konfirmasi(item) {
        this.target = item;
        this.salinRombel = true;
        this.$dispatch('open-modal', 'aktifkan');
    },

    aktifkan() {
        if (!this.target) return;
        this.semester.forEach((s) => {
            if (s.status === 'aktif') s.status = 'selesai';
        });
        this.target.status = 'aktif';
        this.$dispatch('close-modal', 'aktifkan');
        this.$dispatch('toast', {
            message: `Semester ${this.target.tahun_ajaran} ${this.target.semester} diaktifkan`,
            type: 'success',
        });
    },

    segeraHadir(fitur) {
        this.$dispatch('toast', { message: `${fitur} belum tersedia di desain.`, type: 'info' });
    },
}));
