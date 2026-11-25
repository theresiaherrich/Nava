<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · SIAKAD SMA Negeri 1 Malang</title>
    @fonts
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/siakad-api.js'])
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
    <main class="flex flex-col px-6 py-10 sm:px-10" x-data="{ lihat: false, bantuan: false }">

        <div class="mx-auto my-auto w-full max-w-[420px]">
            <div class="mb-8 flex items-center gap-2.5 lg:hidden">
                <img src="{{ asset('images/logo-siakad.png') }}" alt="" class="h-10 w-auto rounded-control bg-[#089bd5] p-1">
                <p class="text-lg leading-tight font-bold text-ink">SIAKAD <span class="block text-xs font-normal text-muted">SMA Negeri 1 Malang</span></p>
            </div>

            <p class="text-xs font-bold tracking-wide text-link uppercase">Selamat datang</p>
            <h1 class="mt-2 text-[30px] leading-tight font-bold text-ink">Masuk ke akun Anda</h1>
            <p class="mt-1.5 text-sm text-muted">Gunakan akun sekolah yang telah terdaftar.</p>

            <form method="POST" action="/api/auth/login" class="mt-6" data-auth="login">
                @csrf
                <label for="username" class="mb-1.5 block text-[13px] font-semibold text-ink">Nama pengguna</label>
                <div class="relative">
                    <x-ui.icon name="user" :size="16" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-muted" />
                    <input id="username" name="username" required autocomplete="username" autofocus
                           class="h-12 w-full rounded-control border border-line bg-surface pr-4 pl-11 text-sm text-ink focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
                </div>

                <label for="password" class="mt-5 mb-1.5 block text-[13px] font-semibold text-ink">Kata sandi</label>
                <div class="relative">
                    <x-ui.icon name="lock" :size="16" class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-muted" />
                    <input id="password" name="password" :type="lihat ? 'text' : 'password'" type="password" required autocomplete="current-password"
                           class="h-12 w-full rounded-control border border-line bg-surface pr-28 pl-11 text-sm text-ink focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
                    <button type="button" x-on:click="lihat = !lihat" :aria-pressed="lihat" aria-controls="password" aria-label="Tampilkan kata sandi"
                            class="absolute top-1/2 right-3 -translate-y-1/2 rounded-md px-1.5 py-1 text-xs font-semibold text-link hover:bg-primary-faint"
                            x-text="lihat ? 'Sembunyikan' : 'Lihat'">Lihat</button>
                </div>
                {{-- Lupa password (modul A1) --}}
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="mt-2 inline-block text-[13px] font-semibold text-link hover:underline">Lupa password?</a>
                @endif
                <p data-error role="alert" class="mt-4 text-sm text-danger"></p>

                <button type="submit"
                        class="mt-5 flex h-12 w-full items-center justify-center gap-2 rounded-control bg-primary text-[15px] font-semibold text-white hover:bg-primary-strong focus-visible:ring-2 focus-visible:ring-primary-soft focus-visible:ring-offset-2 focus-visible:outline-none disabled:opacity-70">
                    <span>Masuk</span><x-ui.icon name="arrow-right" :size="16" />
                </button>
            </form>

            {{-- Pendaftaran akun orang tua (modul A1/A4) --}}
            @if (Route::has('register'))
                <p class="mt-5 text-center text-sm text-muted">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-link hover:underline">Register Sekarang</a></p>
            @endif

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
