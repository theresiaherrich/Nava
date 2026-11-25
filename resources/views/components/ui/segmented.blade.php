@props([
    'options' => [],    // ['nilai' => 'Label']
    'model',            // ekspresi Alpine
    'label' => null,    // label aksesibel grup
    'size' => 'md',
])

<div role="radiogroup" @if ($label) aria-label="{{ $label }}" @endif
     {{ $attributes->class('inline-flex items-center rounded-control bg-neutral-soft p-0.5') }}>
    @foreach ($options as $nilai => $teks)
        <button type="button" role="radio" :aria-checked="{{ $model }} === @js($nilai)"
                x-on:click="{{ $model }} = @js($nilai)"
                @class([
                    'rounded-[6px] font-medium transition-colors',
                    'px-3 py-1.5 text-[13px]' => $size === 'md',
                    'px-2.5 py-1 text-xs' => $size === 'sm',
                ])
                :class="{{ $model }} === @js($nilai) ? 'bg-surface text-link shadow-card' : 'text-muted hover:text-ink'">
            {{ $teks }}
        </button>
    @endforeach
</div>
