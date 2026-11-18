@props(['icon' => 'clipboard', 'title', 'description' => null])

<div {{ $attributes->class('flex flex-col items-center justify-center px-6 py-16 text-center') }}>
    <span class="flex size-24 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
        <x-ui.icon :name="$icon" :size="36" :stroke="1.6" />
    </span>
    <p class="mt-5 text-base font-bold text-ink">{{ $title }}</p>
    @if ($description)
        <p class="mt-2 text-sm text-muted">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
