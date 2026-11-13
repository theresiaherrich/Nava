// resources/js/presensi/budaya-positif.js
// Halaman Budaya Positif: Dashboard (statistik + tabel) dan Admin Presensi (scan QR + presensi manual).
// Satu file dipakai dua halaman; bagian yang tidak ada di halaman itu dilewati otomatis.
// Frontend saja: data dummy. Titik backend ditandai "TODO API".
import { Html5Qrcode } from 'html5-qrcode';

/* ============ HELPER & DATA DUMMY ============ */
const $ = (id) => document.getElementById(id);
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const norm = (s) => String(s ?? '').trim().replace(/\s+/g, ' ').toLowerCase();

const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
const sekarang = new Date();
const kemarin = new Date(sekarang); kemarin.setDate(kemarin.getDate() - 1);
const TGL_INI = iso(sekarang);
const TGL_KEMARIN = iso(kemarin);
const BATAS_TEPAT_WAKTU = '07.00';   
const BATAS_ALPA = '07.30';         

const adaDashboard = Boolean($('bpDonut'));
const adaAdmin = Boolean($('bpQrReader'));

// TODO API: ganti dengan daftar siswa dari backend
const SISWA = [
    ['Mutiara Bintang', '245150600'], ['Bintang Mutiara', '245150601'], ['Mutiara Lautan', '245150602'],
    ['Lautan Biru', '245150603'], ['Angkasa Raya', '245150604'], ['Putri Andromeda', '245150605'],
    ['Putra Mahkota', '245150606'], ['Peri Indah', '245150607'], ['Bintang Samudra', '245150608'],
    ['Nusa Cendana', '245150609'], ['Rama Segara', '245150610'],   // dua siswa ini belum presensi hari ini (untuk uji scan/manual)
].map(([nama, nisn]) => ({ nama, nisn, kelas: 'XI MIPA 2' }));

const HARI_INI = [
    ['Telat', '07.28 WIB'], ['Telat', '07.10 WIB'], ['Hadir', '07.00 WIB'], ['Hadir', '06.58 WIB'],
    ['Hadir', '06.58 WIB'], ['Hadir', '06.56 WIB'], ['Hadir', '06.56 WIB'], ['Hadir', '06.56 WIB'], ['Hadir', '06.56 WIB'],
];

// TODO API: ganti dengan presensi dari backend (per tanggal)
const PRESENSI = [
    ...HARI_INI.map(([status, jam], i) => ({ nisn: SISWA[i].nisn, tanggal: TGL_INI, status, jam, metode: 'QR Code', ket: '-' })),
    ...SISWA.map((s, i) => ({ nisn: s.nisn, tanggal: TGL_KEMARIN, status: 'Hadir', jam: `06.${40 + i} WIB`, metode: 'QR Code', ket: '-' })),
];

// Dashboard dan Admin Presensi adalah dua halaman terpisah, jadi catatan baru disimpan sementara di browser
// supaya muncul di Dashboard. Hapus manual: localStorage.removeItem('siakad_bp_presensi_baru_v1')
const KUNCI = 'siakad_bp_presensi_baru_v1';
const muatBaru = () => { try { const d = JSON.parse(localStorage.getItem(KUNCI) || '[]'); return Array.isArray(d) ? d : []; } catch { return []; } };
const simpanBaru = (rec) => { try { localStorage.setItem(KUNCI, JSON.stringify([...muatBaru(), rec])); } catch { /* abaikan */ } };
PRESENSI.push(...muatBaru());

const state = { tanggal: TGL_INI, q: '', status: '', urut: 'newest' };
const cariSiswaNisn = (nisn) => SISWA.find((s) => s.nisn === String(nisn).trim());
const menit = (jam) => { const m = /^(\d{1,2})[.:](\d{2})/.exec(jam); return m ? Number(m[1]) * 60 + Number(m[2]) : -1; };
const jamSekarang = () => { const n = new Date(); return `${String(n.getHours()).padStart(2, '0')}.${String(n.getMinutes()).padStart(2, '0')}`; };

