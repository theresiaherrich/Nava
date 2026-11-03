@props(['src' => null, 'initials' => '?'])

@php $ada = $src && file_exists(public_path($src)); @endphp

<span {{ $attributes->merge(['class' => 'pt-avatar']) }}>
    @if ($ada)
        <img src="{{ asset($src) }}" alt="">
    @else
        {{ $initials }}
    @endif
</span>
