<x-layouts.admin title="Profil Guru BK - SIAKAD SMAN 1 Malang">

    @php
        $namaGuru = $guru->nama ?? $user->name ?? '-';
        $initials = collect(explode(' ', $namaGuru))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
        $emailGuru = $guruBk->email ?? $user->email ?? $guru->email ?? '';
        $teleponGuru = $guruBk->no_telepon ?? $guru->no_telepon ?? '';
        $errorProfil = $errors->hasAny(['nama', 'email', 'no_telepon', 'jadwal_layanan']);
        $errorPassword = $errors->hasAny(['password_lama', 'password_baru']);
    @endphp

    {{-- Header --}}
    <div class="mb-6">
        <x-page-label />
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Profil Guru BK</h1>
            <p class="text-xs text-gray-500 mt-1">Kelola informasi akun dan data profil Anda.</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2.5"/>
            </svg>
            <p class="text-xs font-semibold text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Avatar Card --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center gap-3">
                <div class="w-20 h-20 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-2xl font-black">
                    {{ $initials }}
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">{{ $namaGuru }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Guru Bimbingan dan Konseling</p>
                </div>
                <span class="px-3 py-1 bg-emerald-100/80 text-emerald-800 text-[10px] font-bold rounded-full">Akun Aktif</span>

                <div class="w-full border-t border-gray-100 pt-3">
                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">NIP</p>
                    <p class="text-xs font-bold text-slate-800">{{ $guru->nip ?? '-' }}</p>
                </div>
            </div>

            {{-- Keamanan Akun --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="text-xs font-bold text-slate-900 mb-3">Keamanan Akun</h3>
                <div class="space-y-2.5">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-gray-500">Kata Sandi</span>
                        <span class="text-xs font-semibold text-gray-800">••••••••</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-gray-500">Terakhir Diubah</span>
                        <span class="text-xs text-gray-600">
                            {{ $logPassword ? \Carbon\Carbon::parse($logPassword->created_at)->format('d M Y') : 'Belum pernah' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-gray-500">Status Akun</span>
                        <span class="px-2.5 py-0.5 bg-emerald-100/80 text-emerald-800 text-[10px] font-bold rounded-full">Aktif</span>
                    </div>
                </div>

                <button type="button" id="toggle-password-form"
                    class="mt-4 w-full py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                    Ubah Kata Sandi
                </button>

                <div id="password-form" class="hidden mt-4 pt-4 border-t border-gray-100">
                    <form method="POST" action="{{ route('bk.profil.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="space-y-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 mb-1">Password Lama</label>
                                <input type="password" name="password_lama" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('password_lama')
                                    <p class="text-[11px] text-red-500 mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 mb-1">Password Baru</label>
                                <input type="password" name="password_baru" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('password_baru')
                                    <p class="text-[11px] text-red-500 mt-0.5">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="password_baru_confirmation" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                            <button type="submit"
                                class="w-full py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                                Simpan Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Informasi Pribadi --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Informasi Pribadi</h3>
                        <p class="text-xs text-gray-400">Data diri dan kontak Guru BK</p>
                    </div>
                    <button type="button" id="open-edit-modal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Profil
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Nama Lengkap</p>
                        <p class="text-xs font-semibold text-gray-800">{{ $namaGuru }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">NIP</p>
                        <p class="text-xs font-semibold text-gray-800">{{ $guru->nip ?? '-' }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Email</p>
                        <p class="text-xs font-semibold text-gray-800">{{ $emailGuru ?: '-' }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Nomor Telepon</p>
                        <p class="text-xs font-semibold text-gray-800">{{ $teleponGuru ?: '-' }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Jadwal Layanan</p>
                        <p class="text-xs font-semibold text-gray-800">{{ $guruBk->jadwal_layanan ?? '-' }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Unit Kerja</p>
                        <p class="text-xs font-semibold text-gray-800">{{ $guru->unit_kerja ?? 'SMAN 1 Malang' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL EDIT PROFIL ===================== --}}
    <div id="modal-edit-profil" class="fixed inset-0 z-[60] hidden items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
        <div data-close-modal class="absolute inset-0 bg-slate-900/50"></div>

        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl max-h-[92vh] overflow-y-auto">
            {{-- Header modal --}}
            <div class="flex items-start justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] font-bold text-[#009bf2] uppercase tracking-widest">SMA Negeri 1 Kota Malang</p>
                    <h2 id="modal-edit-title" class="text-lg font-semibold text-[#0f1b33] mt-0.5">Edit Profil</h2>
                </div>
                <button type="button" data-close-modal aria-label="Tutup"
                    class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('bk.profil.update') }}">
                @csrf
                @method('PUT')

                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-800 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $namaGuru) }}" required maxlength="100"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('nama')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-800 mb-1.5">NIP</label>
                        <input type="text" value="{{ $guru->nip ?? '-' }}" disabled
                            class="w-full border border-gray-200 bg-gray-100 rounded-xl px-3 py-2.5 text-xs text-gray-400 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-800 mb-1.5">Nomor Telepon</label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon', $teleponGuru) }}" maxlength="25"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('no_telepon')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-800 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $emailGuru) }}" maxlength="100"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('email')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-gray-800 mb-1.5">Jadwal Layanan</label>
                        <input type="text" name="jadwal_layanan" value="{{ old('jadwal_layanan', $guruBk->jadwal_layanan ?? '') }}" maxlength="100"
                            placeholder="Contoh: Senin–Jumat, 08.00–14.00"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('jadwal_layanan')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer modal --}}
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" data-close-modal
                        class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1a80e5] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21" stroke-width="2"/>
                            <polyline points="7 3 7 8 15 8" stroke-width="2"/>
                        </svg>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('modal-edit-profil');

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            document.getElementById('open-edit-modal').addEventListener('click', openModal);
            modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
                el.addEventListener('click', closeModal);
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });

            // Form ubah password
            var togglePwBtn = document.getElementById('toggle-password-form');
            var pwForm = document.getElementById('password-form');
            if (togglePwBtn && pwForm) {
                togglePwBtn.addEventListener('click', function () { pwForm.classList.toggle('hidden'); });
            }

            @if($errorProfil)
                openModal();
            @endif
            @if($errorPassword)
                if (pwForm) pwForm.classList.remove('hidden');
            @endif
        });
    </script>

</x-layouts.admin>