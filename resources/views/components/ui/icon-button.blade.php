@props([
    'icon',
    'label',                // wajib: dipakai untuk aria-label
    'tone' => 'default',    // default | danger
])

<button type="button" aria-label="{{ $label }}" title="{{ $label }}"
    {{ $attributes->class([
        'inline-flex size-8 items-center justify-center rounded-control border bg-surface transition-colors',
        'border-line text-ink-2 hover:bg-surface-2' => $tone === 'default',
        'border-danger-soft text-danger hover:bg-danger-soft' => $tone === 'danger',
    ]) }}>
    <x-ui.icon :name="$icon" :size="14" :stroke="2" />
</button>
