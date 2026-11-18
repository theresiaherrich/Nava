{{--
    Dropdown "Pilih guru pengampu" (desain Kurikulum 4.06). Dipakai di dalam komponen Alpine
    yang memakai pemilihGuru() dari resources/js/kurikulum-rombel.js dan punya method pilih(guru).
--}}
<div x-show="picker.buka" x-cloak x-transition.opacity x-on:click.outside="tutupPicker()" x-on:keydown.escape.window="tutupPicker()"
     x-on:scroll.window="tutupPicker()" x-on:resize.window="tutupPicker()"
     class="fixed z-40 rounded-card border border-line bg-surface p-2 shadow-pop" :style="picker.gaya"
     role="dialog" aria-label="Pilih guru pengampu">
    <x-ui.search-input placeholder="Cari guru…" x-model="picker.cari" x-ref="cariGuru"
                       class="[&_input]:border-primary [&_input]:ring-2 [&_input]:ring-primary-soft" />
    <p class="px-2 pt-3 pb-1.5 text-[11px] font-bold text-muted uppercase" x-text="`Pengampu ${picker.mapel}`"></p>
    <ul role="listbox" class="max-h-72 overflow-y-auto">
        <template x-for="g in calonTersaring" :key="g.id_guru">
            <li>
                <button type="button" role="option" x-on:click="pilih(g)" :disabled="g.penuh" :aria-disabled="g.penuh"
                        class="flex w-full items-center gap-2.5 rounded-control px-2 py-2 text-left hover:bg-primary-faint disabled:cursor-not-allowed disabled:hover:bg-transparent">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold text-white"
                          :class="g.penuh ? 'bg-subtle/60' : warnaAvatar(g.nama)" x-text="inisial(g.nama)"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[13px] font-semibold" :class="g.penuh ? 'text-subtle' : 'text-ink'" x-text="g.nama"></span>
                        <span class="block text-xs" :class="g.penuh ? 'text-danger' : 'text-muted'"
                              x-text="g.penuh ? `Beban penuh (${g.beban} rombel)` : `mengampu ${g.beban} rombel`"></span>
                    </span>
                    <span class="rounded-md px-1.5 py-0.5 text-[11px] font-bold"
                          :class="g.penuh ? 'text-subtle' : (g.status === 'GTT' ? 'bg-violet-soft text-violet' : 'bg-info-soft text-info')"
                          x-text="g.status"></span>
                </button>
            </li>
        </template>
        <li x-show="!calonTersaring.length" class="px-2 py-3 text-[13px] text-muted">Guru tidak ditemukan.</li>
    </ul>
</div>
