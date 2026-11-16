@props([
    'variant' => 'primary',   // primary | secondary | warning | danger | ghost | soft
    'size' => 'md',           // sm | md | lg
    'icon' => null,
    'iconRight' => null,
    'href' => null,
    'type' => 'button',
])

@php
    $varian = [
        'primary' => 'bg-primary text-white hover:bg-primary-strong disabled:bg-line disabled:text-subtle',
        'secondary' => 'border border-line bg-surface text-ink hover:bg-surface-2 disabled:text-subtle',
        'warning' => 'border border-warning-strong/40 bg-surface text-warning hover:bg-warning-faint',
        'danger' => 'bg-danger text-white hover:bg-red-700',
        'ghost' => 'text-link hover:bg-primary-faint',
        'soft' => 'bg-neutral-soft text-subtle',
    ][$variant] ?? '';

    $ukuran = [
        'sm' => 'h-8 px-3 text-[13px]',
        'md' => 'h-9 px-4 text-sm',
        'lg' => 'h-10 px-4 text-[15px]',
    ][$size] ?? 'h-9 px-4 text-sm';

    $kelas = "inline-flex items-center justify-center gap-2 rounded-control font-semibold whitespace-nowrap transition-colors disabled:cursor-not-allowed $varian $ukuran";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($kelas) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="16" />@endif
        {{ $slot }}
        @if ($iconRight)<x-ui.icon :name="$iconRight" :size="14" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($kelas) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="16" />@endif
        {{ $slot }}
        @if ($iconRight)<x-ui.icon :name="$iconRight" :size="14" />@endif
    </button>
@endif
