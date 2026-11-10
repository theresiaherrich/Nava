@extends('layouts.app')

@section('title', 'Data Kelas & Anggota Kelas')

@section('content')
@vite(['resources/css/data-kelas.css'])

@php
    $semester          = $semester          ?? 'ganjil';
    $tingkat           = $tingkat           ?? 'X';
    $kelasAktif        = $kelasAktif        ?? '';
    $infoKelas         = $infoKelas         ?? null;
    $kelasTingkat      = collect($kelasTingkat ?? []);
    $anggota           = collect($anggota   ?? []);
    $jumlahAktif       = $jumlahAktif       ?? 0;
    $totalAnggota      = $totalAnggota      ?? 0;
    $totalKelasTingkat = $totalKelasTingkat ?? 0;
    $totalSiswaTingkat = $totalSiswaTingkat ?? 0;
    $q                 = $q                 ?? '';
    $page              = $page              ?? 1;
    $lastPage          = $lastPage          ?? 1;
    $opsiSemester      = $opsiSemester      ?? [];
    $opsiTingkat       = $opsiTingkat       ?? [];
    $opsiPindah        = $opsiPindah        ?? [];

    $adaKelas     = $kelasAktif !== '';
    $aksiTambah   = $adaKelas ? route('data-kelas.siswa.store', $kelasAktif) : '#';
    $aktifPindah  = old('_modal') === 'dk-pindah' && $errors->any();
@endphp

<div class="dk-page">

    {{-- HEADER --}}
    <header class="dk-head">
        <div>
            <p class="dk-eyebrow">SMA Negeri 1 Kota Malang · Operator</p>
            <h1 class="dk-title">Data Kelas & Anggota Kelas</h1>
            <p class="dk-subtitle">Kelas asal siswa per semester.</p>
        </div>
    </header>

    @if (session('status'))
        <div class="mk-alert">{{ session('status') }}</div>
    @endif

    {{-- FILTER --}}
    <div class="dk-card dk-filter-card">
        <form method="GET" action="{{ route('data-kelas.index') }}" class="dk-filter" data-filter-form>
            <input type="hidden" name="kelas" value="{{ $kelasAktif }}">
            <x-dropdown name="semester" :value="$semester" :options="$opsiSemester" autosubmit />
            <x-dropdown name="tingkat"  :value="$tingkat"  :options="$opsiTingkat"  autosubmit />
            <span class="dk-summary">{{ $totalKelasTingkat }} kelas · {{ $totalSiswaTingkat }} siswa</span>
        </form>
    </div>

    <div class="dk-grid">

        {{-- DAFTAR KELAS --}}
        <aside class="dk-card dk-kelas-panel">
            <div class="dk-panel-head">
                <div class="dk-panel-head-left">
                    <h2 class="dk-panel-title">Kelas {{ $tingkat }}</h2>
                    <span class="dk-count">{{ $totalKelasTingkat }} kelas</span>
                </div>
                <button type="button" class="dk-btn-sm dk-btn-add" data-mk-open="dk-tambah-kelas">+ Kelas</button>
            </div>

            @forelse ($kelasTingkat as $k)
                <a href="{{ route('data-kelas.index', ['semester' => $semester, 'tingkat' => $tingkat, 'kelas' => $k['kode']]) }}"
                   class="dk-kelas-item {{ $k['kode'] === $kelasAktif ? 'is-active' : '' }}">
                    <span class="dk-kelas-badge">{{ $k['kode'] }}</span>
                    <span class="dk-kelas-body">
                        <span class="dk-kelas-name">Kelas {{ $k['kode'] }}</span>
                        <span class="dk-kelas-wali">Wali: {{ $k['wali_kelas'] ?? '-' }}</span>
                    </span>
                    <span class="dk-kelas-jml">{{ $k['jumlah'] ?? 0 }} siswa</span>
                </a>
            @empty
                <p class="dk-empty-text">Belum ada kelas pada tingkat ini.</p>
            @endforelse
        </aside>

        {{-- ANGGOTA KELAS --}}
        <section class="dk-card dk-table-card">
            <div class="dk-table-head">
                <div>
                    <h2 class="dk-panel-title">Anggota Kelas {{ $kelasAktif !== '' ? $kelasAktif : '-' }}</h2>
                    <p class="dk-note">{{ $jumlahAktif }} siswa · Wali kelas: {{ $infoKelas['wali_kelas'] ?? '-' }}</p>
                </div>

                <div class="dk-head-right">
                    <form method="GET" action="{{ route('data-kelas.index') }}" class="dk-search-form" data-filter-form>
                        <input type="hidden" name="semester" value="{{ $semester }}">
                        <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                        <input type="hidden" name="kelas" value="{{ $kelasAktif }}">
                        <div class="dk-search-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                            </svg>
                            <input type="search" name="q" value="{{ $q }}" placeholder="Cari siswa..." class="dk-search" data-search>
                        </div>
                    </form>

                    <button type="button" class="dk-btn-primary" data-mk-open="dk-tambah" @disabled(! $adaKelas)>+ Tambah Siswa</button>
                </div>
            </div>

            <div class="dk-table-wrap">
                <table class="dk-table">
                    <thead>
                        <tr>
                            <th class="is-center">No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>L/P</th>
                            <th>Agama</th>
                            <th class="is-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($anggota as $i => $s)
                            <tr>
                                <td class="is-center dk-muted">{{ ($page - 1) * 10 + $i + 1 }}</td>
                                <td class="dk-muted">{{ $s['nis'] ?? '-' }}</td>
                                <td class="dk-bold">{{ $s['nama'] ?? '-' }}</td>
                                <td>{{ $s['lp'] ?? '-' }}</td>
                                <td>{{ $s['agama'] ?? '-' }}</td>
                                <td class="is-right">
                                    <button type="button" class="dk-btn-sm"
                                            data-pindah
                                            data-id="{{ $s['id'] ?? '' }}"
                                            data-nama="{{ $s['nama'] ?? '' }}"
                                            data-nis="{{ $s['nis'] ?? '' }}"
                                            data-kelas="{{ $kelasAktif }}">Pindahkan</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="dk-empty">Belum ada anggota kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="dk-foot">
                <span class="dk-note">ⓘ Satu siswa hanya memiliki satu kelas asal per semester.</span>

                <div class="dk-foot-right">
                    <span class="dk-note">{{ $anggota->count() }} dari {{ $totalAnggota }} siswa</span>

                    @if ($lastPage > 1)
                        <nav class="dk-pager">
                            <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                               class="dk-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">‹</a>
                            @for ($i = 1; $i <= $lastPage; $i++)
                                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                                   class="dk-page-btn {{ $i === $page ? 'is-active' : '' }}">{{ $i }}</a>
                            @endfor
                            <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                               class="dk-page-btn {{ $page >= $lastPage ? 'is-disabled' : '' }}">›</a>
                        </nav>
                    @endif
                </div>
            </div>
        </section>
    </div>

