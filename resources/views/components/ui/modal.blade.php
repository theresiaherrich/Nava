@props([
    'name',                 // dibuka dengan $dispatch('open-modal', 'nama') atau ?modal=nama
    'title' => null,
    'subtitle' => null,
    'width' => 'max-w-[480px]',
])

{{--
    Modal dengan fokus terkunci (Alpine focus), tutup dengan Esc / klik latar / tombol X.
    Tutup dari dalam: $dispatch('close-modal', 'nama').
--}}
<div x-data="{ open: @js(request('modal') === $name) }"
     x-on:open-modal.window="if ($event.detail === @js($name)) open = true"
     x-on:close-modal.window="if ($event.detail === @js($name)) open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog" aria-modal="true" @if ($title) aria-labelledby="modal-{{ $name }}-title" @endif>
    <div class="absolute inset-0 bg-slate-900/45" x-on:click="open = false" x-show="open" x-transition.opacity></div>

    <div x-show="open" x-transition x-trap.noscroll="open"
         {{ $attributes->class("relative w-full $width max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-surface p-6 shadow-pop") }}>
        <button type="button" x-on:click="open = false" aria-label="Tutup"
                class="absolute top-5 right-5 inline-flex size-8 items-center justify-center rounded-control text-muted hover:bg-neutral-soft">
            <x-ui.icon name="x" :size="18" />
        </button>

        @isset($icon)
            <div class="mb-4">{{ $icon }}</div>
        @endisset

        @if ($title)
            <h2 id="modal-{{ $name }}-title" class="pr-10 text-lg font-bold text-ink">{{ $title }}</h2>
        @endif
        @if ($subtitle)
            <p class="mt-1 text-[13px] text-muted">{{ $subtitle }}</p>
        @endif

        <div @class(['mt-4' => $title || $subtitle])>{{ $slot }}</div>

        @isset($footer)
            <div class="mt-5 flex items-center justify-end gap-2.5 border-t border-line-soft pt-4">{{ $footer }}</div>
        @endisset
    </div>
</div>
