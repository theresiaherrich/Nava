import Alpine from 'alpinejs';
import { api, download, toast } from './siakad-api';

const read = async path => (await api(path)).data;
const query = params => '?' + new URLSearchParams(params);
const localDate = value => value ? new Date(value).toLocaleString('sv-SE', { timeZone: 'Asia/Jakarta' }).slice(0, 16).replace(' ', 'T') : '';

// Nilai <input type="datetime-local">: tahun tepat 4 digit, 2000–2099 (sama dengan validasi server).
const FORMAT_WAKTU = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})$/;
const cekWaktu = value => {
    if (!value) return 'Wajib diisi lengkap (tanggal, jam, dan tahun 4 digit).';
    const cocok = FORMAT_WAKTU.exec(value);
    if (!cocok) return 'Tahun harus 4 digit.';
    const tahun = Number(cocok[1]);
    if (tahun < 2000 || tahun > 2099) return 'Tahun harus antara 2000 dan 2099.';
    return '';
};

function page(data) {
    return Object.defineProperties({
        error: '',
        busy: false,
        async run(callback) {
            this.error = '';
            this.busy = true;
            try {
                await callback();
            } catch (error) {
                this.error = error.message;
            } finally {
                this.busy = false;
            }
        },
    }, Object.getOwnPropertyDescriptors(data));
}

Alpine.data('dashboardAkademik', endpoint => page({
    data: {},
    init() { if (endpoint) this.run(async () => { this.data = await read(endpoint); }); },
    get counts() { return Object.entries(this.data).filter(([, value]) => typeof value === 'number'); },
}));

Alpine.data('penilaianAkademik', mode => page({
    mode,
    periods: [],
    periodId: '',
    rows: [],
    detail: null,
    note: '',
    openAt: '',
    closeAt: '',
    levels: [],
    touched: { openAt: false, closeAt: false },
    tandai(field) { this.touched[field] = true; },
    /** Pesan error per field jadwal; string kosong = valid. Field kosong baru ditandai setelah disentuh. */
    get errors() {
        const errors = { openAt: cekWaktu(this.openAt), closeAt: cekWaktu(this.closeAt) };
        if (!errors.openAt && !errors.closeAt && this.closeAt < this.openAt) {
            errors.closeAt = 'Waktu selesai tidak boleh sebelum waktu mulai.';
        }
        for (const field of ['openAt', 'closeAt']) {
            if (!this[field] && !this.touched[field]) errors[field] = '';
        }
        return errors;
    },
    get jadwalValid() {
        return cekWaktu(this.openAt) === '' && cekWaktu(this.closeAt) === '' && this.closeAt >= this.openAt;
    },
    /** Jadwal yang tersimpan di server, untuk mendeteksi perubahan input. */
    saved: { openAt: '', closeAt: '', levels: '' },
    get jadwalBerubah() {
        return this.openAt !== this.saved.openAt || this.closeAt !== this.saved.closeAt
            || [...this.levels].sort().join() !== this.saved.levels;
    },
    /** Periode sudah ditutup/dikunci: jadwal tidak bisa diubah lagi dari halaman ini. */
    get periodeSelesai() { return ['ditutup', 'dikunci'].includes(this.period?.status_efektif); },
    get sudahDijadwalkan() { return !!this.period && this.period.status_efektif !== 'belum_diatur'; },
    get tampilSimpan() { return !this.periodeSelesai && (!this.sudahDijadwalkan || this.jadwalBerubah); },
    get tampilTutup() { return !this.periodeSelesai && this.sudahDijadwalkan && !this.jadwalBerubah; },
    async tutupPeriode() {
        const ok = await this.$store.confirm.ask({
            title: `Tutup periode ${this.period?.jenis ?? ''}?`,
            message: 'Guru tidak dapat lagi mengisi atau mengubah nilai pada periode ini. Jadwal tidak dapat diubah setelah periode ditutup.',
            confirmLabel: 'Tutup periode',
            tone: 'warning',
        });
        if (ok) this.mutate(this.path + '/tutup');
    },
    simpanJadwal() {
        this.touched = { openAt: true, closeAt: true };
        if (!this.jadwalValid) return;
        this.mutate(this.path, 'PUT', { tgl_buka: this.openAt + '+07:00', tgl_tutup: this.closeAt + '+07:00', tingkat: this.levels });
    },
    init() {
        this.run(async () => {
            this.periods = await read(this.mode === 'periode' ? '/kurikulum/periode' : '/wali/periode');
            this.periodId = this.periods[0]?.id_periode ?? '';
            await this.load();
        });
    },
    get period() { return this.periods.find(p => String(p.id_periode) === String(this.periodId)); },
    get path() { return '/kurikulum/periode/' + this.periodId; },
    async load() {
        this.rows = [];
        this.detail = null;
        this.note = '';
        if (!this.period) return;
        if (this.mode === 'periode') {
            const data = await read(this.path + '/ringkasan');
            this.periods = this.periods.map(p => p.id_periode === data.periode.id_periode ? data.periode : p);
            this.rows = data.kelas;
            this.openAt = localDate(data.periode.tgl_buka);
            this.closeAt = localDate(data.periode.tgl_tutup);
            this.levels = [...data.periode.tingkat];
            this.touched = { openAt: false, closeAt: false };
            this.saved = { openAt: this.openAt, closeAt: this.closeAt, levels: [...this.levels].sort().join() };
        } else {
            this.rows = await read('/wali/paket' + query({ id_periode: this.periodId }));
        }
    },
    openDetail(id) {
        this.run(async () => { this.detail = await read('/wali/paket/' + id); this.note = ''; });
    },
    mutate(path, method = 'POST', body = {}) {
        this.run(async () => {
            const result = await api(path, method, body);
            toast(result.message);
            await this.load();
        });
    },
}));