/* ============ MENCATAT PRESENSI (dipakai scan QR & manual) ============ */
function catat(siswa, status, metode) {
    const ada = PRESENSI.find((p) => p.nisn === siswa.nisn && p.tanggal === TGL_INI && p.status !== 'Alpa');
    if (ada) return { ok: false, pesan: `${siswa.nama} sudah tercatat ${ada.status} hari ini (${ada.jam}).` };

    const rec = { nisn: siswa.nisn, tanggal: TGL_INI, status, jam: `${jamSekarang()} WIB`, metode, ket: '-' };
    // TODO API: kirim ke backend (POST /presensi), sertakan CSRF token
    PRESENSI.push(rec);
    simpanBaru(rec);
    if (adaDashboard) renderSemua();
    return { ok: true, pesan: `Presensi ${siswa.nama} tercatat sebagai ${status} (${rec.jam}).` };
}

/* ============ DASHBOARD ============ */
function dataTampil() {
    const q = norm(state.q);
    return PRESENSI
        .filter((p) => p.tanggal === state.tanggal)
        .map((p) => ({ p, s: cariSiswaNisn(p.nisn) }))
        .filter(({ p, s }) => (!state.status || p.status === state.status)
            && (!q || [s.nama, s.nisn, s.kelas].some((v) => norm(v).includes(q))))
        .sort((a, b) => (state.urut === 'oldest' ? menit(a.p.jam) - menit(b.p.jam) : menit(b.p.jam) - menit(a.p.jam)));
}

