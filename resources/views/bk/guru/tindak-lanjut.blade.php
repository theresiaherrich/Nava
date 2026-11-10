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

        $siswa       = $pengajuan->riwayatKelas->siswa ?? null;
        $namaSiswa   = $siswa->nama ?? '-';
        $namaKelas   = $pengajuan->riwayatKelas->kelas->nama ?? '-';
        $nis         = $siswa->nis ?? '-';
        $initials    = collect(explode(' ', $namaSiswa))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
        $idPengajuan = $pengajuan->getKey();

        $statusSekarang = old('status', $pengajuan->status ?? '');
        // Nilai untuk input datetime-local (format Y-m-d\TH:i)
        $jadwalValue = old('jadwal_bimbingan', $pengajuan->jadwal_bimbingan?->format('Y-m-d\TH:i'));
    @endphp

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Tindak Lanjut Pengajuan</h1>
                <p class="text-xs text-gray-500 mt-1">Proses dan tindak lanjuti pengajuan bimbingan siswa.</p>
            </div>
            <a href="{{ route('bk.pengajuan') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali
            </a>
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
                    <div class="w-11 h-11 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-sm font-bold flex-none">
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
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Tanggal Pengajuan</p>
                        <p class="text-xs text-gray-700">{{ $pengajuan->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>
                    @if(!empty($pengajuan->deskripsi))
                        <div>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Deskripsi Siswa</p>
                            <p class="text-xs text-gray-700 leading-relaxed">{{ $pengajuan->deskripsi }}</p>
                        </div>
                    @endif
                    @if($pengajuan->jadwal_bimbingan)
                        <div class="p-3 bg-purple-50 border border-purple-100 rounded-xl">
                            <p class="text-[10px] text-purple-700 font-bold uppercase tracking-wider mb-0.5">Jadwal Bimbingan</p>
                            <p class="text-xs font-semibold text-purple-900">{{ $pengajuan->jadwal_bimbingan->format('d M Y, H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Form Tindak Lanjut --}}
        <div class="lg:col-span-3">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-1">Form Tindak Lanjut</h2>
                <p class="text-xs text-gray-400 mb-5">Isi data di bawah untuk memproses pengajuan bimbingan ini.</p>

                <form method="POST" action="{{ route('bk.pengajuan.update', $idPengajuan) }}" id="form-tindak-lanjut">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        {{-- Dropdown Guru BK --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Guru BK yang Menangani</label>
                            <select name="id_guru" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">— Pilih Guru BK —</option>
                                @foreach($guruBkList as $gbk)
                                    <option value="{{ $gbk->id_guru }}"
                                        {{ (string) old('id_guru', $pengajuan->id_guru ?? $guruBk->id_guru ?? '') === (string) $gbk->id_guru ? 'selected' : '' }}>
                                        {{ $gbk->guru->nama ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_guru')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Dropdown Status --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Status Pengajuan</label>
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
                                <span id="catatan-required-mark" class="text-red-500 {{ in_array($statusSekarang, ['dijadwalkan','selesai','ditolak']) ? '' : 'hidden' }}">*</span>
                            </label>
                            <textarea name="balasan_bk" rows="4"
                                placeholder="Tulis catatan, jadwal, atau alasan penolakan..."
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none">{{ old('balasan_bk', $pengajuan->balasan_bk) }}</textarea>
                            @error('balasan_bk')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit"
                                class="flex-1 py-2.5 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition flex items-center justify-center gap-2">
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
                catatanMark.classList.toggle('hidden', !['dijadwalkan', 'selesai', 'ditolak'].includes(val));
            }

            statusSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        });
    </script>

</x-layouts.admin>