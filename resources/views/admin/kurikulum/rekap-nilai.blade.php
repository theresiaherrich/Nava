@extends('layouts.app')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{
    tingkat: 'Kelas X',
    selectedKelas: 'Semua kelas',
    selectedMapel: 'Semua',
    periode: 'STS',
    searchQuery: '',
    activeTab: 'kelengkapan',

    exportOpen: false,
    showToast: false,
    toastMessage: '',

    downloadExcel(fileName) {
        this.exportOpen = false;
        this.toastMessage = 'Mengunduh ' + fileName + '...';
        this.showToast = true;

        const csvRows = [
            ['Tahun Ajaran', '2026/2027 Ganjil'],
            ['Tingkat/Kelas', this.tingkat + ' - ' + this.selectedKelas],
            ['Periode', this.periode],
            [],
            ['Kelas', 'Wali Kelas', 'Matematika', 'B. Indonesia', 'B. Inggris', 'Fisika', 'Kelengkapan', 'Status Kunci'],
            ...this.rombels.map(r => [
                r.nama,
                r.wali,
                r.mapels.mat === 'approved' ? 'Disetujui' : 'Sedang Diisi',
                r.mapels.bind === 'approved' ? 'Disetujui' : 'Sedang Diisi',
                r.mapels.bing === 'approved' ? 'Disetujui' : 'Sedang Diisi',
                r.mapels.fis === 'approved' ? 'Disetujui' : 'Sedang Diisi',
                r.completeness + '%',
                r.statusKunci
            ])
        ];

        const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + csvRows.map(e => e.join(',')).join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', fileName + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        setTimeout(() => { this.showToast = false; }, 3000);
    },

    get kelasOptions() {
        if (this.tingkat === 'Kelas X') return ['Semua kelas', 'X-A', 'X-B', 'X-C', 'X-D', 'X-E', 'X-F', 'X-G', 'X-H', 'X-I', 'X-J'];
        if (this.tingkat === 'Kelas XI') return ['Semua kelas', 'XI-A', 'XI-B', 'XI-C', 'XI-D', 'XI-E'];
        return ['Semua kelas', 'XII-A', 'XII-B', 'XII-C'];
    },

    get metrics() {
        if (this.tingkat === 'Kelas XI') {
            return { totalSiswa: 315, totalKelasText: 'Kelas XI · 10 kelas', avgScore: 84.1, avgLabel: 'STS Ganjil 2026/2027', completeness: 96, lockedCount: 6, lockedList: 'XI-A, XI-B, XI-D' };
        }
        return { totalSiswa: 320, totalKelasText: 'Kelas X · 10 kelas', avgScore: 82.7, avgLabel: 'STS Ganjil 2026/2027', completeness: 92, lockedCount: 4, lockedList: 'X-B, X-G, X-H, X-J' };
    },

    rombels: [
        { nama: 'X-A', wali: 'Andini Pratiwi, S.Pd.', statusKunci: 'Terbuka', completeness: 100, mapels: { mat: 'approved', bind: 'approved', bing: 'approved', fis: 'approved', bio: 'approved', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'approved' } },
        { nama: 'X-B', wali: 'Siti Rahmawanti, S.Pd.', statusKunci: 'Terkunci', completeness: 100, mapels: { mat: 'approved', bind: 'approved', bing: 'approved', fis: 'approved', bio: 'approved', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'approved' } },
        { nama: 'X-C', wali: 'Bayu Nugraha, S.Pd.', statusKunci: 'Terbuka', completeness: 88, mapels: { mat: 'approved', bind: 'approved', bing: 'sent', fis: 'approved', bio: 'draft', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'empty' } },
        { nama: 'X-D', wali: 'Ratna Sari, M.Pd.', statusKunci: 'Terbuka', completeness: 63, mapels: { mat: 'sent', bind: 'approved', bing: 'draft', fis: 'approved', bio: 'draft', kim: 'sent', sej: 'approved', ppkn: 'draft', inf: 'draft' } },
        { nama: 'X-E', wali: 'Yusuf Hidayat, S.Pd.', statusKunci: 'Koreksi', completeness: 94, mapels: { mat: 'draft', bind: 'approved', bing: 'approved', fis: 'approved', bio: 'approved', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'sent' } },
        { nama: 'X-F', wali: 'Lestari Wulandari, S.Pd.', statusKunci: 'Terbuka', completeness: 100, mapels: { mat: 'approved', bind: 'approved', bing: 'approved', fis: 'approved', bio: 'approved', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'approved' } },
        { nama: 'X-G', wali: 'Dedi Kurniawan, S.Pd.', statusKunci: 'Terkunci', completeness: 100, mapels: { mat: 'approved', bind: 'approved', bing: 'approved', fis: 'approved', bio: 'approved', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'approved' } },
        { nama: 'X-H', wali: 'Maria Ulfa, S.Pd.', statusKunci: 'Terkunci', completeness: 100, mapels: { mat: 'approved', bind: 'approved', bing: 'approved', fis: 'approved', bio: 'approved', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'approved' } },
        { nama: 'X-I', wali: 'Rizal Fahmi, S.Pd.', statusKunci: 'Terbuka', completeness: 75, mapels: { mat: 'approved', bind: 'sent', bing: 'approved', fis: 'draft', bio: 'approved', kim: 'approved', sej: 'sent', ppkn: 'approved', inf: 'draft' } },
        { nama: 'X-J', wali: 'Novi Andriani, S.Pd.', statusKunci: 'Terkunci', completeness: 100, mapels: { mat: 'approved', bind: 'approved', bing: 'approved', fis: 'approved', bio: 'approved', kim: 'approved', sej: 'approved', ppkn: 'approved', inf: 'approved' } }
    ],

    siswaList: [
        { nis: '1001', nama: 'Ahmad Rizky', kelas: 'X-A', mat: 88, bind: 90, bing: 85, fis: 82, bio: 88, rata: 86.6, status: 'Lengkap' },
        { nis: '1002', nama: 'Andini Putri', kelas: 'X-A', mat: 92, bind: 94, bing: 88, fis: 90, bio: 91, rata: 91.0, status: 'Lengkap' },
        { nis: '1003', nama: 'Bagas Pratama', kelas: 'X-B', mat: 75, bind: 80, bing: 78, fis: 72, bio: 79, rata: 76.8, status: 'Belum Lengkap' }
    ],

    get filteredRombels() {
        return this.rombels.filter(r => {
            const matchKelas = this.selectedKelas === 'Semua kelas' || r.nama === this.selectedKelas;
            const matchSearch = !this.searchQuery || 
                                r.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                r.wali.toLowerCase().includes(this.searchQuery.toLowerCase());
            return matchKelas && matchSearch;
        });
    }
}">

    {{-- Title Header & Dropdown Ekspor Excel --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] font-bold text-sky-500 tracking-wider uppercase">SMA NEGERI 1 KOTA MALANG · KURIKULUM</p>
            <h1 class="text-3xl font-bold text-gray-900 mt-1">Rekap Nilai (DKN)</h1>
            <p class="text-xs text-gray-500 mt-1">Pantau kelengkapan dan ringkasan nilai seluruh kelas.</p>
        </div>

        {{-- CUSTOM DROPDOWN EKSPOR EXCEL --}}
        <div class="relative" @click.away="exportOpen = false">
            <button @click="exportOpen = !exportOpen" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Ekspor Excel</span>
                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="exportOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <!-- Menu Popover Dropdown -->
            <div x-show="exportOpen" x-cloak class="absolute right-0 mt-2 w-72 bg-white rounded-2xl border border-gray-200 shadow-xl z-30 p-2 space-y-1">
                <button @click="downloadExcel('DKN_Kelas_Terpilih')" class="w-full text-left p-3 rounded-xl hover:bg-gray-50 transition group">
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5 font-bold text-[10px]">XLS</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 group-hover:text-sky-600">DKN kelas terpilih</h4>
                            <p class="text-[11px] text-gray-400 mt-0.5">Semua kelas X · 1 berkas per kelas</p>
                        </div>
                    </div>
                </button>
                <button @click="downloadExcel('DKN_Seluruh_Kelas_X')" class="w-full text-left p-3 rounded-xl hover:bg-gray-50 transition group">
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5 font-bold text-[10px]">XLS</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 group-hover:text-sky-600">DKN seluruh Kelas X</h4>
                            <p class="text-[11px] text-gray-400 mt-0.5">320 siswa · 1 berkas</p>
                        </div>
                    </div>
                </button>
                <button @click="downloadExcel('DKN_Seluruh_Sekolah')" class="w-full text-left p-3 rounded-xl hover:bg-gray-50 transition group">
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5 font-bold text-[10px]">XLS</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 group-hover:text-sky-600">DKN seluruh sekolah</h4>
                            <p class="text-[11px] text-gray-400 mt-0.5">960 siswa · 30 sheet</p>
                        </div>
                    </div>
                </button>
                <div class="pt-2 px-3 pb-1 border-t border-gray-100 flex justify-between items-center text-[10px] text-gray-400">
                    <span>Format .xlsx</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metrics Cards --}}
    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; width: 100%;">
        
        <!-- Metric 1: Jumlah Siswa -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500">Jumlah siswa</p>
            <h3 class="text-3xl font-black text-gray-900 mt-1" x-text="metrics.totalSiswa"></h3>
            <p class="text-xs text-gray-400 mt-1" x-text="metrics.totalKelasText"></p>
        </div>

        <!-- Metric 2: Rata-rata Angkatan -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500">Rata-rata angkatan</p>
            <h3 class="text-3xl font-black text-gray-900 mt-1" x-text="metrics.avgScore"></h3>
            <p class="text-xs text-gray-400 mt-1" x-text="metrics.avgLabel"></p>
        </div>

        <!-- Metric 3: Kelengkapan Nilai -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500">Kelengkapan nilai</p>
            <h3 class="text-3xl font-black text-gray-900 mt-1" x-text="metrics.completeness + '%' "></h3>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3 overflow-hidden">
                <div class="h-1.5 bg-sky-500 rounded-full transition-all duration-500" :style="'width: ' + metrics.completeness + '%'"></div>
            </div>
        </div>

        <!-- Metric 4: Kelas Terkunci -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500">Kelas terkunci</p>
            <h3 class="text-3xl font-black text-gray-900 mt-1">
                <span x-text="metrics.lockedCount"></span><span class="text-lg text-gray-400 font-bold"> / 10</span>
            </h3>
            <p class="text-xs text-gray-400 mt-1 truncate" x-text="metrics.lockedList"></p>
        </div>
    </div>

    {{-- Filter Selection Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-200 flex flex-wrap items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3 flex-wrap">
            
            {{-- Dropdown Tingkat --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-500">Tingkat:</label>
                <select x-model="tingkat" @change="selectedKelas = 'Semua kelas'" class="bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="Kelas X">Kelas X</option>
                    <option value="Kelas XI">Kelas XI</option>
                    <option value="Kelas XII">Kelas XII</option>
                </select>
            </div>

            <div class="h-4 w-px bg-gray-200"></div>

            {{-- Dropdown Kelas Dinamis --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-500">Kelas:</label>
                <select x-model="selectedKelas" class="bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                    <template x-for="k in kelasOptions" :key="k">
                        <option :value="k" x-text="k"></option>
                    </template>
                </select>
            </div>

            <div class="h-4 w-px bg-gray-200"></div>

            {{-- Dropdown Mapel --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-500">Mapel:</label>
                <select x-model="selectedMapel" class="bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="Semua">Semua</option>
                    <option value="Matematika">Matematika</option>
                    <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                    <option value="Bahasa Inggris">Bahasa Inggris</option>
                </select>
            </div>

            <div class="h-4 w-px bg-gray-200"></div>

            {{-- Switcher Periode (STS / Semester) --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-500">Periode</span>
                <div class="bg-gray-100 p-1 rounded-xl flex items-center gap-1">
                    <button @click="periode = 'STS'" :class="periode === 'STS' ? 'bg-white shadow-sm text-sky-600 font-bold' : 'text-gray-500 font-medium'" class="px-3 py-1 rounded-lg text-xs transition">
                        STS
                    </button>
                    <button @click="periode = 'Semester'" :class="periode === 'Semester' ? 'bg-white shadow-sm text-sky-600 font-bold' : 'text-gray-500 font-medium'" class="px-3 py-1 rounded-lg text-xs transition">
                        Semester
                    </button>
                </div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="relative w-full sm:w-64">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="searchQuery" placeholder="Cari kelas atau wali..." class="w-full bg-gray-50 border border-gray-200 rounded-xl text-xs pl-9 pr-3 py-2.5 outline-none focus:ring-2 focus:ring-sky-500">
        </div>
    </div>

    {{-- Sub-Tab Navigation Bar --}}
    <div class="border-b border-gray-200 flex gap-6 text-xs font-bold">
        <button @click="activeTab = 'persiswa'" :class="activeTab === 'persiswa' ? 'text-sky-600 border-b-2 border-sky-600 pb-3' : 'text-gray-400 hover:text-gray-600 pb-3'" class="transition">
            Per siswa
        </button>
        <button @click="activeTab = 'kelengkapan'" :class="activeTab === 'kelengkapan' ? 'text-sky-600 border-b-2 border-sky-600 pb-3' : 'text-gray-400 hover:text-gray-600 pb-3'" class="transition">
            Kelengkapan per kelas
        </button>
    </div>

    {{-- TAB CONTENT 1: KELENGKAPAN PER KELAS --}}
    <div x-show="activeTab === 'kelengkapan'" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden space-y-4">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                        <th class="py-3.5 px-4 w-20">Kelas</th>
                        <th class="py-3.5 px-4">Wali Kelas</th>
                        <th class="py-3.5 px-2 text-center w-10">Mat</th>
                        <th class="py-3.5 px-2 text-center w-10">B.Ind</th>
                        <th class="py-3.5 px-2 text-center w-10">B.Ing</th>
                        <th class="py-3.5 px-2 text-center w-10">Fis</th>
                        <th class="py-3.5 px-2 text-center w-10">Bio</th>
                        <th class="py-3.5 px-2 text-center w-10">Kim</th>
                        <th class="py-3.5 px-2 text-center w-10">Sej</th>
                        <th class="py-3.5 px-2 text-center w-10">PPKn</th>
                        <th class="py-3.5 px-2 text-center w-10">Inf</th>
                        <th class="py-3.5 px-2 text-center w-10 text-gray-300">...</th>
                        <th class="py-3.5 px-4 w-36">Kelengkapan</th>
                        <th class="py-3.5 px-4 w-28 text-right">Status kunci</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    <template x-for="r in filteredRombels" :key="r.nama">
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-4 px-4 font-bold text-gray-900" x-text="r.nama"></td>
                            <td class="py-4 px-4 font-medium text-gray-600" x-text="r.wali"></td>
                            
                            {{-- Mapels Dot Status --}}
                            <template x-for="mapelKey in ['mat', 'bind', 'bing', 'fis', 'bio', 'kim', 'sej', 'ppkn', 'inf']">
                                <td class="py-4 px-2 text-center">
                                    <template x-if="r.mapels[mapelKey] === 'approved'">
                                        <div class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-50 text-emerald-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    </template>
                                    <template x-if="r.mapels[mapelKey] === 'sent'">
                                        <div class="inline-block w-2.5 h-2.5 rounded-full bg-sky-500"></div>
                                    </template>
                                    <template x-if="r.mapels[mapelKey] === 'draft'">
                                        <div class="inline-block w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                                    </template>
                                    <template x-if="r.mapels[mapelKey] === 'empty'">
                                        <span class="text-rose-500 font-bold text-xs">—</span>
                                    </template>
                                </td>
                            </template>

                            <td class="py-4 px-2 text-center text-gray-300 font-bold">+7</td>

                            {{-- Progress Bar Kelengkapan --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full transition-all duration-500" 
                                             :class="r.completeness === 100 ? 'bg-emerald-500' : (r.completeness >= 80 ? 'bg-sky-500' : 'bg-amber-400')" 
                                             :style="'width: ' + r.completeness + '%'"></div>
                                    </div>
                                    <span class="font-bold text-xs text-gray-800 w-10 text-right" x-text="r.completeness + '%'"></span>
                                </div>
                            </td>

                            {{-- Status Kunci Badge --}}
                            <td class="py-4 px-4 text-right">
                                <span x-show="r.statusKunci === 'Terbuka'" class="px-2.5 py-1 text-[10px] font-bold bg-gray-100 text-gray-600 rounded-full border border-gray-200">Terbuka</span>
                                <span x-show="r.statusKunci === 'Terkunci'" class="px-2.5 py-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200 flex items-center gap-1 justify-end w-max ml-auto">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Terkunci
                                </span>
                                <span x-show="r.statusKunci === 'Koreksi'" class="px-2.5 py-1 text-[10px] font-bold bg-purple-50 text-purple-700 rounded-full border border-purple-200 flex items-center gap-1 justify-end w-max ml-auto">
                                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Koreksi
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Table Legend Footer --}}
        <div class="p-4 bg-gray-50/50 border-t border-gray-100 flex flex-wrap justify-between items-center text-xs text-gray-500 gap-4">
            <div class="flex items-center gap-4 flex-wrap font-semibold">
                <span class="flex items-center gap-1.5"><div class="w-4 h-4 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div> Disetujui</span>
                <span class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-sky-500"></div> Dikirim</span>
                <span class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div> Sedang diisi</span>
                <span class="flex items-center gap-1.5"><span class="text-rose-500 font-bold">—</span> Belum ada TP</span>
            </div>
            <div class="text-gray-400 font-medium">
                9 dari 16 mapel ditampilkan · +7 mapel lain
            </div>
        </div>

    </div>

    {{-- TAB CONTENT 2: VIEW PER SISWA --}}
    <div x-show="activeTab === 'persiswa'" x-cloak class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50">
                        <th class="py-3.5 px-4">NIS</th>
                        <th class="py-3.5 px-4">Nama Siswa</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-3 text-center">Matematika</th>
                        <th class="py-3.5 px-3 text-center">B. Indonesia</th>
                        <th class="py-3.5 px-3 text-center">B. Inggris</th>
                        <th class="py-3.5 px-3 text-center">Fisika</th>
                        <th class="py-3.5 px-3 text-center">Biologi</th>
                        <th class="py-3.5 px-4 text-center">Rata-rata</th>
                        <th class="py-3.5 px-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    <template x-for="s in siswaList" :key="s.nis">
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-4 px-4 font-mono text-gray-400" x-text="s.nis"></td>
                            <td class="py-4 px-4 font-bold text-gray-900" x-text="s.nama"></td>
                            <td class="py-4 px-4 font-semibold text-gray-600" x-text="s.kelas"></td>
                            <td class="py-4 px-3 text-center font-bold text-gray-800" x-text="s.mat"></td>
                            <td class="py-4 px-3 text-center font-bold text-gray-800" x-text="s.bind"></td>
                            <td class="py-4 px-3 text-center font-bold text-gray-800" x-text="s.bing"></td>
                            <td class="py-4 px-3 text-center font-bold text-gray-800" x-text="s.fis"></td>
                            <td class="py-4 px-3 text-center font-bold text-gray-800" x-text="s.bio"></td>
                            <td class="py-4 px-4 text-center font-black text-sky-600" x-text="s.rata"></td>
                            <td class="py-4 px-4 text-right">
                                <span :class="s.status === 'Lengkap' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'" class="px-2.5 py-1 text-[10px] font-bold rounded-full border" x-text="s.status"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
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