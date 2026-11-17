@extends('layouts.app')

@section('title', 'Kelola Tujuan Pembelajaran')

@push('styles')
    @vite(['resources/js/guru-tp.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="tpPage(JSON.parse(document.getElementById('data-tp').textContent))">
    <x-ui.json-data id="data-tp" :data="[
        'rombel' => $rombel, 'aktif' => $aktif, 'stsTerkunci' => $stsTerkunci, 'maks' => $maks,
        'tampil' => request('tampil'), 'modal' => request('modal'), 'bukaRombel' => request('pilih-rombel') === '1',
    ]" />

    <x-ui.page-header title="Kelola Tujuan Pembelajaran" subtitle="Susun TP Matematika untuk menjadi dasar penilaian dan rapor.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="copy" size="lg" x-bind:disabled="!tp.length" x-on:click="bukaSalin()">Salin TP ke kelas lain</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Pilih rombel & semester --}}
    <x-ui.card class="flex flex-wrap items-end gap-4">
        <div>
            <p class="mb-1.5 text-[13px] font-semibold text-ink-2">Kelas / Rombel</p>
            <div class="relative" x-on:click.outside="pilihRombel = false" x-on:keydown.escape="pilihRombel = false">
                <button type="button" x-on:click="pilihRombel = !pilihRombel" :aria-expanded="pilihRombel" aria-haspopup="listbox"
                        class="flex h-9 w-[210px] items-center justify-between rounded-control border border-line px-3 text-[13px] text-ink">
                    <span x-text="`${r.kelas} · Matematika`"></span><x-ui.icon name="chevron-down" :size="14" class="text-ink-2" />
                </button>
                <div x-show="pilihRombel" x-cloak class="absolute top-full left-0 z-30 mt-1 w-[300px] rounded-card border border-line bg-surface p-2 shadow-pop">
                    <x-ui.search-input placeholder="Cari rombel…" x-model="cariRombel" />
                    <ul role="listbox" aria-label="Rombel" class="mt-1.5">
                        <template x-for="x in rombelTersaring" :key="x.id">
                            <li>
                                <button type="button" role="option" :aria-selected="x.id === aktifId" x-on:click="gantiRombel(x.id)"
                                        class="flex w-full items-center gap-2 rounded-control px-2 py-2 text-left text-[13px]" :class="x.id === aktifId ? 'bg-primary-faint font-semibold' : 'hover:bg-surface-2'">
                                    <span class="w-4"><x-ui.icon name="check" :size="14" class="text-link" x-show="x.id === aktifId" /></span>
                                    <span class="flex-1 text-ink" x-text="`${x.kelas} · Matematika`"></span>
                                    <span class="flex items-center gap-1 text-xs" :class="x.tp.length ? 'text-muted' : 'text-danger'">
                                        <span x-show="!x.tp.length" class="size-1.5 rounded-full bg-danger"></span><span x-text="`${x.tp.length} TP`"></span>
                                    </span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
        <x-ui.field label="Semester" for="tp-semester" class="[&>label]:text-ink-2">
            <x-ui.select id="tp-semester" x-model="semester" :options="['Ganjil · 2026/2027', 'Genap · 2025/2026']" class="!h-9 w-[200px] !text-[13px]" />
        </x-ui.field>
        <div class="ml-auto w-[150px] text-right">
            <p class="text-xs text-muted">TP terdaftar</p>
            <p class="mt-0.5"><span class="text-[22px] font-bold" :class="penuh ? 'text-warning-strong' : 'text-primary-strong'" x-text="tp.length"></span> <span class="text-[13px] font-semibold text-muted" x-text="`/ ${maks} TP`"></span></p>
            <div class="mt-1 h-1 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full" :class="penuh ? 'bg-warning-strong' : 'bg-primary'" :style="`width: ${tp.length / maks * 100}%`"></div></div>
        </div>
    </x-ui.card>

    <x-ui.alert tone="warning" title="Kuota TP penuh" class="mt-4" x-show="penuh" x-cloak>Hapus atau gabungkan TP yang belum dinilai untuk menambah TP baru.</x-ui.alert>
    <div x-show="stsTerkunci" x-cloak class="mt-4 flex items-start gap-3.5 rounded-card border border-success/30 bg-success-soft/50 px-5 py-4" role="status">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-surface text-success shadow-card"><x-ui.icon name="lock" :size="16" /></span>
        <div>
            <p class="text-sm font-bold text-success">Nilai STS sudah dikunci</p>
            <p class="mt-0.5 text-[13px] text-ink-2" x-text="`TP yang masuk STS tidak dapat diubah. ${tp.length > jumlahSts ? `TP${jumlahSts + 1}–TP${tp.length}` : ''} masih dapat diubah untuk penilaian semester.`"></p>
        </div>
    </div>

    <div class="mt-5 grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_327px]">
        {{-- Daftar TP --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft px-5 py-4">
                <div><h2 class="text-[15px] font-bold text-ink">Daftar Tujuan Pembelajaran</h2><p class="text-xs text-muted">Seret untuk mengubah urutan TP.</p></div>
                <div class="flex items-center gap-2.5">
                    <span class="text-[13px] text-muted">Tampilkan:</span>
                    <x-ui.segmented :options="['semua' => 'Semua TP', 'sts' => 'STS saja']" model="tampil" label="Tampilkan TP" size="sm" />
                    <span :class="$store.ref.badge(tp.length ? 'success' : 'neutral')" x-text="`${tp.length} TP${tp.length ? ' aktif' : ''}`"></span>
                </div>
            </div>

            {{-- Kosong (06) --}}
            <div x-show="!tp.length" x-cloak>
                <x-ui.empty-state icon="clipboard" title="" class="py-14">
                    <p class="-mt-6 text-base font-bold text-ink" x-text="`Belum ada TP untuk Matematika ${r.kelas}`"></p>
                    <p class="mt-2 text-sm text-muted">Tambahkan TP atau salin dari rombel lain yang Anda ajar.</p>
                    <div class="mt-5 flex gap-2.5">
                        <x-ui.button icon="plus" x-on:click="bukaTambah()">Tambah TP</x-ui.button>
                        <x-ui.button variant="secondary" icon="copy" x-on:click="$dispatch('open-modal', 'salin-dari')">Salin dari rombel lain</x-ui.button>
                    </div>
                </x-ui.empty-state>
            </div>

            <div x-show="tp.length">
                <p x-show="tampil === 'sts'" class="flex items-center gap-1.5 px-5 pt-3 text-xs text-muted"><x-ui.icon name="info" :size="13" /><span x-text="`Menampilkan ${jumlahSts} TP yang masuk penilaian STS (setengah dari ${tp.length} TP).`"></span></p>
                <ol class="px-2 py-1" x-on:dragend="seret = { dari: null, ke: null }">
                    <template x-for="(t, i) in tampilTp" :key="t.id">
                        <li class="relative" x-on:dragover.prevent="lewat(i)" x-on:drop.prevent="lepas()">
                            <span x-show="seret.dari !== null && seret.ke === i && seret.ke < seret.dari" class="absolute -top-px right-3 left-3 flex items-center" aria-hidden="true">
                                <span class="size-2.5 rounded-full border-2 border-primary bg-surface"></span><span class="flex-1 border-t-2 border-dashed border-primary"></span>
                            </span>
                            <div :draggable="!terkunci(i) && tampil === 'semua'" x-on:dragstart="mulaiSeret(i, $event)"
                                 class="flex items-center gap-3 rounded-control border-b border-line-soft px-3"
                                 :class="[tp.length >= 8 ? 'py-2' : 'py-3', seret.dari === i ? 'border border-primary/50 bg-primary-faint shadow-pop' : '']">
                                <button type="button" :data-geser="i" class="cursor-grab rounded text-subtle hover:text-ink-2 disabled:cursor-default disabled:opacity-40" :disabled="terkunci(i)"
                                        :aria-label="`Geser ${kode(i)} (panah atas/bawah)`" x-on:keydown.arrow-up.prevent="geserKeyboard(i, -1)" x-on:keydown.arrow-down.prevent="geserKeyboard(i, 1)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
                                </button>
                                <span class="w-6 text-[13px] text-muted" x-text="String(i + 1).padStart(2, '0')"></span>
                                <div class="min-w-0 flex-1" :class="tp.length >= 8 && 'flex items-baseline gap-4'">
                                    <p class="text-[13px] font-bold text-ink" :class="tp.length >= 8 && 'w-10 shrink-0'" x-text="kode(i)"></p>
                                    <p class="text-[13px] text-ink-2" x-text="t.deskripsi"></p>
                                </div>
                                <span x-show="isSts(i)" :class="$store.ref.badge('info')">STS</span>
                                <span x-show="t.sas" :class="$store.ref.badge('yellow')">SAS · TP terakhir</span>
                                <span x-show="terkunci(i)" class="flex items-center gap-1 text-xs text-muted"><x-ui.icon name="lock" :size="12" />Terkunci</span>
                                <div x-show="!terkunci(i)" class="flex gap-1.5">
                                    <x-ui.icon-button icon="pencil" label="Ubah TP" x-on:click="bukaUbah(t)" />
                                    <x-ui.icon-button icon="trash" tone="danger" label="Hapus TP" x-on:click="hapus(t)" />
                                </div>
                            </div>
                            <span x-show="seret.dari !== null && seret.ke === i && seret.ke > seret.dari" class="absolute right-3 -bottom-px left-3 flex items-center" aria-hidden="true">
                                <span class="size-2.5 rounded-full border-2 border-primary bg-surface"></span><span class="flex-1 border-t-2 border-dashed border-primary"></span>
                            </span>
                        </li>
                    </template>
                </ol>
                <div class="flex flex-wrap items-center gap-3 border-t border-line-soft px-5 py-3.5">
                    <x-ui.button size="sm" icon="plus" x-bind:disabled="penuh" x-on:click="bukaTambah()">Tambah TP</x-ui.button>
                    <span x-show="!penuh" class="text-xs text-muted">Maksimal 10 TP — gabungkan TP yang mirip jika kuota tercapai.</span>
                    <span x-show="penuh" x-cloak class="rounded-md bg-toast px-2.5 py-1 text-xs font-semibold text-white" role="status">Kuota 10 TP sudah terpenuhi</span>
                </div>
            </div>
        </x-ui.card>

        {{-- Ringkasan --}}
        <x-ui.card>
            <h2 class="text-[15px] font-bold text-ink">Ringkasan TP</h2>
            <p x-show="!tp.length" class="mt-1 text-[13px] text-muted">Belum ada TP di rombel ini.</p>
            <ul x-show="tp.length" class="mt-2 space-y-1.5 text-[13px] text-ink-2">
                <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-success-strong"></span><strong class="text-ink" x-text="tp.length"></strong> TP terdaftar</li>
                <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-primary"></span><strong class="text-ink" x-text="jumlahSts"></strong> masuk STS</li>
                <li x-show="tpSas" class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-warning-strong"></span><strong class="text-ink" x-text="tpSas ? kode(tp.indexOf(tpSas)) : ''"></strong> dinilai lewat SAS</li>
            </ul>
            <div class="mt-4 border-t border-line-soft pt-4">
                <p class="flex items-center gap-2 text-[13px] font-bold text-ink"><span class="flex size-7 items-center justify-center rounded-control bg-warning-soft text-warning-strong"><x-ui.icon name="target" :size="14" /></span>TP terakhir untuk SAS</p>
                <p class="mt-2 text-xs text-muted">Nilai SAS dimasukkan pada kolom TP terakhir dan tidak dihitung dua kali dalam nilai rapor.</p>
            </div>
            <div class="mt-4 border-t border-line-soft pt-4">
                <p class="text-[13px] font-bold text-ink">Panduan cepat</p>
                <ul class="mt-1.5 space-y-1 text-xs text-ink-2 marker:text-primary">
                    <li class="ml-4 list-disc">Maksimal 10 TP per rombel</li>
                    <li class="ml-4 list-disc">Setengah TP pertama otomatis masuk STS</li>
                    <li class="ml-4 list-disc">Urutan TP tampil di rapor</li>
                    <li class="ml-4 list-disc">TP dapat disalin ke rombel lain</li>
                </ul>
            </div>
            <a :href="tp.length ? `{{ route('input-nilai.index') }}?rombel=${r.id}` : null" :aria-disabled="!tp.length"
               class="mt-4 flex h-10 items-center justify-center gap-1 rounded-control text-sm font-semibold"
               :class="tp.length ? 'bg-primary text-white hover:bg-primary-strong' : 'pointer-events-none bg-line text-subtle'">Lanjut input nilai <x-ui.icon name="arrow-right" :size="14" /></a>
            <p x-show="!tp.length" class="mt-2 text-center text-xs text-muted">Tambahkan minimal 1 TP untuk mulai mengisi nilai.</p>
        </x-ui.card>
    </div>

    {{-- 07/08/11 Tambah / ubah TP --}}
    <x-ui.modal name="tp" width="max-w-[480px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="form.id ? 'Ubah Tujuan Pembelajaran' : 'Tambah Tujuan Pembelajaran'"></h2>
        <p class="mt-1 text-[13px] text-muted" x-text="`Matematika · ${r.kelas} · Semester Ganjil 2026/2027`"></p>
        <form class="mt-4 space-y-4" x-on:submit.prevent="simpan()" novalidate>
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="tp-kode" class="text-[13px] font-semibold text-ink">Kode TP</label>
                    <span x-show="!form.id" :class="$store.ref.badge('info')" x-text="`Sisa kuota: ${maks - tp.length} dari ${maks} TP`"></span>
                </div>
                <x-ui.input id="tp-kode" x-bind:value="kodeForm" disabled class="!w-40" />
                <p class="mt-1.5 text-xs text-muted">Dibuat otomatis sesuai urutan.</p>
            </div>
            <div>
                <label for="tp-deskripsi" class="mb-1.5 block text-[13px] font-semibold text-ink">Deskripsi TP<span class="text-danger"> *</span></label>
                <textarea id="tp-deskripsi" x-model="form.deskripsi" rows="3" :maxlength="maksDeskripsi + 50" x-on:input="delete error.deskripsi"
                          placeholder="Contoh: Peserta didik mampu menyelesaikan masalah kontekstual yang berkaitan dengan fungsi kuadrat."
                          class="w-full rounded-control border px-3 py-2.5 text-sm text-ink placeholder:text-subtle focus:ring-2 focus:outline-none"
                          :class="error.deskripsi ? 'border-danger focus:ring-danger-soft' : 'border-line focus:border-primary focus:ring-primary-soft'"></textarea>
                <div class="mt-1 flex justify-between text-xs">
                    <span x-show="error.deskripsi" class="flex items-center gap-1 font-medium text-danger" role="alert"><x-ui.icon name="alert-circle" :size="13" /><span x-text="error.deskripsi"></span></span>
                    <span class="ml-auto" :class="form.deskripsi.length > maksDeskripsi ? 'text-danger' : 'text-muted'" x-text="`${form.deskripsi.length}/${maksDeskripsi}`"></span>
                </div>
            </div>
            <fieldset>
                <legend class="mb-1.5 text-[13px] font-semibold text-ink">Pengaturan penilaian</legend>
                <label class="flex items-start gap-3 rounded-control border border-line bg-surface-2 px-3 py-2.5 opacity-80">
                    <input type="checkbox" disabled :checked="(form.id ? tp.findIndex((t) => t.id === form.id) : tp.length) < Math.floor((tp.length + (form.id ? 0 : 1)) / 2)" class="mt-0.5 size-4">
                    <span><span class="block text-[13px] font-semibold text-muted">Masuk penilaian STS</span>
                        <span class="block text-xs text-muted" x-text="`Otomatis: setengah TP pertama masuk Rapor STS (TP1–TP${Math.max(1, Math.floor((tp.length + (form.id ? 0 : 1)) / 2))} dari ${tp.length + (form.id ? 0 : 1)} TP).`"></span></span>
                </label>
                <label class="mt-2 flex cursor-pointer items-start gap-3 rounded-control border px-3 py-2.5" :class="error.sas ? 'border-danger/40 bg-danger-soft/40' : 'border-line'">
                    <input type="checkbox" x-model="form.sas" x-on:change="delete error.sas" class="mt-0.5 size-4 accent-primary">
                    <span><span class="block text-[13px] font-semibold text-ink">TP terakhir (dinilai lewat SAS)</span><span class="block text-xs text-muted">Hanya satu TP yang boleh ditandai.</span></span>
                </label>
                <p x-show="error.sas" x-cloak class="mt-1.5 flex items-center gap-1 text-xs font-medium text-danger" role="alert"><x-ui.icon name="alert-circle" :size="13" /><span x-text="error.sas"></span></p>
            </fieldset>
            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'tp')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Simpan TP</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- 14 Salin ke kelas lain --}}
    <x-ui.modal name="salin" title="Salin TP ke kelas lain" width="max-w-[460px]">
        <p class="-mt-3 text-[13px] text-muted" x-text="`Pilih kelas tujuan. Seluruh ${tp.length} TP disalin dengan urutan dan penanda STS/SAS yang sama.`"></p>
        <ul class="mt-4 space-y-2">
            <template x-for="x in tujuanSalin" :key="x.id">
                <li>
                    <label class="flex items-center gap-3 rounded-control border px-3 py-2.5"
                           :class="x.fase !== r.fase ? 'cursor-not-allowed border-line bg-surface-2 opacity-70' : (salin.tujuan.includes(x.id) ? 'cursor-pointer border-primary/40 bg-primary-faint' : 'cursor-pointer border-line hover:bg-surface-2')">
                        <input type="checkbox" class="size-4 accent-primary" :checked="salin.tujuan.includes(x.id)" :disabled="x.fase !== r.fase" x-on:change="toggleSalin(x)">
                        <span class="flex-1 text-[13px] font-semibold" :class="x.fase !== r.fase ? 'text-subtle' : 'text-ink'" x-text="`${x.kelas} · Matematika`"></span>
                        <span x-show="x.fase !== r.fase" :class="$store.ref.badge('neutral')">Fase berbeda</span>
                        <span x-show="x.fase === r.fase && melebihi(x)" :class="$store.ref.badge('danger')" x-text="`${x.tp.length} TP — melebihi kuota`"></span>
                        <span x-show="x.fase === r.fase && !melebihi(x)" :class="$store.ref.badge(x.tp.length && salin.mode === 'ganti' ? 'warning' : 'neutral')"
                              x-text="x.tp.length && salin.mode === 'ganti' ? `${x.tp.length} TP — akan ditimpa` : `${x.tp.length} TP`"></span>
                    </label>
                </li>
            </template>
        </ul>
        <p class="mt-4 mb-2 text-[13px] font-semibold text-ink">Jika kelas tujuan sudah punya TP</p>
        <div class="grid grid-cols-2 gap-2.5" role="radiogroup" aria-label="Cara menyalin">
            @foreach (['tambah' => 'Tambahkan ke TP yang ada', 'ganti' => 'Ganti seluruh TP'] as $nilai => $teks)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-control border px-3 py-2.5" :class="salin.mode === '{{ $nilai }}' ? 'border-primary/40 bg-primary-faint' : 'border-line'">
                    <input type="radio" name="mode-salin" value="{{ $nilai }}" x-model="salin.mode" class="size-4 accent-primary"><span class="text-[13px] font-semibold text-ink">{{ $teks }}</span>
                </label>
            @endforeach
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'salin')">Batal</x-ui.button>
            <x-ui.button size="lg" x-bind:disabled="!salinValid" x-on:click="jalankanSalin()">Salin TP</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Salin dari rombel lain (rombel kosong) --}}
    <x-ui.modal name="salin-dari" title="Salin TP dari rombel lain" width="max-w-[440px]">
        <p class="-mt-3 text-[13px] text-muted" x-text="`TP disalin ke Matematika ${r.kelas} beserta urutan dan penanda STS/SAS.`"></p>
        <ul class="mt-4 space-y-2">
            <template x-for="x in tujuanSalin.filter((y) => y.tp.length)" :key="x.id">
                <li>
                    <button type="button" :disabled="x.fase !== r.fase"
                            x-on:click="r.tp = x.tp.map((t, i) => ({ ...t, id: `tp-${Date.now()}-${i}`, jumlah_nilai: 0 })); $dispatch('close-modal', 'salin-dari'); $dispatch('toast', { message: `${x.tp.length} TP disalin dari ${x.kelas}`, type: 'success' })"
                            class="flex w-full items-center gap-3 rounded-control border border-line px-3 py-2.5 text-left hover:border-primary/40 hover:bg-primary-faint disabled:cursor-not-allowed disabled:opacity-60">
                        <span class="flex-1 text-[13px] font-semibold text-ink" x-text="`${x.kelas} · Matematika`"></span>
                        <span :class="$store.ref.badge(x.fase !== r.fase ? 'neutral' : 'info')" x-text="x.fase !== r.fase ? 'Fase berbeda' : `${x.tp.length} TP`"></span>
                    </button>
                </li>
            </template>
        </ul>
    </x-ui.modal>

    {{-- 16 TP tidak dapat dihapus --}}
    <x-ui.modal name="tidak-dihapus" width="max-w-[400px]">
        <x-slot:icon><span class="flex size-11 items-center justify-center rounded-full bg-warning-soft text-warning-strong"><x-ui.icon name="lock" :size="19" /></span></x-slot:icon>
        <template x-if="tidakDihapus">
            <div>
                <h2 class="text-lg font-bold text-ink" x-text="`${kode(tp.indexOf(tidakDihapus))} tidak dapat dihapus`"></h2>
                <p class="mt-2 text-sm text-ink-2" x-text="`TP ini sudah memiliki ${tidakDihapus.jumlah_nilai} nilai siswa. Agar nilai tidak hilang, TP yang sudah dinilai tidak dapat dihapus.`"></p>
            </div>
        </template>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="lg" x-on:click="bukaUbah(tidakDihapus)">Ubah Deskripsi</x-ui.button>
            <x-ui.button size="lg" x-on:click="$dispatch('close-modal', 'tidak-dihapus')">Mengerti</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection
