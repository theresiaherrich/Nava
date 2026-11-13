
// ==========================================================
// PRESENSI SISWA
// Frontend only.
// Integrasi backend ditandai dengan TODO API.
// ==========================================================

import QRCode from 'qrcode';

/* ============ KONFIGURASI ============ */

// Titik tengah lapangan SMAN 1 Malang
const SEKOLAH = {
    lat: -7.976774,
    lng: 112.635085,
    radius: 50
};

// Batas akurasi GPS
const MAX_AKURASI = 50;

// Aturan waktu presensi
const BATAS_MULAI = '06.00';
const BATAS_TEPAT_WAKTU = '07.00';
const BATAS_TERLAMBAT = '07.30';

// Aturan langsung muncul saat halaman dibuka
const ATURAN_OTOMATIS = true;

/*
 * TODO API:
 * Ganti data contoh ini dengan data siswa yang sedang login.
 */
const SISWA = {
    id: '245150600',
    nama: 'Mutiara Bintang',
    kelas: 'XI MIPA 2'
};

/*
 * TODO API:
 * Ganti dengan data riwayat presensi dari backend.
 */
let riwayat = [
    {
        tanggal: '2026-10-14',
        items: [
            { status: 'hadir', jam: '06.45', metode: 'QR Code' }
        ]
    },
    {
        tanggal: '2026-10-13',
        items: [
            { status: 'terlambat', jam: '07.15', metode: 'QR Code' }
        ]
    },
    {
        tanggal: '2026-10-12',
        items: [
            { status: 'hadir', jam: '06.45', metode: 'QR Code' }
        ]
    },
    {
        tanggal: '2026-10-11',
        items: [
            { status: 'izin', jam: '-', metode: 'Surat Izin' }
        ]
    }
];

/* ============ HELPER ============ */

const $ = (id) => document.getElementById(id);

const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
}[char]));

const norm = (value) =>
    String(value ?? '').trim().replace(/\s+/g, ' ').toLowerCase();

const fmtTanggal = (iso) => {
    const [year, month, day] = iso.split('-').map(Number);

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(new Date(year, month - 1, day));
};

const hariIni = () => {
    const now = new Date();

    return [
        now.getFullYear(),
        String(now.getMonth() + 1).padStart(2, '0'),
        String(now.getDate()).padStart(2, '0')
    ].join('-');
};

const jamSekarang = () => {
    const now = new Date();

    return `${String(now.getHours()).padStart(2, '0')}.${String(now.getMinutes()).padStart(2, '0')}`;
};

const waktuKeMenit = (waktu) => {
    const [jam, menit] = waktu.split('.').map(Number);
    return jam * 60 + menit;
};

/* ============ VALIDASI ELEMEN ============ */

const elemenWajib = [
    'psNamaHeader',
    'psKelasHeader',
    'psAvatar',
    'psQrImage',
    'psQrLoading',
    'psQrLarge',
    'psAttendanceForm',
    'psNama',
    'psKelas',
    'psLocation',
    'psLocationText',
    'psSubmit',
    'psFeedback',
    'psSearch',
    'psSearchReset',
    'psHistory',
    'psRulesModal'
];

const elemenTidakAda = elemenWajib.filter((id) => !$(id));

if (elemenTidakAda.length > 0) {
    console.error(
        '[Presensi Siswa] Elemen HTML tidak ditemukan:',
        elemenTidakAda
    );
}

/* ============ PROFIL SISWA ============ */

if ($('psNamaHeader')) {
    $('psNamaHeader').textContent = SISWA.nama;
}

if ($('psKelasHeader')) {
    $('psKelasHeader').textContent = SISWA.kelas;
}

if ($('psAvatar')) {
    $('psAvatar').textContent = SISWA.nama.charAt(0).toUpperCase();
}

if ($('psNama')) {
    $('psNama').value = SISWA.nama;
    $('psNama').readOnly = true;
}

if ($('psKelas')) {
    $('psKelas').value = SISWA.kelas;
    $('psKelas').readOnly = true;
}

/* ============ MODAL ============ */

const openModal = (modal) => {
    if (!modal) return;

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
};

