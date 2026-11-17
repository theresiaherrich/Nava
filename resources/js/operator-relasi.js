import Alpine from 'alpinejs';

/**
 * Relasi Orang Tua – Anak (Operator 2.09–2.10).
 * TODO API: POST /api/master/orang-tua/{id}/anak, PUT /api/master/orang-tua/{id}/anak, DELETE /api/master/orang-tua/{id}/anak/{nis}
 */
Alpine.data('relasiPage', ({ relasi, akunOrtu, calonAnak, bukaModal }) => ({
    relasi,
    akunOrtu,
    calonAnak,
    form: {},
    error: {},
    editId: null,
    combo: { buka: false, cari: '' },

    init() {
        this.reset();
        if (bukaModal) {
            // Contoh isian sesuai desain 2.10.
            this.form = { ortu: this.akunOrtu.find((a) => a.username === 'hendra.g') ?? null, anak: ['2526001', '2425007'], cariAnak: '', hubungan: 'Ayah' };
        }
    },

    reset() {
        this.form = { ortu: null, anak: [], cariAnak: '', hubungan: 'Ayah' };
        this.error = {};
        this.editId = null;
        this.combo = { buka: false, cari: '' };
    },

    get ortuTersaring() {
        const q = this.combo.cari.trim().toLowerCase();
        return this.akunOrtu.filter((a) => !q || a.nama.toLowerCase().includes(q) || a.username.includes(q)).slice(0, 8);
    },

    /** Anak terpilih + saran. Tanpa pencarian, saran diutamakan yang nama keluarganya mirip nama orang tua. */
    get anakTampil() {
        const q = this.form.cariAnak.trim().toLowerCase();
        const terpilih = this.calonAnak.filter((s) => this.form.anak.includes(s.nis));
        const keluarga = (this.form.ortu?.nama.split(' ').pop() ?? '').slice(0, 5).toLowerCase();
        const mirip = (s) => (keluarga && s.nama.toLowerCase().includes(keluarga) ? 0 : 1);
        const lain = this.calonAnak
            .filter((s) => !this.form.anak.includes(s.nis) && (!q || s.nama.toLowerCase().includes(q) || s.nis.includes(q)))
            .sort((a, b) => mirip(a) - mirip(b));
        return [...terpilih, ...lain.slice(0, q ? 12 : Math.max(1, 3 - terpilih.length))];
    },

    pilihOrtu(a) {
        this.form.ortu = a;
        this.combo = { buka: false, cari: '' };
        delete this.error.ortu;
    },

    toggleAnak(nis) {
        const i = this.form.anak.indexOf(nis);
        i === -1 ? this.form.anak.push(nis) : this.form.anak.splice(i, 1);
        delete this.error.anak;
    },

    tambah() {
        this.reset();
        this.$dispatch('open-modal', 'hubungkan');
    },

    ubah(r) {
        this.reset();
        this.editId = r.id_orang_tua;
        // Anak yang sudah terhubung ikut ditampilkan sebagai pilihan.
        r.anak.forEach((a) => {
            if (!this.calonAnak.some((s) => s.nama === a.nama)) this.calonAnak.unshift({ nis: `${r.id_orang_tua}-${a.nama}`, nama: a.nama, kelas: a.kelas });
        });
        this.form = {
            ortu: { id: r.id_orang_tua, nama: r.nama, username: r.username },
            anak: this.calonAnak.filter((s) => r.anak.some((a) => a.nama === s.nama)).map((s) => s.nis),
            cariAnak: '',
            hubungan: r.hubungan,
        };
        this.$dispatch('open-modal', 'hubungkan');
    },

    simpan() {
        const f = this.form;
        const e = {};
        if (!f.ortu) e.ortu = 'Pilih akun orang tua';
        if (!f.anak.length) e.anak = 'Pilih minimal satu anak';
        this.error = e;
        if (Object.keys(e).length) return;

        const anak = this.calonAnak.filter((s) => f.anak.includes(s.nis)).map((s) => ({ nama: s.nama, kelas: s.kelas }));
        const ada = this.relasi.find((r) => r.id_orang_tua === (this.editId ?? f.ortu.id));
        if (ada) Object.assign(ada, { anak, hubungan: f.hubungan });
        else this.relasi.unshift({ id_orang_tua: f.ortu.id ?? Date.now(), nama: f.ortu.nama, username: f.ortu.username, hubungan: f.hubungan, anak, status: 'menunggu' });

        this.$dispatch('close-modal', 'hubungkan');
        this.$dispatch('toast', { message: `${f.ortu.nama} terhubung ke ${anak.length} anak`, type: 'success' });
    },

    async hapus(r) {
        const ok = await this.$store.confirm.ask({
            title: `Putuskan relasi ${r.nama}?`,
            message: 'Orang tua tidak lagi dapat melihat rapor anak di portal. Akun orang tua tetap ada.',
            confirmLabel: 'Putuskan',
            tone: 'danger',
        });
        if (!ok) return;
        this.relasi = this.relasi.filter((x) => x.id_orang_tua !== r.id_orang_tua);
        this.$dispatch('toast', { message: `Relasi ${r.nama} diputuskan`, type: 'warning' });
    },
}));
