@php
    $preview = app()->isLocal() && config('app.frontend_preview');
    // Mode demo (desain 1.01): mengisi nama pengguna contoh; saat preview langsung membuka halaman peran.
    $demo = collect([
        ['peran' => 'admin', 'label' => 'Admin', 'username' => 'wahyuni', 'route' => 'dashboard-kurikulum.index'],
        ['peran' => 'guru', 'label' => 'Guru', 'username' => 'siti.rahmawati', 'route' => 'dashboard-guru.index'],
        ['peran' => 'wali', 'label' => 'Wali Kelas', 'username' => 'siti.wali', 'route' => 'dashboard-kelas.index'],
        ['peran' => 'ortu', 'label' => 'Orang Tua', 'username' => 'ortu.citra', 'route' => 'beranda-akademik.index'],
        ['peran' => 'siswa', 'label' => 'Siswa', 'username' => 'citra.lestari', 'route' => 'beranda-akademik.index'],
    ])->map(fn ($d) => $d + ['url' => \Illuminate\Support\Facades\Route::has($d['route']) ? route($d['route']) : null])->all();
    $peranAwal = in_array(request('peran'), array_column($demo, 'peran'), true) ? request('peran') : 'admin';
    // ?state=belum-aktif menampilkan varian desain Siswa 1.02 tanpa backend.
    $galat = $errors->first('username') ?: (request('state') === 'belum-aktif' ? '__belum_aktif__' : null);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · SIAKAD SMA Negeri 1 Malang</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/login.js'])
