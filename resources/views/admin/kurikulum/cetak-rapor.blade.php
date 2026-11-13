@extends('layouts.app')

@section('content')
{{-- Hanya butuh jsPDF murni (Menggambar PDF Vektor Asli, Bebas Error CORS/CSS) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
    [x-cloak] { display: none !important; }

    .cetak-rapor-grid {
        display: grid;
        grid-template-columns: 310px 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .cetak-rapor-grid { grid-template-columns: 1fr; }
    }

    /* Lembar Pratinjau A4 */
    .rapor-sheet {
        width: 100%;
        max-width: 660px;
        min-height: 860px;
        box-sizing: border-box;
        background: #ffffff;
        margin: 0 auto;
        padding: 36px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        font-size: 11px;
        line-height: 1.5;
        color: #111827;
    }

    /* Perbaikan Input Search agar tidak tertimpa CSS global project */
    .search-box-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 320px;
    }
    .search-box-wrap svg {
        position: absolute;
        left: 12px;
        width: 15px;
        height: 15px;
        color: #9ca3af;
        pointer-events: none;
    }
    .search-box-input {
        width: 100% !important;
        height: 34px !important;
        padding: 0 12px 0 34px !important;
        font-size: 12px !important;
        background-color: #f9fafb !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 12px !important;
        color: #1f2937 !important;
        outline: none !important;
        box-sizing: border-box !important;
    }
    .search-box-input:focus {
        background-color: #ffffff !important;
        border-color: #0ea5e9 !important;
        box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.15) !important;
    }

    /* Mode Print Browser (Ctrl+P / Tombol Cetak) */
    @media print {
        @page { size: A4; margin: 12mm; }
        body * { visibility: hidden !important; }
        #area-print-rapor, #area-print-rapor * { visibility: visible !important; }
        #area-print-rapor {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
        }
        .rapor-sheet {
            max-width: none !important;
            min-height: 0 !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .no-print { display: none !important; }
    }
</style>

<div class="p-6 max-w-7xl mx-auto space-y-5" x-data="raporCetak" x-cloak>

    {{-- HEADER --}}
    <div class="flex items-start justify-between no-print">
        <div>
            <p class="text-[11px] font-bold text-sky-500 tracking-wider uppercase">SMA NEGERI 1 KOTA MALANG · KURIKULUM</p>
            <h1 class="text-2xl font-bold text-gray-900 mt-0.5">Cetak & Ekspor Rapor</h1>
            <p class="text-xs text-gray-500 mt-0.5">Pratinjau, cetak, dan unduh rapor siswa tanpa mengolah ulang dokumen.</p>
        </div>
        <a href="{{ url('/admin/kurikulum/terbitkan-rapor') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1 transition mt-1">
            Lanjut ke Terbitkan Rapor
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>

    {{-- FILTER TOOLBAR --}}
    <div class="bg-white p-2.5 rounded-2xl border border-gray-200 shadow-xs flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-3 flex-1 min-w-[280px]">

            {{-- Dropdown Kelas --}}
            <div class="relative">
                <button @click="showClassDropdown = !showClassDropdown"
                        class="px-3.5 py-1.5 bg-gray-50 border border-gray-200 hover:bg-gray-100 rounded-xl text-xs font-bold text-gray-800 flex items-center gap-2 transition">
                    <span>Kelas: <strong x-text="selectedClass"></strong></span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="showClassDropdown"
                     @click.outside="showClassDropdown = false"
                     x-transition
                     class="absolute top-full left-0 mt-2 w-64 bg-white border border-gray-200 rounded-2xl shadow-xl z-30 p-3 space-y-1 text-xs">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2 py-1">Kelas X</p>
                    <div class="max-h-60 overflow-y-auto space-y-0.5">
                        <template x-for="k in daftarKelas" :key="k.name">
                            <button @click="pilihKelas(k.name)"
                                    :class="selectedClass === k.name ? 'bg-sky-50 text-sky-700 font-bold' : 'hover:bg-gray-50 text-gray-700 font-medium'"
                                    class="w-full px-2.5 py-2 rounded-xl flex items-center justify-between transition text-left">
                                <span class="flex items-center gap-2">
                                    <svg x-show="selectedClass === k.name" class="w-3.5 h-3.5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="'Kelas ' + k.name"></span>
                                </span>
                                <span :class="badge(k.status)" class="px-2 py-0.5 rounded-full text-[9px] font-bold" x-text="k.status"></span>
                            </button>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-400 px-2 pt-2 border-t border-gray-100">Hanya kelas terkunci yang dapat dicetak final.</p>
                </div>
            </div>

            {{-- Pencarian Nama atau NISN (Presisi Rapi) --}}
            <div class="search-box-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="searchQuery" @input="currentPage = 1"
                       placeholder="Cari nama atau NISN..."
                       class="search-box-input">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button @click="pdfSatuKelas()"
                    class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold shadow-xs transition active:scale-95">
                PDF satu kelas
            </button>
            <button @click="pdfSemuaTerkunci()"
                    class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold shadow-xs transition active:scale-95">
                PDF semua kelas terkunci
            </button>
        </div>
    </div>

    {{-- GRID UTAMA --}}
    <div class="cetak-rapor-grid">

        {{-- KOLOM KIRI: DAFTAR SISWA --}}