const closeModal = (modal) => {
    if (!modal) return;

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
};

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-open-modal]');

    if (opener) {
        event.preventDefault();

        const modal = $(opener.dataset.openModal);

        if (modal) openModal(modal);
        return;
    }

    const closer = event.target.closest('[data-close-modal]');

    if (closer) {
        closeModal($(closer.dataset.closeModal));
        return;
    }

    if (event.target.classList?.contains('ps-modal')) {
        closeModal(event.target);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.ps-modal.is-open').forEach(closeModal);
    }
});

/* ============ TAB ATURAN ============ */

document.querySelectorAll('.ps-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.ps-tab').forEach((item) => {
            const aktif = item === tab;
            const panel = $(item.dataset.tab);

            item.classList.toggle('is-active', aktif);
            item.setAttribute('aria-selected', aktif ? 'true' : 'false');

            if (panel) panel.hidden = !aktif;
        });
    });
});

/* ============ QR CODE ============ */

async function buatQR() {
    const gambarKecil = $('psQrImage');
    const gambarBesar = $('psQrLarge');
    const loading = $('psQrLoading');

    if (!gambarKecil || !gambarBesar || !loading) return;

    try {
        // Tampilkan status loading
        loading.hidden = false;
        loading.textContent = 'Menyiapkan QR Code...';

        gambarKecil.hidden = true;

        /*
         * FRONTEND DEMO:
         * QR dibuat otomatis agar tampilan frontend
         * tetap bisa diuji tanpa menunggu backend.
         *
         * Jika backend menyediakan token, token tersebut
         * akan digunakan secara otomatis.
         *
         * Jangan gunakan ID atau NISN mentah sebagai QR.
         */

        const tokenQR =
            window.SIAKAD_PRESENSI_QR_TOKEN ||
            'DEMO-PRESENSI-SISWA';

        // Membuat QR Code
        const urlQR = await QRCode.toDataURL(
            String(tokenQR),
            {
                width: 400,
                margin: 2,
                errorCorrectionLevel: 'H',
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }
        );

        // QR ukuran normal
        gambarKecil.src = urlQR;
        gambarKecil.alt = 'QR Code Presensi Siswa';
        gambarKecil.hidden = false;

        // QR ukuran besar untuk modal
        gambarBesar.src = urlQR;
        gambarBesar.alt = 'QR Code Presensi Siswa';

        // Sembunyikan loading
        loading.hidden = true;

        console.info('[Presensi Siswa] QR Code berhasil dibuat.');

    } catch (error) {
        gambarKecil.hidden = true;
        loading.hidden = false;
        loading.textContent = 'QR gagal dibuat';

        console.error(
            '[Presensi Siswa] Gagal membuat QR Code:',
            error
        );
    }
}

/* ============ LOKASI GPS ============ */

let lokasiSesuai = false;
let posisiTerakhir = null;
let watchId = null;

const jarakMeter = (titikA, titikB) => {
    const R = 6371000;

    const rad = (angka) => angka * Math.PI / 180;

    const dLat = rad(titikB.lat - titikA.lat);
    const dLng = rad(titikB.lng - titikA.lng);

    const h =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(rad(titikA.lat)) *
        Math.cos(rad(titikB.lat)) *
        Math.sin(dLng / 2) ** 2;

    return 2 * R * Math.asin(Math.sqrt(h));
};

const fmtJarak = (meter) => {
    if (meter >= 1000) {
        return `${(meter / 1000).toFixed(1).replace('.', ',')} km`;
    }

    return `${Math.round(meter)} m`;
};

function setLokasi(state, teks) {
    lokasiSesuai = state === 'ok';

    if ($('psLocation')) {
        $('psLocation').dataset.state = state;
    }

    if ($('psLocationText')) {
        $('psLocationText').textContent = teks;
    }

    if ($('psSubmit')) {
        $('psSubmit').disabled = !lokasiSesuai;
    }
}

function nilaiPosisi(posisi) {
    const lat = posisi.coords.latitude;
    const lng = posisi.coords.longitude;
    const akurasi = posisi.coords.accuracy;

    const jarak = jarakMeter(
        { lat, lng },
        { lat: SEKOLAH.lat, lng: SEKOLAH.lng }
    );

    posisiTerakhir = {
        lat,
        lng,
        akurasi,
        jarak
    };

    console.info(
        `[Presensi] GPS: ${lat}, ${lng} | Akurasi: ±${Math.round(akurasi)} m | Jarak: ${Math.round(jarak)} m`
    );

    if (akurasi > MAX_AKURASI) {
        setLokasi(
            'error',
            `GPS kurang akurat (±${Math.round(akurasi)} m). Maksimal 50 meter.`
        );
        return;
    }

    if (jarak > SEKOLAH.radius) {
        setLokasi(
            'out',
            `Di luar area sekolah (${fmtJarak(jarak)} dari titik lapangan).`
        );
        return;
    }

    setLokasi(
        'ok',
        `Lokasi sesuai area sekolah (${Math.round(jarak)} m dari titik lapangan).`
    );
}

