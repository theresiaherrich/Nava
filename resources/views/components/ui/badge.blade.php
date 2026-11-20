@props([
    'tone' => 'neutral',   // success | warning | danger | info | violet | neutral | outline
    'icon' => null,        // nama ikon x-ui.icon, mis. lock / check
])

@php
    $kelas = [
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-info',
        'violet' => 'bg-violet-soft text-violet',
        'neutral' => 'bg-neutral-soft text-neutral',
        'outline' => 'bg-surface text-ink-2 ring-1 ring-inset ring-line',
    ][$tone] ?? 'bg-neutral-soft text-neutral';
@endphp

<span {{ $attributes->class("inline-flex items-center gap-1 whitespace-nowrap rounded-md px-2 py-0.5 text-xs font-semibold $kelas") }}>
    @if ($icon)
        <x-ui.icon :name="$icon" :size="12" :stroke="2.2" />
    @endif
    {{ $slot }}
</span>
