
/* ==========================================================
   PRESENSI DASHBOARD
   File: resources/js/presensi/guru.js

   Digunakan bersama oleh:
   - Guru Wali Kelas
   - Kurikulum
   - Budaya Positif

   MODE SAAT INI:
   - Data masih menggunakan dummy.
   - Struktur data dummy disesuaikan dengan backend.
   - Belum mengirim request API.
   - Data tambahan biodata tetap berupa simulasi.
   ========================================================== */

const $ = (id) => document.getElementById(id);

/* ==========================================================
   1. HELPER
   ========================================================== */

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
}[c]));

const iso = (d) =>
    `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

const parseIso = (tanggal) => {
    const [tahun, bulan, hari] = tanggal.split('-').map(Number);
    return new Date(tahun, bulan - 1, hari);
};

const fmt = (tanggal, opsi) =>
    new Intl.DateTimeFormat('id-ID', opsi).format(parseIso(tanggal));

const tglPanjang = (tanggal) =>
    fmt(tanggal, {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

const tglHari = (tanggal) =>
    fmt(tanggal, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

const tglPendek = (tanggal) =>
    fmt(tanggal, {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });

const norm = (teks) =>
    String(teks ?? '').trim().toLowerCase();

const sekarang = new Date();
const TGL_INI = iso(sekarang);

/* ==========================================================
   2. IDENTITAS PENGGUNA
   ========================================================== */

const halamanPresensi = document.querySelector('.gw-page');

const ROLE_AKTIF = halamanPresensi?.dataset.role || 'guru';

const PROFIL = {
    nama: halamanPresensi?.dataset.profileName || 'Nama Pengguna',
    jabatan: halamanPresensi?.dataset.profileLabel || 'Pengguna',
    detail: halamanPresensi?.dataset.profileDetail || 'SMAN 1 Malang'
};

const KELAS_AKTIF = ROLE_AKTIF === 'guru'
    ? 'XI MIPA 2'
    : 'SMAN 1 Malang';

const TAHUN_AJARAN = '2026/2027';
const SEMESTER = 'Ganjil';

/* ==========================================================
   3. DATA SISWA DUMMY
   Struktur identitas mengikuti backend.
   Data tambahan hanya untuk kebutuhan tampilan.
   ========================================================== */

let SISWA = [
    ['Andini Putri', '245150600', 'Perempuan'],
    ['Bintang Mutiara', '245150601', 'Laki-laki'],
    ['Mutiara Lautan', '245150602', 'Perempuan'],
    ['Lautan Biru', '245150603', 'Laki-laki'],
    ['Angkasa Raya', '245150604', 'Laki-laki'],
    ['Putri Andromeda', '245150605', 'Perempuan'],
    ['Putra Mahkota', '245150606', 'Laki-laki'],
    ['Peri Indah', '245150607', 'Perempuan'],
    ['Bintang Samudra', '245150608', 'Laki-laki'],
    ['Nusa Cendana', '245150609', 'Perempuan'],
    ['Rama Segara', '245150610', 'Laki-laki']
].map(([nama, nisn, gender], i) => ({
    // Identitas simulasi mengikuti struktur backend.
    // ID ini bukan ID siswa asli dari database.
    id_siswa: i + 1,
    nis: null,
    nisn,
    nama,
    jenis_kelamin: gender,

    // Alias untuk kebutuhan tampilan lama.
    gender,
    kelas: KELAS_AKTIF === 'SMAN 1 Malang'
        ? 'XI MIPA 2'
        : KELAS_AKTIF,

    // Data tambahan berikut masih dummy.
    ttl: i === 0
        ? 'Malang, 17 Agustus 2009'
        : `Malang, ${i + 1} Januari 2009`,

    alamat: i === 0
        ? 'Jl. Semesta No. 09 Kec. Lowokwaru Kota Malang'
        : 'Kota Malang',

    hp: i === 0
        ? '088556677334'
        : `0812345${6000 + i}`,

    ayah: 'Belum tersedia',
    ibu: 'Belum tersedia',
    hpOrtu: 'Belum tersedia'
}));

/* ==========================================================
   4. DATA PRESENSI DUMMY
   Struktur mengikuti field backend Presensi.
   ========================================================== */

// Urutan:
// [status, jam, metode, keterangan]

const HARI_INI = [
    ['Terlambat', '07.28 WIB', 'QR Code'],
    ['Terlambat', '07.10 WIB', 'QR Code'],
    ['Hadir', '07.00 WIB', 'QR Code'],
    ['Hadir', '06.58 WIB', 'QR Code'],
    ['Hadir', '06.56 WIB', 'QR Code'],
    ['Hadir', '06.56 WIB', 'QR Code'],
    ['Hadir', '06.56 WIB', 'QR Code'],
    ['Hadir', '06.56 WIB', 'QR Code'],
    ['Hadir', '06.56 WIB', 'QR Code'],
    ['Sakit', '-', 'Geofencing', 'Surat sakit dari orang tua'],
    ['Alpa', '-', '-', 'Tanpa keterangan']
];

const PERSEN_ALPA = [0, 2, 0, 10, 5, 1, 0, 0, 0, 3, 4];
const PERSEN_IZIN = [0, 3, 7, 20, 16, 0, 2, 0, 0, 5, 3];

// ID simulasi, bukan ID dari database.
let idPresensiDummy = 1;

// Mengubah format jam tampilan menjadi format waktu backend.
const jamKeApi = (jam) => {
    if (!jam || jam === '-') return null;

    const cocok = jam.match(/^(\d{2})\.(\d{2})/);

    return cocok
        ? `${cocok[1]}:${cocok[2]}:00`
        : null;
};

// Mengubah metode tampilan menjadi nilai metode API.
const metodeKeApi = (metode) => {
    if (metode === 'QR Code') return 'QR';
    if (metode === 'Geofencing') return 'Geofencing';

    return null;
};

function buatRiwayatDummy() {
    const hasil = [];

    for (let k = 1; k <= 120; k++) {
        const tanggal = new Date(sekarang);
        tanggal.setDate(tanggal.getDate() - k);

        // Tidak membuat presensi pada Sabtu dan Minggu.
        if (tanggal.getDay() === 0 || tanggal.getDay() === 6) {
            continue;
        }

        SISWA.forEach((s, i) => {
            const angka = (((i + 3) * 7919 + k * 104729) % 1000) / 10;

            let status = 'Hadir';
            let jam = `06.${String(40 + ((k * 3 + i) % 19)).padStart(2, '0')} WIB`;
            let metode = 'QR Code';
            let keterangan = '-';

            if (angka < PERSEN_ALPA[i]) {
                status = 'Alpa';
                jam = '-';
                metode = '-';
                keterangan = 'Tanpa keterangan';
            } else if (angka < PERSEN_ALPA[i] + PERSEN_IZIN[i]) {
                status = 'Izin';
                jam = '-';
                metode = 'Geofencing';
                keterangan = 'Surat izin dari orang tua';
            } else if (
                angka < PERSEN_ALPA[i] + PERSEN_IZIN[i] + 12
            ) {
                status = 'Terlambat';
                jam = `07.${String(5 + ((k + i) % 24)).padStart(2, '0')} WIB`;
            }

            hasil.push({
                // Field identitas sesuai backend.
                id_presensi: idPresensiDummy++,
                id_siswa: s.id_siswa,
                nisn: s.nisn,

                // Field presensi sesuai backend.
                tanggal: iso(tanggal),
                status,
                waktu_presensi: jamKeApi(jam),
                metode_api: metodeKeApi(metode),
                file_bukti: null,
                keterangan,

                // Alias untuk kebutuhan tampilan lama.
                jam,
                metode,
                berkas: '-'
            });
        });
    }

    return hasil;
}

let PRESENSI = [
    ...SISWA.map((s, i) => {
        const jam = HARI_INI[i][1];
        const metode = HARI_INI[i][2];

        return {
            // Field identitas sesuai backend.
            id_presensi: idPresensiDummy++,
            id_siswa: s.id_siswa,
            nisn: s.nisn,

            // Field presensi sesuai backend.
            tanggal: TGL_INI,
            status: HARI_INI[i][0],
            waktu_presensi: jamKeApi(jam),
            metode_api: metodeKeApi(metode),
            file_bukti: null,
            keterangan: HARI_INI[i][3] || '-',

            // Alias untuk kebutuhan tampilan lama.
            jam,
            metode,
            berkas: '-'
        };
    }),

    ...buatRiwayatDummy()
];

/* ==========================================================
   5. STATE
   ========================================================== */

const state = {
    mode: 'satu',
    tanggal: TGL_INI,
    awal: TGL_INI,
    akhir: TGL_INI,

    view: 'presensi',

    q: '',
    filters: {
        kelas: [],
        status: [],
        metode: [],
        tanggalAwal: '',
        tanggalAkhir: ''
    },
    urut: 'terbaru',
    rekap: '',

    aktif: null
};

let uiRentang = false;

/* ==========================================================
   6. PENCARIAN DATA
   ========================================================== */

const cariSiswa = (idSiswa) =>
    SISWA.find(
        (s) => String(s.id_siswa) === String(idSiswa)
    );

const cariPresensi = (idPresensi) =>
    PRESENSI.find(
        (p) => String(p.id_presensi) === String(idPresensi)
    );

const dalamPeriode = (p) => {
    if (state.mode === 'satu') {
        return p.tanggal === state.tanggal;
    }

    return p.tanggal >= state.awal && p.tanggal <= state.akhir;
};

const multiHari = () =>
    state.mode === 'rentang' && state.awal !== state.akhir;

const cocokCari = (s) => {
    const q = norm(state.q);

    return !q || [
        s.nama,
        s.nisn,
        s.kelas
    ].some((v) => norm(v).includes(q));
};

// Membaca jam presensi menjadi menit.
const menit = (jam) => {
    const m = /^(\d{1,2})[.:](\d{2})/.exec(jam || '');

    return m
        ? Number(m[1]) * 60 + Number(m[2])
        : -1;
};

/* ==========================================================
   7. PROFIL PENGGUNA
   ========================================================== */

const elemenNama = $('gwGuruNama');
const elemenKelas = $('gwGuruKelas');
const elemenAvatar = $('gwGuruAvatar');

if (elemenNama) {
    elemenNama.textContent = PROFIL.nama;
}

if (elemenKelas) {
    elemenKelas.textContent =
        `${PROFIL.jabatan} • ${PROFIL.detail}`;
}

if (elemenAvatar) {
    elemenAvatar.textContent =
        PROFIL.nama.trim().charAt(0).toUpperCase() || 'P';
}

/* ==========================================================
   8. TABEL PRESENSI
   ========================================================== */

const ikonEdit = `
    <svg viewBox="0 0 24 24"
        width="13" height="13"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true">
        <path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/>
        <path d="m15 5 4 4"/>
    </svg>
