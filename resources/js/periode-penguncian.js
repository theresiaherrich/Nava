// Halaman Periode & Penguncian (frontend, data masih dummy)
// Mengatur: tab semester & tingkat, kartu STS/SAS, kunci nilai, buka koreksi,
// tambah periode, ubah jadwal, dan notifikasi (toast).

// Status per kelas (harus sama dengan $statusMap di index.blade.php)
const STATUS = {
    siap:     { label: 'Siap dikunci',         cls: 'pk-status-siap' },
    terkunci: { label: 'Terkunci',             cls: 'pk-status-kunci' },
    menunggu: { label: 'Belum lengkap',        cls: 'pk-status-menunggu' },
    koreksi:  { label: 'Dibuka untuk koreksi', cls: 'pk-status-koreksi' },
    belum:    { label: 'Belum dibuka',         cls: 'pk-status-belum' },
};

// Status periode (harus sama dengan $badgePeriode di index.blade.php)
const STATUS_PERIODE = {
    terjadwal: { label: 'Terjadwal', cls: 'pk-badge-muted' },
    dibuka:    { label: 'Dibuka',    cls: 'pk-badge-open' },
    ditutup:   { label: 'Ditutup',   cls: 'pk-badge-closed' },
};

const ICON_LOCK = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>';
const ICON_UNLOCK = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>';
const ICON_OK = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12.5 2.5 2.5L16 9.5"/></svg>';
const ICON_INFO = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>';

const page = document.querySelector('[data-pk-page]');
if (page) initPeriodePenguncian(page);

