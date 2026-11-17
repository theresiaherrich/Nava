import Alpine from 'alpinejs';

const TOKEN = ['[Nama Siswa]', '[TP Tertinggi]', '[TP di Bawah KKTP]', '[Mapel]'];
const escapeHtml = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
const kecilAwal = (s) => s.charAt(0).toLowerCase() + s.slice(1);
const jam = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');

/**
 * KKTP & Deskripsi Capaian (Kurikulum 5.03).
 * TODO API: PUT /api/kurikulum/kktp { mapel, rombel, interval[], tp[{kode,kktp}], templat{} }
 */
Alpine.data('kktpPage', (data) => ({
    ...data,
    tahun: data.tahun_ajaran[0],
    mapelAktif: data.mapel[0],
    rombelAktif: 'X-B',
    interval: [...data.interval],
    editInterval: null,
    contohKe: 0,
    templatFokus: 'terbaik',
    kotor: false,
    tokenList: TOKEN,

    init() {
        this.$watch('tp', () => (this.kotor = true), { deep: true });
        this.$watch('templat', () => (this.kotor = true), { deep: true });
        this.$watch('interval', () => (this.kotor = true), { deep: true });
    },

    get kktpValid() {
        return this.tp.every((t) => Number.isInteger(+t.kktp) && +t.kktp >= 0 && +t.kktp <= 100 && String(t.kktp).trim() !== '');
    },

    get intervalValid() {
        const [a, b, c] = this.interval.map(Number);
        return a > 0 && a < b && b < c && c <= 100;
    },

    get labelInterval() {
        const [a, b, c] = this.interval.map(Number);
        return [`< ${a}`, `${a}–${b - 1}`, `${b}–${c - 1}`, `≥ ${c}`];
    },

    get contoh() {
        return this.contoh_list[this.contohKe];
    },

    get contoh_list() {
        return data.contoh;
    },

    get tertinggi() {
        const n = this.contoh.nilai;
        return n.indexOf(Math.max(...n));
    },

    /** TP dengan nilai terendah, hanya bila di bawah KKTP-nya. */
    get dibawah() {
        const n = this.contoh.nilai;
        let idx = -1;
        n.forEach((v, i) => {
            if (v < +this.tp[i].kktp && (idx === -1 || v < n[idx])) idx = i;
        });
        return idx;
    },

    isiTemplat(teks) {
        const ganti = {
            '[Nama Siswa]': escapeHtml(this.contoh.nama),
            '[Mapel]': escapeHtml(this.mapelAktif),
            '[TP Tertinggi]': `<strong class="text-success">${escapeHtml(kecilAwal(this.tp[this.tertinggi].deskripsi))}</strong>`,
            '[TP di Bawah KKTP]': this.dibawah === -1 ? '' : `<strong class="text-danger">${escapeHtml(kecilAwal(this.tp[this.dibawah].deskripsi))}</strong>`,
        };
        return escapeHtml(teks).replace(/\[(Nama Siswa|Mapel|TP Tertinggi|TP di Bawah KKTP)\]/g, (m) => ganti[m]);
    },

    get pratinjau() {
        const kalimat = [this.isiTemplat(this.templat.terbaik)];
        if (this.dibawah !== -1) kalimat.push(this.isiTemplat(this.templat.penguatan));
        return kalimat.join(' ');
    },

    terapkanSemua() {
        this.tp.forEach((t) => (t.kktp = 75));
        this.$dispatch('toast', { message: 'KKTP 75 diterapkan ke semua TP', type: 'info' });
    },

    sisipkan(token) {
        const el = this.$refs[`templat_${this.templatFokus}`];
        const awal = el.selectionStart ?? el.value.length;
        const akhir = el.selectionEnd ?? el.value.length;
        this.templat[this.templatFokus] = el.value.slice(0, awal) + token + el.value.slice(akhir);
        this.$nextTick(() => {
            el.focus();
            el.setSelectionRange(awal + token.length, awal + token.length);
        });
    },

    gantiContoh() {
        this.contohKe = (this.contohKe + 1) % this.contoh_list.length;
    },

    simpan() {
        if (!this.kktpValid) {
            this.$dispatch('toast', { message: 'KKTP harus bilangan bulat 0–100', type: 'danger' });
            return;
        }
        if (!this.intervalValid) {
            this.$dispatch('toast', { message: 'Batas interval harus naik berurutan (mis. 60 < 75 < 90)', type: 'danger' });
            return;
        }
        this.tersimpan = jam();
        this.$nextTick(() => (this.kotor = false));
        this.$dispatch('toast', { message: `KKTP ${this.mapelAktif} ${this.rombelAktif} disimpan`, type: 'success' });
    },
}));
