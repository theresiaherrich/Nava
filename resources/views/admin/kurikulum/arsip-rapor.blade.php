@extends('layouts.app')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    [x-cloak] { display: none !important; }

    .ar-wrap { padding: 24px; max-width: 1200px; margin: 0 auto; color: #111827; font-size: 12px; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
    .ar-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .08em; color: #0ea5e9; text-transform: uppercase; }
    .ar-title { font-size: 22px; font-weight: 700; margin-top: 2px; }
    .ar-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }

    .ar-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 10px; margin-top: 18px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .ar-sel { border: 1px solid #e5e7eb; background: #fff; border-radius: 10px; padding: 7px 10px; font-size: 11px; color: #374151; font-weight: 600; outline: none; cursor: pointer; }
    .ar-sel:focus, .ar-search input:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
    .ar-search { position: relative; margin-left: auto; }
    .ar-search input { width: 220px; border: 1px solid #e5e7eb; background: #fff; border-radius: 10px; padding: 7px 12px 7px 32px; font-size: 11px; outline: none; }
    .ar-search svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #9ca3af; }

    .ar-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; margin-top: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .ar-table { width: 100%; border-collapse: collapse; }
    .ar-table th { text-align: left; font-size: 10.5px; font-weight: 600; color: #6b7280; padding: 11px 14px; background: #fafafa; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
    .ar-table td { padding: 11px 14px; border-bottom: 1px solid #f3f4f6; font-size: 11.5px; color: #374151; vertical-align: middle; }
    .ar-table tr.main:hover td { background: #fafafa; }
    .ar-table tr.open td { background: #f5f3ff; }
    .ar-table .r { text-align: right; }
    .ar-table .muted { color: #9ca3af; }

    .ar-name { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; color: #111827; background: none; border: 0; padding: 0; cursor: pointer; font-size: 11.5px; }
    .ar-name:hover { color: #0284c7; }
    .ar-chev { width: 14px; height: 14px; color: #9ca3af; transition: transform .2s; flex-shrink: 0; }
    .ar-chev.open { transform: rotate(90deg); }

    .ar-pill { display: inline-flex; align-items: center; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 999px; border: 1px solid; white-space: nowrap; }
    .ar-pill.ver { background: #fff; color: #374151; border-color: #d1d5db; }
    .ar-pill.ver.k { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
    .ar-pill.ok { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .ar-pill.gray { background: #f3f4f6; color: #6b7280; border-color: #e5e7eb; }

    .ar-btn { display: inline-flex; align-items: center; gap: 5px; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 9px; padding: 5px 10px; font-size: 11px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .15s; }
    .ar-btn:hover { background: #f9fafb; }
    .ar-btn.primary { background: #0ea5e9; border-color: #0ea5e9; color: #fff; }
    .ar-btn.primary:hover { background: #0284c7; }
    .ar-btn:disabled { opacity: .4; cursor: not-allowed; }
    .ar-btn svg { width: 12px; height: 12px; }
    .ar-acts { display: flex; justify-content: flex-end; gap: 6px; }

    .ar-hist { padding: 12px 16px 16px 44px; }
    .ar-hist-title { font-size: 10px; font-weight: 700; letter-spacing: .06em; color: #7c3aed; text-transform: uppercase; margin-bottom: 8px; }
    .ar-ver { display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 14px; margin-top: 8px; }
    .ar-ver-dot { width: 8px; height: 8px; border-radius: 50%; background: #a78bfa; flex-shrink: 0; }
    .ar-ver-dot.gray { background: #d1d5db; }
    .ar-ver-main { flex: 1; min-width: 0; }
    .ar-ver-time { font-size: 11.5px; font-weight: 700; color: #111827; }
    .ar-ver-ket { font-size: 10.5px; color: #6b7280; margin-top: 2px; }
    .ar-ver-acts { display: flex; gap: 6px; }

    .ar-footer { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-top: 1px solid #f3f4f6; background: #fafafa; font-size: 11px; color: #6b7280; flex-wrap: wrap; gap: 8px; }
    .ar-lock { display: inline-flex; align-items: center; gap: 6px; }
    .ar-pages { display: flex; align-items: center; gap: 4px; }
    .ar-pg { min-width: 26px; height: 26px; padding: 0 6px; border-radius: 7px; border: 1px solid #e5e7eb; background: #fff; color: #4b5563; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .ar-pg:hover:not(:disabled) { background: #f9fafb; }
    .ar-pg:disabled { opacity: .4; cursor: not-allowed; }
    .ar-pg.active { background: #0ea5e9; border-color: #0ea5e9; color: #fff; font-weight: 700; }

    .ar-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 16px; }
    .ar-modal { background: #fff; border-radius: 18px; width: 100%; max-width: 880px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,.35); position: relative; }
    .ar-modal-head { padding: 18px 22px 12px; border-bottom: 1px solid #f3f4f6; }
    .ar-modal-close { position: absolute; top: 14px; right: 16px; border: 0; background: transparent; color: #9ca3af; font-size: 20px; cursor: pointer; line-height: 1; }
    .ar-modal-title { font-size: 15px; font-weight: 700; padding-right: 28px; }
    .ar-modal-sub { font-size: 11.5px; color: #6b7280; margin-top: 3px; }
    .ar-modal-body { overflow: auto; background: #e2e8f0; padding: 16px; flex: 1; text-align: center; }
    .ar-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 12px 22px; border-top: 1px solid #f3f4f6; }
    .ar-doc { display: inline-block; text-align: left; zoom: .72; }

    .ar-toast { position: fixed; bottom: 24px; right: 24px; background: #1e293b; color: #fff; padding: 11px 16px; border-radius: 12px; font-size: 12px; font-weight: 500; box-shadow: 0 10px 30px -5px rgba(0,0,0,.3); z-index: 60; display: flex; align-items: center; gap: 10px; }
    .ar-toast .ok-dot { width: 18px; height: 18px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; }
    .ar-toast .ok-dot svg { width: 11px; height: 11px; color: #fff; }

    @media (max-width: 900px) {
        .ar-search { margin-left: 0; width: 100%; }
        .ar-search input { width: 100%; }
    }
</style>

<div class="ar-wrap" x-data="arsipRapor" x-cloak>

    {{-- HEADER --}}
    <div>
        <p class="ar-eyebrow">SMA Negeri 1 Kota Malang · Kurikulum</p>
        <h1 class="ar-title">Arsip & Versi Rapor</h1>
        <p class="ar-sub">Setiap versi rapor menyimpan data final saat dibuat. Cetak ulang versi lama memakai data lama.</p>
    </div>

    {{-- FILTER --}}
    <div class="ar-filters">
        <select class="ar-sel" x-model="fTahun" @change="page = 1">
            <option value="2026/2027">Tahun ajaran: 2026/2027</option>
        </select>
        <select class="ar-sel" x-model="fSemester" @change="page = 1">
            <option value="Ganjil">Semester: Ganjil</option>
            <option value="Genap">Semester: Genap</option>
        </select>
        <select class="ar-sel" x-model="fJenis" @change="page = 1">
            <option value="Semua">Jenis rapor: Semua</option>
            <option value="Rapor STS">Jenis rapor: Rapor STS</option>
            <option value="Rapor Semester">Jenis rapor: Rapor Semester</option>
        </select>
        <select class="ar-sel" x-model="fKelas" @change="page = 1">
            <option value="Semua">Kelas: Semua</option>
            <template x-for="k in daftarKelas" :key="k">
                <option :value="k" x-text="'Kelas: ' + k"></option>
            </template>
        </select>

        <div class="ar-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Cari nama siswa..." x-model="cari" @input="page = 1">
        </div>
    </div>

    {{-- TABEL --}}
    <div class="ar-card">
        <table class="ar-table">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jenis Rapor</th>
                    <th>Versi</th>
                    <th>Dibuat</th>
                    <th>Oleh</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th class="r">Aksi</th>
                </tr>
            </thead>

            <template x-for="s in halaman" :key="s.id">
                <tbody>
                    <tr class="main" :class="isOpen(s.id) ? 'open' : ''">
                        <td>
                            <button type="button" class="ar-name" @click="toggle(s.id)">
                                <svg class="ar-chev" :class="isOpen(s.id) ? 'open' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <span x-text="s.name"></span>
                            </button>
                        </td>
                        <td x-text="s.kelas"></td>
                        <td x-text="s.jenis"></td>
                        <td>
                            <span class="ar-pill ver" :class="latest(s).koreksi ? 'k' : ''" x-text="labelVersi(latest(s))"></span>
                        </td>
                        <td x-text="latest(s).tanggal.split(',')[0]"></td>
                        <td x-text="latest(s).oleh"></td>
                        <td>
                            <span x-show="latest(s).alasan !== '—'" x-text="latest(s).alasan"></span>
                            <span x-show="latest(s).alasan === '—'" class="muted">—</span>
                        </td>
                        <td>
                            <span :class="latest(s).status === 'Terbit' ? 'ar-pill ok' : 'ar-pill gray'" x-text="latest(s).status"></span>
                        </td>
                        <td>
                            <div class="ar-acts">
                                <button type="button" class="ar-btn" @click="bukaPratinjau(s, latest(s))">Pratinjau</button>
                                <button type="button" class="ar-btn" @click="unduh(s, latest(s))">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Unduh
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr x-show="isOpen(s.id)" class="open">
                        <td colspan="9" style="padding:0;">
                            <div class="ar-hist">
                                <p class="ar-hist-title" x-text="'Riwayat versi · ' + s.name"></p>
                                <template x-for="v in riwayat(s)" :key="v.no">
                                    <div class="ar-ver">
                                        <span :class="v.koreksi ? 'ar-ver-dot' : 'ar-ver-dot gray'"></span>
                                        <span class="ar-pill ver" :class="v.koreksi ? 'k' : ''" x-text="labelVersi(v)"></span>
                                        <div class="ar-ver-main">
                                            <div class="ar-ver-time" x-text="(v.status === 'Terbit' ? 'Terbit ' : 'Dibuat ') + v.tanggal"></div>
                                            <div class="ar-ver-ket" x-text="v.ket"></div>
                                        </div>
                                        <span :class="v.status === 'Terbit' ? 'ar-pill ok' : 'ar-pill gray'" x-text="v.status"></span>
                                        <div class="ar-ver-acts">
                                            <button type="button" class="ar-btn" @click="bukaPratinjau(s, v)">Pratinjau</button>
                                            <button type="button" class="ar-btn" @click="unduh(s, v)">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                Unduh ulang
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </template>

            <tbody x-show="hasil.length === 0">
                <tr>
                    <td colspan="9" style="text-align:center; padding:40px 14px; color:#9ca3af;">Tidak ada rapor yang sesuai filter.</td>
                </tr>
            </tbody>
        </table>

        {{-- FOOTER --}}
        <div class="ar-footer">
            <span class="ar-lock">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M8 11V7a4 4 0 118 0v4"/></svg>
                Versi lama tidak dapat diubah.
            </span>
            <div style="display:flex; align-items:center; gap:10px;">
                <span><span x-text="dariMulai"></span>–<span x-text="dariSampai"></span> dari <span x-text="hasil.length"></span></span>
                <div class="ar-pages">
                    <button type="button" class="ar-pg" @click="page > 1 && page--" :disabled="page === 1">&lsaquo;</button>
                    <template x-for="p in daftarHalaman" :key="p + '-' + page">
                        <button type="button" class="ar-pg"
                                :class="p === page ? 'active' : ''"
                                :disabled="p === '…'"
                                @click="p !== '…' && (page = p)"
                                x-text="p"></button>
                    </template>
                    <button type="button" class="ar-pg" @click="page < totalHalaman && page++" :disabled="page === totalHalaman">&rsaquo;</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PRATINJAU --}}
    <div class="ar-overlay" x-show="modal.open" x-transition.opacity
         @click.self="tutupModal()" @keydown.escape.window="tutupModal()">
        <div class="ar-modal" @click.stop>
            <button type="button" class="ar-modal-close" @click="tutupModal()">&times;</button>
            <div class="ar-modal-head">
                <h2 class="ar-modal-title" x-text="'Pratinjau rapor · ' + (modal.s ? modal.s.name : '')"></h2>
                <p class="ar-modal-sub"
                   x-text="modal.s ? labelVersi(modal.v) + ' · ' + modal.s.jenis + ' · Kelas ' + modal.s.kelas + ' · ' + modal.v.status + ' ' + modal.v.tanggal : ''"></p>
            </div>
            <div class="ar-modal-body">
                <div class="ar-doc" x-html="modal.html"></div>
            </div>
            <div class="ar-modal-foot">
                <button type="button" class="ar-btn" @click="tutupModal()">Tutup</button>
                <button type="button" class="ar-btn primary" @click="modal.s && unduh(modal.s, modal.v)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh PDF
                </button>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <div class="ar-toast" x-show="showToast" x-transition.opacity>
        <span class="ok-dot">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </span>
        <span x-text="toastMsg"></span>
    </div>
</div>

<script>
// ===== Helper dokumen rapor (dipakai pratinjau & PDF) =====
const AR_MAPEL = [
    'Pendidikan Agama Islam dan Budi Pekerti', 'Pendidikan Pancasila', 'Bahasa Indonesia',
    'Matematika', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Sejarah'
];
const AR_CATATAN = 'Pertahankan prestasi akademis yang memuaskan ini. Tingkatkan motivasi belajar di semester berikutnya.';

function arEsc(t) {
    return String(t).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
}
function arKolom(jenis) {
    return jenis === 'Rapor STS' ? ['TP1', 'TP2', 'TP3', 'TP4'] : ['STS', 'SAS'];
}
function arBuatNilai(seed, jenis, koreksi, versiNo) {
    const n = arKolom(jenis).length;
    return AR_MAPEL.map((m, j) => {
        const sc = Array.from({ length: n }, (_, k) => 74 + ((seed * 7 + j * 13 + k * 11) % 22));
        if (koreksi && j === 3) sc[jenis === 'Rapor STS' ? 2 : 1] = versiNo === 1 ? 8 : 80;
        const akhir = Math.round(sc.reduce((a, b) => a + b, 0) / sc.length);
        return { no: j + 1, nama: m, sc, akhir };
    });
}

function arDokumen(s, v) {
    const kol = arKolom(s.jenis);
    const judul = s.jenis === 'Rapor STS' ? 'SUMATIF TENGAH SEMESTER' : 'SEMESTER GANJIL';
    const PG = 'width:794px;min-height:1120px;box-sizing:border-box;padding:48px 56px;background:#fff;display:flex;flex-direction:column;font-family:Arial,Helvetica,sans-serif;color:#111;font-size:11px;line-height:1.45;';
    const TD = 'border:1px solid #333;padding:6px 8px;';
    const TH = TD + 'background:#f3f4f6;font-weight:700;text-align:center;';

    const thNilai = kol.map(c => `<th style="${TH}width:58px;">${c}</th>`).join('');
    const rows = v.nilai.map(r => `
        <tr>
            <td style="${TD}text-align:center;">${r.no}</td>
            <td style="${TD}">${arEsc(r.nama)}</td>
            ${r.sc.map(x => `<td style="${TD}text-align:center;">${x}</td>`).join('')}
            <td style="${TD}text-align:center;font-weight:700;">${r.akhir}</td>
        </tr>`).join('');

    const ekskul = [
        { nama: 'Pramuka', predikat: 'Sangat Baik', ket: 'Aktif dalam kegiatan latihan rutin' },
        { nama: 'Paskibra', predikat: 'Baik', ket: 'Menunjukkan rasa kedisiplinan dan kepemimpinan' },
    ];

    const hal1 = `
    <div style="${PG}page-break-after:always;">
        <div style="display:flex;align-items:center;gap:14px;border-bottom:2px solid #111;padding-bottom:8px;">
            <div style="width:44px;height:44px;border:2px solid #111;font-weight:900;font-size:16px;display:flex;align-items:center;justify-content:center;">S</div>
            <div style="flex:1;text-align:center;padding-right:44px;">
                <div style="font-size:9px;font-weight:700;letter-spacing:.05em;">PEMERINTAH PROVINSI JAWA TIMUR · DINAS PENDIDIKAN</div>
                <div style="font-size:13px;font-weight:900;margin-top:2px;">SMA NEGERI 1 KOTA MALANG</div>
                <div style="font-size:9px;color:#555;">Jl. Tugu Utara No. 1, Kota Malang, Jawa Timur</div>
            </div>
        </div>
        <div style="text-align:center;margin-top:18px;">
            <div style="font-size:12px;font-weight:700;">LAPORAN HASIL BELAJAR — ${judul}</div>
            <div style="font-size:10px;color:#555;">Tahun Ajaran 2026/2027</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 24px;font-size:10px;margin-top:16px;padding:8px 0;border-top:1px solid #ddd;border-bottom:1px solid #ddd;">
            <div style="display:flex;"><span style="width:130px;color:#555;">Nama Peserta Didik</span><b>: ${arEsc(s.name)}</b></div>
            <div style="display:flex;"><span style="width:80px;color:#555;">Kelas</span>: ${arEsc(s.kelas)}</div>
            <div style="display:flex;"><span style="width:130px;color:#555;">NISN</span>: ${s.nisn}</div>
            <div style="display:flex;"><span style="width:80px;color:#555;">Fase</span>: E</div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:10.5px;margin-top:16px;">
            <thead>
                <tr>
                    <th style="${TH}width:36px;">No</th>
                    <th style="${TH}text-align:left;">Mata Pelajaran</th>
                    ${thNilai}
                    <th style="${TH}width:80px;">Nilai Akhir</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
        <div style="margin-top:auto;display:flex;justify-content:space-between;font-size:10.5px;padding-top:24px;">
            <div style="text-align:center;width:200px;">
                <div>Mengetahui,</div>
                <div>Orang Tua/Wali Siswa</div>
                <div style="height:52px;"></div>
                <div style="font-weight:700;">..........................................</div>
            </div>
            <div style="text-align:center;width:220px;">
                <div>Malang, ${v.tanggal.split(',')[0]}</div>
                <div>Wali Kelas ${arEsc(s.kelas)}</div>
                <div style="height:52px;"></div>
                <div style="font-weight:700;text-decoration:underline;">${arEsc(s.wali)}</div>
                <div style="font-size:9px;color:#555;">NIP. 198005122006042001</div>
            </div>
        </div>
        <div style="margin-top:10px;padding-top:6px;border-top:1px solid #eee;display:flex;justify-content:space-between;font-size:9px;color:#888;">
            <span>SMA Negeri 1 Kota Malang</span><span>Halaman 1 dari 2</span>
        </div>
    </div>`;

    const hal2 = `
    <div style="${PG}">
        <div style="display:flex;justify-content:space-between;border-bottom:2px solid #111;padding-bottom:6px;font-size:10.5px;">
            <b>NAMA: ${arEsc(s.name).toUpperCase()} · NISN ${s.nisn}</b><b>${arEsc(s.kelas)}</b>
        </div>
        <div style="font-size:12px;font-weight:700;margin-top:20px;">Catatan Perkembangan, Ekstrakurikuler & Kehadiran</div>

        <div style="border:1px solid #333;padding:12px;margin-top:16px;font-size:10.5px;line-height:1.7;">
            <div style="font-weight:700;text-transform:uppercase;">Kegiatan Ekstrakurikuler</div>
            ${ekskul.map((e, i) => `<div>${i + 1}. ${e.nama}: <b>${e.predikat}</b> (${e.ket})</div>`).join('')}
        </div>
        <div style="border:1px solid #333;padding:12px;margin-top:12px;font-size:10.5px;">
            <div style="font-weight:700;text-transform:uppercase;">Ketidakhadiran</div>
            <div>Sakit: <b>${v.absen.sakit} hari</b> | Izin: <b>${v.absen.izin} hari</b> | Tanpa Keterangan: <b>${v.absen.alpa} hari</b></div>
        </div>
        <div style="border:1px solid #333;padding:12px;margin-top:12px;font-size:10.5px;">
            <div style="font-weight:700;text-transform:uppercase;">Catatan Wali Kelas</div>
            <div style="font-style:italic;margin-top:4px;">&ldquo;${AR_CATATAN}&rdquo;</div>
        </div>

        <div style="margin-top:auto;padding-top:6px;border-top:1px solid #eee;display:flex;justify-content:space-between;font-size:9px;color:#888;">
            <span>SMA Negeri 1 Kota Malang</span><span>Halaman 2 dari 2</span>
        </div>
    </div>`;

    return hal1 + hal2;
}

const AR_NAMA = [
    'Adinda Putri', 'Bagas Pratama', 'Citra Lestari', 'Dimas Saputra', 'Eka Wulandari',
    'Ahmad Rizky', 'Farhan Akbar', 'Gita Maharani', 'Hanif Ramadhan', 'Intan Sari',
    'Jovan Pradana', 'Kirana Dewi', 'Laras Puspita', 'Mahesa Nugroho', 'Nadia Safitri',
    'Oki Setiawan', 'Putri Anggraini', 'Qori Hidayat', 'Rafi Kurniawan', 'Sekar Ayu',
    'Tegar Wijaya', 'Ulfa Lestari', 'Vino Saputro', 'Wulan Sari', 'Yoga Prasetya',
    'Zahra Amelia', 'Arya Dwi', 'Bella Oktavia', 'Cahya Nur', 'Dinda Ramadhani',
    'Erland Putra', 'Fira Anindya'
];
const AR_KELAS = ['X-A', 'X-B', 'X-C', 'X-D', 'X-E', 'X-F', 'X-G', 'X-H', 'X-I', 'X-J'];
const AR_KOREKSI_IDX = [3, 9, 15, 22];

const AR_DATA = AR_NAMA.map((nama, i) => {
    const koreksi = AR_KOREKSI_IDX.includes(i);
    const jenis = i % 3 === 2 ? 'Rapor Semester' : 'Rapor STS';
    const seed = i + 5;
    const absen = { sakit: i % 3, izin: (i + 1) % 2, alpa: 0 };
    const versi = [{
        no: 1,
        tanggal: '26 Okt 2026, 14.05',
        oleh: 'Sistem (kunci nilai)',
        alasan: '—',
        ket: 'Versi awal setelah nilai dikunci · Sistem',
        status: koreksi ? 'Arsip' : 'Terbit',
        koreksi: false,
        nilai: arBuatNilai(seed, jenis, koreksi, 1),
        absen,
        wali: 'Siti Rahmawati, S.Pd.',
    }];
    if (koreksi) {
        versi.push({
            no: 2,
            tanggal: '30 Okt 2026, 09.00',
            oleh: 'Dra. Wahyuni, M.Pd.',
            alasan: 'Koreksi nilai TP3 Matematika',
            ket: 'Koreksi nilai TP3 Matematika (8 → 80) · Dra. Wahyuni, M.Pd.',
            status: 'Terbit',
            koreksi: true,
            nilai: arBuatNilai(seed, jenis, true, 2),
            absen,
            wali: 'Siti Rahmawati, S.Pd.',
        });
    }
    return {
        id: i + 1,
        name: nama,
        nisn: String(81234567 + i).padStart(10, '0'),
        kelas: i === 5 ? 'X-A' : 'X-B',
        jenis,
        tahun: '2026/2027',
        semester: 'Ganjil',
        versi,
    };
});

document.addEventListener('alpine:init', () => {

    Alpine.data('arsipRapor', () => ({
        semua: JSON.parse(JSON.stringify(AR_DATA)),
        daftarKelas: AR_KELAS,

        fTahun: '2026/2027',
        fSemester: 'Ganjil',
        fJenis: 'Semua',
        fKelas: 'Semua',
        cari: '',

        page: 1,
        perHalaman: 6,
        expanded: [4],

        modal: { open: false, s: null, v: null, html: '' },
        showToast: false,
        toastMsg: '',
        toastTimer: null,

        // ===== Getter =====
        get hasil() {
            const q = this.cari.trim().toLowerCase();
            return this.semua.filter(s =>
                s.tahun === this.fTahun &&
                s.semester === this.fSemester &&
                (this.fJenis === 'Semua' || s.jenis === this.fJenis) &&
                (this.fKelas === 'Semua' || s.kelas === this.fKelas) &&
                (!q || s.name.toLowerCase().includes(q))
            );
        },
        get totalHalaman() { return Math.max(1, Math.ceil(this.hasil.length / this.perHalaman)); },
        get halaman() {
            const awal = (this.page - 1) * this.perHalaman;
            return this.hasil.slice(awal, awal + this.perHalaman);
        },
        get dariMulai() { return this.hasil.length ? (this.page - 1) * this.perHalaman + 1 : 0; },
        get dariSampai() { return Math.min(this.page * this.perHalaman, this.hasil.length); },
        get daftarHalaman() {
            const t = this.totalHalaman;
            if (t <= 5) return Array.from({ length: t }, (_, i) => i + 1);
            if (this.page <= 2) return [1, 2, '…', t];
            if (this.page >= t - 1) return [1, '…', t - 1, t];
            return [1, '…', this.page, '…', t];
        },

        // ===== Helper =====
        latest(s) { return s.versi[s.versi.length - 1]; },
        riwayat(s) { return [...s.versi].reverse(); },
        labelVersi(v) { return v.koreksi ? 'v' + v.no + ' · Koreksi' : 'v' + v.no; },
        isOpen(id) { return this.expanded.includes(id); },
        toggle(id) {
            this.expanded = this.isOpen(id)
                ? this.expanded.filter(x => x !== id)
                : [...this.expanded, id];
        },
        toast(msg) {
            this.toastMsg = msg;
            this.showToast = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => this.showToast = false, 3000);
        },
        tutupModal() { this.modal.open = false; },

        // ===== Aksi =====
        bukaPratinjau(s, v) {
            this.modal = { open: true, s, v, html: arDokumen(s, v) };
        },

        async unduh(s, v) {
            const label = this.labelVersi(v);
            const namaFile = 'Rapor_' + s.name.replace(/\s+/g, '_') + '_' +
                label.replace(/[^a-zA-Z0-9]+/g, '_') + '.pdf';

            this.toast('Menyiapkan PDF ' + label + ' · ' + s.name + '...');

            if (typeof window.html2pdf !== 'undefined') {
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'position:fixed;left:0;top:0;width:794px;opacity:0;pointer-events:none;z-index:-9999;background:#fff;';
                wrapper.innerHTML = arDokumen(s, v);
                document.body.appendChild(wrapper);

                try {
                    await new Promise(r => setTimeout(r, 120));
                    await window.html2pdf().set({
                        margin: 0,
                        filename: namaFile,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0 },
                        jsPDF: { unit: 'px', format: [794, 1122], orientation: 'portrait' },
                    }).from(wrapper).save();

                    this.toast('PDF berhasil diunduh: ' + namaFile);
                    wrapper.remove();
                    return;
                } catch (err) {
                    console.error('html2pdf error:', err);
                    wrapper.remove();
                }
            }

            const win = window.open('', '_blank', 'width=900,height=1100');
            if (win) {
                win.document.write(`<!DOCTYPE html><html><head><title>${namaFile}</title><style>@page{size:A4;margin:0;}body{margin:0;}</style></head><body>${arDokumen(s, v)}<script>window.onload=function(){window.print();}<\/script></body></html>`);
                win.document.close();
            } else {
                this.toast('Popup diblokir browser. Izinkan popup untuk menyimpan PDF.');
            }
        },
    }));
});
</script>
@endsection