@props([
    'page',     // ekspresi Alpine: halaman aktif (1-based)
    'pages',    // ekspresi Alpine: jumlah halaman
])

{{-- Pagination sisi klien: ‹ 1 2 3 … n ›. --}}
<nav aria-label="Navigasi halaman" {{ $attributes->class('flex items-center gap-1.5') }}>
    <button type="button" x-on:click="{{ $page }} = Math.max(1, {{ $page }} - 1)" :disabled="{{ $page }} <= 1"
            aria-label="Halaman sebelumnya"
            class="inline-flex size-7 items-center justify-center rounded-control border border-line bg-surface text-ink-2 disabled:opacity-40">
        <x-ui.icon name="chevron-left" :size="13" />
    </button>
    <template x-for="p in (() => { const n = {{ $pages }}, c = {{ $page }}; if (n <= 5) return Array.from({ length: n }, (_, i) => i + 1); const s = new Set([1, 2, 3, c, n]); return [...s].filter(x => x >= 1 && x <= n).sort((a, b) => a - b).flatMap((x, i, a) => i && x - a[i - 1] > 1 ? ['…', x] : [x]); })()" :key="String(p)">
        <span>
            <span x-show="p === '…'" class="inline-flex size-7 items-center justify-center rounded-control border border-line bg-surface text-xs text-muted">…</span>
            <button type="button" x-show="p !== '…'" x-on:click="{{ $page }} = p" x-text="p"
                    :aria-current="p === {{ $page }} ? 'page' : null"
                    class="inline-flex size-7 items-center justify-center rounded-control border text-xs font-medium"
                    :class="p === {{ $page }} ? 'border-primary bg-primary text-white' : 'border-line bg-surface text-ink-2 hover:bg-surface-2'"></button>
        </span>
    </template>
    <button type="button" x-on:click="{{ $page }} = Math.min({{ $pages }}, {{ $page }} + 1)" :disabled="{{ $page }} >= {{ $pages }}"
            aria-label="Halaman berikutnya"
            class="inline-flex size-7 items-center justify-center rounded-control border border-line bg-surface text-ink-2 disabled:opacity-40">
        <x-ui.icon name="chevron-right" :size="13" />
    </button>
</nav>
