import Alpine from 'alpinejs';

const PERAN_FORM = ['Guru', 'Wali Kelas', 'Admin Kurikulum', 'Admin Kesiswaan', 'Guru BK', 'Operator'];

function sandiAcak() {
    const huruf = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    const simbol = '!@#$%';
    let s = '';
    for (let i = 0; i < 9; i++) s += huruf[Math.floor(Math.random() * huruf.length)];
    return s + simbol[Math.floor(Math.random() * simbol.length)] + Math.floor(Math.random() * 10);
}

/**
 * Kelola Akun & Peran (Operator 2.02–2.03).
 * TODO API: POST /api/akun, PUT /api/akun/{id}, POST /api/akun/{id}/reset-password, PATCH /api/akun/{id}/status
 */
Alpine.data('akunPage', ({ akun, username, bukaModal }) => ({
    akun,
    usernameTerpakai: username,
    peranForm: PERAN_FORM,
    form: {},
    error: {},
    editId: null,
    lihatSandi: false,

    init() {
        this.reset();
        if (bukaModal) {
            // Contoh isian sesuai desain 2.03 (username bentrok).
            this.form = {
                nama: 'Rudi Hartono, M.Pd.', username: 'rudi.hartono', email: '', sandi: 'Rh2026!aa',
                peran: ['Guru', 'Wali Kelas'], nip: '197804122005011003', status: 'ASN',
            };
            this.validasi();
        }
    },

    reset() {
        this.form = { nama: '', username: '', email: '', sandi: '', peran: [], nip: '', status: 'ASN' };
        this.error = {};
        this.editId = null;
        this.lihatSandi = false;
    },

    get pilihGuru() {
        return this.form.peran.includes('Guru');
    },

    tambah() {
        this.reset();
        this.$dispatch('open-modal', 'akun');
    },

    ubah(a) {
        this.reset();
        this.editId = a.id_user;
        this.form = { nama: a.nama, username: a.username, email: a.email ?? '', sandi: '', peran: [...a.peran], nip: '', status: 'ASN' };
        this.$dispatch('open-modal', 'akun');
    },

    buatSandi() {
        this.form.sandi = sandiAcak();
        this.lihatSandi = true;
        delete this.error.sandi;
    },

    togglePeran(p) {
        const i = this.form.peran.indexOf(p);
        i === -1 ? this.form.peran.push(p) : this.form.peran.splice(i, 1);
        delete this.error.peran;
    },

    validasi() {
        const f = this.form;
        const e = {};
        if (!f.nama.trim()) e.nama = 'Nama lengkap wajib diisi';
        if (!f.username.trim()) e.username = 'Username wajib diisi';
        else if (!/^[a-z0-9._]{3,}$/.test(f.username.trim())) e.username = 'Minimal 3 karakter: huruf kecil, angka, titik, atau garis bawah';
        else {
            const lama = this.editId ? this.akun.find((x) => x.id_user === this.editId)?.username : null;
            if (f.username.trim() !== lama && this.usernameTerpakai.includes(f.username.trim())) e.username = 'Username sudah dipakai';
        }
        if (f.email && !/^\S+@\S+\.\S+$/.test(f.email)) e.email = 'Format email tidak valid';
        if (!this.editId && f.sandi.length < 8) e.sandi = 'Password awal minimal 8 karakter';
        if (!f.peran.length) e.peran = 'Pilih minimal satu peran';
        if (this.pilihGuru && f.status === 'ASN' && f.nip && !/^\d{18}$/.test(f.nip)) e.nip = 'NIP harus 18 digit angka';
        this.error = e;
        return !Object.keys(e).length;
    },

    simpan() {
        if (!this.validasi()) return;
        const f = this.form;
        if (this.editId) {
            Object.assign(this.akun.find((x) => x.id_user === this.editId), { nama: f.nama.trim(), username: f.username.trim(), peran: [...f.peran] });
            this.$dispatch('toast', { message: `Akun ${f.nama.trim()} diperbarui`, type: 'success' });
        } else {
            this.akun.unshift({ id_user: Date.now(), nama: f.nama.trim(), username: f.username.trim(), peran: [...f.peran], status: 'aktif', login: '—' });
            this.usernameTerpakai.push(f.username.trim());
            this.$dispatch('toast', { message: `Akun ${f.nama.trim()} dibuat. Wajib ganti sandi saat login pertama.`, type: 'success' });
        }
        this.$dispatch('close-modal', 'akun');
    },

    async resetSandi(a) {
        const ok = await this.$store.confirm.ask({
            title: `Reset kata sandi ${a.nama}?`,
            message: 'Sandi sementara dibuat otomatis dan pengguna wajib menggantinya saat login berikutnya.',
            confirmLabel: 'Reset sandi',
            tone: 'warning',
        });
        if (ok) this.$dispatch('toast', { message: `Sandi ${a.nama} direset · sementara: ${sandiAcak()}`, type: 'info', timeout: 8000 });
    },

    async ubahStatus(a) {
        const aktif = a.status === 'aktif';
        const ok = await this.$store.confirm.ask({
            title: `${aktif ? 'Nonaktifkan' : 'Aktifkan'} akun ${a.nama}?`,
            message: aktif ? 'Pengguna tidak dapat masuk sampai akun diaktifkan kembali. Data tetap tersimpan.' : 'Pengguna dapat masuk kembali dengan sandinya.',
            confirmLabel: aktif ? 'Nonaktifkan' : 'Aktifkan',
            tone: aktif ? 'danger' : 'primary',
        });
        if (!ok) return;
        a.status = aktif ? 'nonaktif' : 'aktif';
        this.$dispatch('toast', { message: `Akun ${a.nama} ${aktif ? 'dinonaktifkan' : 'diaktifkan'}`, type: aktif ? 'warning' : 'success' });
    },
}));
