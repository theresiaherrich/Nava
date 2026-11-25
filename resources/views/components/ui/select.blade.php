@props(['options' => [], 'placeholder' => null])

{{-- Select bawaan dengan gaya seragam. `options`: [nilai => teks] atau list teks. --}}
<div class="relative">
    <select {{ $attributes->class('h-10 w-full appearance-none rounded-control border border-line bg-surface pr-9 pl-3 text-sm text-ink focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none') }}>
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $nilai => $teks)
            <option value="{{ array_is_list($options) ? $teks : $nilai }}">{{ $teks }}</option>
        @endforeach
        {{ $slot }}
    </select>
    <x-ui.icon name="chevron-down" :size="14" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-ink-2" />
</div>
