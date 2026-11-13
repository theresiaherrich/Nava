@extends('layouts.app')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{ 
    // Status kunci STS dari Laravel Controller / Admin (Ubah ke true jika periode STS sudah dikunci/lewat deadline)
    isStsLocked: {{ isset($isStsLocked) && $isStsLocked ? 'true' : 'false' }}, 
    
    // State Filter & Dropdown
    filterTab: 'semua', // 'semua' atau 'sts'
    dropdownOpen: false,
    searchRombel: '',
    selectedRombelId: 2, // Default terdaftar di X-B (ID 2)
    
    // 1. Data Rombel / Kelas
    rombelList: [
        { id: 1, name: 'X-A · Matematika' },
        { id: 2, name: 'X-B · Matematika' },
        { id: 3, name: 'X-C · Matematika' },
        { id: 4, name: 'XI-A · Matematika' },
        { id: 5, name: 'XI-B · Matematika' },
        { id: 6, name: 'XI-C · Matematika' }
    ],

    // 2. Data Master TP Dipisah berdasarkan rombel_id
    allTps: {
        // Data TP untuk X-A (6 TP)
        1: [
            { id: 'TP1', desc: 'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar', nilaiCount: 30 },
            { id: 'TP2', desc: 'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual', nilaiCount: 30 },
            { id: 'TP3', desc: 'Menyelesaikan sistem persamaan linear tiga variabel', nilaiCount: 0 },
            { id: 'TP4', desc: 'Menganalisis pertidaksamaan linear dua variabel', nilaiCount: 0 },
            { id: 'TP5', desc: 'Menginterpretasi karakteristik fungsi kuadrat', nilaiCount: 0 },
            { id: 'TP6', desc: 'Menyelesaikan masalah dengan pemodelan fungsi kuadrat', nilaiCount: 0 }
        ],
        // Data TP untuk X-B (6 TP)
        2: [
            { id: 'TP1', desc: 'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar', nilaiCount: 30 },
            { id: 'TP2', desc: 'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual', nilaiCount: 30 },
            { id: 'TP3', desc: 'Menyelesaikan sistem persamaan linear tiga variabel', nilaiCount: 30 },
            { id: 'TP4', desc: 'Menganalisis pertidaksamaan linear dua variabel', nilaiCount: 0 },
            { id: 'TP5', desc: 'Menginterpretasi karakteristik fungsi kuadrat', nilaiCount: 0 },
            { id: 'TP6', desc: 'Menyelesaikan masalah dengan pemodelan fungsi kuadrat', nilaiCount: 0 }
        ],
        // Data TP untuk X-C (0 TP / Empty State)
        3: [],
        // Data TP untuk XI-A (10 TP / Kuota Penuh State)
        4: [
            { id: 'TP1', desc: 'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar', nilaiCount: 30 },
            { id: 'TP2', desc: 'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual', nilaiCount: 30 },
            { id: 'TP3', desc: 'Menyelesaikan sistem persamaan linear tiga variabel', nilaiCount: 30 },
            { id: 'TP4', desc: 'Menganalisis pertidaksamaan linear dua variabel', nilaiCount: 0 },
            { id: 'TP5', desc: 'Menginterpretasi karakteristik fungsi kuadrat', nilaiCount: 0 },
            { id: 'TP6', desc: 'Menyelesaikan masalah dengan pemodelan fungsi kuadrat', nilaiCount: 0 },
            { id: 'TP7', desc: 'Menerapkan konsep peluang kejadian majemuk', nilaiCount: 0 },
            { id: 'TP8', desc: 'Menyajikan dan menafsirkan data statistika', nilaiCount: 0 },
            { id: 'TP9', desc: 'Menggunakan trigonometri pada segitiga siku-siku', nilaiCount: 0 },
            { id: 'TP10', desc: 'Memecahkan masalah vektor pada bidang', nilaiCount: 0 }
        ],
        // Data TP untuk XI-B (0 TP)
        5: [],
        // Data TP untuk XI-C (5 TP)
        6: [
            { id: 'TP1', desc: 'Menjelaskan sifat-sifat bilangan berpangkat', nilaiCount: 0 },
            { id: 'TP2', desc: 'Menerapkan deret aritmetika', nilaiCount: 0 },
            { id: 'TP3', desc: 'Menyelesaikan sistem persamaan linear', nilaiCount: 0 },
            { id: 'TP4', desc: 'Menganalisis pertidaksamaan linear', nilaiCount: 0 },
            { id: 'TP5', desc: 'Menginterpretasi fungsi kuadrat', nilaiCount: 0 }
        ]
    },

    // State Toast Notification
    showToast: false,
    toastMessage: 'Urutan TP disimpan',
    triggerToast(msg) {
        this.toastMessage = msg || 'Urutan TP disimpan';
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 3000);
    },

    // State Drag and Drop
    draggedIndex: null,
    dragOverIndex: null,

    // Modals & Form State
    modalSalin: false,
    modalForm: false,
    modalHapus: false,
    modalHapusError: false,
    formMode: 'tambah',
    
    // Form Inputs & Validation
    formDesc: '',
    formError: false,
    selectedTp: null,

    // Computed Properties & Logika Kunci
    get currentRombel() {
        return this.rombelList.find(r => r.id === this.selectedRombelId) || this.rombelList[0];
    },
    get tps() {
        return this.allTps[this.selectedRombelId] || [];
    },
    get rombelsWithCount() {
        return this.rombelList.map(r => ({
            ...r,
            tpCount: (this.allTps[r.id] || []).length,
            isCurrent: r.id === this.selectedRombelId
        }));
    },
    get filteredRombels() {
        if (!this.searchRombel) return this.rombelsWithCount;
        return this.rombelsWithCount.filter(r => r.name.toLowerCase().includes(this.searchRombel.toLowerCase()));
    },
    get totalTp() { return this.tps.length; },
    get stsCount() { return Math.ceil(this.totalTp / 2); },
    isSts(index) { return index < this.stsCount; },
    isSas(index) { return index === this.totalTp - 1; },
    
    // Logika Pengunci Otomatis (TP separuh teratas dikunci jika status kunci aktif)
    isLocked(index) { return this.isStsLocked && this.isSts(index); },

    // Actions
    selectRombel(rombel) {
        this.selectedRombelId = rombel.id;
        this.dropdownOpen = false;
    },
    openTambah() { 
        this.formMode = 'tambah'; 
        this.formDesc = ''; 
        this.formError = false; 
        this.selectedTp = null; 
        this.modalForm = true; 
    },
    openUbah(tp, index) { 
        this.formMode = 'ubah'; 
        this.formDesc = tp.desc; 
        this.formError = false; 
        this.selectedTp = { ...tp, index: index }; 
        this.modalForm = true; 
    },
    openHapus(tp, index) {
        this.selectedTp = { ...tp, index: index };
        if (tp.nilaiCount > 0) {
            this.modalHapusError = true;
        } else {
            this.modalHapus = true;
        }
    },
    submitForm() {
        if (!this.formDesc.trim()) {
            this.formError = true;
            return;
        }
        this.formError = false;
        if (!this.allTps[this.selectedRombelId]) {
            this.allTps[this.selectedRombelId] = [];
        }

        if (this.formMode === 'tambah') {
            this.allTps[this.selectedRombelId].push({
                id: `TP${this.allTps[this.selectedRombelId].length + 1}`,
                desc: this.formDesc,
                nilaiCount: 0
            });
            this.triggerToast('TP berhasil ditambahkan');
        } else if (this.formMode === 'ubah' && this.selectedTp) {
            this.allTps[this.selectedRombelId][this.selectedTp.index].desc = this.formDesc;
            this.triggerToast('Perubahan TP disimpan');
        }
        this.modalForm = false;
    },
    confirmHapus() {
        if (this.selectedTp) {
            this.allTps[this.selectedRombelId].splice(this.selectedTp.index, 1);
            this.triggerToast('TP berhasil dihapus');
        }
        this.modalHapus = false;
    },

    // Drag and Drop Handlers
    dragStart(index) { this.draggedIndex = index; },
    dragOver(index) { this.dragOverIndex = index; },
    drop(dropIndex) {
        if (this.draggedIndex === null || this.draggedIndex === dropIndex) return;
        const currentList = this.allTps[this.selectedRombelId];
        const movedItem = currentList.splice(this.draggedIndex, 1)[0];
        currentList.splice(dropIndex, 0, movedItem);
        this.draggedIndex = null;
        this.dragOverIndex = null;
        this.triggerToast('Urutan TP disimpan');
    }
}">

    {{-- Title Header & Button Salin --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-sky-500 tracking-wider uppercase">SMA NEGERI 1 KOTA MALANG</p>
            <h1 class="text-3xl font-bold text-gray-900 mt-1">Kelola Tujuan Pembelajaran</h1>
            <p class="text-sm text-gray-500 mt-1">Susun TP Matematika untuk menjadi dasar penilaian dan rapor.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- TOMBOL TOGGLE TESTING STATUS KUNCI --}}
            <button @click="isStsLocked = !isStsLocked; triggerToast(isStsLocked ? 'STS Berhasil Dikunci' : 'STS Dibuka Kembali')" 
                    :class="isStsLocked ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-gray-100 text-gray-600 border-gray-300'"
                    class="px-3 py-2 border rounded-lg text-xs font-bold flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span x-text="isStsLocked ? 'Status: STS Terkunci' : 'Simulasi: Lock STS'"></span>
            </button>

            <button @click="modalSalin = true" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Salin TP ke kelas lain
            </button>
        </div>
    </div>

    {{-- Filter Dropdown Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-200 flex items-center justify-between shadow-sm relative z-20">
        <div class="flex gap-4 flex-1">
            {{-- DROPDOWN CUSTOM KELAS / ROMBEL --}}
            <div class="w-72 relative" @click.away="dropdownOpen = false">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas / Rombel</label>
                <button @click="dropdownOpen = !dropdownOpen" class="w-full bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-800 px-3 py-2.5 flex items-center justify-between shadow-sm hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                    <span x-text="currentRombel.name"></span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="dropdownOpen" x-cloak class="absolute left-0 mt-2 w-80 bg-white rounded-2xl border border-gray-200 shadow-xl z-30 p-3 space-y-2">
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="searchRombel" placeholder="Cari rombel..." class="w-full bg-gray-50 border border-gray-200 rounded-xl text-xs pl-9 pr-3 py-2.5 outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div class="max-h-56 overflow-y-auto space-y-1 pr-1">
                        <template x-for="r in filteredRombels" :key="r.id">
                            <button @click="selectRombel(r)" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition" :class="r.isCurrent ? 'bg-sky-50 text-sky-600' : 'hover:bg-gray-50 text-gray-700'">
                                <div class="flex items-center gap-2">
                                    <svg x-show="r.isCurrent" class="w-4 h-4 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="r.name"></span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md font-bold text-[10px]" :class="r.tpCount === 0 ? 'bg-red-50 text-red-500' : 'bg-gray-100 text-gray-500'" x-text="`${r.tpCount} TP`"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- DROPDOWN SEMESTER --}}
            <div class="w-64">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Semester</label>
                <select class="w-full bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-800 px-3 py-2.5 outline-none focus:ring-2 focus:ring-sky-500 shadow-sm cursor-pointer">
                    <option>Ganjil · 2026/2027</option>
                    <option>Genap · 2026/2027</option>
                </select>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="text-right pl-6 border-l border-gray-200 min-w-[130px]">
            <p class="text-xs font-semibold text-gray-500">TP terdaftar</p>
            <p class="text-3xl font-black mt-1" :class="totalTp >= 10 ? 'text-orange-600' : 'text-sky-600'">
                <span x-text="totalTp"></span> <span class="text-sm font-medium text-gray-400">/ 10 TP</span>
            </p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-300" :class="totalTp >= 10 ? 'bg-orange-500' : 'bg-sky-500'" :style="`width: ${(totalTp / 10) * 100}%`"></div>
            </div>
        </div>
    </div>

    {{-- Banner Peringatan (Terkunci oleh Deadline/Admin & Kuota Penuh) --}}
    <div class="space-y-3">
        <!-- Banner Hijau: STS Terkunci oleh Admin/Deadline -->
        <div x-show="isStsLocked && totalTp > 0" x-cloak class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl flex gap-3 items-start">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <div>
                <h4 class="text-sm font-bold text-emerald-800">Periode Nilai STS Telah Ditutup</h4>
                <p class="text-xs text-emerald-600 mt-0.5 leading-relaxed">
                    Tenggat waktu pengisian nilai Tengah Semester telah berakhir dan dikunci oleh Administrator. 
                    <b>TP1-TP<span x-text="stsCount"></span> (STS) tidak dapat diubah lagi.</b> 
                    Namun, TP<span x-text="stsCount + 1"></span>-TP<span x-text="totalTp"></span> masih dapat dikelola untuk persiapan penilaian Akhir Semester (SAS).
                </p>
            </div>
        </div>

        <!-- Banner Oranye: Kuota Penuh -->
        <div x-show="totalTp >= 10" x-cloak class="bg-orange-50 border border-orange-200 p-4 rounded-xl flex gap-3 items-start">
            <svg class="w-5 h-5 text-orange-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h4 class="text-sm font-bold text-orange-800">Kuota TP penuh</h4>
                <p class="text-xs text-orange-600 mt-0.5">Hapus atau gabungkan TP yang belum dinilai untuk menambah TP baru.</p>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri: Tabel Data --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">
            
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Daftar Tujuan Pembelajaran</h2>
                    <p class="text-sm text-gray-500">Seret untuk mengubah urutan — penanda STS/SAS ikut menyesuaikan.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex bg-gray-100 p-1 rounded-lg">
                        <button @click="filterTab = 'semua'" :class="filterTab === 'semua' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="px-3 py-1.5 text-xs font-semibold rounded-md transition">Semua TP</button>
                        <button @click="filterTab = 'sts'" :class="filterTab === 'sts' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="px-3 py-1.5 text-xs font-semibold rounded-md transition">STS saja</button>
                    </div>
                    <span :class="totalTp >= 10 ? 'bg-orange-50 text-orange-700 border-orange-200' : (totalTp === 0 ? 'bg-gray-100 text-gray-500 border-gray-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200')" class="px-3 py-1.5 text-xs font-bold rounded-lg border" x-text="`${totalTp} TP aktif`"></span>
                </div>
            </div>

            {{-- Info Text saat Tab STS Saja Dipilih --}}
            <div x-show="filterTab === 'sts' && totalTp > 0" x-cloak class="px-5 py-2.5 bg-gray-50/80 border-b border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Menampilkan <b class="text-gray-700" x-text="stsCount"></b> TP yang masuk penilaian STS.</span>
            </div>

            {{-- Empty State (0 TP) --}}
            <div x-show="totalTp === 0" x-cloak class="flex-1 flex flex-col items-center justify-center p-16 text-center">
                <div class="w-16 h-16 bg-sky-50 rounded-full flex items-center justify-center text-sky-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900" x-text="`Belum ada TP untuk ${currentRombel.name.split('·')[1] || 'Matematika'} ${currentRombel.name.split('·')[0]}`"></h3>
                <p class="text-sm text-gray-500 mt-1">Tambahkan TP atau salin dari rombel lain yang Anda ajar.</p>
                <div class="mt-6 flex items-center gap-3">
                    <button @click="openTambah()" class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-sm font-semibold shadow-sm flex items-center gap-2 transition">+ Tambah TP</button>
                    <button @click="modalSalin = true" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-semibold shadow-sm flex items-center gap-2 transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Salin dari rombel lain
                    </button>
                </div>
            </div>

            {{-- List TP dengan Drag & Drop --}}
            <div x-show="totalTp > 0" class="divide-y divide-gray-100 flex-1 overflow-auto">
                <template x-for="(tp, index) in tps" :key="index">
                    <div x-show="filterTab === 'semua' || (filterTab === 'sts' && isSts(index))" 
                         :draggable="!isLocked(index)"
                         @dragstart="dragStart(index)"
                         @dragover.prevent="dragOver(index)"
                         @drop="drop(index)"
                         :class="{
                            'bg-sky-50/60 border-l-4 border-sky-500': dragOverIndex === index,
                            'opacity-40': draggedIndex === index
                         }"
                         class="p-5 flex items-center justify-between hover:bg-gray-50/80 transition group relative">

                        <div x-show="dragOverIndex === index" class="absolute -top-1 left-0 right-0 border-t-2 border-dashed border-sky-400 z-10 pointer-events-none"></div>
                        
                        <div class="flex items-start gap-4">
                            <span :class="isLocked(index) ? 'opacity-0 cursor-default' : 'cursor-grab text-gray-300 group-hover:text-gray-500'" class="font-bold pt-1 select-none">⋮⋮</span>
                            <span class="text-sm font-bold text-gray-400 pt-1" x-text="String(index + 1).padStart(2, '0')"></span>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900" x-text="`TP${index + 1}`"></h3>
                                <p class="text-sm text-gray-600 mt-1" x-text="tp.desc"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <span x-show="isSts(index)" class="px-2.5 py-1 text-[10px] font-bold bg-sky-50 text-sky-600 rounded border border-sky-100">STS</span>
                            <span x-show="isSas(index)" class="px-2.5 py-1 text-[10px] font-bold bg-orange-50 text-orange-600 rounded border border-orange-100">SAS · TP terakhir</span>

                            <template x-if="isLocked(index)">
                                <div class="flex items-center gap-1.5 text-gray-400 text-xs font-semibold pl-3 border-l border-gray-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Terkunci
                                </div>
                            </template>
                            <template x-if="!isLocked(index)">
                                <div class="flex items-center gap-1.5 border-l border-gray-200 pl-3">
                                    <button @click="openUbah(tp, index)" class="p-2 text-gray-400 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button @click="openHapus(tp, index)" class="p-2 text-red-400 hover:text-red-700 border border-gray-200 rounded-lg hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer Button --}}
            <div x-show="totalTp > 0" class="p-5 border-t border-gray-100 bg-gray-50/50 flex items-center gap-4 mt-auto">
                <button @click="if(totalTp < 10) openTambah()" :disabled="totalTp >= 10" :class="totalTp >= 10 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-sky-500 hover:bg-sky-600 text-white'" class="px-4 py-2 rounded-lg text-sm font-semibold shadow-sm flex items-center gap-2 transition">
                    + Tambah TP
                </button>
                <template x-if="totalTp >= 10">
                    <span class="px-3 py-1 bg-gray-800 text-white text-xs font-bold rounded-lg shadow-sm">Kuota 10 TP sudah terpenuhi</span>
                </template>
                <template x-if="totalTp < 10">
                    <p class="text-sm text-gray-500">Maksimal 10 TP — gabungkan TP yang mirip jika kuota tercapai.</p>
                </template>
            </div>
        </div>

        {{-- Kolom Kanan: Ringkasan & Panduan --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4">Ringkasan TP</h3>
                <div x-show="totalTp === 0" x-cloak><p class="text-sm text-gray-500 mb-4">Belum ada TP di rombel ini.</p></div>
                <ul x-show="totalTp > 0" class="space-y-3 text-sm text-gray-700">
                    <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> <b x-text="totalTp"></b> TP terdaftar</li>
                    <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-sky-500"></span> <b x-text="stsCount"></b> masuk STS</li>
                    <li class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-orange-500"></span> <b x-text="`TP${totalTp}`"></b> dinilai lewat SAS</li>
                </ul>

                <hr class="my-5 border-gray-100">
                <div class="bg-orange-50/50 border border-orange-100 p-4 rounded-xl">
                    <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> STS & SAS otomatis
                    </h4>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        STS = separuh TP teratas <span x-show="totalTp > 0">(<span x-text="totalTp"></span> TP &rarr; TP1-TP<span x-text="stsCount"></span>)</span>.<br>
                        TP paling bawah otomatis dinilai lewat SAS.
                    </p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4">Panduan cepat</h3>
                <ul class="text-sm text-gray-500 space-y-2 list-disc list-inside mb-6">
                    <li>Maksimal 10 TP per rombel</li>
                    <li>STS otomatis: separuh TP teratas</li>
                    <li>TP paling bawah otomatis jadi SAS</li>
                    <li>TP dapat disalin ke rombel lain</li>
                </ul>
                <button :disabled="totalTp === 0" :class="totalTp === 0 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-sky-500 hover:bg-sky-600 text-white shadow-sm'" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 transition">
                    Lanjut input nilai &rarr;
                </button>
                <p x-show="totalTp === 0" x-cloak class="text-[11px] text-gray-400 mt-3 text-center">Tambahkan minimal 1 TP untuk mulai mengisi nilai.</p>
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

    {{-- MODAL BACKDROP --}}
    <div x-show="modalSalin || modalForm || modalHapus || modalHapusError" x-cloak class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 transition-opacity"></div>

    {{-- 1. POP-UP MODAL: SALIN TP KE KELAS LAIN --}}
    <div x-show="modalSalin" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click.away="modalSalin = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Salin TP ke kelas lain</h3>
                <button @click="modalSalin = false" class="text-gray-400 hover:text-gray-700 transition">&times;</button>
            </div>
            <div class="p-6 overflow-y-auto space-y-6">
                <p class="text-sm text-gray-600">Pilih kelas tujuan. Seluruh <span x-text="totalTp"></span> TP disalin dengan urutan dan penanda STS/SAS otomatis.</p>
                <div class="space-y-3">
                    <label class="flex items-center justify-between p-3.5 border-2 border-sky-500 rounded-xl cursor-pointer bg-sky-50/30 transition">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" checked class="w-4 h-4 text-sky-500 border-gray-300 rounded focus:ring-sky-500">
                            <span class="text-sm font-bold text-gray-900">X-A · Matematika</span>
                        </div>
                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2.5 py-1 rounded-full border border-orange-100">6 TP — akan ditimpa</span>
                    </label>
                    <label class="flex items-center justify-between p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" checked class="w-4 h-4 text-sky-500 border-gray-300 rounded focus:ring-sky-500">
                            <span class="text-sm font-bold text-gray-900">X-C · Matematika</span>
                        </div>
                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">0 TP</span>
                    </label>
                    <label class="flex items-center justify-between p-3.5 border border-gray-100 rounded-xl cursor-not-allowed opacity-50 bg-gray-50">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" disabled class="w-4 h-4 text-gray-300 rounded">
                            <span class="text-sm font-bold text-gray-400">XI-A · Matematika</span>
                        </div>
                        <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">Fase berbeda</span>
                    </label>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900 mb-3">Jika kelas tujuan sudah punya TP</p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 p-3.5 border-2 border-sky-500 rounded-xl cursor-pointer bg-sky-50/30 transition">
                            <input type="radio" name="overwrite" checked class="mt-0.5 w-4 h-4 text-sky-500 border-gray-300 focus:ring-sky-500">
                            <span class="text-sm font-semibold text-gray-900 leading-tight">Tambahkan ke TP yang ada</span>
                        </label>
                        <label class="flex items-start gap-3 p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" name="overwrite" class="mt-0.5 w-4 h-4 text-sky-500 border-gray-300 focus:ring-sky-500">
                            <span class="text-sm font-semibold text-gray-900 leading-tight">Ganti seluruh TP</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button @click="modalSalin = false" class="px-5 py-2.5 text-gray-600 bg-white border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button @click="modalSalin = false; triggerToast('TP berhasil disalin ke kelas tujuan')" class="px-6 py-2.5 text-white bg-sky-500 rounded-xl text-sm font-semibold hover:bg-sky-600 shadow-sm transition">Salin TP</button>
            </div>
        </div>
    </div>

    {{-- 2. POP-UP MODAL: TAMBAH & UBAH TP (Dengan Min-Height Konsisten) --}}
    <div x-show="modalForm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click.away="modalForm = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col min-h-[580px] justify-between">
            <div class="p-5 flex justify-between items-start border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="formMode === 'tambah' ? 'Tambah Tujuan Pembelajaran' : 'Ubah Tujuan Pembelajaran'"></h3>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="`${currentRombel.name} · Semester Ganjil 2026/2027`"></p>
                </div>
                <button @click="modalForm = false" class="text-gray-400 hover:text-gray-700 transition">&times;</button>
            </div>
            
            <div class="p-6 space-y-5 flex-1">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-xs font-semibold text-gray-900">Kode TP</label>
                        <span class="text-xs font-bold text-sky-600">Sisa kuota: <span x-text="10 - totalTp"></span> dari 10 TP</span>
                    </div>
                    <input type="text" disabled :value="formMode === 'tambah' ? `TP${totalTp + 1}` : selectedTp?.id" class="w-24 bg-gray-50 border border-gray-200 text-gray-500 rounded-xl text-sm px-3 py-2 font-semibold">
                    <p class="text-xs text-gray-400 mt-1">Dibuat otomatis sesuai urutan.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-1">Deskripsi TP <span class="text-red-500">*</span></label>
                    <textarea x-model="formDesc" rows="3" 
                              :class="formError ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300 focus:ring-sky-500'"
                              placeholder="Contoh: Peserta didik mampu menyelesaikan masalah kontekstual yang berkaitan dengan fungsi kuadrat." 
                              class="w-full bg-white border rounded-xl text-sm p-3 outline-none focus:ring-2 resize-none transition"></textarea>
                    
                    <div x-show="formError" x-cloak class="flex items-center gap-1.5 mt-1 text-red-500 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Deskripsi TP wajib diisi.</span>
                    </div>

                    <div class="flex justify-end mt-1"><span class="text-xs text-gray-400"><span x-text="formDesc.length"></span>/300</span></div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs font-bold text-gray-900 mb-3 flex items-center gap-2">
                        Pengaturan penilaian 
                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-md text-[10px] font-bold border border-emerald-100">Otomatis</span>
                    </p>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Penilaian STS</p>
                                <p class="text-xs text-gray-500" x-text="formMode === 'tambah' ? `Separuh TP teratas: TP1-TP${Math.ceil((totalTp + 1)/2)} dari ${totalTp + 1} TP.` : `TP${selectedTp?.index + 1} ada di separuh teratas (TP1-TP${stsCount} dari ${totalTp} TP).`"></p>
                            </div>
                            <span :class="(formMode === 'tambah' ? isSts(totalTp) : isSts(selectedTp?.index)) ? 'bg-sky-50 text-sky-600 border-sky-100' : 'bg-gray-200 text-gray-500 border-transparent'" class="text-xs font-bold px-3 py-1.5 rounded-lg border" x-text="(formMode === 'tambah' ? isSts(totalTp) : isSts(selectedTp?.index)) ? 'Ya · STS' : 'Tidak'"></span>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <p class="text-sm font-bold text-gray-900">TP terakhir (dinilai lewat SAS)</p>
                                <p class="text-xs text-gray-500" x-text="formMode === 'tambah' ? `TP${totalTp + 1} jadi TP paling bawah, SAS pindah dari TP${totalTp}.` : `Hanya TP paling bawah (TP${totalTp}) yang dinilai SAS.`"></p>
                            </div>
                            <span :class="(formMode === 'tambah' ? true : isSas(selectedTp?.index)) ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-gray-200 text-gray-500 border-transparent'" class="text-xs font-bold px-3 py-1.5 rounded-lg border" x-text="(formMode === 'tambah' ? true : isSas(selectedTp?.index)) ? 'Ya · SAS' : 'Tidak'"></span>
                        </div>
                    </div>
                </div>

                {{-- Alert Info Biru yang Hanya Muncul jika Memiliki Nilai (>0) --}}
                <div x-show="formMode === 'ubah' && selectedTp?.nilaiCount > 0" x-cloak class="bg-sky-50/60 border border-sky-200 p-3.5 rounded-xl flex gap-2.5 items-start">
                    <svg class="w-4 h-4 text-sky-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-sky-800 leading-relaxed">TP ini sudah memiliki <b x-text="selectedTp?.nilaiCount"></b> nilai. Mengubah deskripsi tidak mengubah nilai.</p>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 mt-auto">
                <button @click="modalForm = false" class="px-5 py-2.5 text-gray-600 bg-white border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button @click="submitForm()" class="px-6 py-2.5 text-white bg-sky-500 rounded-xl text-sm font-semibold hover:bg-sky-600 shadow-sm transition" x-text="formMode === 'tambah' ? 'Simpan TP' : 'Simpan Perubahan'"></button>
            </div>
        </div>
    </div>

    {{-- 3. POP-UP MODAL: HAPUS TP --}}
    <div x-show="modalHapus" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click.away="modalHapus = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden text-center p-6">
            <div class="w-12 h-12 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1" x-text="`Hapus ${selectedTp?.id}?`"></h3>
            <p class="text-xs text-gray-500 mb-4 px-2 leading-relaxed">"<span x-text="selectedTp?.desc"></span>" akan dihapus. Tindakan ini tidak dapat dibatalkan.</p>
            
            <div class="bg-gray-50 p-3 rounded-xl flex items-center gap-2 text-left mb-6 border border-gray-100">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs text-gray-500">Belum ada nilai untuk TP ini.</span>
            </div>
            
            <div class="flex justify-center gap-3">
                <button @click="modalHapus = false" class="px-5 py-2.5 text-gray-600 bg-white border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 transition">Batal</button>
                <button @click="confirmHapus()" class="px-5 py-2.5 text-white bg-red-600 rounded-xl text-sm font-semibold hover:bg-red-700 shadow-sm transition">Hapus TP</button>
            </div>
        </div>
    </div>

    {{-- 4. POP-UP MODAL: ERROR UNABLE TO DELETE --}}
    <div x-show="modalHapusError" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click.away="modalHapusError = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden text-center p-6 relative">
            <button @click="modalHapusError = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition">&times;</button>
            
            <div class="w-12 h-12 border-2 border-orange-200 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="`${selectedTp?.id} tidak dapat dihapus`"></h3>
            <p class="text-xs text-gray-500 mb-6 leading-relaxed">TP ini sudah memiliki <b x-text="selectedTp?.nilaiCount"></b> nilai siswa. Agar nilai tidak hilang, TP yang sudah dinilai tidak dapat dihapus.</p>
            
            <div class="flex justify-center gap-3">
                <button @click="modalHapusError = false; openUbah(selectedTp, selectedTp.index);" class="px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-50 shadow-sm transition">Ubah Deskripsi</button>
                <button @click="modalHapusError = false" class="px-5 py-2.5 text-white bg-sky-500 rounded-xl text-sm font-semibold hover:bg-sky-600 shadow-sm transition">Mengerti</button>
            </div>
        </div>
    </div>

</div>
@endsection