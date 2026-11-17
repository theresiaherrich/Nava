@extends('layouts.app')

@section('title', 'Kelola Akun & Peran')

@push('styles')
    @vite(['resources/js/operator-akun.js'])
@endpush

@php
    use App\Support\Format;
    $s = $statistik;
@endphp

@section('content')
<div class="px-7 pt-7 pb-10" x-data="akunPage(JSON.parse(document.getElementById('data-akun').textContent))">
    <x-ui.json-data id="data-akun" :data="['akun' => $akun->items(), 'username' => $username, 'bukaModal' => request('modal') === 'akun']" />

    <x-ui.page-header title="Kelola Akun & Peran" role="Operator" subtitle="Atur akun pengguna dan peran yang dimiliki.">
        <x-slot:actions>
            <x-ui.button icon="plus" size="lg" x-on:click="tambah()">Tambah Akun</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.stat-card label="Total akun" :value="Format::angka($s['total'])" :hint="Format::angka($s['aktif']).' aktif · '.$s['nonaktif'].' nonaktif'" />
        <x-ui.stat-card label="Akun guru" :value="$s['guru']" :hint="$s['guru_tanpa_akun'].' guru belum punya akun'" />
        <x-ui.stat-card label="Akun orang tua" :value="Format::angka($s['ortu'])" :hint="'terhubung ke '.Format::angka($s['ortu']).' siswa'" />
    </div>

    <x-ui.card :padding="false" class="mt-5 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 pb-3">
            <div>
                <h2 class="text-[15px] font-bold text-ink">Daftar Akun</h2>
                <p class="text-xs text-muted">{{ Format::angka($akun->total()) }} akun</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <x-ui.filter-link label="Peran" param="peran" :options="['' => 'Semua'] + array_combine($peran, $peran)" />
                <x-ui.filter-link label="Status" param="status" :options="['' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" />
                <x-ui.search-form placeholder="Cari nama atau username…" class="w-[220px]" />
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="w-[56px] px-5 py-3">No</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">Username</th>
                        <th class="px-3 py-3">Peran</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Terakhir login</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="(a, i) in akun" :key="a.id_user">
                        <tr>
                            <td class="px-5 py-3 text-ink-2" x-text="{{ max(0, $akun->firstItem() - 1) }} + i + 1"></td>
                            <td class="px-3 py-3 font-semibold text-ink" x-text="a.nama"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="a.username"></td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="p in a.peran" :key="p"><span :class="$store.ref.peranBadge(p)" x-text="p"></span></template>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <span :class="$store.ref.badge(a.status === 'aktif' ? 'success' : 'neutral')" x-text="a.status === 'aktif' ? 'Aktif' : 'Nonaktif'"></span>
                            </td>
                            <td class="px-3 py-3 text-muted" x-text="a.login"></td>
                            <td class="px-5 py-2.5">
                                <div class="flex justify-end gap-1.5">
                                    <x-ui.icon-button icon="pencil" label="Ubah akun" x-on:click="ubah(a)" />
                                    <x-ui.icon-button icon="key" label="Reset kata sandi" x-on:click="resetSandi(a)" />
                                    <x-ui.icon-button icon="toggle" label="Aktifkan atau nonaktifkan akun" x-on:click="ubahStatus(a)" />
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!akun.length"><td colspan="7" class="px-5 py-10 text-center text-sm text-muted">Tidak ada akun yang cocok dengan filter.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
            <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="info" :size="13" />Satu akun dapat memiliki lebih dari satu peran.</p>
            <x-ui.pager :paginator="$akun" satuan="akun" />
        </div>
    </x-ui.card>

    {{-- 2.03 Tambah / Ubah Akun --}}
    <x-ui.modal name="akun" width="max-w-[520px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="editId ? 'Ubah Akun' : 'Tambah Akun'"></h2>
        <p class="mt-1 text-[13px] text-muted">Akun dipakai untuk masuk ke seluruh modul SIAKAD.</p>

        <form class="mt-4 space-y-4" x-on:submit.prevent="simpan()" novalidate>
            <div class="grid gap-3.5 sm:grid-cols-2">
                <x-ui.field label="Nama lengkap" for="ak-nama" required error="error.nama">
                    <x-ui.input id="ak-nama" x-model="form.nama" error="error.nama" autocomplete="off" />
                </x-ui.field>
                <x-ui.field label="Username" for="ak-username" required error="error.username">
                    <x-ui.input id="ak-username" x-model="form.username" error="error.username" autocomplete="off" x-on:input="delete error.username" />
                </x-ui.field>
                <x-ui.field label="Email (opsional)" for="ak-email" error="error.email">
                    <x-ui.input id="ak-email" type="email" x-model="form.email" error="error.email" placeholder="nama@sman1malang.sch.id" />
                </x-ui.field>
                <x-ui.field label="Password awal" for="ak-sandi" :required="true" error="error.sandi">
                    <div class="flex items-center gap-2">
                        <x-ui.input id="ak-sandi" x-bind:type="lihatSandi ? 'text' : 'password'" x-model="form.sandi" error="error.sandi"
                                    x-bind:placeholder="editId ? 'Kosongkan bila tidak diubah' : ''" autocomplete="new-password" />
                        <button type="button" x-on:click="buatSandi()" class="shrink-0 text-[13px] font-semibold text-link hover:underline">Buat otomatis</button>
                    </div>
                </x-ui.field>
            </div>

            <fieldset>
                <legend class="mb-1.5 text-[13px] font-semibold text-ink">Peran<span class="text-danger"> *</span></legend>
                <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                    <template x-for="p in peranForm" :key="p">
                        <label class="flex min-h-12 cursor-pointer items-center gap-2.5 rounded-control border px-3 py-2"
                               :class="form.peran.includes(p) ? 'border-primary/40 bg-primary-faint' : 'border-line hover:bg-surface-2'">
                            <input type="checkbox" class="size-4 accent-primary" :checked="form.peran.includes(p)" x-on:change="togglePeran(p)">
                            <span class="text-[13px] font-semibold text-ink" x-text="p"></span>
                        </label>
                    </template>
                </div>
                <p x-show="error.peran" x-cloak class="mt-1.5 text-xs font-medium text-danger" role="alert" x-text="error.peran"></p>
            </fieldset>

            <div x-show="pilihGuru" x-cloak class="rounded-card border border-line bg-surface-2 p-3.5">
                <div class="flex flex-wrap items-end gap-3">
                    <x-ui.field label="NIP (opsional)" for="ak-nip" error="error.nip" class="min-w-0 flex-1">
                        <x-ui.input id="ak-nip" x-model="form.nip" error="error.nip" inputmode="numeric" x-bind:disabled="form.status === 'GTT'" />
                    </x-ui.field>
                    <div>
                        <p class="mb-1.5 text-[13px] font-semibold text-ink">Status</p>
                        <x-ui.segmented :options="['ASN' => 'ASN', 'GTT' => 'GTT']" model="form.status" label="Status kepegawaian" size="sm" class="bg-surface ring-1 ring-line" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-muted">Muncul karena peran Guru dipilih</p>
            </div>

            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'akun')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Simpan Akun</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
@endsection