function galatLokasi(error) {
    posisiTerakhir = null;

    if (error.code === 1) {
        setLokasi(
            'error',
            'Izin lokasi ditolak. Izinkan akses lokasi melalui pengaturan browser.'
        );
    } else if (error.code === 2) {
        setLokasi(
            'error',
            'Lokasi tidak tersedia. Aktifkan GPS atau koneksi internet.'
        );
    } else {
        setLokasi(
            'error',
            'Waktu pencarian lokasi habis. Silakan coba lagi.'
        );
    }
}

function mulaiLokasi() {
    if (!window.isSecureContext) {
        setLokasi(
            'error',
            'Lokasi hanya dapat digunakan melalui HTTPS atau localhost.'
        );
        return;
    }

    if (!navigator.geolocation) {
        setLokasi(
            'error',
            'Browser tidak mendukung layanan lokasi.'
        );
        return;
    }

    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }

    setLokasi('checking', 'Memeriksa lokasi…');

    watchId = navigator.geolocation.watchPosition(
        nilaiPosisi,
        galatLokasi,
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

const posisiBaru = () => new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
        reject(new Error('Browser tidak mendukung GPS.'));
        return;
    }

    navigator.geolocation.getCurrentPosition(
        resolve,
        reject,
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
});

// Perubahan izin lokasi dari browser
if (navigator.permissions?.query) {
    navigator.permissions
        .query({ name: 'geolocation' })
        .then((izin) => {
            izin.addEventListener('change', mulaiLokasi);
        })
        .catch(() => {});
}

/* ============ STATUS PRESENSI ============ */

const labelStatus = {
    hadir: 'Hadir',
    terlambat: 'Terlambat',
    alpa: 'Alpa',
    izin: 'Izin',
    sakit: 'Sakit'
};

function tentukanStatus(jam) {
    const waktu = waktuKeMenit(jam);
    const mulai = waktuKeMenit(BATAS_MULAI);
    const tepatWaktu = waktuKeMenit(BATAS_TEPAT_WAKTU);
    const terlambat = waktuKeMenit(BATAS_TERLAMBAT);

    if (waktu < mulai) {
        return {
            status: null,
            pesan: 'Presensi baru dibuka mulai pukul 06.00 WIB.'
        };
    }

    if (waktu <= tepatWaktu) {
        return {
            status: 'hadir',
            pesan: ''
        };
    }

    if (waktu <= terlambat) {
        return {
            status: 'terlambat',
            pesan: ''
        };
    }

    return {
        status: null,
        pesan: 'Batas presensi sudah lewat. Mulai pukul 07.31 WIB, status Alpa ditangani oleh sistem.'
    };
}

/* ============ RIWAYAT ============ */

function barisHTML(item, terbaru) {
    const status = String(item.status || '').toLowerCase();
    const label = labelStatus[status] || esc(status);

    const bagian = [
        `<span class="ps-st-${esc(status)}">${label}</span>`,
        `<span>${esc(item.jam || '-')}</span>`
    ];

    if (item.metode) {
        bagian.push(`<span>${esc(item.metode)}</span>`);
    }

    return `
        <div class="ps-row${terbaru ? ' is-latest' : ''}">
            ${bagian.join('<i class="ps-dot"></i>')}
        </div>
    `;
}

function renderRiwayat(tanggal = '') {
    const box = $('psHistory');

    if (!box) return;

    const data = [...riwayat].sort((a, b) =>
        b.tanggal.localeCompare(a.tanggal)
    );

    const hasil = data
        .filter((hari) => !tanggal || hari.tanggal === tanggal);

    if (hasil.length === 0) {
        box.innerHTML = `
            <p class="ps-empty">
                Tidak ada presensi pada tanggal itu.
            </p>
        `;
        return;
    }

    box.innerHTML = hasil.map((hari, index) => `
        <div class="ps-day${hari.items.length > 1 ? ' ps-day--multi' : ''}">
            <p class="ps-day-title">${fmtTanggal(hari.tanggal)}</p>

            <div class="ps-day-rows">
                ${hari.items.map((item) =>
                    barisHTML(item, index === 0)
                ).join('')}
            </div>
        </div>
    `).join('');
}

