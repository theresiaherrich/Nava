@props([
    'paginator',          // LengthAwarePaginator
    'satuan' => 'data',   // "akun", "siswa", ...
])

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    $n = $paginator->lastPage();
    $c = $paginator->currentPage();
    $halaman = $n <= 5
        ? range(1, $n)
        : collect([1, 2, 3, $c, $n])->filter(fn ($x) => $x >= 1 && $x <= $n)->unique()->sort()->values()->all();
    $dariKe = $paginator->total()
        ? $paginator->firstItem().'–'.$paginator->lastItem()
        : '0';
    $btn = 'inline-flex size-7 items-center justify-center rounded-control border text-xs font-medium';
@endphp

{{-- Pagination sisi server: "1–10 dari 2.010 akun" + ‹ 1 2 3 … n ›, mempertahankan query filter. --}}
<div {{ $attributes->class('flex flex-wrap items-center gap-3') }}>
    <span class="text-xs text-muted">{{ $dariKe }} dari {{ \App\Support\Format::angka($paginator->total()) }} {{ $satuan }}</span>
    <nav aria-label="Navigasi halaman" class="flex items-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="{{ $btn }} border-line bg-surface text-ink-2 opacity-40" aria-hidden="true"><x-ui.icon name="chevron-left" :size="13" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="{{ $btn }} border-line bg-surface text-ink-2 hover:bg-surface-2" aria-label="Halaman sebelumnya"><x-ui.icon name="chevron-left" :size="13" /></a>
        @endif

        @foreach ($halaman as $i => $p)
            @if ($i > 0 && $p - $halaman[$i - 1] > 1)
                <span class="{{ $btn }} border-line bg-surface text-muted">…</span>
            @endif
            @if ($p === $c)
                <span class="{{ $btn }} border-primary bg-primary text-white" aria-current="page">{{ $p }}</span>
            @else
                <a href="{{ $paginator->url($p) }}" class="{{ $btn }} border-line bg-surface text-ink-2 hover:bg-surface-2">{{ $p }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="{{ $btn }} border-line bg-surface text-ink-2 hover:bg-surface-2" aria-label="Halaman berikutnya"><x-ui.icon name="chevron-right" :size="13" /></a>
        @else
            <span class="{{ $btn }} border-line bg-surface text-ink-2 opacity-40" aria-hidden="true"><x-ui.icon name="chevron-right" :size="13" /></span>
        @endif
    </nav>
</div>