</head>
<body class="min-h-screen bg-surface font-sans text-ink antialiased">
<div class="grid min-h-screen lg:grid-cols-[45%_1fr]">
    {{-- Panel merek (dekoratif) --}}
    <aside class="relative hidden overflow-hidden bg-[#089bd5] px-16 py-14 text-white lg:flex lg:flex-col">
        <span class="pointer-events-none absolute -top-24 right-[-120px] size-[480px] rounded-full bg-white/10" aria-hidden="true"></span>
        <span class="pointer-events-none absolute -top-40 right-[-40px] size-[520px] rounded-full border border-white/15" aria-hidden="true"></span>
        <span class="pointer-events-none absolute -bottom-40 left-[-120px] size-[480px] rounded-full border border-white/15" aria-hidden="true"></span>
        <span class="pointer-events-none absolute right-[150px] -bottom-28 size-[280px] rounded-full bg-white/10" aria-hidden="true"></span>

        <div class="relative flex items-center gap-3">
            <img src="{{ asset('images/logo-siakad.png') }}" alt="" class="h-12 w-auto">
            <div class="leading-tight"><p class="text-xl font-bold">SIAKAD</p><p class="text-[13px] text-white/85">SMA Negeri 1 Malang</p></div>
        </div>
        <div class="relative my-auto max-w-[470px]">
            <p class="text-xs font-bold tracking-wide uppercase">Sistem Akademik Terpadu</p>
            <p class="mt-5 text-[46px] leading-[1.1] font-extrabold">Nilai per TP, rapor langsung jadi.</p>
            <p class="mt-5 text-base leading-relaxed text-white/90">Satu ruang kerja untuk guru, wali kelas, dan orang tua. Lebih rapi, transparan, dan mudah dipantau.</p>
        </div>
        <div class="relative flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-control bg-white/20 text-sm font-bold" aria-hidden="true">1</span>
            <div class="leading-tight"><p class="text-[13px] font-bold">SMA Negeri 1 Kota Malang</p><p class="text-xs text-white/85">Jl. Tugu Utara No. 1, Kota Malang</p></div>
        </div>
    </aside>

    {{-- Form masuk --}}
    <main class="flex flex-col px-6 py-10 sm:px-10" x-data="loginPage(JSON.parse(document.getElementById('data-login').textContent))">
        <x-ui.json-data id="data-login" :data="['demo' => $demo, 'peranAwal' => $peranAwal, 'usernameAwal' => old('username'), 'preview' => $preview]" />

        <div class="mx-auto my-auto w-full max-w-[420px]">
            <div class="mb-8 flex items-center gap-2.5 lg:hidden">
                <img src="{{ asset('images/logo-siakad.png') }}" alt="" class="h-10 w-auto rounded-control bg-[#089bd5] p-1">
                <p class="text-lg leading-tight font-bold text-ink">SIAKAD <span class="block text-xs font-normal text-muted">SMA Negeri 1 Malang</span></p>
            </div>

            <p class="text-xs font-bold tracking-wide text-link uppercase">Selamat datang</p>
            <h1 class="mt-2 text-[30px] leading-tight font-bold text-ink">Masuk ke akun Anda</h1>
            <p class="mt-1.5 text-sm text-muted">Gunakan akun sekolah yang telah terdaftar.</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-6" x-on:submit="kirim($event)" novalidate>
                @csrf
                <label for="username" class="mb-1.5 block text-[13px] font-semibold text-ink">Nama pengguna</label>
                <div class="relative">
                    <x-ui.icon name="user" :size="16" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-muted" />
                    <input id="username" name="username" x-model="username" required autocomplete="username" autofocus
                           @if ($galat) aria-invalid="true" aria-describedby="galat-login" @endif
                           class="h-12 w-full rounded-control border border-line bg-surface pr-4 pl-11 text-sm text-ink focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
                </div>

                <label for="password" class="mt-5 mb-1.5 block text-[13px] font-semibold text-ink">Kata sandi</label>
                <div class="relative">
                    <x-ui.icon name="lock" :size="16" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-muted" />
                    <input id="password" name="password" x-model="password" :type="lihat ? 'text' : 'password'" type="password" required autocomplete="current-password"
                           class="h-12 w-full rounded-control border border-line bg-surface pr-28 pl-11 text-sm text-ink focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
                    <button type="button" x-on:click="lihat = !lihat" :aria-pressed="lihat" aria-controls="password" aria-label="Tampilkan kata sandi"
                            class="absolute top-1/2 right-3 -translate-y-1/2 rounded-md px-1.5 py-1 text-xs font-semibold text-link hover:bg-primary-faint"
                            x-text="lihat ? 'Sembunyikan' : 'Lihat'">Lihat</button>
                </div>
                <div class="mt-2.5 text-right"><a href="{{ route('password.request') }}" class="text-xs font-semibold text-link hover:underline">Lupa kata sandi?</a></div>

                @if ($galat)
                    <p id="galat-login" role="alert" class="mt-4 flex items-center gap-2 rounded-control border border-danger/30 bg-danger-soft px-4 py-3 text-[13px] text-danger">
                        <x-ui.icon name="alert-circle" :size="15" />
                        @if ($galat === '__belum_aktif__')
                            <span><strong>Akun Anda belum aktif.</strong> Hubungi operator sekolah.</span>
                        @else
                            <span>{{ $galat }}</span>
                        @endif
                    </p>
                @endif

                <button type="submit" x-ref="masuk" :disabled="memuat"
                        class="mt-5 flex h-12 w-full items-center justify-center gap-2 rounded-control bg-primary text-[15px] font-semibold text-white hover:bg-primary-strong focus-visible:ring-2 focus-visible:ring-primary-soft focus-visible:ring-offset-2 focus-visible:outline-none disabled:opacity-70">
                    <span x-text="memuat ? 'Memproses…' : 'Masuk'">Masuk</span><x-ui.icon name="arrow-right" :size="16" x-show="!memuat" />
                </button>
            </form>

            <div class="mt-6 border-t border-line-soft pt-5">
                <p class="text-[11px] font-semibold tracking-wide text-muted uppercase" id="label-demo">Mode demo</p>
                <div class="mt-2.5 flex flex-wrap gap-2" role="radiogroup" aria-labelledby="label-demo">
                    <template x-for="d in demo" :key="d.peran">
                        <button type="button" role="radio" :aria-checked="peran === d.peran" x-on:click="pilih(d)" x-text="d.label"
                                class="rounded-full border px-3.5 py-1.5 text-[13px] font-semibold"
                                :class="peran === d.peran ? 'border-primary/40 bg-primary-faint text-link' : 'border-line text-ink hover:bg-surface-2'"></button>
                    </template>
                </div>
                <p class="mt-2.5 text-xs text-muted">Pilih peran untuk melihat alur dan tampilan yang berbeda.</p>
            </div>
        </div>

        <footer class="mt-10 text-center text-xs text-muted">
            © 2026 SMA Negeri 1 Kota Malang ·
            <button type="button" x-on:click="bantuan = !bantuan" :aria-expanded="bantuan" aria-controls="info-bantuan" class="font-semibold text-link hover:underline">Pusat Bantuan</button>
            <p id="info-bantuan" x-show="bantuan" x-cloak class="mt-2">Kendala akun? Hubungi operator sekolah di ruang Tata Usaha.</p>
        </footer>
    </main>
</div>
</body>
</html>
