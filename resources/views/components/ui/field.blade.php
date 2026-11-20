@props([
    'label',
    'for' => null,
    'required' => false,
    'error' => null,   // ekspresi Alpine yang berisi pesan error (string kosong = tidak ada)
    'hint' => null,
])

<div {{ $attributes->class('flex flex-col') }}>
    <label @if ($for) for="{{ $for }}" @endif class="mb-1.5 text-[13px] font-semibold text-ink">
        {{ $label }}@if ($required)<span class="text-danger"> *</span>@endif
    </label>
    {{ $slot }}
    @if ($error)
        <p x-show="{{ $error }}" x-cloak class="mt-1.5 flex items-center gap-1 text-xs font-medium text-danger" role="alert">
            <x-ui.icon name="alert-circle" :size="13" />
            <span x-text="{{ $error }}"></span>
        </p>
    @endif
    @if ($hint)
        <p class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif
</div>
