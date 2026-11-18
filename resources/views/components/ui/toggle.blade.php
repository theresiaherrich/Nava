@props(['model', 'label', 'hint' => null])

{{-- Saklar on/off (role="switch") terikat ke ekspresi Alpine `model`. --}}
<label {{ $attributes->class('flex cursor-pointer items-center gap-3') }}>
    <button type="button" role="switch" :aria-checked="!!{{ $model }}" x-on:click="{{ $model }} = !{{ $model }}"
            class="relative h-5 w-9 shrink-0 rounded-full transition-colors" :class="{{ $model }} ? 'bg-primary' : 'bg-line'">
        <span class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white shadow transition-transform" :class="{{ $model }} && 'translate-x-4'"></span>
        <span class="sr-only">{{ $label }}</span>
    </button>
    <span>
        <span class="block text-[13px] font-semibold text-ink">{{ $label }}</span>
        @if ($hint)<span class="block text-xs text-muted">{{ $hint }}</span>@endif
    </span>
</label>
