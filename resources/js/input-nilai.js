/*
 * resources/js/input-nilai.js
 * Interaksi halaman Input Nilai (frontend saja, simulasi).
 *
 * Nilai dan status hanya ada di memori halaman dan hilang saat halaman dimuat ulang.
 * Tidak ada localStorage dan tidak ada request ke server.
 *
 * Uji gagal simpan: buka halaman dengan ?simulasi=gagal-simpan
 * (autosave pertama gagal, lalu tombol "Coba lagi" berhasil).
 */

export const JUMLAH_TP = 6;
export const NILAI_MIN = 0;
export const NILAI_MAX = 100;

const PRATINJAU_MAKS_BARIS = 8;
const DAFTAR_MASALAH_MAKS = 6;
const LABEL_TP = ['TP1', 'TP2', 'TP3', 'TP4', 'TP5', 'TP6 · SAS'];
const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

const LABEL_STATUS = {
    'sedang-diisi': 'Sedang diisi',
    'perlu-revisi': 'Perlu revisi',
    'terkirim': 'Terkirim',
    'final': 'Final',
    'periode-tutup': 'Ditutup',
    'disetujui': 'Disetujui',
    'belum-mulai': 'Belum mulai',
    'belum-ada-tp': 'Belum ada TP',
};

const BADGE_STATUS = {
    'perlu-revisi': { teks: 'Perlu revisi', kelas: 'iv-badge-revisi' },
    'terkirim': { teks: 'Terkirim · Menunggu verifikasi', kelas: 'iv-badge-terkirim' },
    'final': { teks: 'Final', kelas: 'iv-badge-final' },
};

const VARIAN_BANNER = {
    'perlu-revisi': 'iv-banner-revisi',
    'terkirim': 'iv-banner-terkirim',
    'final': 'iv-banner-final',
    'periode-tutup': 'iv-banner-tutup',
};

const STATUS_TERKUNCI = ['terkirim', 'final', 'periode-tutup', 'disetujui'];
const TAMPIL_LENGKAP = ['sedang-diisi', 'belum-mulai', 'periode-tutup'];
const TAMPIL_SIMPAN = ['sedang-diisi', 'perlu-revisi'];

export function isTerkunci(status) {
    return STATUS_TERKUNCI.includes(status);
}

/**
 * Mengubah teks input menjadi { status, nilai }.
 * status: 'kosong' | 'valid' | 'invalid'
 */
export function parseNilai(raw) {
    const teks = String(raw ?? '').trim().replace(',', '.');

    if (teks === '') {
        return { status: 'kosong', nilai: null };
    }

    const angka = Number(teks);

    if (!Number.isFinite(angka) || angka < NILAI_MIN || angka > NILAI_MAX) {
        return { status: 'invalid', nilai: null };
    }

    return { status: 'valid', nilai: angka };
}

/**
 * Nilai rapor = rata-rata TP1 sampai TP6, dibulatkan.
 * SAS adalah TP6 dan hanya dihitung satu kali.
 * Mengembalikan null jika ada nilai yang kosong atau tidak valid.
 */
export function hitungRapor(nilaiTp) {
    if (nilaiTp.length !== JUMLAH_TP || nilaiTp.some((n) => n === null)) {
        return null;
    }

    const total = nilaiTp.reduce((a, b) => a + b, 0);
    return Math.round(total / JUMLAH_TP);
}

/** Jumlah siswa yang seluruh nilai TP-nya lengkap dan valid. */
export function hitungKelengkapan(daftarNilai) {
    const total = daftarNilai.length;
    const lengkap = daftarNilai.filter((nilai) => hitungRapor(nilai) !== null).length;

    return { lengkap, total };
}

/** Mengubah teks clipboard menjadi grid [baris][kolom]. */
export function bacaClipboard(teks) {
    const baris = teks.replace(/\r/g, '').split('\n');

    while (baris.length > 0 && baris[baris.length - 1].trim() === '') {
        baris.pop();
    }

    return baris.map((line) => line.split('\t').map((sel) => sel.trim()));
}

/** Format waktu seperti "12 Okt 2026, 10.15". */
export function formatWaktu(d = new Date()) {
    const jam = String(d.getHours()).padStart(2, '0');
    const menit = String(d.getMinutes()).padStart(2, '0');
    return `${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}, ${jam}.${menit}`;
}