<style>
    .sl-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; box-shadow:0 1px 2px rgba(0,0,0,.05); min-height:480px; display:flex; flex-direction:column; }
    .sl-head { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #f3f4f6; background:#fafafa; }
    .sl-title { font-size:11px; font-weight:700; letter-spacing:.08em; color:#6b7280; text-transform:uppercase; }
    .sl-count { font-size:10px; font-weight:600; color:#6b7280; background:#fff; border:1px solid #e5e7eb; padding:2px 8px; border-radius:999px; }
    .sl-item { position:relative; width:100%; display:flex; align-items:center; gap:12px; padding:12px 16px; background:#fff; border:0; border-bottom:1px solid #f3f4f6; text-align:left; cursor:pointer; transition:background .15s; }
    .sl-item:last-child { border-bottom:0; }
    .sl-item:hover { background:#f9fafb; }
    .sl-item.active { background:#f0f9ff; }
    .sl-item.active::before { content:""; position:absolute; left:0; top:8px; bottom:8px; width:3px; border-radius:0 3px 3px 0; background:#0ea5e9; }
    .sl-avatar { width:36px; height:36px; border-radius:50%; background:#f3f4f6; color:#6b7280; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .sl-item.active .sl-avatar { background:#0ea5e9; color:#fff; }
    .sl-info { min-width:0; flex:1; }
    .sl-name { font-size:12px; font-weight:700; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sl-meta { font-size:10px; color:#9ca3af; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sl-badge { flex-shrink:0; font-size:10px; font-weight:600; padding:3px 8px; border-radius:999px; white-space:nowrap; border:1px solid; }
    .sl-badge.ok { background:#ecfdf5; color:#047857; border-color:#a7f3d0; }
    .sl-badge.no { background:#f3f4f6; color:#6b7280; border-color:#e5e7eb; }
    .sl-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:48px 16px; text-align:center; font-size:12px; color:#6b7280; }
    .sl-pager { display:flex; align-items:center; justify-content:space-between; font-size:12px; color:#6b7280; padding:0 4px; margin-top:12px; }
    .sl-pages { display:flex; gap:4px; }
    .sl-pg { width:28px; height:28px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; color:#4b5563; font-size:12px; display:flex; align-items:center; justify-content:center; cursor:pointer; }
    .sl-pg:hover { background:#f9fafb; }
    .sl-pg:disabled { opacity:.4; cursor:not-allowed; }
    .sl-pg.active { background:#0ea5e9; border-color:#0ea5e9; color:#fff; font-weight:700; }
</style>

<div class="space-y-3 no-print">

    <div class="sl-card">
        <div class="sl-head">
            <span class="sl-title">Daftar Siswa</span>
            <span class="sl-count"><span x-text="siswaCari.length"></span> siswa</span>
        </div>

        <div class="flex-1">
            <template x-for="s in siswaHalaman" :key="s.id">
                <button type="button"
                        @click="selectedStudent = s"
                        :class="selectedStudent && selectedStudent.id === s.id ? 'sl-item active' : 'sl-item'">
                    <div class="sl-avatar">
                        <span x-text="s.name.split(' ').map(n => n[0]).join('').slice(0, 2)"></span>
                    </div>
                    <div class="sl-info">
                        <div class="sl-name" x-text="s.name"></div>
                        <div class="sl-meta" x-text="s.nisn + ' · ' + s.kelas"></div>
                    </div>
                    <span :class="s.status.startsWith('PDF') ? 'sl-badge ok' : 'sl-badge no'"
                          x-text="s.status"></span>
                </button>
            </template>
        </div>

        <div x-show="siswaCari.length === 0" class="sl-empty">
            <p style="font-weight:600;color:#4b5563;">Siswa tidak ditemukan</p>
            <p style="font-size:10px;color:#9ca3af;margin-top:4px;">Coba kata kunci lain atau ganti kelas.</p>
        </div>
    </div>

    <div class="sl-pager">
        <span>
            <span x-text="dariMulai"></span>–<span x-text="dariSampai"></span>
            dari <span x-text="siswaCari.length"></span>
        </span>
        <div class="sl-pages">
            <button type="button" class="sl-pg" @click="currentPage > 1 && currentPage--" :disabled="currentPage === 1">&lsaquo;</button>
            <template x-for="p in totalHalaman" :key="p">
                <button type="button" class="sl-pg"
                        :class="currentPage === p ? 'active' : ''"
                        @click="currentPage = p"
                        x-text="p"></button>
            </template>
            <button type="button" class="sl-pg" @click="currentPage < totalHalaman && currentPage++" :disabled="currentPage === totalHalaman">&rsaquo;</button>
        </div>
    </div>
</div>

        {{-- KOLOM KANAN: PRATINJAU DOKUMEN --}}
        <div class="space-y-3 min-w-0">

            {{-- Toolbar Pratinjau --}}
            <div class="flex items-center justify-between gap-2 flex-wrap no-print">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs text-gray-500 font-medium">
                        Pratinjau rapor ·
                        <span class="text-gray-900 font-bold" x-text="selectedStudent ? selectedStudent.name + ' - ' + selectedStudent.kelas : '-'"></span>
                    </span>
                    <div class="inline-flex bg-gray-200/80 p-0.5 rounded-lg text-[10px]">
                        <button @click="docPage = 1"
                                :class="docPage === 1 ? 'bg-white text-sky-600 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                                class="px-2.5 py-1 rounded-md transition">Hal 1</button>
                        <button @click="docPage = 2"
                                :class="docPage === 2 ? 'bg-white text-sky-600 font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                                class="px-2.5 py-1 rounded-md transition">Hal 2</button>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="cetakPratinjau()"
                            class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 active:scale-95">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                    <button @click="unduhPdfSiswa()"
                            class="px-3.5 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh PDF
                    </button>
                </div>
            </div>

            {{-- Tab STS / Semester --}}
            <div class="bg-gray-200/70 p-1 rounded-xl flex items-center gap-1 text-xs w-max no-print">
                <button @click="previewTab = 'sts'"
                        :class="previewTab === 'sts' ? 'bg-white text-sky-600 font-bold shadow-xs' : 'text-gray-600 font-medium hover:text-gray-900'"
                        class="px-3.5 py-1 rounded-lg transition">Rapor STS</button>
                <button @click="previewTab = 'semester'"
                        :class="previewTab === 'semester' ? 'bg-white text-sky-600 font-bold shadow-xs' : 'text-gray-600 font-medium hover:text-gray-900'"
                        class="px-3.5 py-1 rounded-lg transition">Rapor Semester</button>
            </div>

            {{-- AREA PRATINJAU KERTAS A4 --}}
            <div id="area-print-rapor" class="bg-[#F1F5F9] p-6 sm:p-8 rounded-2xl border border-gray-200 shadow-inner overflow-x-auto">
                <div x-show="!selectedStudent" class="py-24 text-center text-xs text-gray-400">
                    Pilih siswa untuk melihat pratinjau rapor
                </div>

                <template x-if="laporanAktif">
                    <div>
                        {{-- HALAMAN 1 --}}
                        <div class="rapor-sheet" x-show="docPage === 1">
                            <div class="flex items-center gap-3.5 border-b-2 border-gray-900 pb-2">
                                <div class="w-10 h-10 border-2 border-gray-900 font-black text-xs flex items-center justify-center shrink-0">S</div>
                                <div class="text-center flex-1 pr-8">
                                    <p class="font-bold text-[9px] uppercase tracking-wider text-gray-800">PEMERINTAH PROVINSI JAWA TIMUR · DINAS PENDIDIKAN</p>
                                    <h2 class="font-black text-[12px] uppercase tracking-wide text-gray-900 mt-0.5">SMA NEGERI 1 KOTA MALANG</h2>
                                    <p class="text-[8.5px] text-gray-600">Jl. Tugu Utara No. 1, Kota Malang, Jawa Timur</p>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <h3 class="font-bold text-[11px] uppercase tracking-wide">
                                    LAPORAN HASIL BELAJAR — <span x-text="laporanAktif.judulTab"></span>
                                </h3>
                                <p class="text-[9.5px] text-gray-600 mt-0.5">Tahun Ajaran 2026/2027</p>
                            </div>

                            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 0.5rem;" class="text-[10px] py-2 border-y border-gray-200 mt-4">
                                <div class="space-y-1">
                                    <div class="flex"><span style="width: 110px;" class="text-gray-500 shrink-0">Nama Peserta Didik</span><span class="font-bold" x-text="': ' + laporanAktif.nama"></span></div>
                                    <div class="flex"><span style="width: 110px;" class="text-gray-500 shrink-0">NISN</span><span x-text="': ' + laporanAktif.nisn"></span></div>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex"><span style="width: 45px;" class="text-gray-500 shrink-0">Kelas</span><span x-text="': ' + laporanAktif.kelas"></span></div>
                                    <div class="flex"><span style="width: 45px;" class="text-gray-500 shrink-0">Fase</span><span>: E</span></div>
                                </div>
                            </div>

                            <table class="w-full border-collapse border border-gray-800 text-[10px] mt-4">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-900 border-b border-gray-800 font-bold">
                                        <th class="border border-gray-800 p-1.5 w-6 text-center">No</th>
                                        <th class="border border-gray-800 p-1.5 text-left">Mata Pelajaran</th>
                                        <template x-for="col in laporanAktif.columns" :key="col.key">
                                            <th class="border border-gray-800 p-1.5 w-10 text-center" x-text="col.label"></th>
                                        </template>
                                        <th class="border border-gray-800 p-1.5 w-12 text-center font-bold">Nilai Akhir</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800 text-center">
                                    <template x-for="row in laporanAktif.rows" :key="row.no">
                                        <tr>
                                            <td class="border border-gray-800 p-1.5" x-text="row.no"></td>
                                            <td class="border border-gray-800 p-1.5 text-left font-medium" x-text="row.nama"></td>
                                            <template x-for="col in laporanAktif.columns" :key="col.key">
                                                <td class="border border-gray-800 p-1.5" x-text="row.scores[col.key]"></td>
                                            </template>
                                            <td class="border border-gray-800 p-1.5 font-bold" x-text="row.akhir"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <div class="pt-6 flex justify-between items-end text-[10px] mt-auto">
                                <div class="text-center">
                                    <p>Mengetahui,</p>
                                    <p>Orang Tua/Wali Siswa</p>
                                    <div class="h-12"></div>
                                    <p class="font-bold">.............................................</p>
                                </div>
                                <div class="text-center">
                                    <p>Malang, 30 Oktober 2026</p>
                                    <p x-text="'Wali Kelas ' + laporanAktif.kelas"></p>
                                    <div class="h-12"></div>
                                    <p class="font-bold underline">Siti Rahmawati, S.Pd.</p>
                                    <p class="text-gray-500">NIP. 198005122006042001</p>
                                </div>
                            </div>

                            <div class="pt-2 flex justify-between text-[8.5px] text-gray-400 border-t border-gray-100 mt-3">
                                <span>SMA Negeri 1 Kota Malang</span>
                                <span>Halaman 1 dari 2</span>
                            </div>
                        </div>

                        {{-- HALAMAN 2 --}}
                        <div class="rapor-sheet" x-show="docPage === 2" x-cloak>
                            <p class="font-bold border-b border-gray-300 pb-1 text-[11px]">Catatan Perkembangan & Ekstrakurikuler</p>

                            <div class="border border-gray-800 p-3 space-y-1.5 text-[10px] mt-4">
                                <p class="font-bold uppercase text-gray-900">Kegiatan Ekstrakurikuler</p>
                                <template x-for="(ek, idx) in laporanAktif.ekskul" :key="idx">
                                    <p><span x-text="idx + 1"></span>. <span x-text="ek.nama"></span>: <b x-text="ek.predikat"></b> (<span x-text="ek.ket"></span>)</p>
                                </template>
                            </div>

                            <div class="border border-gray-800 p-3 space-y-1.5 text-[10px] mt-4">
                                <p class="font-bold uppercase text-gray-900">Ketidakhadiran</p>
                                <p>Sakit: <b x-text="laporanAktif.absen.sakit + ' hari'"></b> | Izin: <b x-text="laporanAktif.absen.izin + ' hari'"></b> | Tanpa Keterangan: <b x-text="laporanAktif.absen.alpa + ' hari'"></b></p>
                            </div>

                            <div class="border border-gray-800 p-3 space-y-1.5 text-[10px] mt-4">
                                <p class="font-bold uppercase text-gray-900">Catatan Wali Kelas</p>
                                <p class="italic text-gray-800" x-text="'&quot;' + laporanAktif.catatan + '&quot;'"></p>
                            </div>

                            <div class="pt-6 flex justify-between items-end text-[10px] mt-auto">
                                <div class="text-center">
                                    <p>Mengetahui,</p>
                                    <p>Orang Tua/Wali Siswa</p>
                                    <div class="h-12"></div>
                                    <p class="font-bold">.............................................</p>
                                </div>
                                <div class="text-center">
                                    <p>Malang, 30 Oktober 2026</p>
                                    <p x-text="'Wali Kelas ' + laporanAktif.kelas"></p>
                                    <div class="h-12"></div>
                                    <p class="font-bold underline">Siti Rahmawati, S.Pd.</p>
                                    <p class="text-gray-500">NIP. 198005122006042001</p>
                                </div>
                            </div>

                            <div class="pt-2 flex justify-between text-[8.5px] text-gray-400 border-t border-gray-100 mt-3">
                                <span>SMA Negeri 1 Kota Malang</span>
                                <span>Halaman 2 dari 2</span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- TOAST NOTIFICATION --}}
    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-4 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-4 opacity-0"
         class="fixed bottom-6 right-6 z-50 bg-slate-800 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700 no-print">
        <div :class="toastType === 'error' ? 'bg-rose-500' : 'bg-emerald-500'"
             class="w-6 h-6 rounded-full flex items-center justify-center text-white shrink-0">
            <svg x-show="toastType !== 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="toastType === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <span class="text-sm font-medium" x-text="toastMessage"></span>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    const NAMA = [
        'Citra Lestari', 'Ahmad Fauzi', 'Budi Santoso', 'Dina Maria', 'Eko Prasetyo',
        'Fitri Handayani', 'Gilang Ramadhan', 'Hendra Wijaya', 'Indah Permata', 'Joko Susilo',
        'Kania Putri', 'Lukman Hakim', 'Maya Sandrina', 'Naufal Rizky', 'Olivia Zalianty'
    ];
    const MAPEL = [
        'Pendidikan Agama Islam dan Budi Pekerti', 'Pendidikan Pancasila', 'Bahasa Indonesia',
        'Matematika', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Sejarah'
    ];
    const KELAS = [
        { name: 'X-A', status: 'Terbuka' },
        { name: 'X-B', status: 'Terkunci' },
        { name: 'X-C', status: 'Terbuka' },
        { name: 'X-D', status: 'Terbuka' },
        { name: 'X-E', status: 'Terkoreksi' },
        { name: 'X-G', status: 'Terkunci' },
    ];
    const SISWA = [];
    KELAS.forEach((k, ci) => {
        NAMA.forEach((nama, i) => {
            const idx = ci * 15 + i;
            SISWA.push({
                id: idx + 1,
                name: nama,
                nisn: String(81234552 + idx).padStart(10, '0'),
                kelas: k.name,
                seed: idx * 7 + 3,
            });
        });
    });

    const nilai = (seed, j, k) => 74 + ((seed * 7 + j * 13 + k * 11) % 22);

    Alpine.data('raporCetak', () => ({
        selectedClass: 'X-B',
        showClassDropdown: false,
        searchQuery: '',
        currentPage: 1,
        itemsPerPage: 8,
        docPage: 1,
        previewTab: 'sts',
        selectedStudent: null,
        showToast: false,
        toastMessage: '',
        toastType: 'success',
        toastTimer: null,

        daftarKelas: KELAS,
        semuaSiswa: SISWA,
        mapel: MAPEL,

        init() {
            this.selectedStudent = this.siswaKelas[0] || null;
        },
        get kelasAktif() { return this.daftarKelas.find(k => k.name === this.selectedClass); },
        get bisaCetak() { return !!this.kelasAktif && this.kelasAktif.status === 'Terkunci'; },
        get siswaKelas() { return this.semuaSiswa.filter(s => s.kelas === this.selectedClass); },
        get siswaCari() {
            const q = this.searchQuery.trim().toLowerCase();
            return this.siswaKelas.filter(s => !q || s.name.toLowerCase().includes(q) || s.nisn.includes(q));
        },
        get totalHalaman() { return Math.max(1, Math.ceil(this.siswaCari.length / this.itemsPerPage)); },
        get siswaHalaman() {
            const awal = (this.currentPage - 1) * this.itemsPerPage;
            return this.siswaCari.slice(awal, awal + this.itemsPerPage);
        },
        get dariMulai() { return this.siswaCari.length ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0; },
        get dariSampai() { return Math.min(this.currentPage * this.itemsPerPage, this.siswaCari.length); },
        get laporanAktif() { return this.selectedStudent ? this.buatLaporan(this.selectedStudent, this.previewTab) : null; },

        badge(status) {
            if (status === 'Terkunci')   return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            if (status === 'Terkoreksi') return 'bg-amber-50 text-amber-700 border border-amber-200';
            return 'bg-gray-100 text-gray-600';
        },
        toast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            this.showToast = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => this.showToast = false, 3500);
        },
        pastikanTerkunci() {
            if (!this.bisaCetak) {
                this.toast('Hanya kelas terkunci yang dapat dicetak final.', 'error');
                return false;
            }
            return true;
        },
        pilihKelas(nama) {
            this.selectedClass = nama;
            this.showClassDropdown = false;
            this.currentPage = 1;
            this.docPage = 1;
            this.selectedStudent = this.siswaKelas[0] || null;
        },
        buatLaporan(s, tab) {
            const isSts = tab === 'sts';
            const columns = isSts
                ? [{ key: 'tp1', label: 'TP1' }, { key: 'tp2', label: 'TP2' }, { key: 'tp3', label: 'TP3' }, { key: 'tp4', label: 'TP4' }]
                : [{ key: 'sts', label: 'STS' }, { key: 'sas', label: 'SAS' }];
            const rows = this.mapel.map((nama, j) => {
                const scores = isSts
                    ? { tp1: nilai(s.seed, j, 0), tp2: nilai(s.seed, j, 1), tp3: nilai(s.seed, j, 2), tp4: nilai(s.seed, j, 3) }
                    : { sts: nilai(s.seed, j, 4), sas: nilai(s.seed, j, 5) };
                const vals = Object.values(scores);
                return { no: j + 1, nama, scores, akhir: Math.round(vals.reduce((a, b) => a + b, 0) / vals.length) };
            });
            return {
                nama: s.name, nisn: s.nisn, kelas: s.kelas,
                judulTab: isSts ? 'SUMATIF TENGAH SEMESTER' : 'SEMESTER GANJIL',
                columns, rows,
                ekskul: [
                    { nama: 'Pramuka', predikat: 'Sangat Baik', ket: 'Aktif dalam kegiatan latihan rutin' },
                    { nama: 'Paskibra', predikat: 'Baik', ket: 'Menunjukkan kedisiplinan & kepemimpinan' },
                ],
                absen: { sakit: s.seed % 3, izin: (s.seed + 1) % 2, alpa: 0 },
                catatan: 'Pertahankan prestasi akademis yang memuaskan ini. Tingkatkan motivasi belajar di semester berikutnya.',
            };
        },

        cetakPratinjau() {
            if (!this.selectedStudent || !this.pastikanTerkunci()) return;
            window.print();
        },

        unduhPdfSiswa() {
            if (!this.selectedStudent || !this.pastikanTerkunci()) return;
            const namaFile = 'Rapor_' + this.selectedStudent.name.replace(/\s+/g, '_') + '_' + this.selectedStudent.kelas + '.pdf';
            this.generateVectorPdf([this.laporanAktif], namaFile);
        },

        pdfSatuKelas() {
            if (!this.pastikanTerkunci()) return;
            const daftar = this.siswaKelas.map(s => this.buatLaporan(s, this.previewTab));
            this.generateVectorPdf(daftar, 'Rapor_Kelas_' + this.selectedClass + '.pdf');
        },

        pdfSemuaTerkunci() {
            const kelasTerkunci = this.daftarKelas.filter(k => k.status === 'Terkunci').map(k => k.name);
            const daftar = this.semuaSiswa.filter(s => kelasTerkunci.includes(s.kelas)).map(s => this.buatLaporan(s, this.previewTab));
            this.generateVectorPdf(daftar, 'Rapor_Semua_Kelas_Terkunci.pdf');
        },

        // GENERATOR PDF VEKTOR MURNI (Anti-CORS, Anti-CSS Error, 100% Terunduh)
        generateVectorPdf(daftar, namaFile) {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                this.toast('Library PDF belum termuat, pastikan koneksi internet aktif.', 'error');
                return;
            }
            try {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });

                daftar.forEach((rapor, idx) => {
                    if (idx > 0) doc.addPage();

                    // === HALAMAN 1: NILAI ===
                    doc.setLineWidth(0.5);
                    doc.rect(18, 16, 13, 13);
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(11);
                    doc.text('S', 24.5, 24, { align: 'center' });

                    doc.setFontSize(8.5);
                    doc.text('PEMERINTAH PROVINSI JAWA TIMUR · DINAS PENDIDIKAN', 110, 19, { align: 'center' });
                    doc.setFontSize(12);
                    doc.text('SMA NEGERI 1 KOTA MALANG', 110, 24.5, { align: 'center' });
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(8.5);
                    doc.text('Jl. Tugu Utara No. 1, Kota Malang, Jawa Timur', 110, 29, { align: 'center' });

                    doc.setLineWidth(0.7);
                    doc.line(18, 33, 192, 33);

                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(10.5);
                    doc.text('LAPORAN HASIL BELAJAR — ' + rapor.judulTab, 105, 42, { align: 'center' });
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(9);
                    doc.text('Tahun Ajaran 2026/2027', 105, 47, { align: 'center' });

                    // Identitas
                    doc.setLineWidth(0.2);
                    doc.line(18, 52, 192, 52);
                    doc.setFontSize(9.5);
                    doc.text('Nama Peserta Didik', 18, 58);
                    doc.setFont('helvetica', 'bold');
                    doc.text(': ' + rapor.nama, 54, 58);
                    doc.setFont('helvetica', 'normal');
                    doc.text('NISN', 18, 63.5);
                    doc.text(': ' + rapor.nisn, 54, 63.5);

                    doc.text('Kelas', 142, 58);
                    doc.text(': ' + rapor.kelas, 156, 58);
                    doc.text('Fase', 142, 63.5);
                    doc.text(': E', 156, 63.5);
                    doc.line(18, 67.5, 192, 67.5);

                    // Tabel Header
                    let y = 74;
                    const isSts = rapor.columns.length === 4;
                    doc.setFillColor(243, 244, 246);
                    doc.rect(18, y, 174, 8, 'FD');
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(9);
                    doc.text('No', 23, y + 5.3, { align: 'center' });
                    doc.text('Mata Pelajaran', 30, y + 5.3);

                    if (isSts) {
                        doc.text('TP1', 124, y + 5.3, { align: 'center' });
                        doc.text('TP2', 138, y + 5.3, { align: 'center' });
                        doc.text('TP3', 152, y + 5.3, { align: 'center' });
                        doc.text('TP4', 166, y + 5.3, { align: 'center' });
                    } else {
                        doc.text('STS', 131, y + 5.3, { align: 'center' });
                        doc.text('SAS', 159, y + 5.3, { align: 'center' });
                    }
                    doc.text('Nilai Akhir', 182.5, y + 5.3, { align: 'center' });

                    // Garis Vertikal Header
                    doc.line(28, y, 28, y + 8);
                    if (isSts) {
                        [117, 131, 145, 159, 173].forEach(x => doc.line(x, y, x, y + 8));
                    } else {
                        [117, 145, 173].forEach(x => doc.line(x, y, x, y + 8));
                    }

                    // Isi Tabel
                    y += 8;
                    doc.setFont('helvetica', 'normal');
                    rapor.rows.forEach(row => {
                        doc.rect(18, y, 174, 8);
                        doc.line(28, y, 28, y + 8);
                        doc.text(String(row.no), 23, y + 5.3, { align: 'center' });
                        doc.text(row.nama, 30, y + 5.3);

                        if (isSts) {
                            [117, 131, 145, 159, 173].forEach(x => doc.line(x, y, x, y + 8));
                            doc.text(String(row.scores.tp1), 124, y + 5.3, { align: 'center' });
                            doc.text(String(row.scores.tp2), 138, y + 5.3, { align: 'center' });
                            doc.text(String(row.scores.tp3), 152, y + 5.3, { align: 'center' });
                            doc.text(String(row.scores.tp4), 166, y + 5.3, { align: 'center' });
                        } else {
                            [117, 145, 173].forEach(x => doc.line(x, y, x, y + 8));
                            doc.text(String(row.scores.sts), 131, y + 5.3, { align: 'center' });
                            doc.text(String(row.scores.sas), 159, y + 5.3, { align: 'center' });
                        }
                        doc.setFont('helvetica', 'bold');
                        doc.text(String(row.akhir), 182.5, y + 5.3, { align: 'center' });
                        doc.setFont('helvetica', 'normal');
                        y += 8;
                    });

                    // Tanda Tangan Hal 1
                    this.drawFooter(doc, rapor.kelas, 1);

                    // === HALAMAN 2: CATATAN & EKSKUL ===
                    doc.addPage();
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(11);
                    doc.text('Catatan Perkembangan & Ekstrakurikuler', 18, 22);
                    doc.line(18, 25, 192, 25);

                    // Box Ekskul
                    doc.rect(18, 32, 174, 24);
                    doc.setFontSize(9.5);
                    doc.text('KEGIATAN EKSTRAKURIKULER', 22, 38);
                    doc.setFont('helvetica', 'normal');
                    rapor.ekskul.forEach((ek, i) => {
                        doc.text(`${i + 1}. ${ek.nama}: ${ek.predikat} (${ek.ket})`, 22, 44 + i * 5.5);
                    });

                    // Box Ketidakhadiran
                    doc.rect(18, 62, 174, 18);
                    doc.setFont('helvetica', 'bold');
                    doc.text('KETIDAKHADIRAN', 22, 68);
                    doc.setFont('helvetica', 'normal');
                    doc.text(`Sakit: ${rapor.absen.sakit} hari   |   Izin: ${rapor.absen.izin} hari   |   Tanpa Keterangan: ${rapor.absen.alpa} hari`, 22, 74);

                    // Box Catatan Wali Kelas
                    doc.rect(18, 86, 174, 22);
                    doc.setFont('helvetica', 'bold');
                    doc.text('CATATAN WALI KELAS', 22, 92);
                    doc.setFont('helvetica', 'italic');
                    doc.text(`"${rapor.catatan}"`, 22, 98, { maxWidth: 166 });

                    this.drawFooter(doc, rapor.kelas, 2);
                });

                doc.save(namaFile);
                this.toast('Berhasil mengunduh ' + namaFile, 'success');
            } catch (err) {
                console.error(err);
                this.toast('Gagal membuat PDF: ' + err.message, 'error');
            }
        },

        drawFooter(doc, kelas, pageNum) {
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9.5);
            doc.text('Mengetahui,', 42, 232, { align: 'center' });
            doc.text('Orang Tua/Wali Siswa', 42, 237, { align: 'center' });
            doc.setFont('helvetica', 'bold');
            doc.text('.............................................', 42, 258, { align: 'center' });

            doc.setFont('helvetica', 'normal');
            doc.text('Malang, 30 Oktober 2026', 162, 232, { align: 'center' });
            doc.text('Wali Kelas ' + kelas, 162, 237, { align: 'center' });
            doc.setFont('helvetica', 'bold');
            doc.text('Siti Rahmawati, S.Pd.', 162, 258, { align: 'center' });
            doc.line(143, 259, 181, 259);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.text('NIP. 198005122006042001', 162, 263, { align: 'center' });

            doc.setTextColor(150);
            doc.text('SMA Negeri 1 Kota Malang', 18, 282);
            doc.text(`Halaman ${pageNum} dari 2`, 192, 282, { align: 'right' });
            doc.setTextColor(0);
        }
    }));
});
</script>
@endsection