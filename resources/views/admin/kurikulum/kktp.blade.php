@extends('layouts.app')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{
    // Filter State
    tahunAjaran: '2026/2027',
    selectedMapel: 'Matematika',
    selectedRombel: 'X-B',

    // Status Simpan & Toast Real-time
    lastSaved: '09.40',
    showToast: false,

    // Fungsi Format Jam Real-Time (HH.mm)
    updateLastSavedTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        this.lastSaved = `${hours}.${minutes}`;
    },

    // Database Master Dummy Dinamis (Minimal 2-3 Siswa per Rombel)
    masterData: {
        'Matematika': {
            'X-B': {
                guru: 'Siti Rahmawanti, S.Pd.',
                tps: [
                    { kode: 'TP1', desc: 'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar', kktp: 75, periodeType: 'sts' },
                    { kode: 'TP2', desc: 'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual', kktp: 75, periodeType: 'sts' },
                    { kode: 'TP3', desc: 'Menyelesaikan sistem persamaan linear tiga variabel', kktp: 75, periodeType: 'sts' },
                    { kode: 'TP4', desc: 'Menganalisis pertidaksamaan linear dua variabel', kktp: 75, periodeType: 'sts' },
                    { kode: 'TP5', desc: 'Menginterpretasi karakteristik fungsi kuadrat', kktp: 75, periodeType: 'semester' },
                    { kode: 'TP6', desc: 'Menyelesaikan masalah dengan pemodelan fungsi kuadrat', kktp: 75, periodeType: 'sas' }
                ],
                students: [
                    {
                        name: 'Dimas Saputra',
                        rombel: 'X-B · Matematika',
                        scores: { TP1: 88, TP2: 92, TP3: 70, TP4: 85, TP5: 79, TP6: 81 },
                        highestTp: 'TP2',
                        highestDesc: 'menerapkan barisan dan deret aritmetika dalam masalah kontekstual',
                        lowestTp: 'TP3',
                        lowestDesc: 'menyelesaikan sistem persamaan linear tiga variabel'
                    },
                    {
                        name: 'Siti Nurhaliza',
                        rombel: 'X-B · Matematika',
                        scores: { TP1: 95, TP2: 88, TP3: 82, TP4: 90, TP5: 86, TP6: 91 },
                        highestTp: 'TP1',
                        highestDesc: 'menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar',
                        lowestTp: null,
                        lowestDesc: null
                    },
                    {
                        name: 'Bagas Pratama',
                        rombel: 'X-B · Matematika',
                        scores: { TP1: 72, TP2: 80, TP3: 68, TP4: 76, TP5: 84, TP6: 78 },
                        highestTp: 'TP5',
                        highestDesc: 'menginterpretasi karakteristik fungsi kuadrat',
                        lowestTp: 'TP3',
                        lowestDesc: 'menyelesaikan sistem persamaan linear tiga variabel'
                    }
                ]
            },
            'X-A': {
                guru: 'Siti Rahmawanti, S.Pd.',
                tps: [
                    { kode: 'TP1', desc: 'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar', kktp: 70, periodeType: 'sts' },
                    { kode: 'TP2', desc: 'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual', kktp: 70, periodeType: 'sts' },
                    { kode: 'TP3', desc: 'Menyelesaikan sistem persamaan linear tiga variabel', kktp: 70, periodeType: 'sts' }
                ],
                students: [
                    {
                        name: 'Ahmad Rizky',
                        rombel: 'X-A · Matematika',
                        scores: { TP1: 90, TP2: 85, TP3: 65 },
                        highestTp: 'TP1',
                        highestDesc: 'menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar',
                        lowestTp: 'TP3',
                        lowestDesc: 'menyelesaikan sistem persamaan linear tiga variabel'
                    },
                    {
                        name: 'Nabila Putri',
                        rombel: 'X-A · Matematika',
                        scores: { TP1: 82, TP2: 94, TP3: 78 },
                        highestTp: 'TP2',
                        highestDesc: 'menerapkan barisan dan deret aritmetika dalam masalah kontekstual',
                        lowestTp: null,
                        lowestDesc: null
                    }
                ]
            }
        },
        'Fisika': {
            'X-C': {
                guru: 'Bambang Hermanto, M.Si.',
                tps: [
                    { kode: 'TP1', desc: 'Menganalisis konsep pengukuran dan ketidakpastian besaran fisis', kktp: 78, periodeType: 'sts' },
                    { kode: 'TP2', desc: 'Menerapkan hukum-hukum Newton tentang gerak dan penerapannya', kktp: 78, periodeType: 'sts' },
                    { kode: 'TP3', desc: 'Menganalisis konsep usaha, energi, dan hukum kekekalan energi', kktp: 75, periodeType: 'sas' }
                ],
                students: [
                    {
                        name: 'Rian Febrian',
                        rombel: 'X-C · Fisika',
                        scores: { TP1: 92, TP2: 74, TP3: 80 },
                        highestTp: 'TP1',
                        highestDesc: 'menganalisis konsep pengukuran dan ketidakpastian besaran fisis',
                        lowestTp: 'TP2',
                        lowestDesc: 'menerapkan hukum-hukum Newton tentang gerak dan penerapannya'
                    },
                    {
                        name: 'Dewi Lestari',
                        rombel: 'X-C · Fisika',
                        scores: { TP1: 85, TP2: 89, TP3: 93 },
                        highestTp: 'TP3',
                        highestDesc: 'menganalisis konsep usaha, energi, dan hukum kekekalan energi',
                        lowestTp: null,
                        lowestDesc: null
                    }
                ]
            }
        }
    },

    // Getters Dinamis
    get currentConfig() {
        const mapelData = this.masterData[this.selectedMapel] || this.masterData['Matematika'];
        return mapelData[this.selectedRombel] || Object.values(mapelData)[0];
    },
    get guru() { return this.currentConfig.guru; },
    get tps() { return this.currentConfig.tps; },
    get students() { return this.currentConfig.students; },

    // State Index Siswa Contoh
    sampleStudentIndex: 0,
    get currentStudent() {
        return this.students[this.sampleStudentIndex] || this.students[0];
    },
    nextStudent() {
        this.sampleStudentIndex = (this.sampleStudentIndex + 1) % this.students.length;
    },

    // Interval Capaian
    intervals: [
        { label: 'Perlu bimbingan', range: '< 60', badgeClass: 'bg-rose-50 text-rose-600 border-rose-200' },
        { label: 'Cukup', range: '60–74', badgeClass: 'bg-amber-50 text-amber-600 border-amber-200' },
        { label: 'Baik', range: '75–89', badgeClass: 'bg-sky-50 text-sky-600 border-sky-200' },
        { label: 'Sangat baik', range: '≥ 90', badgeClass: 'bg-emerald-50 text-emerald-600 border-emerald-200' }
    ],

    // Templat Kalimat
    templateTerbaik: 'Menunjukkan penguasaan yang sangat baik dalam [TP Tertinggi].',
    templatePenguatan: 'Perlu penguatan dalam [TP di Bawah KKTP].',

    applyKktpAll(val) {
        this.tps.forEach(tp => tp.kktp = val);
        this.triggerSave('Nilai KKTP berhasil disamakan');
    },

    insertTag(tag) {
        this.templatePenguatan += ' ' + tag;
    },

    triggerSave(msg) {
        this.updateLastSavedTime();
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 3000);
    }
}">

    {{-- Title Header & Quick Save Status --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-sky-500 tracking-wider uppercase">SMA NEGERI 1 KOTA MALANG · KURIKULUM</p>
            <h1 class="text-3xl font-bold text-gray-900 mt-1">KKTP & Deskripsi Capaian</h1>
            <p class="text-sm text-gray-500 mt-1">Kriteria ketercapaian dapat berubah setiap tahun ajaran.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Tersimpan · <span x-text="lastSaved"></span>
            </span>
            <button @click="triggerSave()" class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                Simpan
            </button>
        </div>
    </div>

    {{-- Filter Selection Card --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-200 flex items-center gap-4 shadow-sm">
        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-gray-500">Tahun ajaran:</label>
            <select x-model="tahunAjaran" class="bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                <option>2026/2027</option>
                <option>2025/2026</option>
            </select>
        </div>
        
        <div class="h-4 w-px bg-gray-200"></div>

        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-gray-500">Mapel:</label>
            <select x-model="selectedMapel" @change="sampleStudentIndex = 0" class="bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                <option value="Matematika">Matematika</option>
                <option value="Fisika">Fisika</option>
            </select>
        </div>

        <div class="h-4 w-px bg-gray-200"></div>

        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-gray-500">Rombel:</label>
            <select x-model="selectedRombel" @change="sampleStudentIndex = 0" class="bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                <template x-if="selectedMapel === 'Matematika'">
                    <g>
                        <option value="X-B">X-B</option>
                        <option value="X-A">X-A</option>
                    </g>
                </template>
                <template x-if="selectedMapel === 'Fisika'">
                    <option value="X-C">X-C</option>
                </template>
            </select>
        </div>

        <div class="ml-auto text-xs text-gray-500 font-medium">
            Guru: <b class="text-gray-800" x-text="guru"></b>
        </div>
    </div>

    {{-- Main 2 Columns Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri: Interval & Tabel KKTP --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Card Interval Capaian --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900">Interval Capaian</h3>
                    <span class="text-xs text-gray-400">Klik angka batas untuk mengubah</span>
                </div>

                <div class="w-full h-3 rounded-full flex overflow-hidden">
                    <div class="w-1/4 bg-rose-500"></div>
                    <div class="w-1/4 bg-amber-400"></div>
                    <div class="w-1/4 bg-sky-500"></div>
                    <div class="w-1/4 bg-emerald-500"></div>
                </div>

                <div class="grid grid-cols-4 gap-3 pt-1">
                    <template x-for="item in intervals" :key="item.label">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs font-bold mb-1" :class="item.badgeClass">
                                <span x-text="item.range"></span>
                                <svg class="w-3 h-3 cursor-pointer opacity-70 hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </div>
                            <p class="text-xs font-semibold text-gray-600" x-text="item.label"></p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 2. Card Tabel KKTP per TP --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">KKTP per Tujuan Pembelajaran</h3>
                        <p class="text-xs text-gray-400 mt-0.5"><span x-text="tps.length"></span> TP · KKTP default 75</p>
                    </div>
                    <button @click="applyKktpAll(75)" class="px-3.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-sm transition">
                        Terapkan 75 ke semua
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/30">
                                <th class="py-3 px-4 w-16">Kode TP</th>
                                <th class="py-3 px-4">Deskripsi</th>
                                <th class="py-3 px-4 w-24 text-center">KKTP</th>
                                <th class="py-3 px-4 w-28 text-right">Periode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            <template x-for="(tp, idx) in tps" :key="tp.kode">
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3.5 px-4 font-bold text-sky-600" x-text="tp.kode"></td>
                                    <td class="py-3.5 px-4 text-gray-700 font-medium" x-text="tp.desc"></td>
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="number" x-model.number="tp.kktp" class="w-14 text-center font-bold text-gray-900 bg-gray-50 border border-gray-200 rounded-lg py-1 px-1 outline-none focus:ring-2 focus:ring-sky-500">
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <span x-show="tp.periodeType === 'sts'" class="px-2.5 py-1 text-[10px] font-bold bg-sky-50 text-sky-600 rounded-md border border-sky-100">Masuk STS</span>
                                        <span x-show="tp.periodeType === 'semester'" class="px-2.5 py-1 text-[10px] font-bold bg-gray-100 text-gray-600 rounded-md border border-gray-200">Semester</span>
                                        <span x-show="tp.periodeType === 'sas'" class="px-2.5 py-1 text-[10px] font-bold bg-amber-50 text-amber-600 rounded-md border border-amber-100">SAS</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. Card Templat Kalimat Deskripsi --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-5">
                <h3 class="text-sm font-bold text-gray-900">Templat Kalimat Deskripsi</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">TP terbaik</label>
                        <input type="text" x-model="templateTerbaik" class="w-full bg-gray-50/50 border border-gray-200 rounded-xl text-xs p-3 font-medium text-gray-800 outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">TP perlu penguatan</label>
                        <input type="text" x-model="templatePenguatan" class="w-full bg-gray-50/50 border border-gray-200 rounded-xl text-xs p-3 font-medium text-gray-800 outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-gray-400 font-medium">Sisipkan:</span>
                    <button @click="insertTag('[Nama Siswa]')" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 rounded-lg font-bold transition">[Nama Siswa]</button>
                    <button @click="insertTag('[TP Tertinggi]')" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 rounded-lg font-bold transition">[TP Tertinggi]</button>
                    <button @click="insertTag('[TP di Bawah KKTP]')" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 rounded-lg font-bold transition">[TP di Bawah KKTP]</button>
                    <button @click="insertTag('[Mapel]')" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-600 border border-sky-200 rounded-lg font-bold transition">[Mapel]</button>
                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Pratinjau Deskripsi --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-5 sticky top-6">
                
                <div class="flex items-center gap-2 text-gray-900">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <h3 class="text-sm font-bold">Pratinjau Deskripsi</h3>
                </div>

                {{-- Profile Sample Student --}}
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-sky-600 text-white font-bold text-xs flex items-center justify-center shrink-0"
                         x-text="currentStudent.name.split(' ').map(n => n[0]).join('').substring(0, 2)">
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-900" x-text="currentStudent.name"></h4>
                        <p class="text-[11px] text-gray-400" x-text="currentStudent.rombel"></p>
                    </div>
                </div>

                {{-- Scores Matrix Grid --}}
                <div class="grid grid-cols-6 gap-1 text-center">
                    <template x-for="(score, kode) in currentStudent.scores" :key="kode">
                        <div class="bg-gray-50 p-2 rounded-xl border border-gray-100">
                            <span class="block text-[10px] font-bold text-gray-400" x-text="kode"></span>
                            <span class="block text-xs font-black mt-0.5" 
                                  :class="score < 75 ? 'text-rose-600' : (score >= 90 ? 'text-emerald-600' : 'text-gray-800')" 
                                  x-text="score"></span>
                        </div>
                    </template>
                </div>

                {{-- Highest & Lowest Badge Info --}}
                <div class="flex items-center gap-3 text-[11px] font-bold">
                    <span class="text-emerald-600 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tertinggi: <span x-text="currentStudent.highestTp"></span>
                    </span>
                    <span x-show="currentStudent.lowestTp" class="text-rose-600 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Di bawah KKTP: <span x-text="currentStudent.lowestTp"></span>
                    </span>
                </div>

                {{-- Generated Preview Narrative Box --}}
                <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 text-xs leading-relaxed text-gray-700">
                    Menunjukkan penguasaan yang sangat baik dalam 
                    <b class="text-emerald-600" x-text="currentStudent.highestDesc"></b>. 
                    <template x-if="currentStudent.lowestTp">
                        <span>Perlu penguatan dalam <b class="text-rose-600" x-text="currentStudent.lowestDesc"></b>.</span>
                    </template>
                </div>

                {{-- Information Note --}}
                <div class="flex items-start gap-2 text-[11px] text-gray-400 leading-snug">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>TP terendah belum tentu di bawah KKTP. Jika semua TP mencapai KKTP, kalimat penguatan tidak ditampilkan.</span>
                </div>

                {{-- Button Switch Student --}}
                <button @click="nextStudent()" class="w-full py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Ganti siswa contoh
                </button>

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
        <span class="text-sm font-medium">Perubahan KKTP & Deskripsi berhasil disimpan</span>
    </div>

</div>
@endsection