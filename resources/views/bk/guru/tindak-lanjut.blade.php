<x-layouts.admin title="Tindak Lanjut Pengajuan BK - SIAKAD SMAN 1 Malang">

    @php
        $status = strtolower($pengajuan->status ?? '');
        $badgeMap = [
            'menunggu'    => 'bg-amber-100/80 text-amber-800',
            'diproses'    => 'bg-sky-100/80 text-sky-800',
            'dijadwalkan' => 'bg-purple-100/80 text-purple-800',
            'selesai'     => 'bg-emerald-100/80 text-emerald-800',
            'ditolak'     => 'bg-red-100/80 text-red-800',
        ];
        $badgeClass = $badgeMap[$status] ?? 'bg-gray-100 text-gray-700';

        $siswa       = $pengajuan->siswa ?? $pengajuan->riwayatKelas->siswa ?? null;
        $namaSiswa   = $siswa->nama ?? '-';
        $namaKelas   = $siswa->riwayatKelasAktif?->kelas?->nama ?? '-';
        $nis         = $siswa->nis ?? '-';
        $initials    = collect(explode(' ', $namaSiswa))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
        $idPengajuan = $pengajuan->getKey();

        $statusSekarang = old('status', $pengajuan->status ?? '');
        
        // Tentukan nilai awal jadwal
        $defaultJadwal = null;
        if (!empty($pengajuan->tanggal_bimbingan)) {
            $tgl = \Carbon\Carbon::parse($pengajuan->tanggal_bimbingan)->format('Y-m-d');
            $jam = substr($pengajuan->jam_bimbingan ?? '08:00:00', 0, 5);
            $defaultJadwal = $tgl . 'T' . $jam;
        } elseif (!empty($pengajuan->jadwal_bimbingan)) {
            $defaultJadwal = \Carbon\Carbon::parse($pengajuan->jadwal_bimbingan)->format('Y-m-d\TH:i');
        }
        $jadwalValue = old('jadwal_bimbingan', $defaultJadwal);
        $balasanValue = old('balasan', $pengajuan->balasan ?? '');
    @endphp

    <div class="max-w-6xl mx-auto py-3">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="mt-1">
            <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Tindak Lanjut Pengajuan</h1>
            <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Proses dan tindak lanjuti pengajuan bimbingan siswa.</p>
        </div>
    </div>

    {{-- Success / Error Alert --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl">
            <p class="text-xs font-bold text-red-700 mb-1">Mohon perbaiki kesalahan berikut:</p>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li class="text-xs text-red-600">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Kartu Info Pengajuan --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">ID Pengajuan</p>
                        <p class="text-sm font-black text-slate-900">BK-{{ str_pad($idPengajuan, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <span class="px-3 py-1 {{ $badgeClass }} text-[10px] font-bold rounded-full">{{ ucfirst($status) }}</span>
                </div>

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-full bg-[#089BD5] text-white flex items-center justify-center text-sm font-bold flex-none">
                        {{ $initials }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $namaSiswa }}</p>
                        <p class="text-xs text-gray-500">{{ $namaKelas }} &nbsp;·&nbsp; {{ $nis }}</p>
                    </div>
                </div>

                <div class="space-y-2.5 border-t border-gray-100 pt-4">
                    <div>
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Topik</p>
                        <p class="text-xs font-semibold text-gray-800">{{ $pengajuan->topik ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Deskripsi Masalah</p>
                        <p class="text-xs text-gray-600 leading-relaxed bg-gray-50 p-3 rounded-xl border border-gray-100">{{ $pengajuan->deskripsi ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Waktu Pengajuan</p>
                        <p class="text-xs text-gray-700">{{ $pengajuan->created_at ? \Carbon\Carbon::parse($pengajuan->created_at)->isoFormat('D MMMM Y, HH:mm') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Tindak Lanjut --}}
        <div class="lg:col-span-3">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-1">Form Tindak Lanjut</h2>
                <p class="text-xs text-gray-400 mb-5">Perbarui status dan atur jadwal atau catatan bimbingan</p>

                <form method="POST" action="{{ route('bk.pengajuan.update', $idPengajuan) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        {{-- Guru BK Penanggung Jawab --}}
                        <div>
                            <span class="block text-xs font-bold text-gray-700 mb-1.5">Guru BK Penanggung Jawab</span>
                            <p class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 bg-gray-50">
                                {{ $guru->nama }}
                            </p>
                        </div>

                        {{-- Status Pengajuan --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Status Pengajuan <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status-select" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">— Pilih Status —</option>
                                <option value="diproses"    {{ $statusSekarang === 'diproses'    ? 'selected' : '' }}>Diproses</option>
                                <option value="dijadwalkan" {{ $statusSekarang === 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                                <option value="selesai"     {{ $statusSekarang === 'selesai'     ? 'selected' : '' }}>Selesai</option>
                                <option value="ditolak"     {{ $statusSekarang === 'ditolak'     ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            @error('status')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Jadwal Bimbingan (muncul jika dijadwalkan) --}}
                        <div id="jadwal-section" class="{{ $statusSekarang === 'dijadwalkan' ? '' : 'hidden' }}">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Jadwal Bimbingan <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="jadwal_bimbingan" id="jadwal-input" value="{{ $jadwalValue }}"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('jadwal_bimbingan')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Balasan / Catatan --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                Balasan / Catatan
                                <span id="catatan-required-mark" class="text-red-500 {{ in_array($statusSekarang, ['selesai','ditolak']) ? '' : 'hidden' }}">*</span>
                            </label>
                            <textarea name="balasan" rows="4"
                                placeholder="Tulis catatan, jadwal, atau alasan penolakan..."
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none">{{ $balasanValue }}</textarea>
                            @error('balasan')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit"
                                class="flex-1 py-2.5 bg-[#089BD5] text-white text-xs font-bold rounded-xl hover:bg-[#078ec4] transition flex items-center justify-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Simpan Tindak Lanjut
                            </button>
                            <a href="{{ route('bk.pengajuan') }}"
                                class="px-5 py-2.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                                Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>{{-- /max-w-6xl --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var statusSelect  = document.getElementById('status-select');
            var jadwalSection = document.getElementById('jadwal-section');
            var jadwalInput   = document.getElementById('jadwal-input');
            var catatanMark   = document.getElementById('catatan-required-mark');

            function updateVisibility() {
                var val = statusSelect.value;
                var dijadwalkan = val === 'dijadwalkan';
                jadwalSection.classList.toggle('hidden', !dijadwalkan);
                jadwalInput.required = dijadwalkan;
                catatanMark.classList.toggle('hidden', !['selesai', 'ditolak'].includes(val));
            }

            statusSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        });
    </script>

</x-layouts.admin>