</div>

{{-- MODAL TAMBAH KELAS --}}
@include('components.modal-tambah', [
    'id'     => 'dk-tambah-kelas',
    'judul'  => 'Tambah Kelas',
    'aksi'   => route('data-kelas.kelas.store'),
    'fields' => [
        ['name' => 'kode',       'label' => 'Kode kelas',      'required' => true, 'full' => true, 'placeholder' => 'contoh: X-K'],
        ['name' => 'tingkat',    'label' => 'Tingkat', 'type' => 'select', 'required' => true,
         'options' => ['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII']],
        ['name' => 'wali_kelas', 'label' => 'Wali kelas (opsional)', 'placeholder' => 'Nama wali kelas'],
    ],
])

{{-- MODAL TAMBAH SISWA KE KELAS --}}
@include('components.modal-tambah', [
    'id'     => 'dk-tambah',
    'judul'  => 'Tambah Siswa ke Kelas ' . $kelasAktif,
    'aksi'   => $aksiTambah,
    'fields' => [
        ['name' => 'nis', 'label' => 'NIS siswa', 'required' => true, 'full' => true, 'placeholder' => 'contoh: 2526001'],
    ],
])

{{-- MODAL PINDAHKAN SISWA --}}
<div id="dk-pindah" class="mk-overlay" @if ($aktifPindah) data-auto-open @endif>
    <div class="mk-box" role="dialog" aria-modal="true" aria-labelledby="dk-pindah-judul">
        <div class="mk-head">
            <div>
                <h3 id="dk-pindah-judul" class="mk-title">Pindahkan Siswa</h3>
                <p class="mk-desc">Kelas asal baru berlaku untuk semester Ganjil 2026/2027.</p>
            </div>
            <button type="button" class="mk-close" data-mk-close aria-label="Tutup">×</button>
        </div>

        <form method="POST" action="{{ route('data-kelas.pindah') }}">
            @csrf
            <input type="hidden" name="_modal" value="dk-pindah">
            <input type="hidden" name="siswa_id" value="{{ old('siswa_id') }}" data-f-id>
            <input type="hidden" name="kelas_asal" value="{{ old('kelas_asal', $kelasAktif) }}" data-f-kelas>
            <input type="hidden" name="siswa_nama" value="{{ old('siswa_nama') }}" data-f-nama>
            <input type="hidden" name="siswa_nis" value="{{ old('siswa_nis') }}" data-f-nis>

            <div class="dk-siswa-chip">
                <span class="dk-avatar" data-t-inisial>{{ mb_strtoupper(mb_substr(old('siswa_nama', '-'), 0, 2)) }}</span>
                <div>
                    <p class="dk-siswa-name" data-t-nama>{{ old('siswa_nama', '-') }}</p>
                    <p class="dk-note">NIS <span data-t-nis>{{ old('siswa_nis', '-') }}</span> · saat ini di <b data-t-kelas>{{ old('kelas_asal', $kelasAktif ?: '-') }}</b></p>
                </div>
            </div>

            <div class="mk-field">
                <label>Pindahkan ke kelas <span style="color:#dc2626">*</span></label>
                <div class="dk-full">
                    <x-dropdown name="kelas_tujuan" :value="old('kelas_tujuan', '')" :options="$opsiPindah" />
                </div>
                @error('kelas_tujuan')
                    <p class="mk-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mk-field">
                <label for="dk-alasan">Alasan (opsional)</label>
                <textarea id="dk-alasan" name="alasan" class="dk-textarea" placeholder="Contoh: penyesuaian jumlah siswa per kelas">{{ old('alasan') }}</textarea>
            </div>

            <div class="dk-info">
                <span>ⓘ</span>
                <span>Nilai yang sudah dikunci tetap tersimpan di rapor kelas lama.</span>
            </div>

            <div class="mk-foot">
                <button type="button" class="mk-btn" data-mk-close>Batal</button>
                <button type="submit" class="mk-btn-primary">Pindahkan</button>
            </div>
        </form>
    </div>