function renderTabel() {
    const data = dataTampil();
    $('bpHistoryBody').innerHTML = data.map(({ p, s }, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${esc(s.nama)}</td>
            <td>${esc(s.nisn)}</td>
            <td>${esc(s.kelas)}</td>
            <td><span class="bp-status ${p.status.toLowerCase()}">${esc(p.status)}</span></td>
            <td>${esc(p.jam)}</td>
            <td>${esc(p.metode)}</td>
            <td>${esc(p.ket)}</td>
        </tr>`).join('');
    $('bpEmpty').hidden = data.length > 0;
}

function renderStatistik() {
    const data = PRESENSI.filter((p) => p.tanggal === state.tanggal);
    const total = SISWA.length;
    const n = (...st) => data.filter((p) => st.includes(p.status)).length;
    const hadirNisn = new Set(data.filter((p) => ['Hadir', 'Telat'].includes(p.status)).map((p) => p.nisn));
    const hadir = n('Hadir'), telat = n('Telat'), izin = n('Izin', 'Sakit');
    // Alpa yang kemudian datang terlambat tidak dihitung lagi sebagai Alpa
    const alpa = data.filter((p) => p.status === 'Alpa' && !hadirNisn.has(p.nisn)).length;
    const persen = (v, t) => (t ? Math.round((v / t) * 100) : 0);

    const dasar = hadir + telat + alpa;
    const h = dasar ? (hadir / dasar) * 100 : 0;
    const t = dasar ? (telat / dasar) * 100 : 0;
    $('bpDonut').style.setProperty('--donut', dasar
        ? `conic-gradient(#1565c0 0 ${h}%, #fff500 ${h}% ${h + t}%, #dc137b ${h + t}% 100%)`
        : '#dde6f0');
    $('bpPercentage').textContent = `${persen(hadir + telat, dasar)}%`;

    $('bpIzinCount').textContent = izin;  $('bpIzinTotal').textContent = total;
    $('bpIzinFill').style.width = `${persen(izin, total)}%`; $('bpIzinPct').textContent = `${persen(izin, total)}%`;
    $('bpAlpaCount').textContent = alpa;  $('bpAlpaTotal').textContent = total;
    $('bpAlpaFill').style.width = `${persen(alpa, total)}%`; $('bpAlpaPct').textContent = `${persen(alpa, total)}%`;
}

function renderSemua() { renderTabel(); renderStatistik(); }

if (adaDashboard) {
    $('bpSearch').addEventListener('input', (e) => { state.q = e.target.value; renderTabel(); });
    $('bpStatusFilter').addEventListener('change', (e) => { state.status = e.target.value; renderTabel(); });
    $('bpSort').addEventListener('change', (e) => { state.urut = e.target.value; renderTabel(); });

    $('bpApplyDate').addEventListener('click', () => {
        const tgl = Number($('bpDay').value), bln = Number($('bpMonth').value), thn = Number($('bpYear').value);
        const d = new Date(thn, bln - 1, tgl);
        const msg = $('bpDateMsg');
        if (d.getMonth() !== bln - 1) { msg.textContent = 'Tanggal tidak valid untuk bulan tersebut.'; return; }
        msg.textContent = '';
        state.tanggal = iso(d);   // TODO API: ambil presensi tanggal ini dari backend
        renderSemua();
    });

    renderSemua();
}

/* ============ ADMIN PRESENSI ============ */
if (adaAdmin) {
    /* ---- Presensi manual ---- */
    const pesanForm = (teks, jenis) => {
        const el = $('bpFormMsg');
        el.textContent = teks;
        el.style.color = jenis === 'ok' ? '#157a46' : '#e20a7a';
    };

    $('bpManualForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const nama = $('bpName').value, kelas = $('bpClass').value, status = $('bpManualStatus').value;

        if (!nama.trim() || !kelas.trim() || !status) return pesanForm('Lengkapi nama, kelas, dan status.', 'err');

        const siswa = SISWA.find((s) => norm(s.nama) === norm(nama) && norm(s.kelas) === norm(kelas));
        if (!siswa) return pesanForm('Siswa tidak ditemukan. Pastikan nama dan kelas sudah benar.', 'err');

        const hasil = catat(siswa, status, 'Manual');
        pesanForm(hasil.pesan, hasil.ok ? 'ok' : 'err');
        if (hasil.ok) e.target.reset();
    });

    // Kolom cari di atas: tekan Enter untuk mengisi form manual dengan siswa yang cocok
    $('bpSearch').title = 'Tekan Enter untuk mengisi form presensi manual';
    $('bpSearch').addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const q = norm($('bpSearch').value);
        const s = q && SISWA.find((x) => [x.nama, x.nisn, x.kelas].some((v) => norm(v).includes(q)));
        if (!s) return pesanForm('Siswa tidak ditemukan.', 'err');
        $('bpName').value = s.nama;
        $('bpClass').value = s.kelas;
        pesanForm('Data siswa terisi. Pilih status lalu Submit.', 'ok');
    });

    /* ---- Scan QR (kamera menyala terus, siswa berikutnya langsung bisa dipindai) ---- */
    let scanner = null;
    let kameraAktif = false;
    let terakhir = '';
    let waktuTerakhir = 0;
    const info = $('bpScanInfo');
    const placeholder = $('bpCameraPlaceholder');
    const judul = placeholder.querySelector('p');
    const sub = placeholder.querySelector('span');
    const tombol = $('bpStartCamera');

    // Isi QR bisa NISN langsung, "SIAKAD|<nisn>" (QR dari halaman siswa), atau JSON {nisn}
    const bacaNisn = (teks) => {
        let v = String(teks || '').trim();
        try { const j = JSON.parse(v); v = j.nisn || j.id || v; } catch { /* bukan JSON */ }
        return String(v).split('|').pop().trim();
    };

    const tampilInfo = (teks, ok) => { info.textContent = teks; info.className = `bp-scan-info ${ok ? 'is-ok' : 'is-error'}`; };

    function saatTerbaca(teks) {
        const sekarangMs = Date.now();
        if (teks === terakhir && sekarangMs - waktuTerakhir < 4000) return;   // abaikan QR yang sama berturut-turut
        terakhir = teks;
        waktuTerakhir = sekarangMs;

        const nisn = bacaNisn(teks);
        const siswa = cariSiswaNisn(nisn);
        if (!siswa) return tampilInfo(`QR terbaca (${nisn}), tetapi siswa tidak ditemukan.`, false);

        const status = jamSekarang() > BATAS_TEPAT_WAKTU ? 'Telat' : 'Hadir';
        const hasil = catat(siswa, status, 'QR Code');
        tampilInfo(hasil.pesan, hasil.ok);
    }

    async function mulaiKamera() {
        tombol.hidden = true;
        judul.textContent = 'Membuka kamera…';
        sub.textContent = 'Izinkan akses kamera jika diminta.';
        placeholder.hidden = false;

        try {
            scanner = new Html5Qrcode('bpQrReader');
            await scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 200, height: 200 } }, saatTerbaca, () => {});
            kameraAktif = true;
            placeholder.hidden = true;
            $('bpScanFrame').hidden = false;
        } catch (err) {
            console.error(err);
            scanner = null;
            judul.textContent = 'Kamera tidak dapat diakses';
            sub.textContent = 'Periksa izin kamera dan buka halaman lewat localhost atau HTTPS.';
            tombol.hidden = false;
            tombol.textContent = 'Coba Lagi';
            $('bpScanFrame').hidden = true;
        }
    }

    tombol.addEventListener('click', mulaiKamera);
    window.addEventListener('pagehide', () => { if (kameraAktif && scanner) scanner.stop().catch(() => {}); });
    mulaiKamera();   // langsung dicoba saat halaman dibuka
}