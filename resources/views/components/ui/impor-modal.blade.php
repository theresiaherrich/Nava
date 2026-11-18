@props([
    'judul',              // "Impor Data Siswa"
    'satuan',             // "siswa"
    'hasil',              // Operator::hasilImpor()
    'formatUrl',          // tautan unduh templat
])

{{-- Modal impor Excel (desain Operator 2.05). Buka: $dispatch('open-modal', 'impor') atau ?modal=impor. --}}
<x-ui.modal name="impor" width="max-w-[720px]">
    <div x-data="imporExcel({ hasil: @js($hasil), satuan: @js($satuan), langsungPeriksa: @js(request('modal') === 'impor') })"
         x-on:open-modal.window="$event.detail === 'impor' && mulai()">
        <h2 class="pr-10 text-lg font-bold text-ink">{{ $judul }}</h2>
        <p class="mt-1 text-[13px] text-muted">Unggah berkas Excel sesuai format, periksa, lalu simpan.</p>

        {{-- Langkah --}}
        <ol class="mt-5 flex items-center gap-3 text-[13px] font-semibold" aria-label="Langkah impor">
            @foreach (['Unggah berkas', 'Periksa data', 'Simpan'] as $i => $label)
                @php $n = $i + 1; @endphp
                @if ($i > 0)<li aria-hidden="true" class="h-px flex-1 bg-line"></li>@endif
                <li class="flex items-center gap-2" :aria-current="langkah === {{ $n }} ? 'step' : null">
                    <span class="flex size-7 items-center justify-center rounded-full text-xs font-bold"
                          :class="langkah > {{ $n }} ? 'bg-success-strong text-white' : (langkah === {{ $n }} ? 'bg-primary text-white' : 'bg-neutral-soft text-muted')">
                        <x-ui.icon name="check" :size="14" :stroke="2.6" x-show="langkah > {{ $n }}" />
                        <span x-show="langkah <= {{ $n }}">{{ $n }}</span>
                    </span>
                    <span :class="langkah === {{ $n }} ? 'text-link' : (langkah > {{ $n }} ? 'text-ink' : 'text-muted')">{{ $label }}</span>
                </li>
            @endforeach
        </ol>

        {{-- Langkah 1: unggah --}}
        <div x-show="langkah === 1" class="mt-5">
            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-card border-2 border-dashed px-6 py-10 text-center transition-colors"
                   :class="seret ? 'border-primary bg-primary-faint' : 'border-line hover:border-primary/50'"
                   x-on:dragover.prevent="seret = true" x-on:dragleave.prevent="seret = false"
                   x-on:drop.prevent="seret = false; pilih($event.dataTransfer.files[0])">
                <span class="flex size-12 items-center justify-center rounded-full bg-primary-soft text-primary-strong"><x-ui.icon name="upload" :size="22" /></span>
                <span class="text-sm font-bold text-ink" x-text="memproses ? 'Memeriksa berkas…' : 'Seret berkas ke sini atau klik untuk memilih'"></span>
                <span class="text-xs text-muted">.xlsx, .xls, atau .csv · maks. 5 MB</span>
                <input type="file" class="sr-only" accept=".xlsx,.xls,.csv" x-on:change="pilih($event.target.files[0]); $event.target.value = ''">
            </label>
            <p x-show="galat" x-cloak class="mt-2 flex items-center gap-1 text-xs font-medium text-danger" role="alert"><x-ui.icon name="alert-circle" :size="13" /><span x-text="galat"></span></p>
            <p class="mt-3 text-xs text-muted">Belum punya format? <a href="{{ $formatUrl }}" class="font-semibold text-link hover:underline">Unduh format Excel</a></p>
        </div>

        {{-- Langkah 2: periksa --}}
        <div x-show="langkah >= 2" x-cloak class="mt-5 space-y-4">
            <div class="flex items-center gap-3 rounded-card border border-line px-4 py-3">
                <span class="flex size-9 items-center justify-center rounded-control bg-success-soft text-success"><x-ui.icon name="sheet" :size="18" /></span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-ink" x-text="berkas?.nama"></p>
                    <p class="text-xs text-muted" x-text="berkas?.info"></p>
                </div>
                <x-ui.button variant="secondary" size="sm" x-on:click="mulai()">Ganti berkas</x-ui.button>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-ui.badge tone="success" icon="check"><span x-text="`${$angka(hasil.valid)} baris valid`"></span></x-ui.badge>
                <x-ui.badge tone="danger" icon="alert-circle" x-show="hasil.masalah.length"><span x-text="`${hasil.masalah.length} baris bermasalah`"></span></x-ui.badge>
            </div>

            <div x-show="hasil.masalah.length" class="overflow-hidden rounded-card border border-line">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr><th class="px-4 py-2.5">Baris</th><th class="px-3 py-2.5">Kolom</th><th class="px-3 py-2.5">Nilai</th><th class="px-3 py-2.5">Masalah</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        <template x-for="m in hasil.masalah" :key="m.baris">
                            <tr>
                                <td class="px-4 py-2.5 text-ink-2" x-text="m.baris"></td>
                                <td class="px-3 py-2.5 text-ink-2" x-text="m.kolom"></td>
                                <td class="px-3 py-2.5 text-ink-2" x-text="m.nilai"></td>
                                <td class="px-3 py-2.5 font-semibold text-danger" x-text="m.pesan"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="hasil.masalah.length" class="grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Cara menyimpan">
                @foreach ([['valid', 'Simpan baris valid saja', null], ['perbaiki', 'Perbaiki berkas lalu unggah ulang', 'Tidak ada data yang disimpan']] as [$nilai, $judulOpsi, $ket])
                    <label class="flex cursor-pointer items-start gap-3 rounded-card border px-4 py-3"
                           :class="mode === '{{ $nilai }}' ? 'border-primary/50 bg-primary-faint' : 'border-line hover:bg-surface-2'">
                        <input type="radio" name="mode-impor" value="{{ $nilai }}" x-model="mode" class="mt-0.5 size-4 accent-primary">
                        <span>
                            <span class="block text-[13px] font-bold text-ink">{{ $judulOpsi }}</span>
                            <span class="block text-xs text-muted" @if (! $ket) x-text="`${hasil.masalah.length} baris bermasalah dilewati`" @endif>{{ $ket }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mt-5 flex justify-end gap-2.5 border-t border-line-soft pt-4">
            <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'impor')">Batal</x-ui.button>
            <x-ui.button size="lg" x-show="langkah >= 2" x-on:click="simpan()" x-text="labelSimpan">Simpan</x-ui.button>
        </div>
    </div>
</x-ui.modal>
