@extends('layouts.app')

@section('title', 'Data Siswa')

@push('styles')
    @vite(['resources/js/operator-siswa.js'])
@endpush

@php
    use App\Support\Format;
    use App\Support\Dummy\Operator;

    $semuaSiswa = Operator::siswa();
@endphp

@section('content')
<div class="px-7 pt-7 pb-10" x-data="siswaPage(JSON.parse(document.getElementById('data-siswa').textContent))">
    <x-ui.json-data id="data-siswa" :data="[
        'siswa' => $siswa->items(),
        'nisTerpakai' => $semuaSiswa->pluck('nis')->all(),
        'nisnTerpakai' => $semuaSiswa->pluck('nisn')->all(),
    ]" />

    <x-ui.page-header title="Data Siswa" role="Operator" subtitle="Data induk siswa yang dipakai seluruh modul SIAKAD.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="download" size="lg" :href="route('data-siswa.format')">Unduh Format Excel</x-ui.button>
            <x-ui.button icon="upload" size="lg" x-on:click="$dispatch('open-modal', 'impor')">Impor Excel</x-ui.button>
            <x-ui.button variant="secondary" icon="plus" size="lg" x-on:click="tambah()">Tambah Siswa</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-link label="Tingkat" param="tingkat" :options="['' => 'Semua', 'X' => 'X', 'XI' => 'XI', 'XII' => 'XII']" />
        <x-ui.filter-link label="Kelas asal" param="kelas" :options="['' => 'Semua'] + array_combine($kelas, $kelas)" />
        <x-ui.filter-link label="Agama" param="agama" :options="['' => 'Semua'] + array_combine($agama, $agama)" />
        @if (request('ortu') === 'belum')
            <a href="{{ request()->fullUrlWithQuery(['ortu' => null, 'page' => null]) }}"
               class="inline-flex h-9 items-center gap-1.5 rounded-control bg-warning-soft px-3 text-[13px] font-semibold text-warning hover:bg-orange-200">
                Orang tua: Belum terhubung <x-ui.icon name="x" :size="13" />
            </a>
        @endif
        <x-ui.search-form placeholder="Cari nama, NIS, atau NISN…" class="ml-auto w-[260px]" />
    </x-ui.card>

    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="px-5 pt-4 pb-3">
            <h2 class="text-[15px] font-bold text-ink">Daftar Siswa</h2>
            <p class="text-xs text-muted">{{ Format::angka($siswa->total()) }} siswa aktif · urut nama A–Z</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="w-[56px] px-5 py-3">No</th>
                        <th class="px-3 py-3">NIS</th>
                        <th class="px-3 py-3">NISN</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">L/P</th>
                        <th class="px-3 py-3">Agama</th>
                        <th class="px-3 py-3">Kelas Asal</th>
                        <th class="px-3 py-3">Orang Tua</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="(s, i) in siswa" :key="s.id_siswa">
                        <tr>
                            <td class="px-5 py-3 text-ink-2" x-text="{{ max(0, $siswa->firstItem() - 1) }} + i + 1"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="s.nis"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="s.nisn"></td>
                            <td class="px-3 py-3 font-semibold text-ink" x-text="s.nama"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="s.jenis_kelamin"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="s.agama"></td>
                            <td class="px-3 py-3"><span class="rounded-md border border-line px-1.5 py-0.5 text-xs font-semibold text-ink-2" x-text="s.kelas"></span></td>
                            <td class="px-3 py-3"><span :class="$store.ref.badge(s.orang_tua === 'terhubung' ? 'success' : 'warning')" x-text="s.orang_tua === 'terhubung' ? 'Terhubung' : 'Belum'"></span></td>
                            <td class="px-5 py-2.5">
                                <div class="flex justify-end gap-1.5">
                                    <x-ui.icon-button icon="pencil" label="Ubah data siswa" x-on:click="ubah(s)" />
                                    <x-ui.icon-button icon="eye" label="Lihat detail siswa" x-on:click="lihat(s)" />
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!siswa.length"><td colspan="9" class="px-5 py-10 text-center text-sm text-muted">Tidak ada siswa yang cocok dengan filter.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
            <p class="text-xs text-muted">Kelas asal = kelas administratif siswa pada semester ini</p>
            <x-ui.pager :paginator="$siswa" satuan="siswa" />
        </div>
    </x-ui.card>

    {{-- 2.05 Impor --}}
    <x-ui.impor-modal judul="Impor Data Siswa" satuan="siswa" :hasil="$impor" :format-url="route('data-siswa.format')" />

    {{-- Tambah / Ubah siswa (memakai pola form desain 2.03) --}}
    <x-ui.modal name="siswa" width="max-w-[520px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="editId ? 'Ubah Data Siswa' : 'Tambah Siswa'"></h2>
        <p class="mt-1 text-[13px] text-muted">Akun siswa dibuat otomatis dengan username NIS.</p>
        <form class="mt-4 space-y-4" x-on:submit.prevent="simpan()" novalidate>
            <div class="grid gap-3.5 sm:grid-cols-2">
                <x-ui.field label="NIS" for="sw-nis" required error="error.nis"><x-ui.input id="sw-nis" x-model="form.nis" error="error.nis" inputmode="numeric" maxlength="7" /></x-ui.field>
                <x-ui.field label="NISN" for="sw-nisn" required error="error.nisn"><x-ui.input id="sw-nisn" x-model="form.nisn" error="error.nisn" inputmode="numeric" maxlength="10" /></x-ui.field>
            </div>
            <x-ui.field label="Nama lengkap" for="sw-nama" required error="error.nama"><x-ui.input id="sw-nama" x-model="form.nama" error="error.nama" /></x-ui.field>
            <div class="grid gap-3.5 sm:grid-cols-3">
                <div>
                    <p class="mb-1.5 text-[13px] font-semibold text-ink">L/P</p>
                    <x-ui.segmented :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" model="form.jenis_kelamin" label="Jenis kelamin" size="sm" />
                </div>
                <x-ui.field label="Agama" for="sw-agama"><x-ui.select id="sw-agama" x-model="form.agama" :options="$agama" /></x-ui.field>
                <x-ui.field label="Kelas asal" for="sw-kelas"><x-ui.select id="sw-kelas" x-model="form.kelas" :options="$kelas" /></x-ui.field>
            </div>
            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'siswa')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Detail siswa --}}
    <x-ui.modal name="detail-siswa" width="max-w-[440px]">
        <template x-if="detail">
            <div>
                <div class="flex items-center gap-3 pr-10">
                    <span class="flex size-11 items-center justify-center rounded-full bg-primary text-sm font-bold text-white"
                          x-text="detail.nama.split(' ').slice(0, 2).map((k) => k[0]).join('')"></span>
                    <div>
                        <h2 class="text-lg font-bold text-ink" x-text="detail.nama"></h2>
                        <p class="text-[13px] text-muted" x-text="`Kelas asal ${detail.kelas}`"></p>
                    </div>
                </div>
                <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-[13px]">
                    <div><dt class="text-muted">NIS</dt><dd class="font-semibold text-ink" x-text="detail.nis"></dd></div>
                    <div><dt class="text-muted">NISN</dt><dd class="font-semibold text-ink" x-text="detail.nisn"></dd></div>
                    <div><dt class="text-muted">Jenis kelamin</dt><dd class="font-semibold text-ink" x-text="detail.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'"></dd></div>
                    <div><dt class="text-muted">Agama</dt><dd class="font-semibold text-ink" x-text="detail.agama"></dd></div>
                    <div class="col-span-2"><dt class="text-muted">Orang tua</dt>
                        <dd class="mt-1"><span :class="$store.ref.badge(detail.orang_tua === 'terhubung' ? 'success' : 'warning')" x-text="detail.orang_tua === 'terhubung' ? 'Terhubung' : 'Belum terhubung'"></span></dd></div>
                </dl>
                <div class="mt-5 flex justify-end gap-2.5 border-t border-line-soft pt-4">
                    <x-ui.button variant="secondary" x-show="detail.orang_tua !== 'terhubung'" :href="route('relasi-orang-tua.index', ['modal' => 'hubungkan'])" icon="link">Hubungkan orang tua</x-ui.button>
                    <x-ui.button x-on:click="$dispatch('close-modal', 'detail-siswa')">Tutup</x-ui.button>
                </div>
            </div>
        </template>
    </x-ui.modal>
</div>
@endsection
