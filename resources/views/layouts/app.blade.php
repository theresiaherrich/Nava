<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'SIAKAD SMAN 1')</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/siakad-api.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-surface-2 font-sans text-ink">
@php
    $account = auth()->user();
    $roles = config('siakad.roles');
    $activeRole = \App\Support\AcademicAccess::peranAktif();
    $menus = $account->must_change_password ? [] : \App\Support\WebNavigation::links();
    $semester = \App\Models\Semester::aktif()?->loadMissing('tahunAjaran');
@endphp
<div class="min-h-screen lg:flex">
    <aside class="bg-[#089bd5] px-4 py-5 text-white lg:w-60 lg:shrink-0">
        <a href="/dashboard" class="flex items-center gap-3">
            <img src="{{ asset('images/logo-siakad.png') }}" alt="" class="size-10 object-contain">
            <span class="font-bold">SIAKAD <small class="block font-normal">SMA Negeri 1 Malang</small></span>
        </a>
        <nav aria-label="Menu akademik" class="mt-6 flex flex-wrap gap-1 lg:flex-col">
            @foreach ($menus as $url => $label)
                <a href="{{ $url }}" @class(['rounded-control px-3 py-2 text-sm font-semibold hover:bg-white/20', 'bg-white/25' => request()->is(ltrim($url, '/').'*')])>{{ $label }}</a>
            @endforeach
        </nav>
    </aside>
    <main class="min-w-0 flex-1">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-line bg-surface px-7 py-4 text-sm">
            <div><strong>{{ $account->nama }}</strong><span class="ml-2 text-muted">{{ $roles[$activeRole] ?? '' }}</span>
                @if ($semester)
                    <span class="ml-2 text-muted">{{ $semester->tahunAjaran->nama }} · {{ $semester->nama }}</span>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-4">
                @unless ($account->must_change_password)
                    @if (count($account->kodeRoles()) > 1)
                        <form method="POST" action="/api/auth/switch-role" data-auth="switch-role">
                            @csrf
                            <label for="peran" class="sr-only">Peran aktif</label>
                            <select id="peran" name="peran" class="rounded-control border border-line px-2 py-1">
                                @foreach ($account->kodeRoles() as $role)
                                    <option value="{{ $role }}" @selected($role === $activeRole)>{{ $roles[$role] ?? $role }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="ml-1 font-semibold text-link">Ganti peran</button>
                            <span data-error role="alert" class="block text-danger"></span>
                        </form>
                    @endif
                    <a href="{{ route('password.change') }}" class="font-semibold text-link">Ganti sandi</a>
                @endunless
                <form method="POST" action="/api/auth/logout" data-auth="logout" data-next="/login">
                    @csrf
                    <button type="submit" class="font-semibold text-link">Keluar</button>
                    <span data-error role="alert" class="block text-danger"></span>
                </form>
            </div>
        </header>
        @yield('content')
        <x-ui.toast-stack />
        <x-ui.confirm-dialog />
    </main>
</div>
</body>
</html>
