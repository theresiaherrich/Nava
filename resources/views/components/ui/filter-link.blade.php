@props([
    'label',              // "Peran"
    'param',              // nama query string, mis. "peran"
    'options' => [],      // [nilai => teks]; nilai '' = Semua
])

@php
    $aktif = (string) request($param, '');
    $teksAktif = $options[$aktif] ?? reset($options);
    // Ganti filter → kembali ke halaman 1, pertahankan filter lain.
    $url = fn ($nilai) => request()->fullUrlWithQuery([$param => $nilai === '' ? null : $nilai, 'page' => null]);
@endphp

{{-- Dropdown filter sisi server: setiap opsi adalah tautan ber-query string. --}}
<div x-data="{ buka: false }" x-on:keydown.escape="buka = false" x-on:click.outside="buka = false"
     {{ $attributes->class('relative') }}>
    <button type="button" x-on:click="buka = !buka" :aria-expanded="buka" aria-haspopup="true"
            class="flex h-9 items-center gap-1 rounded-control border border-line bg-surface px-3 text-[13px] text-muted hover:border-subtle">
        {{ $label }}: <span class="font-medium text-ink">{{ $teksAktif }}</span>
        <x-ui.icon name="chevron-down" :size="14" class="ml-1.5 text-ink-2" />
    </button>
    <ul x-show="buka" x-cloak x-transition.opacity aria-label="{{ $label }}"
        class="absolute top-full left-0 z-30 mt-1 max-h-72 min-w-full overflow-y-auto rounded-control border border-line bg-surface py-1 shadow-pop">
        @foreach ($options as $nilai => $teks)
            <li>
                <a href="{{ $url((string) $nilai) }}" @if ((string) $nilai === $aktif) aria-current="true" @endif
                   @class([
                       'flex items-center justify-between gap-4 px-3 py-2 text-[13px] whitespace-nowrap hover:bg-surface-2',
                       'font-semibold text-link' => (string) $nilai === $aktif,
                       'text-ink' => (string) $nilai !== $aktif,
                   ])>
                    {{ $teks }}
                    @if ((string) $nilai === $aktif)<x-ui.icon name="check" :size="14" />@endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