</div>

@include('components.modal-kit')

<script>
(function () {
    var modal = document.getElementById('dk-pindah');

    function inisial(nama) {
        return nama.split(' ').filter(Boolean).slice(0, 2).map(function (p) {
            return p.charAt(0).toUpperCase();
        }).join('');
    }

    // Tombol Pindahkan di tabel
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-pindah]');
        if (!btn || !modal) return;

        var d = btn.dataset;
        modal.querySelector('[data-f-id]').value = d.id;
        modal.querySelector('[data-f-kelas]').value = d.kelas;
        modal.querySelector('[data-f-nama]').value = d.nama;
        modal.querySelector('[data-f-nis]').value = d.nis;

        modal.querySelector('[data-t-inisial]').textContent = inisial(d.nama);
        modal.querySelector('[data-t-nama]').textContent = d.nama;
        modal.querySelector('[data-t-nis]').textContent = d.nis;
        modal.querySelector('[data-t-kelas]').textContent = d.kelas;

        // Reset dropdown dan alasan
        modal.querySelector('[data-dd-input]').value = '';
        modal.querySelector('.dd-text').textContent = 'Pilih kelas tujuan';
        modal.querySelectorAll('.dd-item').forEach(function (it) {
            var on = it.dataset.value === '';
            it.classList.toggle('is-selected', on);
            it.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        modal.querySelector('#dk-alasan').value = '';

        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    });

    // Pencarian dikirim saat Enter atau tombol hapus (×)
    var cari = document.querySelector('[data-search]');
    if (cari) {
        cari.addEventListener('search', function () {
            cari.form.submit();
        });
    }
})();
</script>
@endsection