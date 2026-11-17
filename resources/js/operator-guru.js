import Alpine from 'alpinejs';
import './impor-excel.js';

/**
 * Data Guru (Operator 2.06).
 * TODO API: POST /api/master/guru, PUT /api/master/guru/{id}, POST /api/akun (buat akun guru)
 */
Alpine.data('guruPage', ({ guru, mapel }) => ({
    guru,
    mapel,
    form: {},
    error: {},
    editId: null,

    tambah() {
        this.form = { nama: '', status: 'ASN', nip: '', mapel: this.mapel[0] ?? '' };
        this.error = {};
        this.editId = null;
        this.$dispatch('open-modal', 'guru');
    },

    ubah(g) {
        this.form = { nama: g.nama, status: g.status, nip: g.nip ?? '', mapel: g.mapel };
        this.error = {};
        this.editId = g.id_guru;
        this.$dispatch('open-modal', 'guru');
    },

    simpan() {
        const f = this.form;
        const e = {};
        if (!f.nama.trim()) e.nama = 'Nama wajib diisi';
        if (f.status === 'ASN' && !/^\d{18}$/.test(f.nip)) e.nip = 'NIP guru ASN harus 18 digit angka';
        this.error = e;
        if (Object.keys(e).length) return;

        const data = { nama: f.nama.trim(), status: f.status, nip: f.status === 'ASN' ? f.nip : null, mapel: f.mapel };
        const lama = this.editId ? this.guru.find((x) => x.id_guru === this.editId) : null;
        if (lama) Object.assign(lama, data);
        else this.guru.unshift({ id_guru: Date.now(), ...data, peran: ['Guru'], akun: 'belum' });
        this.$dispatch('close-modal', 'guru');
        this.$dispatch('toast', { message: `Data guru ${data.nama} ${lama ? 'diperbarui' : 'ditambahkan'}`, type: 'success' });
    },

    async akun(g) {
        if (g.akun === 'belum') {
            const ok = await this.$store.confirm.ask({
                title: `Buat akun untuk ${g.nama}?`,
                message: 'Akun dibuat dengan peran Guru dan sandi sementara. Guru wajib mengganti sandi saat login pertama.',
                confirmLabel: 'Buat akun',
            });
            if (!ok) return;
            g.akun = 'aktif';
            this.$dispatch('toast', { message: `Akun ${g.nama} dibuat`, type: 'success' });
            return;
        }
        const ok = await this.$store.confirm.ask({
            title: `Reset kata sandi ${g.nama}?`,
            message: 'Sandi sementara dibuat otomatis dan wajib diganti saat login berikutnya.',
            confirmLabel: 'Reset sandi',
            tone: 'warning',
        });
        if (ok) this.$dispatch('toast', { message: `Sandi ${g.nama} direset`, type: 'info' });
    },
}));
