// resources/js/presensi/admin.js
// Admin Presensi: scan QR + presensi manual + profil. Frontend saja: data dummy.
// Titik backend ditandai "TODO API". Catatan disimpan sementara di localStorage browser.
import { Html5Qrcode } from 'html5-qrcode';

const root = document.getElementById('presensiAdmin');

if (root) {
    const $ = (selector) => root.querySelector(selector);
    const STORAGE_KEY = 'siakad_presensi_admin_v1';
    const BATAS_TEPAT_WAKTU = '07.00';          // lewat jam ini = Telat (sama dengan backend)
    const UKURAN_MAKS = 5 * 1024 * 1024;        // 5 MB (sama dengan backend)

    // TODO API: ganti dengan daftar siswa dari backend
    const students = [
        { nisn: '245150600', nama: 'Mutiara Bintang', kelas: 'XI MIPA 2' },
        { nisn: '245150601', nama: 'Bintang Mutiara', kelas: 'XI MIPA 2' },
        { nisn: '245150602', nama: 'Mutiara Lautan', kelas: 'XI MIPA 2' },
        { nisn: '245150603', nama: 'Lautan Biru', kelas: 'XI MIPA 2' },
        { nisn: '245150604', nama: 'Angkasa Raya', kelas: 'XI MIPA 2' },
        { nisn: '245150605', nama: 'Putri Andromeda', kelas: 'XI MIPA 2' },
        { nisn: '245150606', nama: 'Putra Mahkota', kelas: 'XI MIPA 2' },
        { nisn: '245150607', nama: 'Peri Indah', kelas: 'XI MIPA 2' },
        { nisn: '245150608', nama: 'Bintang Samudra', kelas: 'XI MIPA 2' },
    ];

    const norm = (s) => String(s ?? '').trim().replace(/\s+/g, ' ').toLowerCase();
    const jamSekarang = () => { const n = new Date(); return `${String(n.getHours()).padStart(2, '0')}.${String(n.getMinutes()).padStart(2, '0')}`; };
    const getDateKey = () => {
        const d = new Date();
        return [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
    };

    let records = loadRecords();
    let scanner = null;
    let scannerRunning = false;
    let lastScanValue = '';
    let lastScanAt = 0;
    let toastTimer = null;

    /* ============ PENYIMPANAN SEMENTARA ============ */
    function loadRecords() {
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            return Array.isArray(saved) ? saved : [];
        } catch (error) {
            console.error(error);
            return [];
        }
    }

    function persistRecords() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(records));
        } catch (error) {
            notify('Penyimpanan browser tidak tersedia.');
        }
    }

    /* ============ NOTIFIKASI ============ */
    function notify(message) {
        const toast = $('#paToast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function tampilHasilScan(teks, ok) {
        const el = $('#paScanResult');
        if (!el) return;
        el.textContent = teks;
        el.className = `pa-scan-result ${ok ? 'is-ok' : 'is-error'}`;
    }

    /* ============ DATA SISWA & PENCATATAN ============ */
    const cariNisnAtauNama = (value) => {
        const bersih = String(value || '').trim();
        return students.find((s) => s.nisn === bersih) || students.find((s) => norm(s.nama) === norm(bersih));
    };

    // Satu siswa hanya boleh punya satu catatan "hadir" per hari. Alpa boleh diikuti Telat/Hadir.
    function addAttendance(student, status, method, berkas = '') {
        const dateKey = getDateKey();
        const ada = records.find((r) => r.nisn === student.nisn && r.dateKey === dateKey && r.status !== 'Alpa');

        if (ada) {
            notify(`${student.nama} sudah tercatat ${ada.status} hari ini.`);
            return { ok: false, pesan: `${student.nama} sudah tercatat ${ada.status} hari ini.` };
        }

        // TODO API: kirim ke backend (POST /presensi), sertakan CSRF token dan file bukti untuk Izin/Sakit
        records.unshift({
            id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
            nisn: student.nisn, nama: student.nama, kelas: student.kelas,
            status, metode: method, berkas, dateKey, waktu: new Date().toISOString(),
        });
        persistRecords();
        notify(`Presensi ${student.nama} berhasil dicatat.`);
        return { ok: true, pesan: `${student.nama} • ${student.kelas} tercatat ${status}.` };
    }

    /* ============ PRESENSI MANUAL ============ */
    const manualForm = $('#paManualForm');
    const fileInput = $('#paFile');
    const fileName = $('#paFileName');

    if (manualForm) {
        manualForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const nama = $('#paStudentName').value;
            const kelas = $('#paClass').value;
            const status = $('#paStatus').value;

            if (!nama.trim() || !kelas.trim() || !status) {
                notify('Lengkapi nama, kelas, dan status presensi.');
                return;
            }

            // Hanya siswa yang ada di data yang boleh dicatat
            const siswa = students.find((s) => norm(s.nama) === norm(nama) && norm(s.kelas) === norm(kelas));
            if (!siswa) {
                notify('Siswa tidak ditemukan. Pastikan nama dan kelas sudah benar.');
                return;
            }

            $('#paNisn').value = siswa.nisn;
            const berkas = fileInput && fileInput.files[0] ? fileInput.files[0].name : '';

            if (addAttendance(siswa, status, 'Manual', berkas).ok) {
                manualForm.reset();
                if (fileName) fileName.textContent = '';
            }
        });
    }

    /* ============ UPLOAD FILE ============ */
    if (fileInput && fileName) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) { fileName.textContent = ''; return; }

            if (!['image/jpeg', 'image/png', 'application/pdf'].includes(file.type)) {
                notify('Format file harus JPG, PNG, atau PDF.');
                this.value = '';
                fileName.textContent = '';
                return;
            }

            if (file.size > UKURAN_MAKS) {
                notify('Ukuran file maksimal 5 MB.');
                this.value = '';
                fileName.textContent = '';
                return;
            }

            fileName.textContent = `File dipilih: ${file.name}`;
        });
    }

    /* ============ KOLOM CARI DI ATAS: Enter = isi form manual ============ */
    const headerSearch = $('#paHeaderSearch');
    if (headerSearch) {
        headerSearch.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();

            const q = norm(headerSearch.value);
            const siswa = q && students.find((s) => [s.nama, s.nisn, s.kelas].some((v) => norm(v).includes(q)));

            if (!siswa) { notify('Siswa tidak ditemukan.'); return; }

            $('#paStudentName').value = siswa.nama;
            $('#paClass').value = siswa.kelas;
            $('#paNisn').value = siswa.nisn;
            notify('Data siswa terisi. Pilih status lalu Submit.');
        });
    }

    /* ============ MEMBACA QR CODE ============ */
    // Isi QR bisa NISN langsung, "SIAKAD|<nisn>" (QR dari halaman siswa), atau JSON {nisn}
    function bacaNisn(teks) {
        let value = String(teks || '').trim();
        try {
            const parsed = JSON.parse(value);
            value = parsed.nisn || parsed.id || parsed.student_id || value;
        } catch (error) { /* QR berisi teks biasa */ }
        return String(value).split('|').pop().trim();
    }

    function onScanSuccess(decodedText) {
        const now = Date.now();
        if (decodedText === lastScanValue && now - lastScanAt < 4000) return;   // abaikan QR yang sama berturut-turut
        lastScanValue = decodedText;
        lastScanAt = now;

        const nisn = bacaNisn(decodedText);
        const siswa = cariNisnAtauNama(nisn);

        if (!siswa) {
            tampilHasilScan(`QR terbaca (${nisn}), tetapi siswa tidak ditemukan.`, false);
            notify('Data siswa tidak ditemukan.');
            return;
        }

        const status = jamSekarang() > BATAS_TEPAT_WAKTU ? 'Telat' : 'Hadir';
        const hasil = addAttendance(siswa, status, 'QR Code');
        tampilHasilScan(hasil.pesan, hasil.ok);
    }

    /* ============ KAMERA ============ */
    function updateCameraPlaceholder(title, description) {
        const placeholder = $('#paCameraPlaceholder');
        if (!placeholder) return;
        const strong = placeholder.querySelector('strong');
        const small = placeholder.querySelector('small');
        if (strong) strong.textContent = title;
        if (small) small.textContent = description;
        placeholder.hidden = false;
    }

    async function startScanner() {
        if (scannerRunning) return;

        const startBtn = $('#paStartScan');
        const stopBtn = $('#paStopScan');

        if (!window.isSecureContext) { notify('Kamera hanya bisa digunakan melalui localhost atau HTTPS.'); return; }
        if (!navigator.mediaDevices?.getUserMedia) { notify('Browser tidak mendukung akses kamera.'); return; }

        if (startBtn) { startBtn.disabled = true; startBtn.textContent = 'Menghubungkan kamera...'; }
        updateCameraPlaceholder('Menghubungkan kamera...', 'Silakan izinkan akses kamera pada browser.');

        try {
            const cameras = await Html5Qrcode.getCameras();
            if (!cameras || cameras.length === 0) throw new Error('Kamera tidak ditemukan.');

            const selectedCamera = cameras.find((c) => /back|rear|environment|belakang/i.test(c.label)) || cameras[0];

            scanner = new Html5Qrcode('paReader', { verbose: false });
            await scanner.start(
                selectedCamera.id,
                { fps: 10, qrbox: { width: 220, height: 220 }, aspectRatio: 1 },
                onScanSuccess,
                () => {}
            );

            scannerRunning = true;
            const placeholder = $('#paCameraPlaceholder');
            if (placeholder) placeholder.hidden = true;
            if (startBtn) { startBtn.disabled = true; startBtn.textContent = 'Kamera Aktif'; }
            if (stopBtn) stopBtn.disabled = false;
            notify('Kamera berhasil diaktifkan.');
        } catch (error) {
            console.error('Error scanner:', error);
            scannerRunning = false;

            if (scanner) {
                try { await scanner.clear(); } catch (clearError) { console.warn(clearError); }
                scanner = null;
            }

            updateCameraPlaceholder('Kamera tidak dapat diakses', 'Periksa izin kamera browser dan gunakan localhost atau HTTPS.');
            if (startBtn) { startBtn.disabled = false; startBtn.textContent = 'Mulai Scan'; }
            if (stopBtn) stopBtn.disabled = true;

            if (error.name === 'NotAllowedError') notify('Akses kamera ditolak.');
            else if (error.name === 'NotFoundError') notify('Kamera tidak ditemukan.');
            else if (error.name === 'NotReadableError') notify('Kamera sedang digunakan aplikasi lain.');
            else notify(error.message || 'Gagal membuka kamera.');
        }
    }

    async function stopScanner() {
        const startBtn = $('#paStartScan');
        const stopBtn = $('#paStopScan');

        try {
            if (scanner && scannerRunning) await scanner.stop();
            if (scanner) await scanner.clear();
        } catch (error) {
            console.error('Error menghentikan kamera:', error);
        } finally {
            scannerRunning = false;
            scanner = null;
            lastScanValue = '';
            lastScanAt = 0;
            updateCameraPlaceholder('Kamera belum diaktifkan', 'Tekan tombol Mulai Scan untuk membuka kamera.');
            if (startBtn) { startBtn.disabled = false; startBtn.textContent = 'Mulai Scan'; }
            if (stopBtn) stopBtn.disabled = true;
            notify('Pemindai kamera dihentikan.');
        }
    }

    $('#paStartScan')?.addEventListener('click', startScanner);
    $('#paStopScan')?.addEventListener('click', stopScanner);

    window.addEventListener('pagehide', () => {
        if (scanner && scannerRunning) scanner.stop().catch((error) => console.warn(error));
    });

    /* ============ DROPDOWN PROFIL ============ */
    const profileButton = $('#paProfileButton');
    const profileDropdown = $('#paProfileDropdown');

    if (profileButton && profileDropdown) {
        const tutup = () => {
            profileDropdown.classList.remove('show');
            profileButton.classList.remove('active');
            profileButton.setAttribute('aria-expanded', 'false');
        };

        profileButton.addEventListener('click', (event) => {
            event.stopPropagation();
            const terbuka = profileDropdown.classList.toggle('show');
            profileButton.classList.toggle('active', terbuka);
            profileButton.setAttribute('aria-expanded', String(terbuka));
        });

        document.addEventListener('click', (event) => {
            if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) tutup();
        });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') tutup(); });
    }
}