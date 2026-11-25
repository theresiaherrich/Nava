@props(['placeholder' => 'Cari…', 'label' => null])

<label {{ $attributes->only('class')->class('relative flex h-9 items-center') }}>
    <span class="sr-only">{{ $label ?? $placeholder }}</span>
    <x-ui.icon name="search" :size="15" class="pointer-events-none absolute left-3 text-subtle" />
    <input type="search" placeholder="{{ $placeholder }}" autocomplete="off"
           {{ $attributes->except('class') }}
           class="h-full w-full rounded-control border border-line bg-surface pr-3 pl-9 text-[13px] text-ink placeholder:text-subtle focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
</label>
