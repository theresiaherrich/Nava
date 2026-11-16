<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview Frontend · SIAKAD A2</title>
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
<main class="mx-auto max-w-5xl px-4 py-10">
    <p class="text-xs font-semibold tracking-wide text-link uppercase">SIAKAD A2 · Mode preview (local)</p>
    <h1 class="mt-1 text-3xl font-bold">Daftar halaman frontend</h1>
    <p class="mt-2 text-sm text-muted">
        Halaman memakai data dummy dan dapat dibuka tanpa login selama <code>APP_ENV=local</code> dan <code>FRONTEND_PREVIEW=true</code>.
        Baris ↳ membuka variasi state atau modal sesuai nomor desain.
        <a class="font-semibold text-link hover:underline" href="{{ route('login') }}">Halaman login</a>
    </p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach ($peran as $grup)
            <section class="rounded-card border border-line bg-surface p-5">
                <h2 class="text-base font-semibold">{{ $grup['label'] }}</h2>
                <ul class="mt-3 space-y-1.5 text-sm">
                    @foreach ($grup['items'] as $item)
                        @if (Route::has($item['route']))
                            <li class="flex items-center justify-between gap-3">
                                <a class="font-medium text-link hover:underline" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                                <code class="text-xs text-subtle">{{ parse_url(route($item['route']), PHP_URL_PATH) }}</code>
                            </li>
                            @foreach ($varian[$item['route']] ?? [] as $teks => $param)
                                @php
                                    $rute = $param['_route'] ?? $item['route'];
                                    unset($param['_route']);
                                    $url = route($rute, $param);
                                @endphp
                                <li class="flex items-center justify-between gap-3 pl-4">
                                    <a class="text-[13px] text-link hover:underline" href="{{ $url }}">↳ {{ $teks }}</a>
                                    <code class="truncate text-xs text-subtle">{{ str_replace(url('/'), '', $url) }}</code>
                                </li>
                            @endforeach
                        @else
                            <li class="flex items-center justify-between gap-3">
                                <span class="text-subtle">{{ $item['label'] }}</span>
                                <span class="rounded-full bg-neutral-soft px-2 py-0.5 text-xs text-muted">belum dibuat</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</main>
</body>
</html>
