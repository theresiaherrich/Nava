@props([
    'title',
    'subtitle' => null,
    'eyebrow' => 'SMA Negeri 1 Kota Malang',
    'role' => null,
    'back' => null,       // ['href' => ..., 'label' => ...]
])

{{-- Kepala halaman: tautan kembali (opsional), eyebrow, judul, subjudul, dan slot aksi di kanan. --}}
<header {{ $attributes->merge(['class' => 'mb-5']) }}>
    @if ($back)
        <a href="{{ $back['href'] }}" class="mb-4 inline-flex items-center gap-1 text-[13px] font-semibold text-link hover:underline">
            <x-ui.icon name="chevron-left" :size="14" />
            {{ $back['label'] }}
        </a>
    @endif

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-[0.02em] text-link">
                {{ $eyebrow }}@if ($role) · {{ $role }}@endif
            </p>
            <h1 class="mt-1.5 text-[30px] leading-tight font-bold tracking-[-0.01em] text-ink">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-muted">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2.5">{{ $actions }}</div>
        @endisset
    </div>
</header>