`;

function renderTabel() {
    const data = PRESENSI
        .filter(dalamPeriode)
        .map((p) => ({
            p,
            s: cariSiswa(p.id_siswa)
        }))

        .filter(({ p, s }) => {
            if (!s) return false;

            const f = state.filters;

            const cocokKelas =
                f.kelas.length === 0 ||
                f.kelas.includes(s.kelas);

            const cocokStatus =
                f.status.length === 0 ||
                f.status.includes(p.status);

            const cocokMetode =
                f.metode.length === 0 ||
                f.metode.includes(p.metode);

            const cocokTanggal =
                (!f.tanggalAwal || p.tanggal >= f.tanggalAwal) &&
                (!f.tanggalAkhir || p.tanggal <= f.tanggalAkhir);

            return cocokKelas &&
                cocokStatus &&
                cocokMetode &&
                cocokTanggal &&
                cocokCari(s);
        })
        .sort((a, b) => {
            const selisih =
                b.p.tanggal.localeCompare(a.p.tanggal) ||
                menit(b.p.jam) - menit(a.p.jam);

            return state.urut === 'terlama'
                ? -selisih
                : selisih;
        });

    $('gwTabelPresensi').classList.toggle(
        'is-multi',
        multiHari()
    );

    $('gwBody').innerHTML = data.map(({ p, s }, i) => {
        const kelasStatus = p.status === 'Terlambat'
            ? 'telat'
            : p.status.toLowerCase();

        return `
            <tr>
                <td>${i + 1}</td>

                <td class="gw-col-tgl">
                    ${esc(tglPendek(p.tanggal))}
                </td>

                <td>${esc(s.nama)}</td>
                <td>${esc(s.nisn)}</td>
                <td>${esc(s.kelas)}</td>

                <td>
                    <span class="gw-badge gw-badge--${kelasStatus}">
                        ${esc(p.status)}
                    </span>
                </td>

                <td>${esc(p.jam)}</td>
                <td>${esc(p.metode)}</td>

                <td>
                    <button type="button"
                        class="gw-edit"
                        data-id-presensi="${esc(p.id_presensi)}"
                        aria-label="Edit presensi ${esc(s.nama)}">
                        ${ikonEdit}
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    return data.length;
}

