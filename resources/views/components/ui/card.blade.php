@props(['padding' => true])

<section {{ $attributes->class([
    'rounded-card border border-line bg-surface shadow-card',
    'p-5' => $padding,
]) }}>
    {{ $slot }}
</section>
