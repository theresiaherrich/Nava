import Alpine from 'alpinejs';

const MAKS_BYTE = 5 * 1024 * 1024;

/**
 * Modal impor Excel dua langkah (Operator 2.05): unggah → periksa → simpan.
 * Dipakai bersama oleh Data Siswa dan Data Guru (components/ui/impor-modal).
 * TODO API: POST /api/master/{siswa|guru}/impor/pratinjau (multipart) → { token, valid, masalah[] }
 *           POST /api/master/{siswa|guru}/impor/simpan { token, lewati_masalah }
 */
Alpine.data('imporExcel', ({ hasil, satuan, langsungPeriksa }) => ({
    langkah: langsungPeriksa ? 2 : 1,
    hasil,
    satuan,
    berkas: langsungPeriksa ? { nama: hasil.berkas, info: `${hasil.baris} baris · ${hasil.ukuran}` } : null,
    galat: '',
    seret: false,
    memproses: false,
    mode: 'valid',

    mulai() {
        this.langkah = 1;
        this.berkas = null;
        this.galat = '';
        this.mode = 'valid';
    },

    pilih(file) {
        this.galat = '';
        if (!file) return;
        if (!/\.(xlsx|xls|csv)$/i.test(file.name)) {
            this.galat = 'Format berkas harus .xlsx, .xls, atau .csv.';
            return;
        }
        if (file.size > MAKS_BYTE) {
            this.galat = 'Ukuran berkas maksimal 5 MB.';
            return;
        }
        this.memproses = true;
        // Simulasi pemeriksaan server.
        setTimeout(() => {
            this.berkas = { nama: file.name, info: `${this.hasil.baris} baris · ${Math.max(1, Math.round(file.size / 1024))} KB` };
            this.memproses = false;
            this.langkah = 2;
        }, 600);
    },

    get labelSimpan() {
        return this.mode === 'valid' ? `Simpan ${this.$angka(this.hasil.valid)} Data` : 'Tutup';
    },

    simpan() {
        if (this.mode === 'perbaiki') {
            this.$dispatch('close-modal', 'impor');
            this.$dispatch('toast', { message: 'Tidak ada data yang disimpan. Perbaiki berkas lalu unggah ulang.', type: 'info' });
            return;
        }
        this.langkah = 3;
        this.$dispatch('close-modal', 'impor');
        this.$dispatch('toast', { message: `${this.$angka(this.hasil.valid)} data ${this.satuan} berhasil diimpor`, type: 'success' });
        if (this.hasil.masalah.length) {
            setTimeout(() => this.$dispatch('toast', { message: `Impor gagal — ${this.hasil.masalah.length} baris bermasalah`, type: 'danger', action: 'Lihat' }), 400);
        }
    },
}));
