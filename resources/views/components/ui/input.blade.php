@props(['error' => null])

{{-- Input teks standar; `error` = ekspresi Alpine (truthy → bingkai merah). --}}
<input {{ $attributes->merge(['type' => 'text'])->class([
    'h-10 w-full rounded-control border bg-surface px-3 text-sm text-ink placeholder:text-subtle focus:ring-2 focus:outline-none disabled:bg-surface-2 disabled:text-muted',
    'border-line focus:border-primary focus:ring-primary-soft' => ! $error,
]) }}
@if ($error) :class="{{ $error }} ? 'border-danger focus:ring-danger-soft' : 'border-line focus:border-primary focus:ring-primary-soft'" @endif>
