@props(['name', 'size' => 'md', 'tone' => null])

@php
    $inisial = collect(preg_split('/\s+/', trim(preg_replace('/,.*$|\b(Dra?|Drs|Ir|Hj?)\.\s*/u', '', $name))))
        ->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');

    $palet = ['bg-blue-600', 'bg-violet-600', 'bg-teal-600', 'bg-sky-600', 'bg-indigo-600', 'bg-emerald-600'];
    $warna = $tone ?? $palet[crc32($name) % count($palet)];

    $ukuran = ['xs' => 'size-5 text-[8px]', 'sm' => 'size-7 text-[10px]', 'md' => 'size-8 text-xs', 'lg' => 'size-10 text-sm'][$size];
@endphp

<span {{ $attributes->class("inline-flex shrink-0 items-center justify-center rounded-full font-bold text-white $warna $ukuran") }}
      aria-hidden="true">{{ $inisial }}</span>
