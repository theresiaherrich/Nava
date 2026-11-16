import Alpine from 'alpinejs';

/** Inisial dua huruf dari nama (tanpa gelar). */
export function inisial(nama = '') {
    return nama
        .replace(/,.*$/, '')
        .replace(/\b(Dra?|Drs|Ir|Hj?)\.\s*/g, '')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((k) => k[0]?.toUpperCase() ?? '')
        .join('');
}

const WARNA = ['bg-blue-600', 'bg-violet-600', 'bg-teal-600', 'bg-sky-600', 'bg-indigo-600', 'bg-emerald-600'];
export function warnaAvatar(nama = '') {
    let h = 0;
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) >>> 0;
    return WARNA[h % WARNA.length];
}

export const namaPendek = (nama = '') => nama.replace(/,.*$/, '');

/** Pemilih guru pengampu (dropdown 4.06), dipakai halaman Rombel dan Detail Rombel. */
export function pemilihGuru(calon) {
    return {
        calon,
        picker: { buka: false, id: null, mapel: '', cari: '', gaya: '' },

        bukaPicker(id, mapel, tombol) {
            const r = tombol.getBoundingClientRect();
            const lebar = 320;
            const kiri = Math.min(window.innerWidth - lebar - 16, Math.max(16, r.right - lebar + 40));
            this.picker = { buka: true, id, mapel, cari: '', gaya: `top:${r.bottom + 6}px;left:${kiri}px;width:${lebar}px` };
            this.$nextTick(() => this.$refs.cariGuru?.focus());
        },

        tutupPicker() {
            this.picker.buka = false;
        },

        get calonTersaring() {
            const q = this.picker.cari.trim().toLowerCase();
            return this.calon.filter((g) => !q || g.nama.toLowerCase().includes(q));
        },
    };
}

/** Gabungkan objek komponen tanpa mengevaluasi getter (spread biasa membekukan getter). */
export function gabung(...objek) {
    return objek.reduce((hasil, o) => Object.defineProperties(hasil, Object.getOwnPropertyDescriptors(o)), {});
}

