{{-- Wadah toast global (navy #1F2A44, radius 8, lebar 380). Isi lewat event "toast" (lihat resources/js/app.js). --}}
<div class="pointer-events-none fixed right-6 bottom-6 z-[60] flex w-[380px] max-w-[calc(100vw-2rem)] flex-col gap-2.5"
     x-data aria-live="polite" role="status">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div x-transition.opacity
             class="pointer-events-auto flex items-center gap-3 rounded-control bg-toast px-4 py-3 text-sm text-white shadow-pop">
            <span class="flex size-6 shrink-0 items-center justify-center rounded-full text-white"
                  :class="{
                      'bg-success-strong': t.type === 'success',
                      'bg-primary': t.type === 'info',
                      'bg-warning-strong': t.type === 'warning',
                      'bg-danger': t.type === 'danger',
                      'bg-violet': t.type === 'violet',
                  }">
                <svg x-show="t.type === 'success'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                <svg x-show="t.type === 'info' || t.type === 'violet'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg>
                <svg x-show="t.type === 'warning' || t.type === 'danger'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M12 7v6M12 17h.01"/></svg>
            </span>
            <span class="min-w-0 flex-1" x-text="t.message"></span>
            <button type="button" x-show="t.action" x-text="t.action" class="shrink-0 text-[13px] font-semibold text-sky-300 hover:underline"
                    x-on:click="$store.toasts.remove(t.id)"></button>
        </div>
    </template>
</div>
