@extends('layouts.app')

@section('title', 'Cetak & Ekspor Rapor')

@push('styles')
    @vite(['resources/js/wali-cetak.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="cetakWali(JSON.parse(document.getElementById('data-cetak').textContent))">
    <x-ui.json-data id="data-cetak" :data="[
        'kelas' => $kelas, 'terkunci' => $terkunci, 'siswa' => $siswa, 'dokumen' => $dokumen, 'riwayat' => $riwayat,
        'awal' => ['tab' => request('tab') === 'riwayat' ? 'riwayat' : 'pratinjau', 'jenis' => request('jenis') === 'semester' ? 'semester' : 'sts', 'cari' => (string) request('cari', ''), 'proses' => request('proses') === '1'],
    ]" />

    <x-ui.page-header title="Cetak & Ekspor Rapor" role="Wali Kelas" subtitle="Pratinjau, cetak, dan unduh rapor siswa tanpa mengolah ulang dokumen." />

    {{-- Bilah kelas + cari + unduh satu kelas --}}
    <x-ui.card class="flex flex-wrap items-center gap-2.5 !py-3.5">
        <span class="inline-flex h-9 items-center gap-1.5 rounded-control border border-line bg-surface-2 px-3 text-[13px] font-semibold text-ink" title="Anda hanya dapat mencetak rapor kelas perwalian"><x-ui.icon name="lock" :size="13" />Kelas {{ $kelas }}</span>

        {{-- 5.04 Cari siswa (combobox) --}}
        <div class="relative w-full sm:w-[240px]" x-on:click.outside="bukaCari = false">
            <label class="relative flex h-9 items-center">
                <span class="sr-only">Cari siswa</span>
                <x-ui.icon name="search" :size="15" class="pointer-events-none absolute left-3 text-subtle" />
                <input type="search" x-model="cari" placeholder="Cari nama atau NISN…" autocomplete="off" role="combobox"
                       aria-controls="hasil-cari" :aria-expanded="bukaCari && hasilCari.length > 0" aria-autocomplete="list"
                       :aria-activedescendant="bukaCari && hasilCari[sorot] ? `hasil-${hasilCari[sorot].nis}` : null"
                       x-on:keydown.down.prevent="navigasiCari(1)" x-on:keydown.up.prevent="navigasiCari(-1)"
                       x-on:keydown.enter.prevent="pilih(hasilCari[sorot])" x-on:keydown.escape="bukaCari = false" x-on:focus="bukaCari = !!cari.trim()"
                       class="h-full w-full rounded-control border border-line bg-surface pr-3 pl-9 text-[13px] text-ink placeholder:text-subtle focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
            </label>
            <div x-show="bukaCari" x-cloak class="absolute top-full left-0 z-30 mt-1.5 w-[300px] rounded-card border border-line bg-surface p-2 shadow-pop">
                <p class="px-2.5 pt-1 pb-2 text-[11px] font-bold text-muted uppercase" x-text="`${hasilCari.length} hasil`"></p>
                <ul id="hasil-cari" role="listbox" aria-label="Hasil pencarian siswa">
                    <template x-for="(s, i) in hasilCari" :key="s.nis">
                        <li :id="`hasil-${s.nis}`" role="option" :aria-selected="i === sorot" x-on:click="pilih(s)" x-on:mouseenter="sorot = i"
                            class="cursor-pointer rounded-control px-3 py-2" :class="i === sorot && 'bg-primary-faint'">
                            <p class="text-sm text-ink"><span x-text="potong(s.nama)[0]"></span><strong x-text="potong(s.nama)[1]"></strong><span x-text="potong(s.nama)[2]"></span></p>
                            <p class="text-xs text-muted" x-text="`${s.nisn} · ${s.kelas}`"></p>
                        </li>
                    </template>
                </ul>
                <p x-show="!hasilCari.length" class="px-2.5 pb-2 text-[13px] text-muted">Siswa tidak ditemukan di kelas {{ $kelas }}.</p>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-2.5">
            @if ($terkunci)
                <x-ui.badge tone="success" icon="lock" class="!py-1 !text-[13px]">Nilai dikunci {{ $dikunci }}</x-ui.badge>
            @else
                <x-ui.badge tone="yellow" icon="lock" class="!py-1 !text-[13px]">Belum dikunci</x-ui.badge>
            @endif
            <button type="button" x-on:click="unduhKelas()" :disabled="!terkunci || (proses && proses !== 'selesai')"
                    class="inline-flex h-10 items-center gap-2 rounded-control border px-4 text-[15px] font-semibold disabled:cursor-not-allowed"
                    :class="proses && proses !== 'selesai' ? 'border-primary-soft bg-primary-faint text-link' : (terkunci ? 'border-line bg-surface text-ink hover:bg-surface-2' : 'border-line bg-line text-subtle')">
                <svg x-show="proses && proses !== 'selesai'" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3" /><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
                <x-ui.icon name="download" :size="16" x-show="!proses || proses === 'selesai'" />
                <span x-text="proses && proses !== 'selesai' ? 'Membuat PDF…' : 'PDF satu kelas'"></span>
            </button>
        </div>
    </x-ui.card>

    @unless ($terkunci)
        {{-- 5.01 Belum terkunci --}}
        <x-ui.alert tone="warning" icon="lock" title="Rapor belum dapat dicetak" class="mt-4">
            Nilai {{ $kelas }} belum dikunci Kurikulum. {{ $menunggu }} mapel masih menunggu verifikasi.
            <x-slot:action><a href="{{ route('rekap-nilai.index', ['tab' => 'mapel']) }}" class="inline-flex items-center gap-1 text-[13px] font-semibold text-warning hover:underline">Lihat kelengkapan <x-ui.icon name="arrow-right" :size="13" /></a></x-slot:action>
        </x-ui.alert>
    @endunless

    <nav aria-label="Tampilan cetak rapor" class="mt-5 mb-4 flex gap-6 border-b border-line">
        @foreach (['pratinjau' => 'Pratinjau rapor', 'riwayat' => 'Riwayat cetak'] as $t => $teks)
            <button type="button" x-on:click="tab = @js($t)" :aria-current="tab === @js($t) ? 'page' : null"
                    class="-mb-px border-b-2 px-0.5 pb-2.5 text-sm" :class="tab === @js($t) ? 'border-primary font-semibold text-link' : 'border-transparent text-muted hover:text-ink'">{{ $teks }}</button>
        @endforeach
    </nav>

    {{-- 5.03 / 5.06 Pratinjau --}}
    <div x-show="tab === 'pratinjau'" class="grid items-start gap-5 xl:grid-cols-[291px_minmax(0,1fr)]">
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="flex items-center justify-between px-4 pt-4 pb-2">
                <h2 class="text-[15px] font-bold text-ink" x-text="`${siswa.length} siswa`"></h2>
                @if ($terkunci)
                    <x-ui.badge tone="success" icon="check">Semua siap</x-ui.badge>
                @else
                    <x-ui.badge tone="warning">0 siap</x-ui.badge>
                @endif
            </div>
            <ul role="listbox" aria-label="Siswa" class="px-2 pb-2">
                <template x-for="s in barisHalaman" :key="s.nis">
                    <li>
                        <button type="button" role="option" :aria-selected="aktif === s.nis" x-on:click="aktif = s.nis"
                                class="relative flex w-full items-center gap-3 rounded-control px-2.5 py-2 text-left"
                                :class="aktif === s.nis ? 'bg-primary-faint before:absolute before:inset-y-0 before:left-0 before:w-[3px] before:rounded-l-control before:bg-primary' : 'hover:bg-surface-2'">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full text-[11px] font-bold" :class="aktif === s.nis ? 'bg-primary-soft text-link' : 'bg-neutral-soft text-ink-2'" x-text="inisial(s.nama)"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[13px] font-bold" :class="aktif === s.nis ? 'text-link' : 'text-ink'" x-text="s.nama"></span>
                                <span class="block text-xs text-muted" x-text="`${s.nisn} · ${s.kelas}`"></span>
                            </span>
                            @if ($terkunci)
                                <x-ui.icon name="check-circle" :size="16" class="text-success" /><span class="sr-only">Siap dicetak</span>
                            @else
                                <x-ui.badge tone="warning">Belum siap</x-ui.badge>
                            @endif
                        </button>
                    </li>
                </template>
            </ul>
            <div class="flex items-center justify-between border-t border-line-soft px-4 py-3">
                <span class="text-xs whitespace-nowrap text-muted" x-text="rentang"></span>
                <x-ui.pagination page="page" pages="jumlahHalaman" />
            </div>
        </x-ui.card>

        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="flex flex-wrap items-center gap-3 border-b border-line-soft px-4 py-3">
                <div>
                    <p class="text-[13px] text-muted">Pratinjau rapor · <strong class="text-ink" x-text="`Halaman ${lembar} dari ${jumlahLembar}`"></strong> · <span x-text="siswaAktif?.nama"></span></p>
                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                        <x-ui.segmented :options="['sts' => 'Rapor STS', 'semester' => 'Rapor Semester']" model="jenis" label="Jenis rapor" />
                        <template x-if="jenis === 'semester'">
                            <div class="flex items-center gap-2">
                                <x-ui.badge>2 halaman</x-ui.badge>
                                <div role="group" aria-label="Lompat ke halaman" class="inline-flex items-center rounded-control border border-line p-0.5">
                                    <template x-for="n in [1, 2]" :key="n">
                                        <button type="button" x-on:click="keLembar(n)" :aria-pressed="lembar === n" x-text="`Halaman ${n}`"
                                                class="rounded-[6px] px-2.5 py-1 text-[13px]" :class="lembar === n ? 'bg-primary-faint font-medium text-link' : 'text-muted hover:text-ink'"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="ml-auto flex gap-2.5">
                    @foreach ([['Cetak', 'printer', 'cetak()', 'secondary'], ['Unduh PDF', 'download', 'unduhSiswa()', 'primary']] as [$teks, $ikon, $aksi, $varian])
                        <span class="group relative">
                            <x-ui.button :variant="$varian" :icon="$ikon" x-on:click="{{ $aksi }}" x-bind:disabled="!terkunci" x-bind:aria-describedby="!terkunci ? 'tip-kunci' : null"
                                         x-bind:class="!terkunci && '!border-line !bg-line !text-subtle cursor-not-allowed'">{{ $teks }}</x-ui.button>
                            @unless ($terkunci)
                                <span @if ($loop->last) id="tip-kunci" @endif role="tooltip" class="pointer-events-none absolute right-0 bottom-full z-20 mb-2 hidden rounded-md bg-toast px-2.5 py-1.5 text-xs whitespace-nowrap text-white group-focus-within:block group-hover:block">Tersedia setelah nilai dikunci</span>
                            @endunless
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="relative max-h-[880px] overflow-auto bg-neutral-soft px-4 py-6" x-ref="area" id="area-cetak" x-on:scroll.debounce.100ms="pantauLembar($el)">
                <template x-if="doc"><x-rapor.dokumen doc="doc" draf="!terkunci" /></template>
            </div>
        </x-ui.card>
    </div>

    {{-- 5.07 Riwayat cetak --}}
    <x-ui.card :padding="false" class="overflow-hidden" x-show="tab === 'riwayat'" x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr><th class="px-5 py-3">Tanggal</th><th class="px-3 py-3">Jenis rapor</th><th class="px-3 py-3">Versi</th><th class="px-3 py-3">Dicetak oleh</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="(r, i) in riwayat" :key="i">
                        <tr>
                            <td class="px-5 py-2.5 text-ink-2" x-text="r.waktu"></td>
                            <td class="px-3 font-bold text-ink" x-text="`${r.jenis} · ${r.cakupan}`"></td>
                            <td class="px-3"><span :class="$store.ref.badge(r.versi.includes('Koreksi') ? 'warning' : 'neutral')" x-text="r.versi"></span></td>
                            <td class="px-3 text-ink-2" x-text="r.oleh"></td>
                            <td class="px-5 py-2 text-right"><x-ui.button variant="secondary" size="sm" icon="download" x-on:click="unduhUlang(r)">Unduh ulang</x-ui.button></td>
                        </tr>
                    </template>
                    <tr x-show="!riwayat.length"><td colspan="5"><x-ui.empty-state icon="printer" title="Belum ada riwayat cetak" description="Riwayat muncul setelah rapor dicetak atau diunduh." class="!py-10" /></td></tr>
                </tbody>
            </table>
        </div>
        <p x-show="riwayat.length" class="flex items-center gap-1.5 border-t border-line-soft px-5 py-3 text-xs text-muted"><x-ui.icon name="info" :size="13" />Cetak ulang versi lama memakai data final lama. Versi v2 dibuat setelah koreksi nilai Matematika disetujui Kurikulum.</p>
    </x-ui.card>

    {{-- 5.05 Proses unduh satu kelas --}}
    <div class="fixed right-6 bottom-6 z-40 w-[360px] max-w-[calc(100vw-3rem)]" role="status" aria-live="polite">
        <div x-show="proses && proses !== 'selesai'" x-cloak x-transition class="rounded-card border border-line bg-surface p-4 shadow-pop">
            <p class="flex items-center gap-2.5 text-[15px] font-bold text-ink">
                <svg class="size-5 animate-spin text-primary" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="3" /><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
                <span x-text="`Membuat PDF ${labelJenis} ${kelas}`"></span>
            </p>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Progres pembuatan PDF" :aria-valuenow="persen" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-primary transition-all" :style="`width: ${persen}%`"></div>
            </div>
            <p class="mt-2 flex justify-between text-[13px]"><strong class="text-link" x-text="`${proses?.selesai} / ${proses?.total} siswa`"></strong><span class="text-muted" x-text="`${persen}%`"></span></p>
            <p class="mt-1.5 text-xs text-muted" x-text="`Estimasi ±${sisaDetik} detik lagi. Anda boleh tetap di halaman ini.`"></p>
            <div class="mt-2 text-right"><button type="button" x-on:click="batalkan()" class="text-[13px] font-semibold text-ink-2 hover:text-ink">Batalkan</button></div>
        </div>
        <div x-show="proses === 'selesai'" x-cloak x-transition class="relative rounded-card border border-success/30 bg-surface p-4 shadow-pop">
            <button type="button" x-on:click="proses = null" aria-label="Tutup" class="absolute top-3 right-3 inline-flex size-7 items-center justify-center rounded-control text-muted hover:bg-neutral-soft"><x-ui.icon name="x" :size="15" /></button>
            <p class="flex items-center gap-2.5 text-[15px] font-bold text-success">
                <span class="flex size-7 items-center justify-center rounded-full bg-success-strong text-white"><x-ui.icon name="check" :size="15" /></span>
                <span x-text="`${siswa.length} rapor siap diunduh`"></span>
            </p>
            <p class="mt-2 text-xs text-muted" x-text="`${namaZip} · 4,2 MB`"></p>
            <div class="mt-3 flex gap-2.5">
                <x-ui.button class="flex-1 justify-center" x-on:click="ambil('zip')">Unduh ZIP</x-ui.button>
                <x-ui.button variant="secondary" class="flex-1 justify-center" x-on:click="ambil('pdf')">Unduh satu PDF gabungan</x-ui.button>
            </div>
        </div>
    </div>
</div>
@endsection
