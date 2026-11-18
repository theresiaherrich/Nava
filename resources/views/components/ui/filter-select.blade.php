@props([
    'label',            // awalan, mis. "Semester"
    'options' => [],    // list<string>
    'model',            // ekspresi Alpine yang menyimpan nilai terpilih
])

{{-- Dropdown filter berlabel ("Semester: Ganjil 2026/2027"), dikendalikan Alpine. --}}
<div x-data="{ buka: false }" x-on:keydown.escape="buka = false" x-on:click.outside="buka = false"
     {{ $attributes->class('relative') }}>
    <button type="button" x-on:click="buka = !buka" :aria-expanded="buka" aria-haspopup="listbox"
            class="flex h-9 items-center gap-2 rounded-control border border-line bg-surface pr-3 pl-3 text-[13px] text-muted hover:border-subtle">
        <span>{{ $label }}:</span>
        <span class="font-medium text-ink" x-text="{{ $model }}"></span>
        <x-ui.icon name="chevron-down" :size="14" class="ml-1 text-ink-2" />
    </button>
    <ul x-show="buka" x-cloak x-transition.opacity role="listbox" aria-label="{{ $label }}"
        class="absolute top-full left-0 z-30 mt-1 min-w-full rounded-control border border-line bg-surface py-1 shadow-pop">
        @foreach ($options as $opsi)
            <li>
                <button type="button" role="option" :aria-selected="{{ $model }} === @js($opsi)"
                        x-on:click="{{ $model }} = @js($opsi); buka = false"
                        class="flex w-full items-center justify-between gap-4 px-3 py-2 text-left text-[13px] whitespace-nowrap text-ink hover:bg-surface-2"
                        :class="{{ $model }} === @js($opsi) && 'font-semibold text-link'">
                    {{ $opsi }}
                    <x-ui.icon name="check" :size="14" x-show="{{ $model }} === {{ \Illuminate\Support\Js::from($opsi) }}" />
                </button>
            </li>
        @endforeach
    </ul>
</div>
