{{--
    Dialog konfirmasi global (satu untuk seluruh aplikasi). Dipakai dari Alpine:
        if (await $store.confirm.ask({ title, message, confirmLabel, tone: 'danger' })) { ... }
--}}
<div x-data x-show="$store.confirm.open" x-cloak
     x-on:keydown.escape.window="$store.confirm.open && $store.confirm.jawab(false)"
     class="fixed inset-0 z-[55] flex items-center justify-center p-4" role="alertdialog" aria-modal="true"
     aria-labelledby="konfirmasi-judul">
    <div class="absolute inset-0 bg-slate-900/45" x-on:click="$store.confirm.jawab(false)" x-show="$store.confirm.open" x-transition.opacity></div>
    <div x-show="$store.confirm.open" x-transition x-trap.noscroll="$store.confirm.open"
         class="relative w-full max-w-[420px] rounded-2xl bg-surface p-6 shadow-pop">
        <span class="mb-4 flex size-11 items-center justify-center rounded-full"
              :class="{
                  'bg-danger-soft text-danger': $store.confirm.tone === 'danger',
                  'bg-warning-soft text-warning-strong': $store.confirm.tone === 'warning',
                  'bg-primary-soft text-primary-strong': $store.confirm.tone === 'primary',
              }">
            <x-ui.icon name="alert" :size="20" />
        </span>
        <h2 id="konfirmasi-judul" class="text-lg font-bold text-ink" x-text="$store.confirm.title"></h2>
        <p class="mt-2 text-sm text-ink-2" x-text="$store.confirm.message"></p>
        <div class="mt-5 flex justify-end gap-2.5 border-t border-line-soft pt-4">
            <x-ui.button variant="secondary" size="lg" x-on:click="$store.confirm.jawab(false)">Batal</x-ui.button>
            <button type="button" x-on:click="$store.confirm.jawab(true)" x-text="$store.confirm.confirmLabel"
                    class="inline-flex h-10 items-center justify-center rounded-control px-4 text-[15px] font-semibold text-white"
                    :class="$store.confirm.tone === 'danger' ? 'bg-danger hover:bg-red-700' : 'bg-primary hover:bg-primary-strong'"></button>
        </div>
    </div>
</div>
