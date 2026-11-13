
/* ==========================================================
   PRESENSI DASHBOARD
   File: resources/js/presensi/guru.js

   Digunakan bersama oleh:
   - Guru Wali Kelas
   - Kurikulum
   - Budaya Positif

   FRONTEND ONLY:
   - Data masih menggunakan dummy.
   - Tidak ada request atau perubahan database.
   - Integrasi backend ditandai TODO API.
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

// Identitas dibaca dari atribut data pada .gw-page.
// Nilai dikirim oleh Blade sesuai halaman yang sedang dibuka.

const halamanPresensi = document.querySelector('.gw-page');

const ROLE_AKTIF = halamanPresensi?.dataset.role || 'guru';

const PROFIL = {
    nama: halamanPresensi?.dataset.profileName || 'Nama Pengguna',
    jabatan: halamanPresensi?.dataset.profileLabel || 'Pengguna',
    detail: halamanPresensi?.dataset.profileDetail || 'SMAN 1 Malang'
};

// Data kelas dummy untuk kebutuhan tampilan dashboard.
// TODO API: Sesuaikan dengan data kelas dari backend.

const KELAS_AKTIF = ROLE_AKTIF === 'guru'
    ? 'XI MIPA 2'
    : 'SMAN 1 Malang';

const TAHUN_AJARAN = '2026/2027';
const SEMESTER = 'Ganjil';

/* ==========================================================
   3. DATA SISWA
   ========================================================== */

// TODO API:
// Ganti dengan data siswa yang benar dari backend.

const SISWA = [
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
    nisn,
    nama,
    gender,
    kelas: 'XI MIPA 2',

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
    ['Sakit', '-', 'Manual', 'Surat sakit dari orang tua'],
    ['Alpa', '-', '-', 'Tanpa keterangan']
];

const PERSEN_ALPA = [0, 2, 0, 10, 5, 1, 0, 0, 0, 3, 4];
const PERSEN_IZIN = [0, 3, 7, 20, 16, 0, 2, 0, 0, 5, 3];

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
                metode = 'Manual';
                keterangan = 'Surat izin dari orang tua';
            } else if (
                angka < PERSEN_ALPA[i] + PERSEN_IZIN[i] + 12
            ) {
                status = 'Terlambat';
                jam = `07.${String(5 + ((k + i) % 24)).padStart(2, '0')} WIB`;
            }

            hasil.push({
                nisn: s.nisn,
                tanggal: iso(tanggal),
                status,
                jam,
                metode,
                berkas: '-',
                keterangan
            });
        });
    }

    return hasil;
}

let PRESENSI = [
    ...SISWA.map((s, i) => ({
        nisn: s.nisn,
        tanggal: TGL_INI,
        status: HARI_INI[i][0],
        jam: HARI_INI[i][1],
        metode: HARI_INI[i][2],
        berkas: '-',
        keterangan: HARI_INI[i][3] || '-'
    })),

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
    status: '',
    urut: 'terbaru',
    rekap: '',

    aktif: null
};

let uiRentang = false;

/* ==========================================================
   6. PENCARIAN DATA
   ========================================================== */

const cariSiswa = (nisn) =>
    SISWA.find((s) => s.nisn === nisn);

const cariPresensi = (nisn, tanggal) =>
    PRESENSI.find(
        (p) => p.nisn === nisn && p.tanggal === tanggal
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
            s: cariSiswa(p.nisn)
        }))
        .filter(({ p, s }) => {
            return s &&
                (!state.status || p.status === state.status) &&
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
                        data-nisn="${esc(s.nisn)}"
                        data-tanggal="${esc(p.tanggal)}"
                        aria-label="Edit presensi ${esc(s.nama)}">
                        ${ikonEdit}
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    return data.length;
}

/* ==========================================================
   9. TABEL REKAP
   ========================================================== */

function renderRekap() {
    const baris = SISWA
        .filter(cocokCari)
        .map((s) => {
            const riwayat = PRESENSI.filter(
                (p) => p.nisn === s.nisn && dalamPeriode(p)
            );

            const hadir = riwayat.filter(
                (p) => ['Hadir', 'Terlambat'].includes(p.status)
            ).length;

            const izin = riwayat.filter(
                (p) => p.status === 'Izin'
            ).length;

            const sakit = riwayat.filter(
                (p) => p.status === 'Sakit'
            ).length;

            const alpa = riwayat.filter(
                (p) => p.status === 'Alpa'
            ).length;

            const total = hadir + izin + sakit + alpa;

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
                .map((p) => p.nisn)
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

$('gwSearch').addEventListener('input', (e) => {
    state.q = e.target.value;
    renderSemua();
});

$('gwStatusFilter').addEventListener('change', (e) => {
    state.status = e.target.value;
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

    const { nisn, tanggal } = state.aktif;

    const siswa = cariSiswa(nisn);
    const presensi = cariPresensi(nisn, tanggal);

    if (!siswa || !presensi) return;

    $('gwFoto').textContent =
        siswa.nama.charAt(0).toUpperCase();

    // Riwayat presensi
    $('gwHNama').value = siswa.nama;
    $('gwHNisn').value = siswa.nisn;
    $('gwHKelas').value = siswa.kelas;
    $('gwHStatus').value = presensi.status;
    $('gwHJam').value = presensi.jam;
    $('gwHMetode').value = presensi.metode;
    $('gwHBerkas').value = presensi.berkas;
    $('gwHKet').value = presensi.keterangan;
    $('gwHAlasan').value = '';

    // Biodata siswa
    $('gwBNama').value = siswa.nama;
    $('gwBNisn').value = siswa.nisn;
    $('gwBGender').value = siswa.gender;
    $('gwBTtl').value = siswa.ttl;
    $('gwBAlamat').value = siswa.alamat;
    $('gwBHp').value = siswa.hp;

    // Orang tua siswa
    $('gwPAyah').value = siswa.ayah;
    $('gwPIbu').value = siswa.ibu;
    $('gwPHp').value = siswa.hpOrtu;

    // Kurikulum siswa
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

    tombol.addEventListener('click', () => {
        if (!mengedit) {
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

        const galat = simpan();

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

// Perubahan hanya tersimpan sementara di browser.

const editRiwayat = siapkanEdit(
    'gwEditHistory',
    ['gwHStatus', 'gwHAlasan'],
    () => {
        if (!state.aktif) {
            return 'Data presensi tidak ditemukan.';
        }

        const { nisn, tanggal } = state.aktif;

        const presensi = cariPresensi(nisn, tanggal);

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

        // TODO API:
        // PUT /presensi/{id}/status

        if (!presensi.riwayatPerubahan) {
            presensi.riwayatPerubahan = [];
        }

        presensi.riwayatPerubahan.push({
            dari: presensi.status,
            ke: statusBaru,
            alasan,
            oleh: PROFIL.nama,
            waktu: new Date().toISOString()
        });

        presensi.status = statusBaru;

        $('gwHAlasan').value = '';

        renderSemua();
        isiModal();

        pesan(
            `Status diubah menjadi ${statusBaru} (data sementara).`
        );

        return null;
    }
);

/* ==========================================================
   17. BUKA DAN TUTUP MODAL
   ========================================================== */

function bukaModal(nisn, tanggal) {
    state.aktif = {
        nisn,
        tanggal
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

    bukaModal(
        tombol.dataset.nisn,
        tombol.dataset.tanggal
    );
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

/* ==========================================================
   18. MULAI HALAMAN
   ========================================================== */

renderSemua();