function initPeriodePenguncian(page) {
    // ---------- Data awal dari server ----------
    let data = { semesterTabs: [], periode: [], hariIni: null };
    try {
        data = { ...data, ...JSON.parse(page.querySelector('#pk-data')?.textContent || '{}') };
    } catch (err) {
        console.error('Data periode tidak valid', err);
    }

    const semesterLabels = data.semesterTabs;
    const daftarPeriode = Array.isArray(data.periode) ? data.periode : [];
    let nextId = daftarPeriode.reduce((max, p) => Math.max(max, Number(p.id) || 0), 0) + 1;

    // ---------- Util tanggal (format Y-m-d, tanpa jam supaya aman dari zona waktu) ----------
    const parseIso = (iso) => {
        const [y, m, d] = String(iso).split('-').map(Number);
        return new Date(y, m - 1, d);
    };
    const toIsoDate = (date) => {
        const lokal = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
        return lokal.toISOString().slice(0, 10);
    };
    const hariIni = data.hariIni ? parseIso(data.hariIni) : parseIso(toIsoDate(new Date()));
    const selisihHari = (dari, ke) => Math.round((ke - dari) / 86400000);
    const tambahHari = (date, n) => new Date(date.getFullYear(), date.getMonth(), date.getDate() + n);

    const formatTanggal = (date) =>
        date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const formatPanjang = (date) =>
        date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    const formatRentang = (buka, tutup) => {
        const awal = buka.getFullYear() === tutup.getFullYear()
            ? buka.toLocaleDateString('id-ID', { day: 'numeric', month: 'long' })
            : formatPanjang(buka);
        return `${awal} – ${formatPanjang(tutup)}`;
    };
    const formatRentangSingkat = (buka, tutup) => `${formatTanggal(buka)} – ${formatTanggal(tutup)}`;

    function statusPeriode(p) {
        if (hariIni < parseIso(p.buka)) return 'terjadwal';
        if (hariIni > parseIso(p.tutup)) return 'ditutup';
        return 'dibuka';
    }

    // ---------- Label semester ----------
    const labelSemester = (i) => semesterLabels[i] || `Semester ${Number(i) + 1}`;
    const jenisSemester = (i) => (/genap/i.test(labelSemester(i)) ? 'Genap' : 'Ganjil');
    const tahunAjaran = (i) => labelSemester(i).replace(/^Semester\s+\S+\s*/i, '').trim();
    const periodeLabel = (i, jenis = 'STS') => `${jenis} ${jenisSemester(i)}`;
    const periodeLengkap = (i, jenis = 'STS') => `${periodeLabel(i, jenis)} ${tahunAjaran(i)}`.trim();

    const cariPeriode = (semester, jenis, tingkat) => daftarPeriode.find((p) =>
        Number(p.semester) === Number(semester) && p.jenis === jenis && p.tingkat === tingkat);

    // ---------- Toast ----------
    const toastWrap = page.querySelector('#pk-toasts');

    function showToast(pesan, tipe = 'ok') {
        if (!toastWrap) return;

        const toast = document.createElement('div');
        toast.className = `pk-toast pk-toast-${tipe}`;

        const icon = document.createElement('span');
        icon.className = 'pk-toast-icon';
        icon.innerHTML = tipe === 'ok' ? ICON_OK : ICON_INFO;

        const teks = document.createElement('p');
        teks.className = 'pk-toast-text';
        teks.textContent = pesan;

        const tutup = document.createElement('button');
        tutup.type = 'button';
        tutup.className = 'pk-toast-close';
        tutup.setAttribute('aria-label', 'Tutup notifikasi');
        tutup.textContent = '×';

        const hapus = () => {
            toast.classList.add('is-leaving');
            setTimeout(() => toast.remove(), 200);
        };
        tutup.addEventListener('click', hapus);

        let timer = setTimeout(hapus, 4500);
        toast.addEventListener('mouseenter', () => clearTimeout(timer));
        toast.addEventListener('mouseleave', () => { timer = setTimeout(hapus, 2000); });

        toast.append(icon, teks, tutup);
        toastWrap.append(toast);

        // Maksimal 3 toast sekaligus
        while (toastWrap.children.length > 3) toastWrap.firstElementChild.remove();
    }

    // ---------- Progress bar ----------
    page.querySelectorAll('.pk-fill[data-percent]').forEach((el) => {
        const persen = Math.max(0, Math.min(100, Number(el.dataset.percent) || 0));
        el.style.width = `${persen}%`;
    });

    // ---------- Tab (klik + panah kiri/kanan, Home, End) ----------
    function setActive(tabs, aktif) {
        tabs.forEach((tab) => {
            const on = tab === aktif;
            tab.classList.toggle('is-active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
            tab.tabIndex = on ? 0 : -1;
        });
    }

    function navigasiKeyboard(tabs) {
        tabs.forEach((tab, idx) => {
            tab.addEventListener('keydown', (e) => {
                let target = null;
                if (e.key === 'ArrowRight') target = tabs[(idx + 1) % tabs.length];
                if (e.key === 'ArrowLeft') target = tabs[(idx - 1 + tabs.length) % tabs.length];
                if (e.key === 'Home') target = tabs[0];
                if (e.key === 'End') target = tabs[tabs.length - 1];
                if (!target) return;
                e.preventDefault();
                target.focus();
                target.click();
            });
        });
    }

    const semesterTabs = [...page.querySelectorAll('.pk-semester-tab')];
    const semesterPanels = [...page.querySelectorAll('[data-semester-panel]')];

    const panelSemester = (i) => page.querySelector(`[data-semester-panel="${i}"]`);
    const semesterAktif = () => {
        const tab = semesterTabs.find((t) => t.classList.contains('is-active'));
        return tab ? Number(tab.dataset.semester) : Number(semesterPanels[0]?.dataset.semesterPanel || 0);
    };

    function pilihSemester(i) {
        const tab = semesterTabs.find((t) => Number(t.dataset.semester) === Number(i));
        if (tab) setActive(semesterTabs, tab);
        semesterPanels.forEach((panel) => {
            panel.hidden = Number(panel.dataset.semesterPanel) !== Number(i);
        });
    }

    semesterTabs.forEach((tab) => {
        tab.addEventListener('click', () => pilihSemester(tab.dataset.semester));
    });
    navigasiKeyboard(semesterTabs);

    const tingkatAktif = (semPanel) =>
        semPanel?.querySelector('.pk-tingkat-tab.is-active')?.dataset.tingkat || null;
    const panelTingkatAktif = (semPanel) =>
        semPanel?.querySelector('[data-tingkat-panel]:not([hidden])') || null;

    function pilihTingkat(semPanel, tingkat) {
        const tabs = [...semPanel.querySelectorAll('.pk-tingkat-tab')];
        const tab = tabs.find((t) => t.dataset.tingkat === tingkat);
        if (!tab) return;
        setActive(tabs, tab);
        semPanel.querySelectorAll('[data-tingkat-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.tingkatPanel !== tingkat;
        });
        renderSemester(semPanel);
    }

    semesterPanels.forEach((semPanel) => {
        const tabs = [...semPanel.querySelectorAll('.pk-tingkat-tab')];
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => pilihTingkat(semPanel, tab.dataset.tingkat));
        });
        navigasiKeyboard(tabs);
    });

    // ---------- Kartu STS / SAS ----------
    function renderKartu(semPanel, jenis) {
        const card = semPanel.querySelector(`[data-periode-card="${jenis}"]`);
        if (!card) return;

        const semester = Number(semPanel.dataset.semesterPanel);
        const tingkat = tingkatAktif(semPanel) || 'X';
        const p = cariPeriode(semester, jenis, tingkat);

        const tanggalEl = card.querySelector('[data-periode-tanggal]');
        const badge = card.querySelector('[data-periode-badge]');
        const btnJadwal = card.querySelector('[data-action="jadwal"]');
        const btnText = btnJadwal?.querySelector('[data-jadwal-text]');

        const status = p ? statusPeriode(p) : null;

        if (p) {
            const cfg = STATUS_PERIODE[status];
            tanggalEl.textContent = formatRentang(parseIso(p.buka), parseIso(p.tutup));
            badge.hidden = false;
            badge.className = `pk-badge ${cfg.cls}`;
            badge.textContent = cfg.label;
        } else {
            tanggalEl.textContent = `Belum dijadwalkan untuk Kelas ${tingkat}`;
            badge.hidden = true;
        }

        if (btnJadwal) {
            const verb = p ? 'Ubah jadwal' : 'Atur jadwal';
            if (btnText) btnText.textContent = verb;
            btnJadwal.setAttribute('aria-label', `${verb} ${jenis} Kelas ${tingkat}`);
        }

        if (jenis !== 'SAS') return;

        // Isi kotak tanggal + teks "Dibuka dalam n hari"
        const box = card.querySelector('[data-date-box]');
        const day = card.querySelector('[data-date-day]');
        const month = card.querySelector('[data-date-month]');
        const title = card.querySelector('[data-sas-title]');
        const desc = card.querySelector('[data-sas-desc]');

        box.classList.remove('is-dibuka', 'is-ditutup', 'is-kosong');

        if (!p) {
            box.classList.add('is-kosong');
            day.textContent = '–';
            month.textContent = '';
            title.textContent = 'Belum dijadwalkan';
            desc.textContent = `Belum ada periode SAS untuk Kelas ${tingkat}. Pilih "Atur jadwal" untuk menambahkan.`;
            return;
        }

        const buka = parseIso(p.buka);
        const tutup = parseIso(p.tutup);
        const tampilTanggal = status === 'terjadwal' ? buka : tutup;
        day.textContent = String(tampilTanggal.getDate()).padStart(2, '0');
        month.textContent = tampilTanggal.toLocaleDateString('id-ID', { month: 'short' }).replace('.', '').toUpperCase();

        if (status === 'terjadwal') {
            const n = selisihHari(hariIni, buka);
            title.textContent = n === 1 ? 'Dibuka besok' : `Dibuka dalam ${n} hari`;
            desc.textContent = `Guru Kelas ${tingkat} dapat mulai mengisi nilai SAS mulai tanggal ini.`;
        } else if (status === 'dibuka') {
            box.classList.add('is-dibuka');
            const n = selisihHari(hariIni, tutup);
            title.textContent = n === 0 ? 'Sedang dibuka · ditutup hari ini' : `Sedang dibuka · ditutup dalam ${n} hari`;
            desc.textContent = `Batas akhir pengisian nilai SAS Kelas ${tingkat}.`;
        } else {
            box.classList.add('is-ditutup');
            title.textContent = 'Periode sudah ditutup';
            desc.textContent = `Pengisian nilai SAS Kelas ${tingkat} ditutup sejak ${formatTanggal(tutup)}.`;
        }
    }

    function renderSemester(semPanel) {
        const semester = Number(semPanel.dataset.semesterPanel);
        const adaPeriode = daftarPeriode.some((p) => Number(p.semester) === semester);
        const empty = semPanel.querySelector('[data-semester-empty]');
        const grid = semPanel.querySelector('[data-semester-grid]');
        if (empty) empty.hidden = adaPeriode;
        if (grid) grid.hidden = !adaPeriode;

        renderKartu(semPanel, 'STS');
        renderKartu(semPanel, 'SAS');
        updateKunciSemua(semPanel);
    }

    // ---------- Tampilan status per baris ----------
    function renderRow(row, status, catatan = '') {
        const cfg = STATUS[status];
        if (!cfg) return;

        row.dataset.status = status;
        const kode = row.dataset.kelas;

        const badge = row.querySelector('[data-status-badge]');
        badge.className = `pk-status ${cfg.cls}`;
        badge.textContent = cfg.label;

        let noteEl = row.querySelector('[data-status-note]');
        if (catatan) {
            if (!noteEl) {
                noteEl = document.createElement('span');
                noteEl.className = 'pk-status-note';
                noteEl.dataset.statusNote = '';
                badge.after(noteEl);
            }
            noteEl.textContent = catatan;
        } else if (noteEl) {
            noteEl.remove();
        }

        const aksi = row.querySelector('[data-aksi]');
        if (status === 'siap') {
            aksi.innerHTML = `<button type="button" class="pk-btn-primary pk-btn-sm" data-action="kunci" aria-label="Kunci nilai kelas ${kode}">${ICON_LOCK} Kunci</button>`;
        } else if (status === 'terkunci') {
            aksi.innerHTML = `<button type="button" class="pk-btn-outline pk-btn-sm" data-action="koreksi" aria-label="Buka koreksi nilai kelas ${kode}">${ICON_UNLOCK} Buka koreksi</button>`;
        } else if (status === 'belum') {
            aksi.innerHTML = '<span class="pk-aksi-text">periode belum dibuka</span>';
        } else {
            aksi.innerHTML = '<span class="pk-aksi-text">menunggu guru</span>';
        }
    }

    // ---------- Tombol "Kunci semua yang siap" ----------
    function barisAktif(semPanel) {
        const panel = panelTingkatAktif(semPanel);
        return panel ? [...panel.querySelectorAll('[data-kelas-row]')] : [];
    }

    function updateKunciSemua(semPanel) {
        const btn = semPanel.querySelector('[data-action="kunci-semua"]');
        const label = btn?.querySelector('[data-kunci-label]');
        if (!btn || !label) return;

        const rows = barisAktif(semPanel);
        const siap = rows.filter((r) => r.dataset.status === 'siap').length;
        const semuaTerkunci = rows.length > 0 && rows.every((r) => r.dataset.status === 'terkunci');

        btn.disabled = siap === 0;
        if (siap > 0) {
            label.textContent = `Kunci semua yang siap (${siap} kelas)`;
        } else if (semuaTerkunci) {
            label.textContent = 'Semua kelas sudah terkunci';
        } else {
            label.textContent = 'Belum ada kelas siap dikunci';
        }
    }

    // ---------- Klik tombol (event delegation, supaya tombol buatan JS tetap jalan) ----------
    page.addEventListener('click', (e) => {
        const closeBtn = e.target.closest('[data-close]');
        if (closeBtn) {
            closeBtn.closest('dialog')?.close('batal');
            return;
        }

        const btn = e.target.closest('[data-action]');
        if (!btn || btn.disabled) return;
        const semPanel = btn.closest('[data-semester-panel]');
        const row = btn.closest('[data-kelas-row]');

        switch (btn.dataset.action) {
            case 'kunci':
                if (row) bukaModalKunci([row], btn);
                break;
            case 'koreksi':
                if (row) bukaModalKoreksi(row, btn);
                break;
            case 'kunci-semua': {
                const rows = barisAktif(semPanel).filter((r) => r.dataset.status === 'siap');
                if (rows.length) bukaModalKunci(rows, btn);
                break;
            }
            case 'jadwal':
                if (semPanel) bukaModalJadwal(semPanel, btn.dataset.jenis, btn);
                break;
            case 'tambah':
                bukaModalTambah({
                    semester: btn.dataset.semester ?? semesterAktif(),
                    jenis: btn.dataset.jenis,
                    tingkat: btn.dataset.tingkat,
                }, btn);
                break;
            default:
        }
    });

    // ---------- Util modal ----------
    const fokusSebelum = new WeakMap();

    function bukaDialog(dialog, opener) {
        dialog.returnValue = '';
        fokusSebelum.set(dialog, opener || document.activeElement);
        dialog.showModal();
        dialog.querySelector('[data-autofocus]:not(:disabled)')?.focus();
    }

    // Kembalikan fokus ke tombol pembuka; kalau tombolnya sudah diganti/disabled, pakai cadangan
    function kembalikanFokus(dialog, ...cadangan) {
        const kandidat = [fokusSebelum.get(dialog), ...cadangan];
        const target = kandidat.find((el) => el && el.isConnected && !el.disabled && el.offsetParent !== null);
        target?.focus();
    }

    // Klik area gelap di luar modal = tutup (Esc sudah ditangani <dialog>)
    page.querySelectorAll('dialog.pk-modal').forEach((dialog) => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close('batal');
        });
    });

    function errorHelper(errEl, fields) {
        return (pesan) => {
            if (errEl) {
                errEl.hidden = !pesan;
                const teks = errEl.querySelector('[data-error-text]');
                if (teks) teks.textContent = pesan || '';
            }
            fields.forEach((f) => {
                f.classList.toggle('is-invalid', Boolean(pesan));
                f.setAttribute('aria-invalid', pesan ? 'true' : 'false');
            });
        };
    }

    // Validasi pasangan tanggal (dipakai modal Tambah & Ubah jadwal)
    function validasiTanggal(buka, tutup) {
        const err = { buka: '', tutup: '' };
        if (!buka) err.buka = 'Tanggal buka wajib diisi.';
        if (!tutup) err.tutup = 'Tanggal tutup wajib diisi.';
        if (buka && tutup && parseIso(tutup) <= parseIso(buka)) {
            err.tutup = 'Tanggal tutup harus setelah tanggal buka.';
        }
        return err;
    }

    // Tanggal tutup minimal sehari setelah tanggal buka
    function hubungkanTanggal(bukaInput, tutupInput) {
        bukaInput.addEventListener('change', () => {
            tutupInput.min = bukaInput.value ? toIsoDate(tambahHari(parseIso(bukaInput.value), 1)) : '';
        });
    }

    // ---------- Modal: Kunci nilai ----------
    const modalKunci = page.querySelector('#pk-modal-kunci');
    let barisAkanDikunci = [];

    function bukaModalKunci(rows, opener) {
        if (!modalKunci) return;
        barisAkanDikunci = rows;
        const semester = rows[0].closest('[data-semester-panel]')?.dataset.semesterPanel ?? semesterAktif();
        const kode = rows.map((r) => r.dataset.kelas);
        modalKunci.querySelector('[data-kunci-jumlah]').textContent = rows.length;
        modalKunci.querySelector('[data-kunci-kelas]').textContent = `${kode.join(', ')} · ${periodeLabel(semester)}`;
        bukaDialog(modalKunci, opener);
    }

    modalKunci?.addEventListener('close', () => {
        const rows = barisAkanDikunci;
        barisAkanDikunci = [];
        const semPanel = rows[0]?.closest('[data-semester-panel]');

        if (modalKunci.returnValue === 'kunci' && rows.length) {
            const tanggal = formatTanggal(new Date());
            const kode = rows.map((r) => r.dataset.kelas);
            rows.forEach((row) => {
                row.dataset.dikunci = tanggal;
                renderRow(row, 'terkunci');
            });

            // TODO (backend): kirim request kunci nilai ke server di sini (kelas: kode, periode STS)
            showToast(`Nilai ${kode.length} kelas (${kode.join(', ')}) berhasil dikunci.`);
            if (semPanel) updateKunciSemua(semPanel);
        }

        kembalikanFokus(
            modalKunci,
            rows[0]?.querySelector('[data-aksi] button'),
            semPanel?.querySelector('.pk-tingkat-tab.is-active'),
        );
    });

    // ---------- Modal: Buka koreksi ----------
    const modalKoreksi = page.querySelector('#pk-modal-koreksi');
    const formKoreksi = modalKoreksi?.querySelector('form');
    const chipsWrap = modalKoreksi?.querySelector('[data-mapel-chips]');
    const mapelSelect = modalKoreksi?.querySelector('#pk-mapel-select');
    const alasanInput = modalKoreksi?.querySelector('#pk-alasan');
    const alasanError = modalKoreksi?.querySelector('#pk-alasan-error');
    const batasInput = modalKoreksi?.querySelector('#pk-batas');
    const btnBukaKoreksi = modalKoreksi?.querySelector('#pk-btn-buka-koreksi');
    const infoKoreksi = modalKoreksi?.querySelector('[data-koreksi-info]');

    const mapelDipilih = new Set();
    let barisKoreksi = null;

    function tampilkanErrorAlasan(tampil) {
        alasanError.hidden = !tampil;
        alasanInput.classList.toggle('is-invalid', tampil);
        alasanInput.setAttribute('aria-invalid', tampil ? 'true' : 'false');
    }

    function cekFormKoreksi() {
        btnBukaKoreksi.disabled = !(alasanInput.value.trim() && mapelDipilih.size > 0);
    }

    function setOpsiMapel(value, disabled) {
        const opsi = [...mapelSelect.options].find((o) => o.value === value);
        if (opsi) opsi.disabled = disabled;
    }

    function tambahChip(value) {
        if (!value || mapelDipilih.has(value)) return;
        mapelDipilih.add(value);

        const chip = document.createElement('span');
        chip.className = 'pk-chip';

        const teks = document.createElement('span');
        teks.textContent = value;

        const hapus = document.createElement('button');
        hapus.type = 'button';
        hapus.className = 'pk-chip-remove';
        hapus.setAttribute('aria-label', `Hapus ${value}`);
        hapus.textContent = '×';
        hapus.addEventListener('click', () => {
            mapelDipilih.delete(value);
            chip.remove();
            setOpsiMapel(value, false);
            cekFormKoreksi();
            mapelSelect.focus();
        });

        chip.append(teks, hapus);
        chipsWrap.insertBefore(chip, mapelSelect);
        setOpsiMapel(value, true);
        cekFormKoreksi();
    }

    function bukaModalKoreksi(row, opener) {
        if (!modalKoreksi) return;
        barisKoreksi = row;
        const semester = row.closest('[data-semester-panel]')?.dataset.semesterPanel ?? semesterAktif();

        // Reset form
        mapelDipilih.clear();
        chipsWrap.querySelectorAll('.pk-chip').forEach((c) => c.remove());
        [...mapelSelect.options].forEach((o) => { o.disabled = false; });
        mapelSelect.value = '';
        alasanInput.value = '';
        tampilkanErrorAlasan(false);

        // Batas waktu default: 7 hari dari hari ini
        batasInput.min = toIsoDate(hariIni);
        batasInput.value = toIsoDate(tambahHari(hariIni, 7));

        const info = [`Kelas ${row.dataset.kelas}`, periodeLengkap(semester)];
        if (row.dataset.dikunci) info.push(`Terkunci sejak ${row.dataset.dikunci}`);
        infoKoreksi.textContent = info.join(' · ');

        cekFormKoreksi();
        bukaDialog(modalKoreksi, opener);
    }

    mapelSelect?.addEventListener('change', () => {
        tambahChip(mapelSelect.value);
        mapelSelect.value = '';
    });

    alasanInput?.addEventListener('input', () => {
        if (alasanInput.value.trim()) tampilkanErrorAlasan(false);
        cekFormKoreksi();
    });

    alasanInput?.addEventListener('blur', () => {
        if (!alasanInput.value.trim()) tampilkanErrorAlasan(true);
    });

    formKoreksi?.addEventListener('submit', (e) => {
        if (e.submitter?.value !== 'buka') return;
        if (!alasanInput.value.trim() || mapelDipilih.size === 0) {
            e.preventDefault();
            if (!alasanInput.value.trim()) tampilkanErrorAlasan(true);
            alasanInput.focus();
        }
    });

    modalKoreksi?.addEventListener('close', () => {
        const row = barisKoreksi;
        barisKoreksi = null;
        const semPanel = row?.closest('[data-semester-panel]');

        if (modalKoreksi.returnValue === 'buka' && row) {
            const mapelSingkat = [...mapelDipilih].map((m) => m.split(' — ')[0]).join(', ');
            const batas = batasInput.value ? formatTanggal(parseIso(batasInput.value)) : '';

            // TODO (backend): kirim mapel, alasan, dan batas waktu koreksi ke server di sini
            renderRow(row, 'koreksi', batas ? `${mapelSingkat} · s.d. ${batas}` : mapelSingkat);
            showToast(`Koreksi nilai ${row.dataset.kelas} dibuka untuk ${mapelSingkat}.`);
            if (semPanel) updateKunciSemua(semPanel);
        }

        kembalikanFokus(
            modalKoreksi,
            semPanel?.querySelector('.pk-tingkat-tab.is-active'),
        );
    });

    // ---------- Modal: Tambah periode ----------
    const modalTambah = page.querySelector('#pk-modal-tambah');
    const formTambah = modalTambah?.querySelector('form');
    const tSemester = modalTambah?.querySelector('#pk-tambah-semester');
    const tJenis = modalTambah ? [...modalTambah.querySelectorAll('input[name="jenis"]')] : [];
    const tTingkat = modalTambah ? [...modalTambah.querySelectorAll('input[name="tingkat"]')] : [];
    const tBuka = modalTambah?.querySelector('#pk-tambah-buka');
    const tTutup = modalTambah?.querySelector('#pk-tambah-tutup');
    let tambahSudahDicek = false;

    const errTambah = modalTambah ? {
        semester: errorHelper(modalTambah.querySelector('#pk-tambah-semester-error'), [tSemester]),
        jenis: errorHelper(modalTambah.querySelector('#pk-tambah-jenis-error'), tJenis),
        tingkat: errorHelper(modalTambah.querySelector('#pk-tambah-tingkat-error'), tTingkat),
        buka: errorHelper(modalTambah.querySelector('#pk-tambah-buka-error'), [tBuka]),
        tutup: errorHelper(modalTambah.querySelector('#pk-tambah-tutup-error'), [tTutup]),
    } : {};

    const nilaiTambah = () => ({
        semester: tSemester.value,
        jenis: tJenis.find((r) => r.checked)?.value || '',
        tingkat: tTingkat.filter((c) => c.checked).map((c) => c.value),
        buka: tBuka.value,
        tutup: tTutup.value,
    });

    function validasiTambah() {
        const v = nilaiTambah();
        const err = { semester: '', jenis: '', tingkat: '', ...validasiTanggal(v.buka, v.tutup) };

        if (v.semester === '') err.semester = 'Semester wajib dipilih.';
        if (!v.jenis) err.jenis = 'Jenis periode wajib dipilih.';
        if (!v.tingkat.length) err.tingkat = 'Pilih minimal satu tingkat.';

        // Tidak boleh duplikat: semester + jenis + tingkat
        if (v.semester !== '' && v.jenis && v.tingkat.length) {
            const dobel = v.tingkat.filter((t) => cariPeriode(v.semester, v.jenis, t));
            if (dobel.length) {
                err.tingkat = `Periode ${v.jenis} untuk Kelas ${dobel.join(', ')} sudah ada di `
                    + `${labelSemester(v.semester)}. Gunakan "Ubah jadwal" untuk mengganti tanggalnya.`;
            }
        }

        Object.entries(err).forEach(([key, pesan]) => errTambah[key](pesan));
        return err;
    }

    function bukaModalTambah(prefill = {}, opener) {
        if (!modalTambah) return;
        formTambah.reset();
        tambahSudahDicek = false;
        Object.values(errTambah).forEach((set) => set(''));
        tTutup.min = '';

        tSemester.value = prefill.semester !== undefined && prefill.semester !== null ? String(prefill.semester) : '';
        tJenis.forEach((r) => { r.checked = r.value === prefill.jenis; });
        tTingkat.forEach((c) => { c.checked = c.value === prefill.tingkat; });

        bukaDialog(modalTambah, opener);
    }

    if (modalTambah) {
        hubungkanTanggal(tBuka, tTutup);

        // Setelah percobaan simpan pertama, validasi ulang setiap kali isian berubah
        formTambah.addEventListener('change', () => { if (tambahSudahDicek) validasiTambah(); });
        formTambah.addEventListener('input', () => { if (tambahSudahDicek) validasiTambah(); });

        formTambah.addEventListener('submit', (e) => {
            if (e.submitter && e.submitter.value !== 'simpan') return;
            tambahSudahDicek = true;
            const err = validasiTambah();
            const urutan = [
                ['semester', tSemester],
                ['jenis', tJenis[0]],
                ['tingkat', tTingkat[0]],
                ['buka', tBuka],
                ['tutup', tTutup],
            ];
            const pertama = urutan.find(([key]) => err[key]);
            if (pertama) {
                e.preventDefault();
                pertama[1]?.focus();
            }
        });

        modalTambah.addEventListener('close', () => {
            if (modalTambah.returnValue !== 'simpan') {
                kembalikanFokus(modalTambah, page.querySelector('#pk-btn-tambah'));
                return;
            }

            const v = nilaiTambah();
            const semester = Number(v.semester);
            v.tingkat.forEach((tingkat) => {
                daftarPeriode.push({
                    id: nextId++,
                    semester,
                    jenis: v.jenis,
                    tingkat,
                    buka: v.buka,
                    tutup: v.tutup,
                });
            });
            // TODO (backend): kirim periode baru (semester, jenis, tingkat[], buka, tutup) ke server di sini

            // Tampilkan semester & tingkat yang baru ditambahkan
            pilihSemester(semester);
            const semPanel = panelSemester(semester);
            if (semPanel) {
                if (!v.tingkat.includes(tingkatAktif(semPanel))) {
                    pilihTingkat(semPanel, v.tingkat[0]);
                } else {
                    renderSemester(semPanel);
                }
            }

            showToast(
                `Periode ${v.jenis} ${labelSemester(semester)} untuk Kelas ${v.tingkat.join(', ')} ditambahkan `
                + `(${formatRentangSingkat(parseIso(v.buka), parseIso(v.tutup))}).`,
            );

            kembalikanFokus(
                modalTambah,
                semPanel?.querySelector(`[data-periode-card="${v.jenis}"] [data-action="jadwal"]`),
                page.querySelector('#pk-btn-tambah'),
            );
        });
    }

    // ---------- Modal: Ubah jadwal ----------
    const modalJadwal = page.querySelector('#pk-modal-jadwal');
    const formJadwal = modalJadwal?.querySelector('form');
    const jBuka = modalJadwal?.querySelector('#pk-jadwal-buka');
    const jTutup = modalJadwal?.querySelector('#pk-jadwal-tutup');
    const jJenis = modalJadwal?.querySelector('[data-jadwal-jenis]');
    const jInfo = modalJadwal?.querySelector('[data-jadwal-info]');
    const jWarning = modalJadwal?.querySelector('[data-jadwal-warning]');
    let periodeDiubah = null;
    let jadwalSudahDicek = false;

    const errJadwal = modalJadwal ? {
        buka: errorHelper(modalJadwal.querySelector('#pk-jadwal-buka-error'), [jBuka]),
        tutup: errorHelper(modalJadwal.querySelector('#pk-jadwal-tutup-error'), [jTutup]),
    } : {};

    function validasiJadwal() {
        const err = validasiTanggal(jBuka.value, jTutup.value);
        errJadwal.buka(err.buka);
        errJadwal.tutup(err.tutup);
        return err;
    }

    function bukaModalJadwal(semPanel, jenis, opener) {
        const semester = Number(semPanel.dataset.semesterPanel);
        const tingkat = tingkatAktif(semPanel);
        const p = cariPeriode(semester, jenis, tingkat);

        // Belum ada periode -> arahkan ke Tambah periode yang sudah terisi
        if (!p) {
            bukaModalTambah({ semester, jenis, tingkat }, opener);
            return;
        }
        if (!modalJadwal) return;

        periodeDiubah = p;
        jadwalSudahDicek = false;
        const status = statusPeriode(p);

        jJenis.textContent = jenis;
        jInfo.textContent = `${labelSemester(semester)} · Kelas ${tingkat} · Status: ${STATUS_PERIODE[status].label}`;
        jBuka.value = p.buka;
        jTutup.value = p.tutup;
        jTutup.min = toIsoDate(tambahHari(parseIso(p.buka), 1));
        jWarning.hidden = status !== 'dibuka';
        errJadwal.buka('');
        errJadwal.tutup('');

        bukaDialog(modalJadwal, opener);
    }

    if (modalJadwal) {
        hubungkanTanggal(jBuka, jTutup);
        formJadwal.addEventListener('input', () => { if (jadwalSudahDicek) validasiJadwal(); });
        formJadwal.addEventListener('change', () => { if (jadwalSudahDicek) validasiJadwal(); });

        formJadwal.addEventListener('submit', (e) => {
            if (e.submitter && e.submitter.value !== 'simpan') return;
            jadwalSudahDicek = true;
            const err = validasiJadwal();
            if (err.buka || err.tutup) {
                e.preventDefault();
                (err.buka ? jBuka : jTutup).focus();
            }
        });

        modalJadwal.addEventListener('close', () => {
            const p = periodeDiubah;
            periodeDiubah = null;
            const semPanel = p ? panelSemester(p.semester) : null;

            if (modalJadwal.returnValue === 'simpan' && p) {
                if (p.buka === jBuka.value && p.tutup === jTutup.value) {
                    showToast('Tidak ada perubahan jadwal.', 'info');
                } else {
                    p.buka = jBuka.value;
                    p.tutup = jTutup.value;
                    // TODO (backend): kirim jadwal baru (id periode, buka, tutup) ke server di sini
                    if (semPanel) renderSemester(semPanel);
                    showToast(
                        `Jadwal ${p.jenis} Kelas ${p.tingkat} diperbarui: `
                        + `${formatRentangSingkat(parseIso(p.buka), parseIso(p.tutup))}.`,
                    );
                }
            }

            kembalikanFokus(modalJadwal);
        });
    }

    // ---------- Render awal ----------
    semesterPanels.forEach(renderSemester);
}
