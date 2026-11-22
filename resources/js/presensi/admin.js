
// resources/js/presensi/admin.js
// Admin Presensi: integrasi API siswa, scan QR, presensi manual, dan profil.
// Desain/CSS tidak diubah.

import { Html5Qrcode } from 'html5-qrcode';

const root = document.getElementById('presensiAdmin');

if (root) {
    const $ = (selector) => root.querySelector(selector);

    const API = {
        siswa: '/api/siswa',
        presensi: '/api/presensi',
        manual: '/api/presensi/manual',
    };

    const UKURAN_MAKS = 5 * 1024 * 1024;

    let students = [];
    let selectedStudent = null;

    let scanner = null;
    let scannerRunning = false;
    let lastScanValue = '';
    let lastScanAt = 0;
    let toastTimer = null;

    const manualForm = $('#paManualForm');
    const fileInput = $('#paFile');
    const fileName = $('#paFileName');
    const headerSearch = $('[data-tb-search-input]');

    const nameInput = $('#paStudentName');
    const classInput = $('#paClass');
    const statusInput = $('#paStatus');
    const nisnInput = $('#paNisn');

    // Token login dari public/auth/auth.js
    function getToken() {
        return localStorage.getItem('siakad_token');
    }

    function getUser() {
        try {
            return JSON.parse(localStorage.getItem('siakad_user') || '{}');
        } catch {
            return {};
        }
    }

    function getHeaders() {
        const token = getToken();

        return {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
        };
    }

    async function apiRequest(url, options = {}) {
        if (!getToken()) {
            throw new Error('Token login tidak ditemukan. Silakan login kembali.');
        }

        const response = await fetch(url, {
            ...options,
            headers: {
                ...getHeaders(),
                ...(options.headers || {}),
            },
        });

        const contentType = response.headers.get('content-type') || '';

        let result = {};

        if (contentType.includes('application/json')) {
            result = await response.json();
        }

        if (!response.ok) {
            throw new Error(
                result.message || `Permintaan gagal (${response.status}).`
            );
        }

        return result;
    }

    function norm(value) {
        return String(value ?? '')
            .trim()
            .replace(/\s+/g, ' ')
            .toLowerCase();
    }

    function getCurrentTime() {
        const now = new Date();

        return [
            String(now.getHours()).padStart(2, '0'),
            String(now.getMinutes()).padStart(2, '0'),
        ].join(':');
    }

    /* ================= NOTIFIKASI ================= */

    function notify(message) {
        const toast = $('#paToast');

        if (!toast) return;

        toast.textContent = message;
        toast.classList.add('show');

        clearTimeout(toastTimer);

        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    function tampilHasilScan(message, success) {
        const element = $('#paScanResult');

        if (!element) return;

        element.textContent = message;
        element.className =
            `pa-scan-result ${success ? 'is-ok' : 'is-error'}`;
    }

    /* ================= DATA SISWA ================= */

    async function loadStudents() {
        try {
            const result = await apiRequest(API.siswa);

            students = Array.isArray(result.data)
                ? result.data.map((student) => {
                    const riwayatKelas = Array.isArray(student.riwayat_kelas)
                        ? student.riwayat_kelas
                        : [];

                    const kelasAktif = riwayatKelas[0] || null;

                    return {
                        id_siswa: student.id_siswa,
                        nisn: student.nisn,
                        nama: student.nama,
                        id_kelas: kelasAktif?.id_kelas || null,
                        nama_kelas: kelasAktif?.kelas?.nama || '',
                    };
                }).filter((student) => student.id_siswa)
                : [];

            if (students.length === 0) {
                notify('Data siswa belum tersedia.');
                return;
            }

            // Ambil kelas dari riwayat kelas semester aktif.
            const daftarKelas = [
                ...new Map(
                    students
                        .filter((student) => student.id_kelas && student.nama_kelas)
                        .map((student) => [
                            student.id_kelas,
                            {
                                id: student.id_kelas,
                                nama: student.nama_kelas,
                            },
                        ])
                ).values(),
            ].sort((a, b) => a.nama.localeCompare(b.nama));

            if (classInput) {
                classInput.innerHTML = '<option value="">Pilih kelas</option>';

                daftarKelas.forEach((kelas) => {
                    const option = document.createElement('option');
                    option.value = kelas.id;
                    option.textContent = kelas.nama;
                    classInput.appendChild(option);
                });
            }

            console.log(`Berhasil memuat ${students.length} data siswa.`);
            console.log(`Berhasil memuat ${daftarKelas.length} kelas aktif.`);
        } catch (error) {
            console.error('Gagal mengambil data siswa:', error);
            notify(error.message || 'Gagal mengambil data siswa.');
        }
    }

    function cariSiswa(value) {
        const query = norm(value);

        if (!query) return [];

        return students.filter((student) => {
            return (
                norm(student.nama).includes(query) ||
                norm(student.nisn).includes(query)
            );
        });
    }

    function pilihSiswa(student) {
        if (!student) return;

        selectedStudent = student;

        if (nameInput) {
            nameInput.value = student.nama || '';
        }

        if (nisnInput) {
            nisnInput.value = student.nisn || '';
        }

        if (classInput) {
            classInput.value = student.id_kelas
                ? String(student.id_kelas)
                : '';

            classInput.disabled = Boolean(student.id_kelas);
        }

        notify(
            student.id_kelas
                ? `Data ${student.nama} (${student.nama_kelas}) berhasil dipilih.`
                : `${student.nama} belum memiliki kelas aktif.`
        );
    }

    function pastikanSiswaTerpilih() {
        const nama = norm(nameInput?.value);
        const idKelas = classInput?.value;

        if (!nama) {
            throw new Error('Nama siswa wajib diisi.');
        }

        if (!idKelas) {
            throw new Error('Pilih kelas terlebih dahulu.');
        }

        // Jika siswa dipilih melalui pencarian database.
        if (
            selectedStudent &&
            norm(selectedStudent.nama) === nama
        ) {
            if (!selectedStudent.id_kelas) {
                throw new Error(
                    'Siswa belum memiliki kelas aktif. Presensi manual tidak dapat dilakukan.'
                );
            }

            if (String(selectedStudent.id_kelas) !== String(idKelas)) {
                throw new Error('Kelas tidak sesuai dengan data siswa.');
            }

            return selectedStudent;
        }

        // Jika nama dan kelas diisi secara manual.
        const hasil = students.filter((student) =>
            norm(student.nama) === nama &&
            String(student.id_kelas) === String(idKelas)
        );

        if (hasil.length === 1) {
            return hasil[0];
        }

        if (hasil.length > 1) {
            throw new Error(
                'Terdapat beberapa siswa dengan nama dan kelas yang sama. Gunakan pencarian NISN.'
            );
        }

        const siswaDitemukan = students.some(
            (student) => norm(student.nama) === nama
        );

        if (siswaDitemukan) {
            throw new Error(
                'Kelas yang dipilih tidak sesuai dengan kelas aktif siswa tersebut.'
            );
        }

        throw new Error(
            'Nama siswa tidak ditemukan dalam data siswa aktif.'
        );
    }

    if (headerSearch) {
        headerSearch.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;

            event.preventDefault();

            const hasil = cariSiswa(headerSearch.value);

            if (hasil.length === 0) {
                selectedStudent = null;
                notify('Siswa tidak ditemukan.');
                return;
            }

            if (hasil.length > 1) {
                notify('Hasil lebih dari satu. Masukkan NISN yang lebih spesifik.');
                return;
            }

            pilihSiswa(hasil[0]);
        });
    }

    if (nameInput) {
        nameInput.addEventListener('input', () => {
            if (
                selectedStudent &&
                norm(nameInput.value) !== norm(selectedStudent.nama)
            ) {
                selectedStudent = null;

                if (nisnInput) {
                    nisnInput.value = '';
                }

                if (classInput) {
                    classInput.disabled = false;
                    classInput.value = '';
                }
            }
        });
    }

    /* ================= STATUS MANUAL ================= */

    // Backend manual hanya menerima Hadir, Izin, dan Sakit.
    if (statusInput) {
        [...statusInput.options].forEach((option) => {
            if (['Telat', 'Alpa', 'Terlambat'].includes(option.value)) {
                option.remove();
            }
        });
    }

    /* ================= UPLOAD FILE ================= */

    if (fileInput && fileName) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                fileName.textContent = '';
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'application/pdf',
            ];

            if (!allowedTypes.includes(file.type)) {
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

    /* ================= PRESENSI MANUAL ================= */

    if (manualForm) {
        manualForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = manualForm.querySelector(
                'button[type="submit"]'
            );

            if (submitButton?.disabled) return;

            try {
                const student = pastikanSiswaTerpilih();
                const status = statusInput?.value;

                if (!status) {
                    throw new Error('Pilih status presensi terlebih dahulu.');
                }

                if (!['Hadir', 'Izin', 'Sakit'].includes(status)) {
                    throw new Error('Status presensi manual tidak valid.');
                }

                const formData = new FormData();
        
                formData.append('id_siswa', student.id_siswa);
                formData.append('id_kelas', student.id_kelas);
                formData.append('status', status);
                formData.append('waktu_presensi', getCurrentTime());

                if (fileInput?.files[0]) {
                    formData.append('file_bukti', fileInput.files[0]);
                }

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Menyimpan...';
                }

                const result = await apiRequest(API.manual, {
                    method: 'POST',
                    body: formData,
                });

                notify(result.message || 'Presensi berhasil dicatat.');

                manualForm.reset();
                selectedStudent = null;

                if (nisnInput) nisnInput.value = '';
                if (fileName) fileName.textContent = '';

                if (classInput) {
                    classInput.disabled = false;
                    classInput.value = '';
                }
            } catch (error) {
                console.error('Presensi manual gagal:', error);
                notify(error.message || 'Gagal menyimpan presensi.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit';
                }
            }
        });
    }

    /* ================= SCAN QR CODE ================= */

    async function onScanSuccess(decodedText) {
        const now = Date.now();

        if (
            decodedText === lastScanValue &&
            now - lastScanAt < 4000
        ) {
            return;
        }

        lastScanValue = decodedText;
        lastScanAt = now;

        try {
            // Kirim isi QR asli. Jangan diubah menjadi NISN.
            const result = await apiRequest(API.presensi, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    metode: 'QR',
                    kode_qr: decodedText.trim(),
                }),
            });

            tampilHasilScan(
                result.message || 'Presensi berhasil dicatat.',
                true
            );

            notify(result.message || 'Presensi berhasil dicatat.');
        } catch (error) {
            console.error('Presensi QR gagal:', error);

            tampilHasilScan(
                error.message || 'Presensi QR gagal.',
                false
            );

            notify(error.message || 'Presensi QR gagal.');
        }
    }

    /* ================= KAMERA ================= */

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

        const startButton = $('#paStartScan');
        const stopButton = $('#paStopScan');

        if (!window.isSecureContext) {
            notify('Kamera hanya dapat digunakan melalui localhost atau HTTPS.');
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            notify('Browser tidak mendukung akses kamera.');
            return;
        }

        if (startButton) {
            startButton.disabled = true;
            startButton.textContent = 'Menghubungkan kamera...';
        }

        updateCameraPlaceholder(
            'Menghubungkan kamera...',
            'Silakan izinkan akses kamera pada browser.'
        );

        try {
            const cameras = await Html5Qrcode.getCameras();

            if (!cameras || cameras.length === 0) {
                throw new Error('Kamera tidak ditemukan.');
            }

            const selectedCamera =
                cameras.find((camera) =>
                    /back|rear|environment|belakang/i.test(camera.label)
                ) || cameras[0];

            scanner = new Html5Qrcode('paReader', {
                verbose: false,
            });

            await scanner.start(
                selectedCamera.id,
                {
                    fps: 10,
                    qrbox: (width, height) => {
                        const size = Math.floor(
                            Math.min(width, height) * 0.7
                        );

                        return {
                            width: size,
                            height: size,
                        };
                    },
                    aspectRatio: 16 / 9,
                },
                onScanSuccess,
                () => {}
            );

            scannerRunning = true;

            const placeholder = $('#paCameraPlaceholder');

            if (placeholder) {
                placeholder.hidden = true;
            }

            if (startButton) {
                startButton.disabled = true;
                startButton.textContent = 'Kamera Aktif';
            }

            if (stopButton) {
                stopButton.disabled = false;
            }

            notify('Kamera berhasil diaktifkan.');
        } catch (error) {
            console.error('Error scanner:', error);

            scannerRunning = false;

            if (scanner) {
                try {
                    await scanner.clear();
                } catch (clearError) {
                    console.warn(clearError);
                }

                scanner = null;
            }

            updateCameraPlaceholder(
                'Kamera tidak dapat diakses',
                'Periksa izin kamera browser dan gunakan localhost atau HTTPS.'
            );

            if (startButton) {
                startButton.disabled = false;
                startButton.textContent = 'Mulai Scan';
            }

            if (stopButton) {
                stopButton.disabled = true;
            }

            if (error.name === 'NotAllowedError') {
                notify('Akses kamera ditolak.');
            } else if (error.name === 'NotFoundError') {
                notify('Kamera tidak ditemukan.');
            } else if (error.name === 'NotReadableError') {
                notify('Kamera sedang digunakan aplikasi lain.');
            } else {
                notify(error.message || 'Gagal membuka kamera.');
            }
        }
    }

    async function stopScanner() {
        const startButton = $('#paStartScan');
        const stopButton = $('#paStopScan');

        try {
            if (scanner && scannerRunning) {
                await scanner.stop();
            }

            if (scanner) {
                await scanner.clear();
            }
        } catch (error) {
            console.error('Error menghentikan kamera:', error);
        } finally {
            scannerRunning = false;
            scanner = null;
            lastScanValue = '';
            lastScanAt = 0;

            updateCameraPlaceholder(
                'Kamera belum diaktifkan',
                'Tekan tombol Mulai Scan untuk membuka kamera.'
            );

            if (startButton) {
                startButton.disabled = false;
                startButton.textContent = 'Mulai Scan';
            }

            if (stopButton) {
                stopButton.disabled = true;
            }

            notify('Pemindai kamera dihentikan.');
        }
    }

    $('#paStartScan')?.addEventListener('click', startScanner);
    $('#paStopScan')?.addEventListener('click', stopScanner);

    window.addEventListener('pagehide', () => {
        if (scanner && scannerRunning) {
            scanner.stop().catch((error) => console.warn(error));
        }
    });


    /* ================= INISIALISASI ================= */

    loadStudents();
}