if ($('psSearch')) {
    $('psSearch').addEventListener('input', (event) => {
        const tanggal = event.target.value;

        $('psSearchReset').hidden = !tanggal;
        renderRiwayat(tanggal);
    });
}

if ($('psSearchReset')) {
    $('psSearchReset').addEventListener('click', () => {
        $('psSearch').value = '';
        $('psSearchReset').hidden = true;

        renderRiwayat();
    });
}

/* ============ FEEDBACK ============ */

function feedback(pesan, berhasil = false) {
    const el = $('psFeedback');

    if (!el) return;

    el.textContent = pesan;
    el.className = `ps-feedback ${berhasil ? 'is-ok' : 'is-err'}`;
}

/* ============ SUBMIT PRESENSI ============ */

if ($('psAttendanceForm')) {
    $('psAttendanceForm').addEventListener('submit', async (event) => {
        event.preventDefault();

        const tombol = $('psSubmit');

        if (tombol.disabled) {
            feedback('Lokasi belum memenuhi ketentuan presensi.', false);
            return;
        }

        const nama = $('psNama');
        const kelas = $('psKelas');

        const namaOk = norm(nama.value) === norm(SISWA.nama);
        const kelasOk = norm(kelas.value) === norm(SISWA.kelas);

        nama.setAttribute('aria-invalid', namaOk ? 'false' : 'true');
        kelas.setAttribute('aria-invalid', kelasOk ? 'false' : 'true');

        if (!namaOk || !kelasOk) {
            feedback('Data nama atau kelas tidak sesuai akun siswa.', false);
            return;
        }

        tombol.disabled = true;
        tombol.textContent = 'Memproses...';

        try {
            // Ambil posisi GPS terbaru sebelum proses presensi
            const posisi = await posisiBaru();

            nilaiPosisi(posisi);

            if (!lokasiSesuai || !posisiTerakhir) {
                feedback(
                    'Lokasi belum memenuhi ketentuan. Presensi tidak dapat dikirim.',
                    false
                );
                return;
            }

            const tanggal = hariIni();

            const sudahPresensi = riwayat.some((hari) =>
                hari.tanggal === tanggal &&
                hari.items.some((item) =>
                    ['hadir', 'terlambat'].includes(item.status)
                )
            );

            if (sudahPresensi) {
                feedback('Kamu sudah melakukan presensi hari ini.', false);
                return;
            }

            const jam = jamSekarang();
            const hasilStatus = tentukanStatus(jam);

            if (!hasilStatus.status) {
                feedback(hasilStatus.pesan, false);
                return;
            }

            /*
             * TODO API:
             *
             * Hubungkan ke endpoint backend setelah route tersedia.
             * Data yang perlu dikirim:
             * - token/identitas autentikasi dari sesi login
             * - latitude
             * - longitude
             * - accuracy
             * - metode presensi
             *
             * Backend wajib memvalidasi ulang lokasi, radius,
             * waktu, role, dan duplikasi presensi.
             *
             * Jangan menganggap penyimpanan lokal ini sebagai
             * presensi resmi sebelum API backend terhubung.
             */

            feedback(
                'Tampilan frontend siap. Presensi belum tersimpan ke database karena API backend belum dihubungkan.',
                false
            );

        } catch (error) {
            galatLokasi(error);

            feedback(
                'Lokasi tidak dapat dibaca. Periksa izin GPS dan coba lagi.',
                false
            );

            console.error('[Presensi] Gagal mengambil GPS:', error);

        } finally {
            tombol.textContent = 'Submit';

            // Tombol hanya aktif kembali jika lokasi valid
            tombol.disabled = !lokasiSesuai;
        }
    });
}

/* ============ MULAI HALAMAN ============ */

// Buat QR Code otomatis
buatQR();

// Jalankan pemeriksaan GPS
mulaiLokasi();

// Tampilkan riwayat presensi
renderRiwayat();

// Aturan otomatis muncul saat halaman Presensi dibuka
if (ATURAN_OTOMATIS) {
    openModal($('psRulesModal'));
}
