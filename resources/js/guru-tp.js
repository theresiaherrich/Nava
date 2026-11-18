import Alpine from 'alpinejs';
import { api, toast } from './siakad-api';

Alpine.data('tpPage', ({ rombel, aktif, maks, semester, tampil, bukaRombel }) => ({
    rombel, maks, semester, aktifId: aktif,
    tampil: tampil === 'sts' ? 'sts' : 'semua',
    pilihRombel: bukaRombel,
    cariRombel: '',
    form: { id: null, deskripsi: '', hanya_sts: false },
    error: {},
    sibuk: false,
    seret: { dari: null, ke: null },
    salin: { tujuan: [], mode: 'tambah' },
    tidakDihapus: null,
    maksDeskripsi: 500,
    get r() { return this.rombel.find(item => item.id === aktif); },
    get tp() { return this.r.tp; },
    get jumlahSts() { return this.tp.filter(t => t.hanya_sts).length; },
    get tpSas() { return this.tp.at(-1); },
    get tampilTp() { return this.tampil === 'sts' ? this.tp.filter(t => t.hanya_sts) : this.tp; },
    get penuh() { return this.tp.length >= this.maks; },
    isSts(i) { return this.tp[i]?.hanya_sts; },
    terkunci() { return this.r.terkunci || this.sibuk; },
    kode(i) { return 'TP' + (i + 1); },
    get rombelTersaring() {
        const term = this.cariRombel.trim().toLowerCase();
        return this.rombel.filter(item => item.kelas.toLowerCase().includes(term));
    },
    gantiRombel(id) { window.location.assign('?rombel=' + id); },
    bukaTambah() {
        if (this.penuh || this.terkunci()) return;
        this.form = { id: null, deskripsi: '', hanya_sts: false };
        this.error = {};
        this.$dispatch('open-modal', 'tp');
    },
    bukaUbah(t) {
        this.form = { id: t.id, deskripsi: t.deskripsi, hanya_sts: t.hanya_sts };
        this.error = {};
        this.$dispatch('open-modal', 'tp');
    },
    get kodeForm() {
        return this.kode(this.form.id ? this.tp.findIndex(t => t.id === this.form.id) : this.tp.length);
    },
    async perbarui(ids = [aktif]) {
        await Promise.all(ids.map(async id => {
            const { data } = await api('/guru/rombel/' + id + '/tp');
            const item = this.rombel.find(r => r.id === id);
            item.tp = data.tp.map(t => ({ ...t, id: t.id_tp, sas: t.tp_sas }));
            item.jumlah_tp = item.tp.length;
            item.terkunci = data.terkunci;
        }));
    },
    async mutasi(path, method, body, ids = [aktif]) {
        if (this.sibuk) return false;
        this.sibuk = true;
        try {
            const result = await api(path, method, body);
            await this.perbarui(ids);
            toast(result.message);
            return true;
        } catch (error) {
            toast(error.message, 'danger');
            if (path.endsWith('/tp') || path.includes('/guru/tp/')) this.error.deskripsi = error.message;
            return false;
        } finally {
            this.sibuk = false;
        }
    },
    async simpan() {
        const { id, deskripsi, hanya_sts } = this.form;
        const route = id ? '/guru/tp/' + id : '/guru/rombel/' + aktif + '/tp';
        if (await this.mutasi(route, id ? 'PUT' : 'POST', { deskripsi, hanya_sts })) {
            this.$dispatch('close-modal', 'tp');
        }
    },
    async hapus(t) {
        if (t.jumlah_nilai > 0) {
            this.tidakDihapus = t;
            this.$dispatch('open-modal', 'tidak-dihapus');
            return;
        }
        const confirmed = await this.$store.confirm.ask({
            title: 'Hapus ' + this.kode(this.tp.indexOf(t)) + '?',
            message: t.deskripsi,
            confirmLabel: 'Hapus TP',
            tone: 'danger',
        });
        if (confirmed) await this.mutasi('/guru/tp/' + t.id, 'DELETE');
    },
    mulaiSeret(i, event) {
        if (this.terkunci()) return event.preventDefault();
        this.seret = { dari: i, ke: i };
        event.dataTransfer.setData('text/plain', String(i));
    },
    lewat(i) { if (this.seret.dari !== null) this.seret.ke = i; },
    lepas() {
        const { dari, ke } = this.seret;
        this.seret = { dari: null, ke: null };
        if (dari !== null && dari !== ke) this.pindah(dari, ke);
    },
    geserKeyboard(i, arah) {
        if (this.tampil === 'semua' && !this.terkunci() && this.tp[i + arah]) this.pindah(i, i + arah);
    },
    async pindah(dari, ke) {
        const urutan = this.tp.map(t => t.id);
        const [id] = urutan.splice(dari, 1);
        urutan.splice(ke, 0, id);
        if (await this.mutasi('/guru/rombel/' + aktif + '/tp-urutan', 'PUT', { id_tp: urutan })) {
            this.$nextTick(() => document.querySelector('[data-geser="' + ke + '"]')?.focus());
        }
    },
    get tujuanSalin() { return this.rombel.filter(x => x.id !== aktif && x.id_mapel === this.r.id_mapel); },
    bukaSalin() {
        this.salin.tujuan = [];
        this.$dispatch('open-modal', 'salin');
    },
    melebihi(x) { return x.jumlah_tp + this.tp.length > this.maks; },
    toggleSalin(x) {
        this.salin.tujuan = this.salin.tujuan.includes(x.id)
            ? this.salin.tujuan.filter(id => id !== x.id)
            : [...this.salin.tujuan, x.id];
    },
    get salinValid() {
        return !this.sibuk && this.salin.tujuan.length && this.tujuanSalin.filter(x => this.salin.tujuan.includes(x.id))
            .every(x => !x.terkunci && !this.melebihi(x));
    },
    async salinTp(sumber, tujuan, dialog) {
        if (await this.mutasi('/guru/rombel/' + sumber.id + '/tp-salin', 'POST', {
            id_tp: sumber.tp.map(t => t.id),
            id_rombel_tujuan: tujuan,
        }, tujuan)) this.$dispatch('close-modal', dialog);
    },
    jalankanSalin() { if (this.salinValid) this.salinTp(this.r, this.salin.tujuan, 'salin'); },
    salinDari(sumber) { this.salinTp(sumber, [aktif], 'salin-dari'); },
}));
