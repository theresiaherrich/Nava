<x-layouts.admin title="Profil - SIAKAD SMA Negeri 1 Malang">
    @php
        $initials = collect(explode(' ', trim($profile['name'])))
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
        $roleLabel = match ($role) {
            'ADMIN' => 'Administrator',
            'GURU', 'GURU_BK' => 'Guru Bimbingan dan Konseling',
            default => 'Siswa',
        };
        $passwordUpdatedAt = $user->password_changed_at ?? $logPassword?->created_at;
    @endphp

    <div class="mb-6">
        <x-page-label fitur="Profil" />
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Profil Saya</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola identitas dan keamanan akun Anda.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-700" role="status">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-700" role="alert">
            <p class="font-bold">Periksa kembali data yang dimasukkan.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="space-y-4">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center gap-3">
                <div class="w-20 h-20 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-2xl font-black">
                    {{ $initials !== '' ? $initials : 'SI' }}
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">{{ $profile['name'] }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $profile['job_title'] }}</p>
                </div>
                <span class="px-3 py-1 bg-emerald-100/80 text-emerald-800 text-[10px] font-bold rounded-full">
                    {{ $user->aktif ? 'Akun Aktif' : 'Akun Tidak Aktif' }}
                </span>
                @if ($profile['identifier'])
                    <div class="w-full border-t border-gray-100 pt-3">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider mb-0.5">{{ $profile['identifier_label'] }}</p>
                        <p class="text-xs font-bold text-slate-800">{{ $profile['identifier'] }}</p>
                    </div>
                @endif
            </div>

            <div id="keamanan-akun" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-xs font-bold text-slate-900 mb-3">Keamanan Akun</h2>
                <div class="space-y-2.5">
                    <div class="flex justify-between gap-3">
                        <span class="text-[11px] text-gray-500">Kata sandi</span>
                        <span class="text-xs font-semibold text-gray-800">••••••••</span>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-[11px] text-gray-500">Terakhir diubah</span>
                        <span class="text-xs text-gray-600">
                            {{ $passwordUpdatedAt ? \Illuminate\Support\Carbon::parse($passwordUpdatedAt)->format('d M Y') : 'Belum pernah' }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route($profileRoute.'.password') }}" class="mt-4 border-t border-gray-100 pt-4 space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="password_lama" class="block text-[11px] font-bold text-gray-700 mb-1">Kata sandi lama</label>
                        <input id="password_lama" type="password" name="password_lama" autocomplete="current-password" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('password_lama')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_baru" class="block text-[11px] font-bold text-gray-700 mb-1">Kata sandi baru</label>
                        <input id="password_baru" type="password" name="password_baru" autocomplete="new-password" minlength="8" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('password_baru')
                            <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_baru_confirmation" class="block text-[11px] font-bold text-gray-700 mb-1">Konfirmasi kata sandi baru</label>
                        <input id="password_baru_confirmation" type="password" name="password_baru_confirmation" autocomplete="new-password" minlength="8" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                        Simpan Kata Sandi
                    </button>
                </form>
            </div>
        </section>

        <section id="informasi-pribadi" class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Informasi Pribadi</h2>
                    <p class="text-xs text-gray-400">Data profil {{ $roleLabel }} yang sedang login.</p>
                </div>
                <button type="button" id="open-profile-form"
                    class="px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                    Edit Profil
                </button>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @if ($role === 'ADMIN')
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Nama Admin / Nama pengguna</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['name'] }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Jabatan</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">Administrator</dd>
                    </div>
                @elseif (in_array($role, ['GURU', 'GURU_BK'], true))
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Nama Guru</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['name'] }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">NIP</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['identifier'] }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Jabatan</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['job_title'] }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Email</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['email'] ?: '-' }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Nomor Telepon</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['phone'] ?: '-' }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Alamat</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['address'] ?: '-' }}</dd>
                    </div>
                @else
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Nama Siswa</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['name'] }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">NIS</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['identifier'] }}</dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Kelas dan Jenjang</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">
                            {{ $profile['class_name'] ?: 'Belum ditetapkan' }}{{ $profile['grade'] ? ' · Kelas '.$profile['grade'] : '' }}
                        </dd>
                    </div>
                    <div class="p-3.5 bg-gray-50/60 rounded-xl">
                        <dt class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Alamat</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-1">{{ $profile['address'] ?: '-' }}</dd>
                    </div>
                @endif
            </dl>

            <div id="profile-form" class="hidden mt-6 border-t border-gray-100 pt-5">
                <h3 class="text-sm font-bold text-slate-900 mb-4">Edit Profil</h3>
                <form method="POST" action="{{ route($profileRoute.'.update') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')
                    @if ($role === 'ADMIN')
                        <div class="sm:col-span-2">
                            <label for="username" class="block text-[11px] font-bold text-gray-700 mb-1">Nama pengguna / Nama Admin</label>
                            <input id="username" type="text" name="username" value="{{ old('username', $profile['name']) }}" maxlength="50" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('username')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div class="sm:col-span-2">
                            <label for="nama" class="block text-[11px] font-bold text-gray-700 mb-1">Nama Lengkap</label>
                            <input id="nama" type="text" name="nama" value="{{ old('nama', $profile['name']) }}" maxlength="120" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('nama')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @if (in_array($role, ['GURU', 'GURU_BK'], true))
                            <div>
                                <label for="email" class="block text-[11px] font-bold text-gray-700 mb-1">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $profile['email']) }}" maxlength="100"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('email')
                                    <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="no_telepon" class="block text-[11px] font-bold text-gray-700 mb-1">Nomor Telepon</label>
                                <input id="no_telepon" type="text" name="no_telepon" value="{{ old('no_telepon', $profile['phone']) }}" maxlength="25"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('no_telepon')
                                    <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <label for="alamat" class="block text-[11px] font-bold text-gray-700 mb-1">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="3"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">{{ old('alamat', $profile['address']) }}</textarea>
                            @error('alamat')
                                <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <div class="sm:col-span-2 flex justify-end gap-3">
                        <button id="cancel-profile-form" type="button"
                            class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-[#1a80e5] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var profileForm = document.getElementById('profile-form');
            var openButton = document.getElementById('open-profile-form');
            var cancelButton = document.getElementById('cancel-profile-form');

            if (!profileForm || !openButton || !cancelButton) {
                return;
            }

            openButton.addEventListener('click', function () {
                profileForm.classList.remove('hidden');
                profileForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
            cancelButton.addEventListener('click', function () {
                profileForm.classList.add('hidden');
            });
        });
    </script>
</x-layouts.admin>
