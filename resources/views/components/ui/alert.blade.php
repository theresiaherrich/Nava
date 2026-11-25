@props([
    'tone' => 'info',   // info | warning | amber | success | violet
    'title' => null,
    'icon' => null,
])

@php
    $gaya = [
        'info' => ['box' => 'border-primary-soft bg-primary-faint', 'title' => 'text-info', 'text' => 'text-ink-2', 'icon' => 'text-primary-strong', 'default' => 'info'],
        'warning' => ['box' => 'border-orange-200 bg-warning-faint', 'title' => 'text-warning', 'text' => 'text-ink-2', 'icon' => 'text-warning-strong', 'default' => 'alert'],
        'amber' => ['box' => 'border-amber-200 bg-amber-50', 'title' => 'text-amber-800', 'text' => 'text-amber-900/80', 'icon' => 'text-amber-600', 'default' => 'lock'],
        'success' => ['box' => 'border-success/30 bg-success-soft/50', 'title' => 'text-success', 'text' => 'text-ink-2', 'icon' => 'text-success', 'default' => 'check-circle'],
        'violet' => ['box' => 'border-violet-200 bg-violet-soft/60', 'title' => 'text-violet-strong', 'text' => 'text-violet-strong', 'icon' => 'text-violet', 'default' => 'info'],
    ][$tone] ?? [];
@endphp

<div role="{{ $tone === 'warning' ? 'alert' : 'status' }}"
     {{ $attributes->class("flex items-start gap-3.5 rounded-card border px-5 py-4 {$gaya['box']}") }}>
    <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-surface shadow-card {{ $gaya['icon'] }}">
        <x-ui.icon :name="$icon ?? $gaya['default']" :size="16" />
    </span>
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="text-sm font-bold {{ $gaya['title'] }}">{{ $title }}</p>
        @endif
        <div class="mt-0.5 text-[13px] {{ $gaya['text'] }}">{{ $slot }}</div>
    </div>
    @isset($action)
        <div class="shrink-0 self-center">{{ $action }}</div>
    @endisset
</div>
