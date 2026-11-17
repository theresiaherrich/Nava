@props([
    'options' => [],    // [nilai => teks]
    'param',            // nama query string
    'default' => null,  // nilai bawaan (tidak ditulis ke URL)
    'label' => null,    // label aksesibel grup
])

@php
    $aktif = (string) request($param, $default);
    $url = fn ($nilai) => request()->fullUrlWithQuery([$param => $nilai === $default ? null : $nilai, 'page' => null]);
@endphp

{{-- Segmented control sisi server: setiap opsi adalah tautan ber-query string. --}}
<div role="group" @if ($label) aria-label="{{ $label }}" @endif {{ $attributes->class('inline-flex items-center rounded-control bg-neutral-soft p-0.5') }}>
    @foreach ($options as $nilai => $teks)
        <a href="{{ $url((string) $nilai) }}" @if ($aktif === (string) $nilai) aria-current="true" @endif
           @class(['rounded-[6px] px-3 py-1.5 text-[13px] font-medium', 'bg-surface text-link shadow-card' => $aktif === (string) $nilai, 'text-muted hover:text-ink' => $aktif !== (string) $nilai])>{{ $teks }}</a>
    @endforeach
</div>