function renderTabelDenganFilter() {
    const jumlah = renderTabel();

    if (state.view === 'presensi') {
        $('gwEmpty').hidden = jumlah > 0;
    }
}

function renderOpsiFilter(jenis) {
    const sumber = {
        kelas: [...new Set(
            SISWA.map((s) => s.kelas).filter((v) => v && v !== '-')
        )],
        status: [...new Set(
            PRESENSI.map((p) => p.status).filter(Boolean)
        )],
        metode: [...new Set(
            PRESENSI.map((p) => p.metode).filter((v) => v && v !== '-')
        )]
    };

    if (jenis === 'tanggal') {
        return `
            <div class="gw-filter-date">
                <label for="gwFilterTanggalAwal">Dari tanggal</label>
                <input type="date" id="gwFilterTanggalAwal"
                    value="${esc(state.filters.tanggalAwal)}">

                <label for="gwFilterTanggalAkhir">Sampai tanggal</label>
                <input type="date" id="gwFilterTanggalAkhir"
                    value="${esc(state.filters.tanggalAkhir)}">
            </div>
        `;
    }

    const terpilih = state.filters[jenis];

    return sumber[jenis].map((nilai) => `
        <label class="gw-filter-option">
            <input type="checkbox"
                value="${esc(nilai)}"
                ${terpilih.includes(nilai) ? 'checked' : ''}>
            <span>${esc(nilai)}</span>
        </label>
    `).join('');
}

