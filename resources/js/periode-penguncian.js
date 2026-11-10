// Halaman Periode & Penguncian (frontend, data masih dummy)
// Mengatur: progress bar, tab semester, tab tingkat, kunci nilai, buka koreksi.
 
const STATUS = {
    siap:     { label: 'Siap dikunci',         cls: 'pk-status-siap' },
    terkunci: { label: 'Terkunci',             cls: 'pk-status-kunci' },
    menunggu: { label: 'Belum lengkap',        cls: 'pk-status-menunggu' },
    koreksi:  { label: 'Dibuka untuk koreksi', cls: 'pk-status-koreksi' },
};
 
const ICON_LOCK = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>';
const ICON_UNLOCK = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>';
 
const page = document.querySelector('[data-pk-page]');
if (page) initPeriodePenguncian(page);
 
function initPeriodePenguncian(page) {
    const periodeLabel = page.dataset.periodeLabel || 'STS';
    const periodeLengkap = page.dataset.periodeLengkap || periodeLabel;
 
    // ---------- Pesan umpan balik ----------
    const feedbackEl = page.querySelector('#pk-feedback');
    let feedbackTimer;
    function showFeedback(pesan) {
        if (!feedbackEl) return;
        feedbackEl.textContent = pesan;
        clearTimeout(feedbackTimer);
        feedbackTimer = setTimeout(() => { feedbackEl.textContent = ''; }, 4000);
    }
 
    const formatTanggal = (date) =>
        date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
 
    const toIsoDate = (date) => {
        const lokal = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
        return lokal.toISOString().slice(0, 10);
    };
 
    // ---------- Progress bar ----------
    page.querySelectorAll('.pk-fill[data-percent]').forEach((el) => {
        const persen = Math.max(0, Math.min(100, Number(el.dataset.percent) || 0));
        el.style.width = `${persen}%`;
    });
 
    // ---------- Tab ----------
    function setActive(tabs, aktif) {
        tabs.forEach((tab) => {
            const on = tab === aktif;
            tab.classList.toggle('is-active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
        });
    }
 
    const semesterTabs = page.querySelectorAll('.pk-semester-tab');
    semesterTabs.forEach((tab) => {
        tab.addEventListener('click', () => setActive(semesterTabs, tab));
    });
 
    const tingkatTabs = page.querySelectorAll('.pk-tingkat-tab');
    const panels = page.querySelectorAll('[data-tingkat-panel]');
    tingkatTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            setActive(tingkatTabs, tab);
            panels.forEach((panel) => {
                panel.hidden = panel.dataset.tingkatPanel !== tab.dataset.tingkat;
            });
            updateKunciSemua();
        });
    });
 
    const activePanel = () => page.querySelector('[data-tingkat-panel]:not([hidden])');
    const barisDenganStatus = (status) => {
        const panel = activePanel();
        return panel ? [...panel.querySelectorAll(`[data-kelas-row][data-status="${status}"]`)] : [];
    };
 
    // ---------- Tampilan status per baris ----------
    function renderRow(row, status, catatan = '') {
        const cfg = STATUS[status];
        if (!cfg) return;
 
        row.dataset.status = status;
 
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
            aksi.innerHTML = `<button type="button" class="pk-btn-primary pk-btn-sm" data-action="kunci">${ICON_LOCK} Kunci</button>`;
        } else if (status === 'terkunci') {
            aksi.innerHTML = `<button type="button" class="pk-btn-outline pk-btn-sm" data-action="koreksi">${ICON_UNLOCK} Buka koreksi</button>`;
        } else {
            aksi.innerHTML = '<span class="pk-aksi-text">menunggu guru</span>';
        }
    }
 
    // ---------- Tombol "Kunci semua yang siap" ----------
    const btnKunciSemua = page.querySelector('#pk-btn-kunci-semua');
    const labelKunciSemua = page.querySelector('#pk-label-kunci');
 
    function updateKunciSemua() {
        if (!btnKunciSemua || !labelKunciSemua) return;
        const jumlah = barisDenganStatus('siap').length;
        btnKunciSemua.disabled = jumlah === 0;
        labelKunciSemua.textContent = jumlah > 0
            ? `Kunci semua yang siap (${jumlah} kelas)`
            : 'Belum ada kelas siap dikunci';
    }
 
    btnKunciSemua?.addEventListener('click', () => {
        const rows = barisDenganStatus('siap');
        if (rows.length) bukaModalKunci(rows);
    });
 
    // Tombol "Kunci" & "Buka koreksi" per baris (event delegation,
    // supaya tombol yang dibuat ulang lewat JS tetap berfungsi)
    page.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const row = btn.closest('[data-kelas-row]');
        if (!row) return;
 
        if (btn.dataset.action === 'kunci') bukaModalKunci([row]);
        if (btn.dataset.action === 'koreksi') bukaModalKoreksi(row);
    });
 
    // Klik area gelap di luar modal = tutup
    function tutupSaatKlikLuar(dialog) {
        dialog?.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close('batal');
        });
    }
 
    // ---------- Modal: Kunci nilai ----------
    const modalKunci = page.querySelector('#pk-modal-kunci');
    let barisAkanDikunci = [];
 
    function bukaModalKunci(rows) {
        if (!modalKunci) return;
        barisAkanDikunci = rows;
        const kode = rows.map((r) => r.dataset.kelas);
        modalKunci.querySelector('[data-kunci-jumlah]').textContent = rows.length;
        modalKunci.querySelector('[data-kunci-kelas]').textContent = `${kode.join(', ')} · ${periodeLabel}`;
        modalKunci.returnValue = '';
        modalKunci.showModal();
    }
 
    modalKunci?.addEventListener('close', () => {
        if (modalKunci.returnValue !== 'kunci' || !barisAkanDikunci.length) return;
 
        const hariIni = formatTanggal(new Date());
        const kode = barisAkanDikunci.map((r) => r.dataset.kelas);
        barisAkanDikunci.forEach((row) => {
            row.dataset.dikunci = hariIni;
            renderRow(row, 'terkunci');
        });
 
        // TODO (backend): kirim request kunci nilai ke server di sini
        showFeedback(`Nilai ${kode.join(', ')} berhasil dikunci.`);
        barisAkanDikunci = [];
        updateKunciSemua();
    });
    tutupSaatKlikLuar(modalKunci);
 
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
        });
 
        chip.append(teks, hapus);
        chipsWrap.insertBefore(chip, mapelSelect);
        setOpsiMapel(value, true);
        cekFormKoreksi();
    }
 
    function bukaModalKoreksi(row) {
        if (!modalKoreksi) return;
        barisKoreksi = row;
 
        // Reset form
        mapelDipilih.clear();
        chipsWrap.querySelectorAll('.pk-chip').forEach((c) => c.remove());
        [...mapelSelect.options].forEach((o) => { o.disabled = false; });
        mapelSelect.value = '';
        alasanInput.value = '';
        tampilkanErrorAlasan(false);
 
        // Batas waktu default: 7 hari dari hari ini
        const hariIni = new Date();
        const batasDefault = new Date();
        batasDefault.setDate(hariIni.getDate() + 7);
        batasInput.min = toIsoDate(hariIni);
        batasInput.value = toIsoDate(batasDefault);
 
        const info = [`Kelas ${row.dataset.kelas}`, periodeLengkap];
        if (row.dataset.dikunci) info.push(`Terkunci sejak ${row.dataset.dikunci}`);
        infoKoreksi.textContent = info.join(' · ');
 
        cekFormKoreksi();
        modalKoreksi.returnValue = '';
        modalKoreksi.showModal();
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
        if (e.submitter?.value !== 'buka') return; // Batal / tutup tidak perlu validasi
        if (!alasanInput.value.trim() || mapelDipilih.size === 0) {
            e.preventDefault();
            if (!alasanInput.value.trim()) tampilkanErrorAlasan(true);
            alasanInput.focus();
        }
    });
 
    modalKoreksi?.addEventListener('close', () => {
        if (modalKoreksi.returnValue !== 'buka' || !barisKoreksi) return;
 
        const mapelSingkat = [...mapelDipilih].map((m) => m.split(' — ')[0]).join(', ');
        const batas = batasInput.value
            ? formatTanggal(new Date(`${batasInput.value}T00:00:00`))
            : '';
 
        // TODO (backend): kirim mapel, alasan, dan batas waktu koreksi ke server di sini
        renderRow(barisKoreksi, 'koreksi', batas ? `${mapelSingkat} · s.d. ${batas}` : mapelSingkat);
        showFeedback(`Koreksi nilai ${barisKoreksi.dataset.kelas} dibuka.`);
        barisKoreksi = null;
        updateKunciSemua();
    });
    tutupSaatKlikLuar(modalKoreksi);
 
    // ---------- Tombol yang belum punya desain modal ----------
    page.querySelector('#pk-btn-tambah')?.addEventListener('click', () => {
        showFeedback('Form tambah periode belum tersedia.');
    });
    page.querySelector('#pk-btn-jadwal')?.addEventListener('click', () => {
        showFeedback('Form ubah jadwal belum tersedia.');
    });
 
    updateKunciSemua();
}
 