{{-- Kop rapor; mengharapkan `d` (dokumen) di scope Alpine. --}}
<header class="flex items-center gap-4 border-b-2 border-ink pb-2.5">
    <span class="flex size-11 shrink-0 items-center justify-center rounded-lg border-2 border-ink text-lg font-bold">S</span>
    <div class="flex-1 text-center">
        <p class="text-[7.5px] uppercase" x-text="`${d.kop.baris1} · ${d.kop.baris2}`"></p>
        <p class="text-[11px] font-bold uppercase" x-text="d.kop.sekolah"></p>
        <p class="text-[6.5px]" x-text="d.kop.alamat"></p>
    </div>
    <span class="size-11 shrink-0" aria-hidden="true"></span>
</header>
