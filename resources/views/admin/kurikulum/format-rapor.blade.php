@extends('layouts.app')

@section('content')
<style>
    .format-rapor-container {
        display: flex;
        flex-direction: row;
        gap: 1.5rem;
        align-items: flex-start;
    }
    .format-rapor-left {
        width: 360px;
        min-width: 360px;
        flex-shrink: 0;
    }
    .format-rapor-right {
        flex: 1;
        min-width: 0;
    }
    /* Hanya beralih ke 1 kolom jika layar benar-benar kecil/layar HP */
    @media (max-width: 1024px) {
        .format-rapor-container {
            flex-direction: column;
        }
        .format-rapor-left {
            width: 100%;
            min-width: 100%;
        }
        .format-rapor-right {
            width: 100%;
        }
    }
</style>

<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{
    // State Simpan & Perubahan
    isSaved: false,
    showToast: false,
    toastMessage: '',

    // Form State (Real-time Binding ke Preview)
    logoName: 'logo_sman1.png',
    baris1: 'PEMERINTAH PROVINSI JAWA TIMUR · DINAS PENDIDIKAN',
    baris2: '',
    namaSekolah: 'SMA NEGERI 1 KOTA MALANG',
    alamat: 'Jl. Tugu Utara No. 1, Kota Malang, Jawa Timur',
    kontak: '(0341) 366454 · sman1malang.sch.id',
    
    // Kepala Sekolah
    namaKepsek: 'Dr. Hendra Prasetyo, M.Pd.',
    nipKepsek: '197205141998021003',
    ttdName: '',

    // Tanggal Rapor
    tempat: 'Malang',
    tanggalSts: '2026-10-30',
    tanggalSemester: '2026-12-18',

    // Toggle Components
    showCapaian: true,
    showKokurikuler: true,
    showEkstra: true,
    showKetidakfahiran: true,
    showCatatanWali: true,
    showQr: false,

    // Active Preview Tab
    previewTab: 'sts',

    markAsUnsaved() {
        this.isSaved = false;
    },

    saveFormat() {
        this.isSaved = true;
        this.toastMessage = 'Format Rapor berhasil disimpan!';
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 3000);
    },

    formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }
}">

    {{-- Title Header & Dynamic Save Indicator Status --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold text-sky-500 tracking-wider uppercase">SMA NEGERI 1 KOTA MALANG · KURIKULUM</p>
            <h1 class="text-3xl font-bold text-gray-900 mt-1">Format Rapor</h1>
            <p class="text-xs text-gray-500 mt-1">Atur kop, identitas, dan komponen yang tampil di rapor.</p>
        </div>
        <div class="flex items-center gap-3">
            <span x-show="!isSaved" class="text-xs text-amber-600 font-semibold flex items-center gap-1.5 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/60">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                Perubahan belum disimpan
            </span>
            <span x-show="isSaved" x-cloak class="text-xs text-emerald-600 font-semibold flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/60">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Perubahan tersimpan
            </span>

            <button @click="saveFormat()" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold shadow-xs transition active:scale-95 shrink-0">
                Simpan Format
            </button>
        </div>
    </div>

    {{-- Layout Utama Flexbox Hybrid --}}
    <div class="format-rapor-container">

        {{-- KOLOM KIRI: FORM PENGATURAN (360px Fixed Desktop) --}}
        <div class="format-rapor-left bg-white p-5 rounded-2xl border border-gray-200 shadow-xs space-y-5">
            <h3 class="text-xs font-bold text-gray-900 border-b border-gray-100 pb-3">Pengaturan</h3>

            {{-- Section: Kop & Identitas --}}
            <div class="space-y-3">
                <h4 class="text-[11px] font-bold text-gray-700">Kop & Identitas</h4>
                
                <!-- Upload Logo -->
                <div class="border border-gray-200 rounded-xl p-2.5 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white border border-gray-300 font-bold text-gray-700 flex items-center justify-center text-xs shadow-xs">
                            S
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-800" x-text="logoName"></p>
                            <p class="text-[9px] text-gray-400">PNG · 512x512 px</p>
                        </div>
                    </div>
                    <label class="px-2.5 py-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-[10px] font-bold shadow-xs cursor-pointer transition flex items-center gap-1">
                        <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/></svg>
                        Ganti
                        <input type="file" class="hidden" accept="image/*" @change="markAsUnsaved(); if($event.target.files[0]) logoName = $event.target.files[0].name">
                    </label>
                </div>

                <!-- Input Baris 1 -->
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 mb-1">Baris 1</label>
                    <input type="text" x-model="baris1" @input="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-3 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Input Baris 2 -->
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 mb-1">Baris 2</label>
                    <input type="text" x-model="baris2" @input="markAsUnsaved()" placeholder="DINAS PENDIDIKAN" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-3 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Input Nama Sekolah -->
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 mb-1">Nama sekolah</label>
                    <input type="text" x-model="namaSekolah" @input="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-3 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Input Alamat -->
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 mb-1">Alamat</label>
                    <input type="text" x-model="alamat" @input="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-3 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Input Kontak -->
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 mb-1">Kontak</label>
                    <input type="text" x-model="kontak" @input="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-3 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            {{-- Section: Kepala Sekolah --}}
            <div class="space-y-3 pt-3 border-t border-gray-100">
                <h4 class="text-[11px] font-bold text-gray-700">Kepala Sekolah</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-400 mb-1">Nama</label>
                        <input type="text" x-model="namaKepsek" @input="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-2.5 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-400 mb-1">NIP</label>
                        <input type="text" x-model="nipKepsek" @input="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-2.5 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <!-- Upload Tanda Tangan -->
                <label class="border border-dashed border-gray-300 hover:border-sky-500 rounded-xl p-2.5 flex items-center justify-between bg-gray-50/30 cursor-pointer transition">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"/></svg>
                        <span class="text-[10px]" x-text="ttdName ? ttdName : 'Unggah tanda tangan (opsional)'"></span>
                    </div>
                    <span class="text-[10px] font-bold text-sky-600 hover:underline">Pilih berkas</span>
                    <input type="file" class="hidden" accept="image/*" @change="markAsUnsaved(); if($event.target.files[0]) ttdName = $event.target.files[0].name">
                </label>
            </div>

            {{-- Section: Tanggal Rapor --}}
            <div class="space-y-3 pt-3 border-t border-gray-100">
                <h4 class="text-[11px] font-bold text-gray-700">Tanggal Rapor</h4>
                
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 mb-1">Tempat</label>
                    <input type="text" x-model="tempat" @input="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-xs px-3 py-2 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-400 mb-1">Tanggal Rapor STS</label>
                        <input type="date" x-model="tanggalSts" @change="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-[11px] px-2 py-1.5 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-400 mb-1">Tanggal Rapor Semester</label>
                        <input type="date" x-model="tanggalSemester" @change="markAsUnsaved()" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-[11px] px-2 py-1.5 text-gray-800 font-medium outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
            </div>

            {{-- Section: Komponen yang Ditampilkan --}}
            <div class="space-y-3 pt-3 border-t border-gray-100">
                <h4 class="text-[11px] font-bold text-gray-700">Komponen yang ditampilkan</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem 0.5rem;">
                    
                    <div class="flex items-center gap-2">
                        <button @click="showCapaian = !showCapaian; markAsUnsaved()" :class="showCapaian ? 'bg-sky-500' : 'bg-gray-200'" class="w-7 h-4 flex items-center rounded-full p-0.5 transition-colors duration-200 shrink-0">
                            <div :class="showCapaian ? 'translate-x-3' : 'translate-x-0'" class="bg-white w-3 h-3 rounded-full shadow-xs transform transition-transform duration-200"></div>
                        </button>
                        <span class="text-[11px] font-medium text-gray-700">Capaian kompetensi</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="showKokurikuler = !showKokurikuler; markAsUnsaved()" :class="showKokurikuler ? 'bg-sky-500' : 'bg-gray-200'" class="w-7 h-4 flex items-center rounded-full p-0.5 transition-colors duration-200 shrink-0">
                            <div :class="showKokurikuler ? 'translate-x-3' : 'translate-x-0'" class="bg-white w-3 h-3 rounded-full shadow-xs transform transition-transform duration-200"></div>
                        </button>
                        <span class="text-[11px] font-medium text-gray-700">Kokurikuler</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="showEkstra = !showEkstra; markAsUnsaved()" :class="showEkstra ? 'bg-sky-500' : 'bg-gray-200'" class="w-7 h-4 flex items-center rounded-full p-0.5 transition-colors duration-200 shrink-0">
                            <div :class="showEkstra ? 'translate-x-3' : 'translate-x-0'" class="bg-white w-3 h-3 rounded-full shadow-xs transform transition-transform duration-200"></div>
                        </button>
                        <span class="text-[11px] font-medium text-gray-700">Ekstrakurikuler</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="showKetidakfahiran = !showKetidakfahiran; markAsUnsaved()" :class="showKetidakfahiran ? 'bg-sky-500' : 'bg-gray-200'" class="w-7 h-4 flex items-center rounded-full p-0.5 transition-colors duration-200 shrink-0">
                            <div :class="showKetidakfahiran ? 'translate-x-3' : 'translate-x-0'" class="bg-white w-3 h-3 rounded-full shadow-xs transform transition-transform duration-200"></div>
                        </button>
                        <span class="text-[11px] font-medium text-gray-700">Ketidakhadiran</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="showCatatanWali = !showCatatanWali; markAsUnsaved()" :class="showCatatanWali ? 'bg-sky-500' : 'bg-gray-200'" class="w-7 h-4 flex items-center rounded-full p-0.5 transition-colors duration-200 shrink-0">
                            <div :class="showCatatanWali ? 'translate-x-3' : 'translate-x-0'" class="bg-white w-3 h-3 rounded-full shadow-xs transform transition-transform duration-200"></div>
                        </button>
                        <span class="text-[11px] font-medium text-gray-700">Catatan wali kelas</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="showQr = !showQr; markAsUnsaved()" :class="showQr ? 'bg-sky-500' : 'bg-gray-200'" class="w-7 h-4 flex items-center rounded-full p-0.5 transition-colors duration-200 shrink-0">
                            <div :class="showQr ? 'translate-x-3' : 'translate-x-0'" class="bg-white w-3 h-3 rounded-full shadow-xs transform transition-transform duration-200"></div>
                        </button>
                        <span class="text-[11px] font-medium text-gray-700">QR verifikasi</span>
                    </div>

                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: REAL-TIME PREVIEW DOKUMEN A4 --}}
        <div class="format-rapor-right space-y-3">
            
            {{-- Top Switcher Tab & Info --}}
            <div class="flex items-center justify-between">
                <div class="bg-gray-200/80 p-1 rounded-xl flex items-center gap-1 text-xs">
                    <button @click="previewTab = 'sts'" :class="previewTab === 'sts' ? 'bg-white text-sky-600 font-bold shadow-xs' : 'text-gray-600 font-medium hover:text-gray-900'" class="px-3.5 py-1 rounded-lg transition text-xs">
                        Rapor STS
                    </button>
                    <button @click="previewTab = 'semester'" :class="previewTab === 'semester' ? 'bg-white text-sky-600 font-bold shadow-xs' : 'text-gray-600 font-medium hover:text-gray-900'" class="px-3.5 py-1 rounded-lg transition text-xs">
                        Rapor Semester
                    </button>
                    <span class="px-2 py-0.5 bg-gray-300/60 text-gray-600 font-semibold rounded-md text-[10px] ml-1">2 halaman</span>
                </div>

                <span class="text-xs text-gray-400 font-medium">Pratinjau langsung · data contoh Citra Lestari</span>
            </div>

            {{-- BINGKAI CONTAINER PREVIEW DOKUMEN --}}
            <div class="bg-[#F1F5F9] p-8 rounded-2xl border border-gray-200 shadow-inner flex justify-center items-start overflow-x-auto min-h-[820px]">
                
                {{-- KERTAS LEMBARAN A4 --}}
                <div style="width: 500px; min-width: 500px; background: white; font-size: 7.5px; line-height: 1.3;" class="p-6 rounded-xs shadow-xl text-gray-900 font-sans flex flex-col justify-between border border-gray-300 space-y-3 shrink-0">
                    
                    <div class="space-y-3">
                        {{-- KOP SURAT DINAMIS --}}
                        <div class="flex items-center gap-3 border-b-2 border-gray-900 pb-2">
                            <div class="w-8 h-8 border-2 border-gray-900 font-black text-xs flex items-center justify-center shrink-0">
                                S
                            </div>
                            <div class="text-center flex-1 pr-8">
                                <p class="font-bold text-[7px] uppercase tracking-wider text-gray-800" x-text="baris1"></p>
                                <h2 class="font-black text-[10px] uppercase tracking-wide text-gray-900 mt-0.5" x-text="namaSekolah"></h2>
                                <p class="text-[6.5px] text-gray-600" x-text="alamat"></p>
                            </div>
                        </div>

                        {{-- JUDUL LAPORAN --}}
                        <div class="text-center">
                            <h3 class="font-bold text-[9.5px] uppercase tracking-wide">
                                LAPORAN HASIL BELAJAR — <span x-text="previewTab === 'sts' ? 'STS GANJIL' : 'SEMESTER GANJIL'"></span>
                            </h3>
                            <p class="text-[7.5px] text-gray-600">Tahun Ajaran 2026/2027</p>
                        </div>

                        {{-- IDENTITAS SISWA GRID --}}
                        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 0.5rem;" class="text-[7.5px] py-1 border-y border-gray-200">
                            <div class="space-y-0.5">
                                <div class="flex"><span style="width: 95px;" class="text-gray-500 shrink-0">Nama Peserta Didik</span><span class="font-bold">: Citra Lestari</span></div>
                                <div class="flex"><span style="width: 95px;" class="text-gray-500 shrink-0">NIS / NISN</span><span>: 2526003 / 0081234567</span></div>
                                <div class="flex"><span style="width: 95px;" class="text-gray-500 shrink-0">Sekolah</span><span x-text="': ' + namaSekolah"></span></div>
                            </div>
                            <div class="space-y-0.5">
                                <div class="flex"><span style="width: 50px;" class="text-gray-500 shrink-0">Kelas</span><span>: X-B</span></div>
                                <div class="flex"><span style="width: 50px;" class="text-gray-500 shrink-0">Fase</span><span>: E</span></div>
                                <div class="flex"><span style="width: 50px;" class="text-gray-500 shrink-0">Semester</span><span>: Ganjil</span></div>
                            </div>
                        </div>

                        {{-- TABEL NILAI MATERI --}}
                        <table class="w-full border-collapse border border-gray-800 text-[7.5px]">
                            <thead>
                                <tr class="bg-gray-100 text-gray-900 border-b border-gray-800 font-bold">
                                    <th class="border border-gray-800 p-1 w-5 text-center">No</th>
                                    <th style="width: 120px;" class="border border-gray-800 p-1 text-left">Mata Pelajaran</th>
                                    <th class="border border-gray-800 p-1 w-7 text-center">Nilai Akhir</th>
                                    <th x-show="showCapaian" class="border border-gray-800 p-1 text-left">Capaian Kompetensi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">1</td>
                                    <td class="border border-gray-800 p-1 font-medium">Pendidikan Agama Islam dan Budi Pekerti</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">88</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang sangat baik dalam memahami makna iman kepada malaikat. Perlu penguatan dalam menerapkan ketentuan zakat.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">2</td>
                                    <td class="border border-gray-800 p-1 font-medium">Pendidikan Pancasila</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">85</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang baik dalam menganalisis penerapan nilai Pancasila. Perlu penguatan dalam mengidentifikasi kasus pelanggaran hak warga negara.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">3</td>
                                    <td class="border border-gray-800 p-1 font-medium">Bahasa Indonesia</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">90</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang sangat baik dalam menyusun teks laporan hasil observasi. Perlu penguatan dalam menyunting teks negosiasi.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">4</td>
                                    <td class="border border-gray-800 p-1 font-medium">Matematika</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">86</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang baik dalam menyelesaikan sistem persamaan linear tiga variabel. Perlu penguatan dalam pemodelan fungsi kuadrat.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">5</td>
                                    <td class="border border-gray-800 p-1 font-medium">Bahasa Inggris</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">84</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang baik dalam memahami teks deskriptif. Perlu penguatan dalam menyusun teks prosedur secara runtut.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">6</td>
                                    <td class="border border-gray-800 p-1 font-medium">Fisika</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">82</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang baik dalam menerapkan konsep besaran dan pengukuran. Perlu penguatan dalam menganalisis gerak lurus berubah beraturan.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">7</td>
                                    <td class="border border-gray-800 p-1 font-medium">Biologi</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">87</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang sangat baik dalam mengklasifikasikan keanekaragaman makhluk hidup. Perlu penguatan dalam menjelaskan peran virus.</td>
                                </tr>
                                <tr>
                                    <td class="border border-gray-800 p-1 text-center">8</td>
                                    <td class="border border-gray-800 p-1 font-medium">Sejarah</td>
                                    <td class="border border-gray-800 p-1 text-center font-bold">89</td>
                                    <td x-show="showCapaian" class="border border-gray-800 p-1">Menunjukkan penguasaan yang sangat baik dalam menjelaskan konsep dasar ilmu sejarah. Perlu penguatan dalam menganalisis sumber sejarah primer.</td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- KOMPONEN EKSTRAKURIKULER & KETIDAKHADIRAN --}}
                        <template x-if="showEkstra || showKetidakfahiran">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;" class="pt-0.5">
                                <div x-show="showEkstra" class="border border-gray-800 p-1 space-y-0.5">
                                    <p class="font-bold uppercase text-[7px]">Ekstrakurikuler</p>
                                    <p class="text-[7px]">1. Pramuka: <b>Sangat Baik</b></p>
                                    <p class="text-[7px]">2. Paskibra: <b>Baik</b></p>
                                </div>
                                <div x-show="showKetidakfahiran" class="border border-gray-800 p-1 space-y-0.5">
                                    <p class="font-bold uppercase text-[7px]">Ketidakhadiran</p>
                                    <p class="text-[7px]">Sakit: <b>0 hari</b> | Izin: <b>1 hari</b> | Tanpa Keterangan: <b>0 hari</b></p>
                                </div>
                            </div>
                        </template>

                        {{-- CATATAN WALI KELAS --}}
                        <template x-if="showCatatanWali">
                            <div class="border border-gray-800 p-1 space-y-0.5">
                                <p class="font-bold uppercase text-[7px]">Catatan Wali Kelas</p>
                                <p class="text-[7px] italic text-gray-700">"Pertahankan prestasi yang sangat baik ini. Tingkatkan keaktifan dalam kegiatan organisasi sekolah."</p>
                            </div>
                        </template>
                    </div>

                    {{-- BOTTOM SIGNATURE SECTION --}}
                    <div class="pt-2 border-t border-gray-100 flex justify-between items-end text-[7.5px]">
                        <div>
                            <p>Mengetahui,</p>
                            <p>Orang Tua / Wali Siswa</p>
                            <div class="h-8"></div>
                            <p class="font-bold">( ................................................. )</p>
                        </div>

                        <div class="text-right">
                            <p x-text="tempat + ', ' + formatDate(previewTab === 'sts' ? tanggalSts : tanggalSemester)"></p>
                            <p>Kepala Sekolah,</p>
                            <div class="h-8 flex items-center justify-end">
                                <span x-show="ttdName" class="text-[6.5px] font-mono text-emerald-600">[TTD Digital Valid]</span>
                            </div>
                            <p class="font-bold underline" x-text="namaKepsek"></p>
                            <p x-text="'NIP. ' + nipKepsek"></p>
                        </div>
                    </div>

                    <div class="pt-1 flex justify-between text-[6.5px] text-gray-400 border-t border-gray-100">
                        <span>SMA Negeri 1 Kota Malang</span>
                        <span>Halaman 1 dari 2</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- TOAST NOTIFICATION --}}
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-4 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-4 opacity-0"
         x-cloak 
         class="fixed bottom-6 right-6 z-50 bg-slate-800 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700">
        <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </div>
        <span class="text-sm font-medium" x-text="toastMessage"></span>
    </div>

</div>
@endsection