function initInputNilai() {
    const root = document.querySelector('[data-iv-page]');

    // Mencegah inisialisasi ganda.
    if (!root || root.dataset.ivReady === '1') {
        return;
    }
    root.dataset.ivReady = '1';

    const $ = (sel) => root.querySelector(sel);
    const $$ = (sel) => Array.from(root.querySelectorAll(sel));
    const d = root.dataset;

    const judul = $('#iv-title');
    const pillLengkap = $('#iv-completeness');
    const statusBadge = $('#iv-status-badge');
    const elSave = $('#iv-save');
    const saveIcon = $('#iv-save-icon');
    const saveText = $('#iv-save-text');
    const btnRekap = $('#iv-lihat-rekap');
    const btnKirim = $('#iv-submit');
    const labelKirim = $('#iv-submit-label');
    const autosaveBox = $('#iv-autosave');
    const autosaveIcon = $('#iv-autosave-icon');
    const autosaveText = $('#iv-autosave-text');
    const feedback = $('#iv-feedback');
    const pillBacaSaja = $('#iv-readonly-pill');
    const statusBanner = $('#iv-status-banner');
    const statusJudul = $('#iv-status-judul');
    const statusDesc = $('#iv-status-desc');
    const statusCatatan = $('#iv-status-catatan');
    const sasBanner = $('#iv-sas-banner');
    const toast = $('#iv-toast');
    const toastText = $('#iv-toast-text');
    const toastRetry = $('#iv-toast-retry');
    const tabel = $('#iv-table');

    const dropdown = $('[data-iv-dropdown]');
    const btnRombel = $('#iv-rombel-btn');
    const nilaiRombel = $('#iv-rombel-value');
    const menuRombel = $('#iv-rombel-menu');
    const options = $$('.iv-option[data-id]');
    const segBtns = $$('.iv-seg-btn');

    const semuaInput = $$('.iv-input');
    const barisEls = $$('tbody tr[data-row]');

    // Tombol dan pop-up tempel dari Excel
    const btnBukaExcel = $('#iv-open-excel');
    const modalPaste = $('#iv-paste-modal');
    const modalSub = $('#iv-modal-sub');
    const modalClose = $('#iv-modal-close');
    const viewInput = $('#iv-view-input');
    const viewPreview = $('#iv-view-preview');
    const excelText = $('#iv-excel-text');
    const modalError = $('#iv-modal-error');
    const btnInputNext = $('#iv-input-next');
    const btnInputCancel = $('#iv-input-cancel');
    const chipValid = $('#iv-chip-valid');
    const chipInvalid = $('#iv-chip-invalid');
    const chipKosong = $('#iv-chip-kosong');
    const modalBody = $('#iv-paste-body');
    const modalCount = $('#iv-modal-count');
    const btnTerapkan = $('#iv-modal-apply');
    const btnBatal = $('#iv-modal-cancel');
    const btnTempelUlang = $('#iv-modal-retry');

    // Pop-up kirim nilai
    const modalKirim = $('#iv-kirim-modal');
    const kirimTitle = $('#iv-kirim-title');
    const kirimDesc = $('#iv-kirim-desc');
    const kirimRingkas = $('#iv-kirim-ringkas');
    const btnKirimClose = $('#iv-kirim-close');
    const btnKirimCancel = $('#iv-kirim-cancel');
    const btnKirimConfirm = $('#iv-kirim-confirm');

    // Pop-up nilai belum lengkap
    const modalBelum = $('#iv-belum-modal');
    const belumText = $('#iv-belum-text');
    const belumList = $('#iv-belum-list');
    const belumMore = $('#iv-belum-more');
    const btnBelumClose = $('#iv-belum-close');
    const btnBelumKembali = $('#iv-belum-kembali');

    // Status per rombel dan simulasi penyimpanan (hanya di memori)
    const statusRombel = new Map();
    const waktuKirim = new Map();
    const pendingKeys = new Set();
    const modeGagal = new URLSearchParams(window.location.search).get('simulasi') === 'gagal-simpan';
    let sisaGagal = modeGagal ? 1 : 0;
    let simpanGagal = false;
    let teksAutosaveOk = 'Belum ada perubahan';
    let timerSimpan = null;

    let paket = null;          // data pratinjau yang sedang tampil
    let mulaiDari = null;      // sel awal tempat data akan ditempel
    let terakhirFokus = null;  // sel TP terakhir yang difokuskan
    let fokusKembali = null;   // elemen yang difokuskan saat pop-up ditutup

    options.forEach((o) => {
        statusRombel.set(o.dataset.id, o.dataset.status || 'sedang-diisi');
        if (o.dataset.waktu) {
            waktuKirim.set(o.dataset.id, o.dataset.waktu);
        }
    });

    // ---------- Helper umum ----------

    function tampilkan(pesan, jenis = 'info') {
        feedback.textContent = pesan;
        feedback.className = `iv-feedback iv-feedback-${jenis}`;
    }

    const keyOf = (el) => `${el.dataset.row}-${el.dataset.col}`;

    const inputBaris = (r) => semuaInput.filter((el) => Number(el.dataset.row) === r);

    const cariInput = (r, c) =>
        semuaInput.find((el) => Number(el.dataset.row) === r && Number(el.dataset.col) === c);

    function namaSiswa(r) {
        return barisEls[r]?.querySelector('.iv-name')?.textContent.trim() ?? '';
    }

    function rombelAktifEl() {
        return options.find((o) => o.classList.contains('is-active'));
    }

    function idRombelAktif() {
        return rombelAktifEl()?.dataset.id ?? '';
    }

    function statusAktif() {
        return statusRombel.get(idRombelAktif()) ?? 'sedang-diisi';
    }

    function terkunciAktif() {
        return isTerkunci(statusAktif());
    }

    function namaRombelAktif() {
        const aktif = rombelAktifEl();
        if (!aktif) return 'Rombel belum dipilih';
        return `${aktif.dataset.mapel} · ${aktif.dataset.kelas}`;
    }

    // ---------- Pop-up umum ----------

    function bukaModal(el, fokusAwal, kembali) {
        el.hidden = false;
        fokusKembali = kembali instanceof HTMLElement ? kembali : null;
        fokusAwal?.focus();
    }

    function tutupModal(el) {
        if (el.hidden) return;

        el.hidden = true;
        const tujuan = fokusKembali;
        fokusKembali = null;
        tujuan?.focus();
    }

    function modalTerbuka() {
        return [modalPaste, modalKirim, modalBelum].find((m) => !m.hidden) ?? null;
    }

    // ---------- Status dan tampilan ----------

    function segarBadgeOption(o) {
        const badge = o.querySelector('.iv-status');
        if (!badge) return;

        const st = statusRombel.get(o.dataset.id) ?? 'sedang-diisi';
        badge.className = `iv-status iv-status-${st}`;
        badge.textContent = LABEL_STATUS[st] ?? st;
    }

    function setSimpan(keadaan) {
        elSave.classList.toggle('is-gagal', keadaan === 'gagal');

        if (keadaan === 'gagal') {
            saveIcon.textContent = '⚠';
            saveText.textContent = 'Gagal menyimpan - Coba lagi';
        } else if (keadaan === 'menyimpan') {
            saveIcon.textContent = '•';
            saveText.textContent = 'Menyimpan…';
        } else {
            saveIcon.textContent = '✓';
            saveText.textContent = 'Tersimpan';
        }
    }

    function updateFooter() {
        const locked = terkunciAktif();

        autosaveBox.classList.toggle('is-kunci', locked);
        autosaveBox.classList.toggle('is-gagal', !locked && simpanGagal);

        if (locked) {
            autosaveIcon.textContent = '🔒';
            autosaveText.textContent = 'Nilai tidak dapat diubah';
            return;
        }

        if (simpanGagal) {
            autosaveIcon.textContent = '⚠';
            autosaveText.textContent = `${pendingKeys.size} perubahan belum tersimpan`;
            return;
        }

        autosaveIcon.textContent = '✓';
        autosaveText.textContent = teksAutosaveOk;
    }

    /** Tombol Kirim: disembunyikan saat terkunci, nonaktif saat ada perubahan belum tersimpan. */
    function updateKirim() {
        const st = statusAktif();

        btnKirim.hidden = isTerkunci(st);
        if (btnKirim.hidden) return;

        const tertahan = simpanGagal || pendingKeys.size > 0;
        btnKirim.disabled = tertahan;
        btnKirim.title = tertahan ? 'Simpan perubahan terlebih dahulu' : '';
        labelKirim.textContent = st === 'perlu-revisi' ? 'Kirim Ulang' : 'Kirim nilai';
    }

    function renderBanner(st, kelas) {
        const varian = VARIAN_BANNER[st];

        statusBanner.hidden = !varian;
        statusCatatan.hidden = true;

        if (!varian) return;

        statusBanner.className = `iv-status-banner ${varian}`;

        if (st === 'perlu-revisi') {
            statusJudul.textContent = `Dikembalikan untuk revisi — oleh ${d.revisiOleh} · ${d.revisiWaktu}`;
            statusDesc.textContent = '';

            if (d.revisiCatatan) {
                statusCatatan.textContent = `Catatan wali kelas: ${d.revisiCatatan}`;
                statusCatatan.hidden = false;
            }
        } else if (st === 'terkirim') {
            statusJudul.textContent = 'Nilai sudah dikirim';
            statusDesc.textContent =
                `${waktuKirim.get(idRombelAktif()) ?? d.kirimWaktu} · Menunggu verifikasi wali kelas ${kelas}. (simulasi)`;
        } else if (st === 'final') {
            statusJudul.textContent = 'Nilai terkunci';
            statusDesc.textContent =
                `Dikunci Waka Kurikulum pada ${d.finalWaktu}. Nilai sudah final untuk rapor. Perlu koreksi? Hubungi Waka Kurikulum.`;
        } else {
            statusJudul.textContent = `Periode ${d.tutupPeriode} ditutup`;
            statusDesc.textContent =
                `${d.tutupWaktu}. Nilai tidak dapat diubah. Hubungi Waka Kurikulum jika perlu perpanjangan.`;
        }

        statusDesc.hidden = !statusDesc.textContent;
    }

    /** Menandai sel yang diminta dicek wali kelas (hanya saat perlu revisi). */
    function renderRevisiCell(st) {
        root.querySelectorAll('td.is-revisi').forEach((td) => {
            td.classList.remove('is-revisi');
            delete td.dataset.tipRevisi;
        });

        if (st !== 'perlu-revisi' || !d.revisiRow) return;

        const td = cariInput(Number(d.revisiRow), Number(d.revisiCol))?.closest('td');
        if (!td) return;

        td.classList.add('is-revisi');
        td.dataset.tipRevisi = d.revisiTooltip || 'Perlu dicek';
    }

    /** Menyesuaikan seluruh tampilan dengan status rombel aktif. */
    function renderStatus() {
        const st = statusAktif();
        const locked = isTerkunci(st);
        const kelas = rombelAktifEl()?.dataset.kelas ?? '';

        tabel.classList.toggle('is-locked', locked);
        semuaInput.forEach((el) => {
            el.readOnly = locked;
            el.setAttribute('aria-readonly', String(locked));
        });

        btnBukaExcel.disabled = locked;
        pillBacaSaja.hidden = !locked;
        pillLengkap.hidden = !TAMPIL_LENGKAP.includes(st);
        elSave.hidden = !TAMPIL_SIMPAN.includes(st);
        btnRekap.hidden = st !== 'final';
        sasBanner.hidden = st !== 'sedang-diisi';

        const badge = BADGE_STATUS[st];
        statusBadge.hidden = !badge;
        if (badge) {
            statusBadge.textContent = badge.teks;
            statusBadge.className = `iv-status-badge ${badge.kelas}`;
        }

        renderBanner(st, kelas);
        renderRevisiCell(st);
        updateFooter();
        updateKirim();
        options.forEach(segarBadgeOption);
    }

    // ---------- Dropdown rombel ----------

    function bukaMenu(buka) {
        if (!menuRombel) return;
        menuRombel.hidden = !buka;
        btnRombel?.setAttribute('aria-expanded', String(buka));
    }

    function pilihRombel(el) {
        if (el.classList.contains('is-disabled')) return;

        options.forEach((o) => {
            const aktif = o === el;
            o.classList.toggle('is-active', aktif);
            o.setAttribute('aria-selected', String(aktif));
        });

        nilaiRombel.textContent = `${el.dataset.kelas} · ${el.dataset.mapel}`;
        judul.textContent = `Input Nilai — ${el.dataset.mapel} ${el.dataset.kelas}`;

        bukaMenu(false);
        renderStatus();

        tampilkan(
            terkunciAktif()
                ? 'Rombel ini hanya dapat dibaca.'
                : 'Rombel diganti. Data peserta didik akan dimuat dari database setelah backend tersedia.',
            'info'
        );
    }

    if (btnRombel && menuRombel) {
        btnRombel.addEventListener('click', () => bukaMenu(menuRombel.hidden));

        options.forEach((el) => {
            el.addEventListener('click', () => pilihRombel(el));
        });

        document.addEventListener('click', (e) => {
            if (dropdown && !dropdown.contains(e.target)) {
                bukaMenu(false);
            }
        });
    }

    // ---------- Pilihan periode (STS / Semester) ----------

    segBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            segBtns.forEach((b) => {
                const aktif = b === btn;
                b.classList.toggle('is-active', aktif);
                b.setAttribute('aria-pressed', String(aktif));
            });
        });
    });

    // ---------- Validasi, rapor, dan kelengkapan ----------

    function segarBaris(r) {
        const cells = inputBaris(r);
        const hasil = cells.map((el) => parseNilai(el.value));

        cells.forEach((el, i) => {
            const invalid = hasil[i].status === 'invalid';
            el.closest('td').classList.toggle('is-invalid', invalid);
            el.setAttribute('aria-invalid', String(invalid));
        });

        const rapor = hitungRapor(hasil.map((h) => h.nilai));
        const elRapor = root.querySelector(`[data-rapor="${r}"]`);

        if (elRapor) {
            elRapor.textContent = rapor === null ? '—' : String(rapor);
            elRapor.classList.toggle('is-kosong', rapor === null);
        }
    }

    function segarSemua() {
        barisEls.forEach((_, r) => segarBaris(r));

        const daftar = barisEls.map((_, r) => inputBaris(r).map((el) => parseNilai(el.value).nilai));
        const { lengkap, total } = hitungKelengkapan(daftar);

        pillLengkap.textContent = `${lengkap}/${total} lengkap`;
        pillLengkap.classList.toggle('is-ok', total > 0 && lengkap === total);
    }

    // ---------- Penyimpanan simulasi ----------

    /** Menandai sel yang perubahannya belum tersimpan. */
    function tandaiBelumTersimpan() {
        pendingKeys.forEach((k) => {
            const [r, c] = k.split('-').map(Number);
            const td = cariInput(r, c)?.closest('td');

            if (td) {
                td.classList.add('is-unsaved');
                td.dataset.tipUnsaved = 'Belum tersimpan';
            }
        });
    }

    function hapusTandaBelumTersimpan() {
        root.querySelectorAll('td.is-unsaved').forEach((td) => td.classList.remove('is-unsaved'));
    }

    /** Mencatat sel yang berubah, lalu memperbarui status kirim. */
    function tandaiPending(el) {
        pendingKeys.add(keyOf(el));

        if (simpanGagal) {
            tandaiBelumTersimpan();
        }
    }

    function catatPerubahan() {
        if (simpanGagal) {
            updateFooter();
            updateKirim();
            return;
        }

        setSimpan('menyimpan');
        clearTimeout(timerSimpan);
        timerSimpan = setTimeout(prosesSimpan, 500);
        updateKirim();
    }

    function prosesSimpan() {
        if (simpanGagal) return;

        if (sisaGagal > 0) {
            sisaGagal -= 1;
            gagalSimpan();
            return;
        }

        pendingKeys.clear();
        hapusTandaBelumTersimpan();

        const w = new Date();
        const jam = String(w.getHours()).padStart(2, '0');
        const menit = String(w.getMinutes()).padStart(2, '0');
        teksAutosaveOk = `Perubahan tersimpan otomatis pukul ${jam}.${menit}`;

        setSimpan('tersimpan');
        updateFooter();
        updateKirim();
    }

    function gagalSimpan() {
        simpanGagal = true;
        setSimpan('gagal');
        tandaiBelumTersimpan();

        toastText.textContent = `Koneksi terputus. ${pendingKeys.size} perubahan belum tersimpan.`;
        toast.hidden = false;

        updateFooter();
        updateKirim();
    }

    function cobaLagi() {
        simpanGagal = false;
        toast.hidden = true;
        setSimpan('menyimpan');
        prosesSimpan();
    }

    // ---------- Pop-up tempel dari Excel ----------

    function tampilkanView(view) {
        viewInput.hidden = view !== 'input';
        viewPreview.hidden = view !== 'preview';
    }

    function tutupPopup() {
        if (modalPaste.hidden) return;

        paket = null;
        modalError.textContent = '';
        tutupModal(modalPaste);
    }

    function buatSel(teks, kelas = '') {
        const td = document.createElement('td');
        if (kelas) td.className = kelas;
        td.textContent = teks;
        return td;
    }

    /** Menyusun data pratinjau dan menghitung jumlah valid, tidak valid, dan kosong. */
    function buatPaket(grid, r0, c0) {
        const peta = new Map();
        let valid = 0;
        let invalid = 0;
        let kosong = 0;
        let diLuar = 0;

        grid.forEach((baris, dr) => {
            const r = r0 + dr;

            if (r >= barisEls.length) {
                diLuar++;
                return;
            }

            baris.forEach((raw, dc) => {
                const c = c0 + dc;
                if (c >= JUMLAH_TP) return;

                const hasil = parseNilai(raw);
                peta.set(`${r}-${c}`, { raw, ...hasil });

                if (hasil.status === 'valid') valid++;
                else if (hasil.status === 'invalid') invalid++;
                else kosong++;
            });
        });

        const kolom = Math.max(...grid.map((b) => b.length));

        return { grid, r0, c0, peta, valid, invalid, kosong, diLuar, kolom };
    }

    function renderPratinjau() {
        const { grid, r0, peta, valid, invalid, kosong, diLuar, kolom } = paket;

        const aktif = rombelAktifEl();
        const mapel = aktif?.dataset.mapel ?? '';
        const kelas = aktif?.dataset.kelas ?? '';

        modalSub.textContent =
            `${mapel} · ${kelas} · ${grid.length} baris × ${kolom} kolom. Periksa sebelum diterapkan.`;

        chipValid.textContent = `${valid} nilai valid`;
        chipInvalid.textContent = `${invalid} tidak valid`;
        chipKosong.textContent = `${kosong} sel kosong`;

        btnTerapkan.textContent = `Terapkan ${valid} Nilai`;
        btnTerapkan.disabled = valid === 0;

        modalBody.replaceChildren();

        const tampil = Math.min(PRATINJAU_MAKS_BARIS, grid.length);

        for (let dr = 0; dr < tampil; dr++) {
            const r = r0 + dr;
            const adaSiswa = r < barisEls.length;
            const tr = document.createElement('tr');

            tr.appendChild(buatSel(adaSiswa ? String(r + 1) : '—', 'iv-muted'));
            tr.appendChild(buatSel(adaSiswa ? namaSiswa(r) : '(di luar tabel)', 'iv-left'));

            for (let c = 0; c < JUMLAH_TP; c++) {
                const cell = peta.get(`${r}-${c}`);

                if (!cell) {
                    tr.appendChild(buatSel(''));
                } else if (cell.status === 'valid') {
                    tr.appendChild(buatSel(String(cell.nilai)));
                } else if (cell.status === 'kosong') {
                    tr.appendChild(buatSel('—', 'is-kosong'));
                } else {
                    const td = buatSel('');
                    const span = document.createElement('span');
                    span.className = 'iv-prev-invalid';
                    span.textContent = cell.raw;
                    td.appendChild(span);
                    tr.appendChild(td);
                }
            }

            modalBody.appendChild(tr);
        }

        modalCount.textContent =
            `Menampilkan ${tampil} dari ${grid.length} baris.` +
            (diLuar > 0 ? ` ${diLuar} baris di luar tabel tidak ditampilkan.` : '');
    }

    /** Tombol "Tempel dari Excel": mulai dari sel TP terakhir yang difokuskan, atau TP1 siswa pertama. */
    function bukaDariTombol() {
        if (terkunciAktif()) return;

        const awal = terakhirFokus && root.contains(terakhirFokus) ? terakhirFokus : cariInput(0, 0);
        if (!awal) return;

        mulaiDari = awal;

        excelText.value = '';
        modalError.textContent = '';
        btnInputNext.disabled = true;

        const r = Number(awal.dataset.row);
        const c = Number(awal.dataset.col);
        modalSub.textContent = `Mulai dari ${namaSiswa(r)} · ${LABEL_TP[c]}`;

        tampilkanView('input');
        bukaModal(modalPaste, excelText, btnBukaExcel);
    }

    /** Dari langkah 1: baca teks, lalu tampilkan pratinjau. */
    function lanjutKePratinjau() {
        const grid = bacaClipboard(excelText.value);

        if (grid.length === 0) {
            modalError.textContent = 'Belum ada data. Salin dari Excel, lalu tempel di kotak ini.';
            return;
        }

        modalError.textContent = '';
        paket = buatPaket(grid, Number(mulaiDari.dataset.row), Number(mulaiDari.dataset.col));
        renderPratinjau();
        tampilkanView('preview');
        btnBatal.focus();
    }

    /** Ctrl+V langsung di sel dengan banyak data: lewati langkah 1 dan tampilkan pratinjau. */
    function bukaPratinjauDariSel(grid, sumber) {
        mulaiDari = sumber;

        paket = buatPaket(grid, Number(sumber.dataset.row), Number(sumber.dataset.col));
        renderPratinjau();
        tampilkanView('preview');
        bukaModal(modalPaste, btnBatal, sumber);
    }

    function terapkanPratinjau() {
        if (!paket) return;

        let jumlah = 0;

        paket.peta.forEach((cell, kunci) => {
            if (cell.status !== 'valid') return;

            const [r, c] = kunci.split('-').map(Number);
            const el = cariInput(r, c);

            if (el) {
                el.value = String(cell.nilai);
                tandaiPending(el);
                jumlah++;
            }
        });

        tutupPopup();
        segarSemua();
        catatPerubahan();
        tampilkan(`${jumlah} nilai diterapkan dari Excel. Belum dikirim ke server.`, 'ok');
    }

    btnBukaExcel.addEventListener('click', bukaDariTombol);

    excelText.addEventListener('input', () => {
        btnInputNext.disabled = excelText.value.trim() === '';
        modalError.textContent = '';
    });

    btnInputNext.addEventListener('click', lanjutKePratinjau);
    btnInputCancel.addEventListener('click', tutupPopup);

    btnTerapkan.addEventListener('click', terapkanPratinjau);
    btnBatal.addEventListener('click', tutupPopup);
    modalClose.addEventListener('click', tutupPopup);

    btnTempelUlang.addEventListener('click', () => {
        tampilkanView('input');
        excelText.focus();
    });

    // ---------- Pop-up kirim nilai ----------

    /** Menghitung ringkasan dan daftar nilai yang kosong atau tidak valid. */
    function ringkasKirim() {
        const total = barisEls.length;
        const daftar = barisEls.map((_, r) => inputBaris(r).map((el) => parseNilai(el.value)));
        const lengkap = daftar.filter((baris) => baris.every((h) => h.status === 'valid')).length;

        const masalah = [];
        daftar.forEach((baris, r) => {
            baris.forEach((h, c) => {
                if (h.status !== 'valid') {
                    masalah.push({ r, c, status: h.status, raw: cariInput(r, c)?.value ?? '' });
                }
            });
        });

        return { total, lengkap, masalah };
    }

    function bukaBelumLengkap(masalah) {
        const pertama = cariInput(masalah[0].r, masalah[0].c);

        belumText.textContent =
            `Masih ada ${masalah.length} nilai kosong atau tidak valid. Lengkapi sebelum mengirim ke wali kelas.`;

        belumList.replaceChildren();

        masalah.slice(0, DAFTAR_MASALAH_MAKS).forEach((m) => {
            const li = document.createElement('li');
            li.className = 'iv-issue';

            const dot = document.createElement('span');
            dot.className = 'iv-issue-dot';

            const tp = document.createElement('span');
            tp.className = 'iv-issue-tp';
            tp.textContent = `TP${m.c + 1}`;

            const nama = document.createElement('span');
            nama.className = 'iv-issue-nama';
            nama.textContent = namaSiswa(m.r);

            const ket = document.createElement('span');
            if (m.status === 'invalid') {
                ket.className = 'iv-issue-ket is-invalid';
                ket.textContent = `(tidak valid: ${m.raw})`;
            } else {
                ket.className = 'iv-issue-ket is-kosong';
                ket.textContent = '(kosong)';
            }

            li.append(dot, tp, nama, ket);
            belumList.appendChild(li);
        });

        const sisa = masalah.length - DAFTAR_MASALAH_MAKS;
        belumMore.textContent = sisa > 0 ? `dan ${sisa} nilai lainnya.` : '';

        bukaModal(modalBelum, btnBelumKembali, pertama);
    }

    function bukaKonfirmasiKirim(total, lengkap) {
        const ulang = statusAktif() === 'perlu-revisi';

        kirimTitle.textContent = ulang ? 'Kirim ulang nilai ke wali kelas?' : 'Kirim nilai ke wali kelas?';
        kirimDesc.textContent = ulang
            ? 'Nilai yang sudah diperbaiki akan dikirim ulang dan menunggu verifikasi wali kelas.'
            : 'Setelah dikirim, nilai tidak dapat diubah sampai wali kelas mengembalikannya untuk revisi.';
        kirimRingkas.textContent = `${namaRombelAktif()} · ${total} siswa · ${lengkap}/${total} lengkap · 6 TP`;
        btnKirimConfirm.textContent = ulang ? 'Kirim ulang' : 'Kirim nilai';

        bukaModal(modalKirim, btnKirimConfirm, btnKirim);
    }

    function onKirimKlik() {
        if (terkunciAktif()) return;

        if (simpanGagal || pendingKeys.size > 0) {
            tampilkan('Simpan perubahan terlebih dahulu sebelum mengirim.', 'error');
            return;
        }

        segarSemua();

        const { total, lengkap, masalah } = ringkasKirim();

        if (total === 0) {
            tampilkan('Belum ada peserta didik. Tidak ada nilai yang bisa dikirim.', 'error');
            return;
        }

        if (masalah.length > 0) {
            bukaBelumLengkap(masalah);
            return;
        }

        bukaKonfirmasiKirim(total, lengkap);
    }

    function onKirimKonfirmasi() {
        const id = idRombelAktif();

        statusRombel.set(id, 'terkirim');
        waktuKirim.set(id, formatWaktu());

        tutupModal(modalKirim);
        renderStatus();

        tampilkan('Simulasi: nilai ditandai terkirim dan menunggu verifikasi. Belum dikirim ke server.', 'ok');
    }

    btnKirim.addEventListener('click', onKirimKlik);
    btnKirimConfirm.addEventListener('click', onKirimKonfirmasi);
    btnKirimCancel.addEventListener('click', () => tutupModal(modalKirim));
    btnKirimClose.addEventListener('click', () => tutupModal(modalKirim));

    btnBelumClose.addEventListener('click', () => tutupModal(modalBelum));
    btnBelumKembali.addEventListener('click', () => tutupModal(modalBelum));

    // Tombol lain
    btnRekap.addEventListener('click', () => tampilkan('Rekap nilai belum tersedia (simulasi).', 'info'));
    toastRetry.addEventListener('click', cobaLagi);

    // Klik area gelap di luar dialog menutup pop-up.
    [modalPaste, modalKirim, modalBelum].forEach((m) => {
        m.addEventListener('click', (e) => {
            if (e.target !== m) return;
            if (m === modalPaste) {
                tutupPopup();
            } else {
                tutupModal(m);
            }
        });
    });

    // ---------- Input nilai ----------

    /** Tempel langsung di sel: satu nilai diisi, banyak nilai membuka pratinjau. */
    function tempelDiSel(e) {
        if (terkunciAktif()) {
            e.preventDefault();
            return;
        }

        const teks = (e.clipboardData || window.clipboardData).getData('text');
        if (!teks) return;

        e.preventDefault();

        const grid = bacaClipboard(teks);
        if (grid.length === 0) return;

        if (grid.length === 1 && grid[0].length === 1) {
            e.target.value = grid[0][0];
            tandaiPending(e.target);
            segarSemua();
            catatPerubahan();
            return;
        }

        bukaPratinjauDariSel(grid, e.target);
    }

    /** Enter berpindah ke baris berikutnya pada kolom yang sama. */
    function onKeydown(e) {
        if (e.key !== 'Enter') return;

        e.preventDefault();
        const r = Number(e.target.dataset.row);
        const c = Number(e.target.dataset.col);
        const berikutnya = cariInput(r + 1, c);

        if (berikutnya) {
            berikutnya.focus();
            berikutnya.select();
        }
    }

    semuaInput.forEach((el) => {
        el.addEventListener('focus', () => {
            terakhirFokus = el;
        });
        el.addEventListener('input', () => {
            if (terkunciAktif()) return;

            tandaiPending(el);
            segarSemua();
            catatPerubahan();
            tampilkan('');
        });
        el.addEventListener('paste', tempelDiSel);
        el.addEventListener('keydown', onKeydown);
    });

    // Satu listener Escape untuk semua pop-up dan dropdown rombel.
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;

        const terbuka = modalTerbuka();

        if (terbuka === modalPaste) {
            tutupPopup();
            return;
        }

        if (terbuka) {
            tutupModal(terbuka);
            return;
        }

        if (menuRombel && !menuRombel.hidden) {
            bukaMenu(false);
            btnRombel?.focus();
        }
    });

    // Sinkronisasi awal.
    renderStatus();
    segarSemua();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInputNilai);
} else {
    initInputNilai();
}