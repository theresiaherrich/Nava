<x-layouts.admin title="Dashboard Guru BK - RIASEC">

    <!-- Header Greeting -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#0f1b33]">Selamat pagi, Bu Rina 👋</h1>
            <p class="text-xs text-[#6b7a90] mt-1">Kelola sesi tes RIASEC, rekap hasil minat bakat, dan pengajuan konsultasi siswa.</p>
        </div>
        <button onclick="openModal()" class="pt-btn-primary text-xs flex items-center gap-2">
            <span>+</span> Tambah Sesi Tes
        </button>
    </div>

    <!-- 4 Card Statistik (Stat Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="pt-card">
            <span class="text-xs font-semibold text-[#6b7a90]">Total Siswa</span>
            <div class="text-3xl font-extrabold text-[#0f1b33] mt-2">{{ number_format($stats['total_siswa']) }}</div>
            <span class="text-[11px] text-gray-400 mt-1 block">Terdaftar di Sistem</span>
        </div>
        <div class="pt-card">
            <span class="text-xs font-semibold text-[#6b7a90]">Sudah Tes RIASEC</span>
            <div class="text-3xl font-extrabold text-[#12b76a] mt-2">{{ number_format($stats['sudah_tes']) }}</div>
            <span class="text-[11px] text-[#12b76a] mt-1 block">77.2% Progress</span>
        </div>
        <div class="pt-card">
            <span class="text-xs font-semibold text-[#6b7a90]">Belum Mengikuti Tes</span>
            <div class="text-3xl font-extrabold text-[#f04438] mt-2">{{ number_format($stats['belum_tes']) }}</div>
            <span class="text-[11px] text-[#f04438] mt-1 block">Perlu Tindak Lanjut</span>
        </div>
        <div class="pt-card">
            <span class="text-xs font-semibold text-[#6b7a90]">Sesi Tes Aktif</span>
            <div class="text-3xl font-extrabold text-[#009bf2] mt-2">{{ $stats['sesi_aktif'] }}</div>
            <span class="text-[11px] text-[#009bf2] mt-1 block">Sedang Berjalan</span>
        </div>
    </div>

    <!-- Section Rekap Tes RIASEC -->
    <div class="pt-card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-[#0f1b33]">Rekap Sesi Tes RIASEC</h2>
            <a href="#" class="text-xs font-semibold text-[#009bf2]">Lihat Semua</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 text-[#6b7a90]">
                        <th class="pb-3 font-semibold">Nama Sesi</th>
                        <th class="pb-3 font-semibold">Jenjang</th>
                        <th class="pb-3 font-semibold">Tanggal Pelaksanaan</th>
                        <th class="pb-3 font-semibold">Status</th>
                        <th class="pb-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr>
                        <td class="py-3 font-medium">Tes RIASEC Kelas XII IPA 2026</td>
                        <td class="py-3">Kelas XII</td>
                        <td class="py-3">10 Mar 2026 - 15 Mar 2026</td>
                        <td class="py-3"><span class="pt-badge-success">Aktif</span></td>
                        <td class="py-3 text-right">
                            <button class="text-[#009bf2] hover:underline font-semibold">Detail</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 font-medium">Tes RIASEC Lanjutan IPS 1</td>
                        <td class="py-3">Kelas XI</td>
                        <td class="py-3">01 Mar 2026 - 05 Mar 2026</td>
                        <td class="py-3"><span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-semibold">Selesai</span></td>
                        <td class="py-3 text-right">
                            <button class="text-[#009bf2] hover:underline font-semibold">Detail</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL POPUP: Tambah / Edit Sesi Tes (Presisi Gambar Mockup A3-BK.pdf) -->
    <div id="modalSesi" class="fixed inset-0 pt-modal-overlay hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl relative">
            
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-[#0f1b33]">Tambah Sesi Tes</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('riasec.simpan-sesi') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-semibold text-[#0f1b33] mb-1">Nama Sesi Tes *</label>
                    <input type="text" name="nama_sesi" placeholder="Contoh: Tes RIASEC Angkatan 2026" required
                        class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-[#009bf2]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#0f1b33] mb-1">Jenjang *</label>
                        <select name="jenjang" class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-[#009bf2]">
                            <option value="Kelas X">Kelas X</option>
                            <option value="Kelas XI">Kelas XI</option>
                            <option value="Kelas XII">Kelas XII</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#0f1b33] mb-1">Kuota Peserta *</label>
                        <input type="number" name="kuota" value="36" required
                            class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-[#009bf2]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#0f1b33] mb-1">Tanggal Mulai *</label>
                        <input type="date" name="tanggal_mulai" required
                            class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-[#009bf2]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#0f1b33] mb-1">Tanggal Selesai *</label>
                        <input type="date" name="tanggal_selesai" required
                            class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-[#009bf2]">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-xs font-semibold text-gray-500 hover:bg-gray-50 rounded-lg">Batal</button>
                    <button type="submit" class="pt-btn-primary text-xs">Simpan Sesi Tes</button>
                </div>
            </form>

        </div>
    </div>

    <!-- Script Modal -->
    <script>
        function openModal() {
            document.getElementById('modalSesi').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('modalSesi').classList.add('hidden');
        }
    </script>
</x-layouts.admin>