Alpine.data('raporAkademik', (prefix, dkn) => page({
    prefix, dkn,
    periods: [],
    classes: [],
    periodId: '',
    classId: '',
    data: null,
    history: [],
    get base() { return '/' + this.prefix + (this.dkn ? '/dkn' : '/rapor'); },
    get ids() { return { id_periode: this.periodId, id_kelas: this.classId }; },
    get params() { return query(this.ids); },
    init() {
        this.run(async () => {
            this.periods = await read('/' + prefix + '/periode');
            this.periodId = this.periods[0]?.id_periode ?? '';
            this.classes = prefix === 'wali' ? (await read('/wali/dashboard')).kelas : await read('/master/kelas?per_page=100');
            this.classId = this.classes[0]?.id_kelas ?? '';
            await this.load();
        });
    },
    async load() {
        this.data = null;
        this.history = [];
        if (!this.periodId || !this.classId) return;
        this.data = await read(this.base + this.params);
        if (!this.dkn) {
            this.history = await read(this.base + '/riwayat-cetak' + query({ id_kelas: this.classId }));
        }
    },
    renderStudent(id) {
        this.run(async () => {
            const result = await api(this.base + '/cetak', 'POST', { id_periode: this.periodId, id_riwayat_kelas: id });
            toast(result.message);
            await this.load();
        });
    },
    renderClass(format) {
        this.run(async () => {
            await download(this.base + '/cetak-kelas', { ...this.ids, format }, 'rapor-kelas.' + format);
            await this.load();
        });
    },
    publish() {
        this.run(async () => {
            const result = await api(this.base + '/terbitkan', 'POST', this.ids);
            toast(result.message);
            await this.load();
        });
    },
}));

Alpine.data('portalAkademik', (prefix, mode, rombel) => page({
    prefix, mode,
    students: [],
    student: new URLSearchParams(window.location.search).get('id_siswa') ?? '',
    data: null,
    get params() { return this.student ? query({ id_siswa: this.student }) : ''; },
    init() {
        this.run(async () => {
            if (prefix === 'orang-tua') {
                this.students = await read('/portal/anak');
                if (!this.student) this.student = this.students[0]?.id_siswa ?? '';
            }
            await this.load();
        });
    },
    async load() {
        this.data = null;
        const path = mode === 'detail' ? '/nilai/' + rombel : '/' + mode;
        this.data = await read('/portal' + path + this.params);
    },
}));

Alpine.data('masterAkademik', tabs => page({
    tabs,
    index: 0,
    data: [],
    meta: null,
    indexPage: 1,
    search: '',
    get tab() { return this.tabs[this.index]; },
    init() { this.run(() => this.load()); },
    async load() {
        const result = await api(this.tab.path + query({ page: this.indexPage, q: this.search }));
        this.data = result.data;
        this.meta = result.meta;
    },
    choose(index) {
        this.index = index;
        this.indexPage = 1;
        this.search = '';
        this.run(() => this.load());
    },
    field(row, keys) {
        const value = keys.split('.').reduce((v, key) => v?.[key], row);
        if (typeof value === 'boolean') return value ? 'Aktif' : 'Nonaktif';
        if (Array.isArray(value)) return value.map(x => x.nama).join(', ');
        return value ?? '–';
    },
}));

Alpine.data('ekskulAkademik', () => page({
    options: [],
    participants: [],
    id: '',
    init() { this.run(async () => {
        this.options = await read('/kesiswaan/ekskul');
        this.id = this.options[0]?.id_ekskul ?? '';
        await this.load();
    }); },
    async load() {
        this.participants = this.id ? await read('/kesiswaan/ekskul/' + this.id + '/peserta') : [];
    },
    save() { this.run(async () => {
        const items = this.participants.map(({ id_peserta_ekskul, predikat, catatan }) => ({ id_peserta_ekskul, predikat: predikat || null, catatan }));
        const result = await api('/kesiswaan/ekskul/' + this.id + '/predikat', 'PUT', { items });
        toast(result.message);
        await this.load();
    }); },
}));
