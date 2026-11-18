@props([
    'label',
    'value',
    'suffix' => null,     // mis. "/ 60"
    'hint' => null,
    'tone' => 'ink',      // ink | warning | success | primary
    'progress' => null,   // 0–100, menampilkan bar
    'progressTone' => 'primary',
])

@php
    $warna = [
        'ink' => 'text-ink',
        'warning' => 'text-warning-strong',
        'success' => 'text-success',
        'primary' => 'text-primary-strong',
    ][$tone] ?? 'text-ink';

    $bar = [
        'primary' => 'bg-primary',
        'success' => 'bg-success-strong',
        'warning' => 'bg-warning-strong',
        'violet' => 'bg-violet',
    ][$progressTone] ?? 'bg-primary';
@endphp

<x-ui.card {{ $attributes->class('px-5 py-5') }} :padding="false">
    <p class="text-[13px] text-muted">{{ $label }}</p>
    <p class="mt-1.5 flex items-baseline gap-1.5">
        <span class="text-[30px] leading-none font-bold {{ $warna }}">{{ $value }}</span>
        @if ($suffix)
            <span class="text-base font-semibold text-muted">{{ $suffix }}</span>
        @endif
    </p>

    @if ($progress !== null)
        <div class="mt-4 h-1 overflow-hidden rounded-full bg-line" role="progressbar"
             aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $label }}">
            <div class="h-full rounded-full {{ $bar }}" style="width: {{ max(0, min(100, $progress)) }}%"></div>
        </div>
    @endif

    @if ($hint)
        <p class="mt-2 text-xs text-muted">{{ $hint }}</p>
    @endif
</x-ui.card>