/** Rombel & Pembagian Mengajar (Kurikulum 4.05–4.07). TODO API: POST /api/kurikulum/rombel, PUT /api/kurikulum/rombel/{id}/pengampu */
Alpine.data('rombelPage', ({ rombel, calon, daftarMapel, daftarKelas, bukaModal, pilihGuru }) => gabung(pemilihGuru(calon), {
    rombel,
    daftarMapel,
    daftarKelas,
    semester: 'Ganjil 2026/2027',
    tingkat: 'Kelas X',
    mapel: 'Semua',
    jenis: 'semua',
    cari: '',
    page: 1,
    perPage: 8,
    form: {},
    error: {},
    kelasCari: '',
    inisial,
    warnaAvatar,
    namaPendek,

    init() {
        this.resetForm();
        ['tingkat', 'mapel', 'jenis', 'cari', 'semester'].forEach((k) => this.$watch(k, () => (this.page = 1)));

        if (bukaModal) {
            // Contoh isian sesuai desain 4.07.
            this.form = { mapel: 'Bahasa Mandarin', nama: 'Mandarin 3', jenis: 'campuran', kelas: ['X-E', 'X-F'], guru: 'Lin Mei, S.Pd.' };
        }
        if (pilihGuru) {
            this.$nextTick(() => {
                const tombol = document.querySelector(`[data-pilih-guru="${pilihGuru}"]`);
                const r = this.rombel.find((x) => x.id_rombel === pilihGuru);
                if (tombol && r) this.bukaPicker(r.id_rombel, r.mapel, tombol);
            });
        }
    },

    get daftarMapelFilter() {
        return ['Semua', ...new Set(this.rombel.map((r) => r.mapel))];
    },

    get ringkasan() {
        return {
            total: this.rombel.length,
            adaPengampu: this.rombel.filter((r) => r.guru).length,
            tanpaPengampu: this.rombel.filter((r) => !r.guru).length,
            campuran: this.rombel.filter((r) => r.jenis === 'campuran').length,
        };
    },

    get tersaring() {
        const q = this.cari.trim().toLowerCase();
        return this.rombel.filter((r) => `Kelas ${r.tingkat}` === this.tingkat
            && (this.mapel === 'Semua' || r.mapel === this.mapel)
            && (this.jenis === 'semua' || r.jenis === this.jenis)
            && (!q || r.nama.toLowerCase().includes(q) || (r.guru ?? '').toLowerCase().includes(q)));
    },

    get halaman() {
        return Math.max(1, Math.ceil(this.tersaring.length / this.perPage));
    },

    get barisHalaman() {
        return this.tersaring.slice((this.page - 1) * this.perPage, this.page * this.perPage);
    },

    kelasRingkas(kelas) {
        return kelas.length > 3 ? [kelas[0], '···', kelas[kelas.length - 1]] : kelas;
    },

    pilih(guru) {
        const r = this.rombel.find((x) => x.id_rombel === this.picker.id);
        if (!r || guru.penuh) return;
        r.guru = guru.nama;
        this.tutupPicker();
        this.$dispatch('toast', { message: `Pengampu ${r.nama} disimpan`, type: 'success' });
    },

    salinSemesterLalu() {
        this.$dispatch('toast', { message: 'Salin dari semester lalu belum tersedia di desain.', type: 'info' });
    },

    // ---- Modal Buat Rombel (4.07) ----
    resetForm() {
        this.form = { mapel: '', nama: '', jenis: 'reguler', kelas: [], guru: '' };
        this.error = {};
        this.kelasCari = '';
    },

    bukaBuat() {
        this.resetForm();
        this.$dispatch('open-modal', 'buat-rombel');
    },

    get saranKelas() {
        const q = this.kelasCari.trim().toLowerCase();
        return this.daftarKelas.filter((k) => !this.form.kelas.includes(k) && (!q || k.toLowerCase().includes(q))).slice(0, 8);
    },

    tambahKelas(k) {
        if (!k || this.form.kelas.includes(k)) return;
        if (this.form.jenis === 'reguler') this.form.kelas = [];
        this.form.kelas.push(k);
        this.kelasCari = '';
        delete this.error.kelas;
    },

    hapusKelas(k) {
        this.form.kelas = this.form.kelas.filter((x) => x !== k);
    },

    get catatanKelasAsal() {
        const k = this.form.kelas;
        if (k.length < 2) return k[0] ?? '';
        return `${k.slice(0, -1).join(', ')} atau ${k[k.length - 1]}`;
    },

    simpanRombel() {
        const f = this.form;
        this.error = {};
        if (!f.mapel) this.error.mapel = 'Pilih mata pelajaran';
        if (!f.nama.trim()) this.error.nama = 'Nama rombel wajib diisi';
        else if (this.rombel.some((r) => r.nama.toLowerCase() === f.nama.trim().toLowerCase())) this.error.nama = 'Nama rombel sudah dipakai';
        if (!f.kelas.length) this.error.kelas = 'Pilih minimal satu kelas asal';
        else if (f.jenis === 'campuran' && f.kelas.length < 2) this.error.kelas = 'Rombel campuran berisi minimal dua kelas asal';
        else if (f.jenis === 'reguler' && f.kelas.length > 1) this.error.kelas = 'Rombel reguler hanya untuk satu kelas';
        if (Object.keys(this.error).length) return;

        const tingkat = f.kelas[0].split('-')[0];
        this.rombel.unshift({
            id_rombel: f.nama.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-'),
            nama: f.nama.trim(),
            mapel: f.mapel,
            tingkat,
            jenis: f.jenis,
            kelas_asal: [...f.kelas],
            jumlah_peserta: 0,
            guru: f.guru || null,
        });
        this.tingkat = `Kelas ${tingkat}`;
        this.page = 1;
        this.$dispatch('close-modal', 'buat-rombel');
        this.$dispatch('toast', { message: `Rombel ${f.nama.trim()} dibuat`, type: 'success' });
    },
}));
