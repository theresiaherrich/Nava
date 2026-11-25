@props(['tanggal'])

{{-- Chip "Hari ini · Senin, 12 Oktober 2026" di kepala dashboard. --}}
<span {{ $attributes->class('inline-flex h-9 items-center gap-2 rounded-control border border-line bg-surface px-3 text-[13px] text-muted') }}>
    <x-ui.icon name="calendar" :size="15" class="text-link" />
    Hari ini · <strong class="font-bold text-ink">{{ $tanggal }}</strong>
</span>
