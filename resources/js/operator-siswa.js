import Alpine from 'alpinejs';
import './impor-excel.js';

/**
 * Data Siswa (Operator 2.04).
 * TODO API: POST /api/master/siswa, PUT /api/master/siswa/{id}
 */
Alpine.data('siswaPage', ({ siswa, nisTerpakai, nisnTerpakai }) => ({
    siswa,
    nisTerpakai,
    nisnTerpakai,
    form: {},
    error: {},
    editId: null,
    detail: null,

    kosong() {
        return { nis: '', nisn: '', nama: '', jenis_kelamin: 'L', agama: 'Islam', kelas: 'X-A' };
    },

    tambah() {
        this.form = this.kosong();
        this.error = {};
        this.editId = null;
        this.$dispatch('open-modal', 'siswa');
    },

    ubah(s) {
        this.form = { nis: s.nis, nisn: s.nisn, nama: s.nama, jenis_kelamin: s.jenis_kelamin, agama: s.agama, kelas: s.kelas };
        this.error = {};
        this.editId = s.id_siswa;
        this.$dispatch('open-modal', 'siswa');
    },

    lihat(s) {
        this.detail = s;
        this.$dispatch('open-modal', 'detail-siswa');
    },

    simpan() {
        const f = this.form;
        const lama = this.editId ? this.siswa.find((x) => x.id_siswa === this.editId) : null;
        const e = {};
        if (!/^\d{7}$/.test(f.nis)) e.nis = 'NIS harus 7 digit angka';
        else if (f.nis !== lama?.nis && this.nisTerpakai.includes(f.nis)) e.nis = 'NIS sudah terdaftar';
        if (!/^\d{10}$/.test(f.nisn)) e.nisn = 'NISN harus 10 digit angka';
        else if (f.nisn !== lama?.nisn && this.nisnTerpakai.includes(f.nisn)) e.nisn = 'NISN sudah terdaftar';
        if (!f.nama.trim()) e.nama = 'Nama lengkap wajib diisi';
        this.error = e;
        if (Object.keys(e).length) return;

        const data = { ...f, nama: f.nama.trim(), tingkat: f.kelas.split('-')[0] };
        if (lama) {
            Object.assign(lama, data);
        } else {
            this.siswa.unshift({ id_siswa: Date.now(), ...data, orang_tua: 'belum' });
            this.nisTerpakai.push(f.nis);
            this.nisnTerpakai.push(f.nisn);
        }
        this.$dispatch('close-modal', 'siswa');
        this.$dispatch('toast', { message: `Data siswa ${data.nama} ${lama ? 'diperbarui' : 'ditambahkan'}`, type: 'success' });
    },
}));