/* ==========================================================
   9. TABEL REKAP
   ========================================================== */


function renderRekap() {
    const baris = SISWA
        .filter(cocokCari)
        .map((s) => {
            const riwayat = PRESENSI.filter(
                (p) =>
                    String(p.id_siswa) === String(s.id_siswa) &&
                    dalamPeriode(p)
            );

            // Kelompokkan catatan berdasarkan tanggal.
            const kelompokHarian = new Map();

            riwayat.forEach((p) => {
                if (!kelompokHarian.has(p.tanggal)) {
                    kelompokHarian.set(p.tanggal, []);
                }

                kelompokHarian.get(p.tanggal).push(p);
            });

            // Tentukan satu status untuk setiap hari.
            const rekapHarian = Array.from(
                kelompokHarian.values()
            ).map((catatan) => {
                // Jika ada Hadir atau Terlambat, hari dihitung Hadir.
                const adaHadir = catatan.some(
                    (p) => ['Hadir', 'Terlambat'].includes(p.status)
                );

                if (adaHadir) {
                    return 'Hadir';
                }

                // Jika tidak ada Hadir, gunakan catatan
                // dengan waktu kejadian paling akhir.
                const terakhir = [...catatan].sort((a, b) =>
                    String(b.waktu_presensi || '').localeCompare(
                        String(a.waktu_presensi || '')
                    )
                )[0];

                return terakhir.status;
            });

            const hadir = rekapHarian.filter(
                (status) => ['Hadir', 'Terlambat'].includes(status)
            ).length;

            const izin = rekapHarian.filter(
                (status) => status === 'Izin'
            ).length;

            const sakit = rekapHarian.filter(
                (status) => status === 'Sakit'
            ).length;

            const alpa = rekapHarian.filter(
                (status) => status === 'Alpa'
            ).length;

            const total = rekapHarian.length;

            return {
                s,
                hadir,
                izin,
                sakit,
                alpa,
                total,
                persen: total
                    ? Math.round((hadir / total) * 1000) / 10
                    : 0
            };
        })
        .filter((r) => r.total > 0)
        .filter((r) => {
            if (!state.rekap) return true;

            return state.rekap === 'rendah'
                ? r.persen < 75
                : r.persen >= 75;
        });

    $('gwRekapBody').innerHTML = baris.map((r, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${esc(r.s.nama)}</td>
            <td>${esc(r.s.nisn)}</td>
            <td>${esc(r.s.kelas)}</td>
            <td class="gw-num gw-num--hadir">${r.hadir}</td>
            <td class="gw-num gw-num--izin">${r.izin}</td>
            <td class="gw-num gw-num--sakit">${r.sakit}</td>
            <td class="gw-num gw-num--alpa">${r.alpa}</td>
            <td class="gw-pct${r.persen < 75 ? ' is-low' : ''}">
                ${r.persen}%
            </td>
        </tr>
    `).join('');

    return baris.length;
}


/* ==========================================================
   10. STATISTIK KEHADIRAN
   ========================================================== */

function renderStatistik() {
    const data = PRESENSI.filter(dalamPeriode);
    const totalSiswa = SISWA.length;

    const jumlah = (...status) =>
        data.filter((p) => status.includes(p.status)).length;

    const jumlahSiswa = (...status) =>
        new Set(
            data
                .filter((p) => status.includes(p.status))
                .map((p) => p.id_siswa)
        ).size;

    const persen = (nilai, total) =>
        total ? Math.round((nilai / total) * 100) : 0;

    const hadir = jumlah('Hadir');
    const terlambat = jumlah('Terlambat');
    const alpa = jumlah('Alpa');

    const dasar = hadir + terlambat + alpa;

    const persenHadir = dasar
        ? (hadir / dasar) * 100
        : 0;

    const persenTerlambat = dasar
        ? (terlambat / dasar) * 100
        : 0;

    $('gwDonut').style.setProperty(
        '--donut',
        dasar
            ? `conic-gradient(
                #1565c0 0 ${persenHadir}%,
                #fff500 ${persenHadir}% ${persenHadir + persenTerlambat}%,
                #dc137b ${persenHadir + persenTerlambat}% 100%
            )`
            : '#dde6f0'
    );

    $('gwPersen').textContent =
        `${persen(hadir + terlambat, dasar)}%`;

    const izinSiswa = jumlahSiswa('Izin');
    const alpaSiswa = jumlahSiswa('Alpa');

    $('gwIzinJumlah').textContent = izinSiswa;
    $('gwIzinTotal').textContent = totalSiswa;
    $('gwIzinBar').style.width =
        `${persen(izinSiswa, totalSiswa)}%`;
    $('gwIzinPersen').textContent =
        `${persen(izinSiswa, totalSiswa)}%`;

    $('gwAlpaJumlah').textContent = alpaSiswa;
    $('gwAlpaTotal').textContent = totalSiswa;
    $('gwAlpaBar').style.width =
        `${persen(alpaSiswa, totalSiswa)}%`;
    $('gwAlpaPersen').textContent =
        `${persen(alpaSiswa, totalSiswa)}%`;
}

/* ==========================================================
   11. TAMPILAN DAN TAB
   ========================================================== */

function renderMeta() {
    $('gwChip').textContent = multiHari()
        ? `${tglPanjang(state.awal)} – ${tglPanjang(state.akhir)}`
        : tglPanjang(
            state.mode === 'satu'
                ? state.tanggal
                : state.awal
        );

    $('gwSubtabs').hidden = state.mode !== 'rentang';

    if (state.mode !== 'rentang') {
        state.view = 'presensi';
    }

    document.querySelectorAll('.gw-subtab').forEach((button) => {
        const aktif = button.dataset.view === state.view;

        button.classList.toggle('is-active', aktif);
        button.setAttribute('aria-selected', String(aktif));
    });

    $('gwViewPresensi').hidden =
        state.view !== 'presensi';

    $('gwViewRekap').hidden =
        state.view !== 'rekap';

    $('gwFilterPresensi').hidden =
        state.view !== 'presensi';

    $('gwFilterRekap').hidden =
        state.view !== 'rekap';
}

function renderSemua() {
    renderMeta();

    const jumlahPresensi = renderTabel();
    const jumlahRekap = renderRekap();

    renderStatistik();

    $('gwEmpty').hidden =
        (state.view === 'presensi'
            ? jumlahPresensi
            : jumlahRekap) > 0;
}

/* ==========================================================
   12. PENCARIAN DAN FILTER
   ========================================================== */

const headerSearch = document.querySelector('[data-tb-search-input]');

headerSearch?.addEventListener('input', (e) => {
    state.q = e.target.value;
    renderSemua();
});

$('gwUrut').addEventListener('change', (e) => {
    state.urut = e.target.value;
    renderSemua();
});

$('gwRekapFilter').addEventListener('change', (e) => {
    state.rekap = e.target.value;
    renderSemua();
});

/* ==========================================================
   FILTER DROPDOWN RIWAYAT PRESENSI
   ========================================================== */

const filterDropdown = $('gwFilterDropdown');

document.querySelectorAll('.gw-filter-trigger').forEach((tombol) => {
    tombol.addEventListener('click', () => {
        const jenis = tombol.dataset.filter;

        const judul = {
            kelas: 'Filter Kelas',
            status: 'Filter Status',
            metode: 'Filter Metode',
            tanggal: 'Filter Tanggal'
        };

        $('gwFilterTitle').textContent = judul[jenis];
        $('gwFilterOptions').innerHTML = renderOpsiFilter(jenis);

        filterDropdown.hidden = false;
        filterDropdown.dataset.jenis = jenis;

        const posisi = tombol.getBoundingClientRect();

        const kiri = Math.max(
            12,
            Math.min(
                posisi.left,
                window.innerWidth - filterDropdown.offsetWidth - 12
            )
        );

        const atas = Math.max(
            12,
            Math.min(
                posisi.bottom + 6,
                window.innerHeight - filterDropdown.offsetHeight - 12
            )
        );

        filterDropdown.style.left = `${kiri}px`;
        filterDropdown.style.top = `${atas}px`;
    });
});

$('gwFilterClose').addEventListener('click', () => {
    filterDropdown.hidden = true;
});

$('gwFilterApply').addEventListener('click', () => {
    const jenis = filterDropdown.dataset.jenis;

    if (jenis === 'tanggal') {
        const awal = $('gwFilterTanggalAwal').value;
        const akhir = $('gwFilterTanggalAkhir').value;

        if (awal && akhir && akhir < awal) {
            pesan('Tanggal akhir tidak boleh lebih awal dari tanggal awal.', true);
            return;
        }

        state.filters.tanggalAwal = awal;
        state.filters.tanggalAkhir = akhir;
    } else {
        state.filters[jenis] = [
            ...$('gwFilterOptions').querySelectorAll(
                'input[type="checkbox"]:checked'
            )
        ].map((input) => input.value);
    }

    filterDropdown.hidden = true;
    renderTabelDenganFilter();
});

$('gwFilterReset').addEventListener('click', () => {
    const jenis = filterDropdown.dataset.jenis;

    if (jenis === 'tanggal') {
        state.filters.tanggalAwal = '';
        state.filters.tanggalAkhir = '';
    } else {
        state.filters[jenis] = [];
    }

    $('gwFilterOptions').innerHTML = renderOpsiFilter(jenis);
    renderTabelDenganFilter();
});

document.querySelectorAll('.gw-subtab').forEach((button) => {
    button.addEventListener('click', () => {
        state.view = button.dataset.view;
        renderSemua();
    });
});

/* ==========================================================
   13. FILTER TANGGAL
   ========================================================== */

function setPilihanTanggal(tanggal) {
    $('gwTanggal').value = tanggal.getDate();
    $('gwBulan').value = tanggal.getMonth() + 1;
    $('gwTahun').value = tanggal.getFullYear();
}

setPilihanTanggal(sekarang);

['gwAwal', 'gwAkhir'].forEach((id) => {
    const el = $(id);

    const perbaruiLabel = () => {
        $(`${id}Label`).textContent = el.value
            ? tglHari(el.value)
            : 'Pilih tanggal';
    };

    el.value = TGL_INI;
    perbaruiLabel();

    el.addEventListener('change', perbaruiLabel);

    el.addEventListener('click', () => {
        try {
            el.showPicker();
        } catch {
            // Browser yang tidak mendukung showPicker.
        }
    });
});

$('gwRentang').addEventListener('click', () => {
    uiRentang = !uiRentang;

    $('gwRentang').setAttribute(
        'aria-pressed',
        String(uiRentang)
    );

    $('gwRentang').classList.toggle(
        'is-active',
        uiRentang
    );

    $('gwModeSatu').hidden = uiRentang;
    $('gwModeRentang').hidden = !uiRentang;
    $('gwDateMsg').textContent = '';
});

$('gwPilihTanggal').addEventListener('click', () => {
    const pesanTanggal = $('gwDateMsg');

    if (uiRentang) {
        const awal = $('gwAwal').value;
        const akhir = $('gwAkhir').value;

        if (!awal || !akhir) {
            pesanTanggal.textContent =
                'Pilih tanggal awal dan tanggal akhir.';
            return;
        }

        if (awal > akhir) {
            pesanTanggal.textContent =
                'Tanggal awal tidak boleh setelah tanggal akhir.';
            return;
        }

        state.mode = 'rentang';
        state.awal = awal;
        state.akhir = akhir;

    } else {
        const tanggal = Number($('gwTanggal').value);
        const bulan = Number($('gwBulan').value);
        const tahun = Number($('gwTahun').value);

        const tanggalDipilih = new Date(
            tahun,
            bulan - 1,
            tanggal
        );

        if (
            tanggalDipilih.getFullYear() !== tahun ||
            tanggalDipilih.getMonth() !== bulan - 1 ||
            tanggalDipilih.getDate() !== tanggal
        ) {
            pesanTanggal.textContent =
                'Tanggal tidak valid untuk bulan tersebut.';
            return;
        }

        state.mode = 'satu';
        state.tanggal = iso(tanggalDipilih);
    }

    pesanTanggal.textContent = '';
    renderSemua();
});

/* ==========================================================
   14. MODAL DETAIL SISWA
   ========================================================== */

const modal = $('gwModal');

const pesan = (teks = '', galat = false) => {
    const el = $('gwMsg');

    el.textContent = teks;

    el.className =
        'gw-msg' +
        (teks ? (galat ? ' is-err' : ' is-ok') : '');
};

function tampilTab(nama) {
    modal.querySelectorAll('.gw-tab').forEach((tab) => {
        tab.classList.toggle(
            'is-active',
            tab.dataset.tab === nama
        );
    });

    modal.querySelectorAll('[data-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.panel !== nama;
    });

    pesan();
}

modal.querySelectorAll('.gw-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
        tampilTab(tab.dataset.tab);
    });
});

/* ==========================================================
   15. ISI DATA MODAL SISWA
   ========================================================== */

function isiModal() {
    if (!state.aktif) return;

    const { idPresensi } = state.aktif;

    const presensi = cariPresensi(idPresensi);
    if (!presensi) return;

    const siswa = SISWA.find(
        (s) => String(s.id_siswa) === String(presensi.id_siswa)
    );

    if (!siswa) return;

    $('gwFoto').textContent =
        siswa.nama.charAt(0).toUpperCase();

    // Riwayat presensi.
    $('gwHNama').value = siswa.nama;
    $('gwHNisn').value = siswa.nisn;
    $('gwHKelas').value = siswa.kelas;
    $('gwHStatus').value = presensi.status;
    $('gwHJam').value = presensi.jam;
    $('gwHMetode').value = presensi.metode;
    $('gwHBerkas').value = presensi.berkas;
    $('gwHKet').value = presensi.keterangan;
    $('gwHAlasan').value = '';

    // Biodata siswa.
    $('gwBNama').value = siswa.nama;
    $('gwBNisn').value = siswa.nisn;
    $('gwBGender').value = siswa.gender;
    $('gwBTtl').value = siswa.ttl;
    $('gwBAlamat').value = siswa.alamat;
    $('gwBHp').value = siswa.hp;

    // Orang tua siswa.
    $('gwPAyah').value = siswa.ayah;
    $('gwPIbu').value = siswa.ibu;
    $('gwPHp').value = siswa.hpOrtu;

    // Kurikulum siswa.
    $('gwKKelas').value = siswa.kelas;
    $('gwKTahun').value = TAHUN_AJARAN;
    $('gwKSemester').value = SEMESTER;
    $('gwKKurikulum').value = 'Kurikulum Merdeka';
}

/* ==========================================================
   16. EDIT RIWAYAT PRESENSI
   ========================================================== */

function siapkanEdit(tombolId, idField, simpan) {
    const tombol = $(tombolId);
    const label = tombol.querySelector('span');

    const elemen = () => idField.map($);

    let mengedit = false;

    const kunci = () => {
        mengedit = false;

        elemen().forEach((el) => {
            if (el.tagName === 'SELECT') {
                el.disabled = true;
            } else {
                el.readOnly = true;
            }
        });

        label.textContent = 'Edit';
    };

        tombol.addEventListener('click', async () => {
            if (!mengedit) {
                if (ROLE_AKTIF !== 'guru') {
                    pesan('Kamu tidak memiliki akses untuk mengedit data presensi.', true);
                    return;
                }

                mengedit = true;

            elemen().forEach((el) => {
                if (el.tagName === 'SELECT') {
                    el.disabled = false;
                } else {
                    el.readOnly = false;
                }
            });

            label.textContent = 'Simpan';
            pesan();

            elemen()[0].focus();
            return;
        }

        const galat = await simpan();

        if (galat) {
            pesan(galat, true);
            return;
        }

        kunci();
    });

    return {
        reset: kunci
    };
}

// Status yang dapat dipilih pada edit riwayat.
const STATUS_VALID = [
    'Hadir',
    'Terlambat',
    'Izin',
    'Sakit',
    'Alpa'
];

// Perubahan masih disimpan sementara di browser.
// Backend PATCH /api/presensi/{id_presensi} hanya
// mengizinkan ADMIN, sehingga tidak dipanggil dari
// halaman bersama Guru, Kurikulum, dan Budaya Positif.


const editRiwayat = siapkanEdit(
    'gwEditHistory',
    ['gwHStatus', 'gwHAlasan'],
    async () => {
        if (!state.aktif) {
            return 'Data presensi tidak ditemukan.';
        }

        const { idPresensi } = state.aktif;
        const presensi = cariPresensi(idPresensi);

        if (!presensi) {
            return 'Data presensi tidak ditemukan.';
        }

        const statusBaru = $('gwHStatus').value;
        const alasan = $('gwHAlasan').value.trim();

        if (!STATUS_VALID.includes(statusBaru)) {
            return 'Status tidak valid.';
        }

        if (statusBaru === presensi.status) {
            return 'Pilih status yang berbeda dari status sekarang.';
        }

        if (alasan.length < 5) {
            return 'Alasan perubahan wajib diisi (minimal 5 karakter).';
        }

        // Mengambil token CSRF dari cookie Laravel.
        const cookieXSRF = document.cookie
            .split('; ')
            .find((cookie) => cookie.startsWith('XSRF-TOKEN='));

        if (!cookieXSRF) {
            return 'Token keamanan tidak ditemukan. Muat ulang halaman.';
        }

        const tokenXSRF = decodeURIComponent(
            cookieXSRF.substring('XSRF-TOKEN='.length)
        );

        try {
            const response = await fetch(
                `/api/presensi/${encodeURIComponent(idPresensi)}`,
                {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': tokenXSRF
                    },
                    body: JSON.stringify({
                        status: statusBaru,
                        alasan_perubahan: alasan,
                        keterangan: presensi.keterangan === '-'
                            ? null
                            : presensi.keterangan
                    })
                }
            );

            const hasil = await response.json();

            if (!response.ok) {
                return hasil.message || 'Gagal menyimpan perubahan presensi.';
            }

            // Perbarui tampilan hanya setelah server berhasil.
            presensi.status = hasil.data?.status || statusBaru;

            $('gwHAlasan').value = '';

            renderSemua();
            isiModal();

            pesan(
                `Status berhasil diperbarui menjadi ${presensi.status}.`
            );

            return null;

        } catch (error) {
            console.error('Gagal memperbarui presensi:', error);

            return 'Terjadi kesalahan saat menghubungi server.';
        }
    }
);


/* ==========================================================
   17. BUKA DAN TUTUP MODAL
   ========================================================== */

function bukaModal(idPresensi) {
    state.aktif = {
        idPresensi
    };

    isiModal();

    editRiwayat.reset();
    tampilTab('history');

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';
}

function tutupModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';

    state.aktif = null;
}

$('gwBody').addEventListener('click', (e) => {
    const tombol = e.target.closest('.gw-edit');

    if (!tombol) return;

    bukaModal(tombol.dataset.idPresensi);
});

modal.addEventListener('click', (e) => {
    if (
        e.target === modal ||
        e.target.closest('[data-gw-close]')
    ) {
        tutupModal();
    }
});

document.addEventListener('keydown', (e) => {
    if (
        e.key === 'Escape' &&
        modal.classList.contains('is-open')
    ) {
        tutupModal();
    }
});


async function muatDataAPI() {
    try {
        const opsi = {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        // Mengambil data siswa dan halaman pertama presensi.
        const [resSiswa, resPresensi] = await Promise.all([
            fetch('/api/siswa', opsi),
            fetch('/api/presensi?per_page=100&page=1', opsi)
        ]);

        if (!resSiswa.ok || !resPresensi.ok) {
            throw new Error('Gagal mengambil data siswa atau presensi.');
        }

        const jsonSiswa = await resSiswa.json();
        const jsonPresensi = await resPresensi.json();

        // Mengambil seluruh halaman presensi.
        const lastPage = jsonPresensi.meta?.last_page || 1;

        const halamanLanjutan = await Promise.all(
            Array.from({ length: lastPage - 1 }, (_, i) =>
                fetch(`/api/presensi?per_page=100&page=${i + 2}`, opsi)
                    .then((res) => {
                        if (!res.ok) {
                            throw new Error('Gagal mengambil halaman presensi.');
                        }

                        return res.json();
                    })
            )
        );

        const semuaPresensi = [
            ...(jsonPresensi.data || []),
            ...halamanLanjutan.flatMap((halaman) => halaman.data || [])
        ];

        // Memetakan data siswa dari database.
        SISWA = (jsonSiswa.data || []).map((s) => {
            const riwayatKelas = s.riwayat_kelas || [];
            const kelasAktif = riwayatKelas.find((r) => r.kelas)?.kelas;

            return {
                id_siswa: s.id_siswa,
                nis: s.nis,
                nisn: s.nisn,
                nama: s.nama,
                jenis_kelamin: s.jenis_kelamin,
                gender: s.jenis_kelamin === 'L'
                    ? 'Laki-laki'
                    : s.jenis_kelamin === 'P'
                        ? 'Perempuan'
                        : '-',
                kelas: kelasAktif?.nama || '-',
                ttl: '-',
                alamat: s.alamat || '-',
                hp: '-',
                ayah: '-',
                ibu: '-',
                hpOrtu: '-'
            };
        });

        // Memetakan seluruh catatan presensi.
        PRESENSI = semuaPresensi.map((p) => ({
            id_presensi: p.id_presensi,
            id_siswa: p.id_siswa,
            nisn: p.siswa?.nisn || '',
            tanggal: String(p.tanggal).slice(0, 10),
            status: p.status,
            waktu_presensi: p.waktu_presensi,
            jam: p.waktu_presensi
                ? `${String(p.waktu_presensi).slice(0, 5).replace(':', '.')} WIB`
                : '-',
            metode_api: p.metode,
            metode: p.metode === 'QR'
                ? 'QR Code'
                : p.metode || '-',
            file_bukti: p.file_bukti,
            berkas: p.file_bukti || '-',
            keterangan: p.keterangan || '-'
        }));

        renderSemua();

    } catch (error) {
        console.error('Gagal memuat data presensi:', error);
        pesan('Data dari server gagal dimuat. Silakan periksa koneksi dan login.');
    }
}

/* ==========================================================
   18. MULAI HALAMAN
   ========================================================== */

muatDataAPI();
