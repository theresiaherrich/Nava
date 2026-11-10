@extends('layouts.app')

@section('title', 'Relasi Orang Tua – Anak')

@section('content')
@vite(['resources/css/relasi-orang-tua.css'])

@php
    $rows           = collect($rows ?? []);
    $page           = $page ?? 1;
    $lastPage       = $lastPage ?? 1;
    $totalFiltered  = $totalFiltered ?? 0;
    $status         = $status ?? 'semua';
    $hubungan       = $hubungan ?? 'semua';
    $q              = $q ?? '';
    $stats          = $stats ?? ['ortu_terhubung' => 0, 'belum_terhubung' => 0];
    $opsiStatus     = $opsiStatus ?? [];
    $opsiHubungan   = $opsiHubungan ?? [];
    $akunOrtu       = collect($akunOrtu ?? []);
    $siswaList      = collect($siswaList ?? []);

    $labelHubungan  = ['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'];
    $editId         = (string) old('_edit_id', '');
    $aktifHubung    = old('_modal') === 'rot-hubung' && $errors->any();
    $akunTerpilih   = (string) old('akun_ortu_id', '');
    $akunNama       = $akunTerpilih !== '' ? ($akunOrtu->firstWhere('id', $akunTerpilih)['nama'] ?? '') : '';
    $idTerpilih     = array_map('strval', (array) old('siswa_ids', []));
@endphp

<div class="ro-page">

    {{-- HEADER --}}
    <header class="ro-head">
        <div>
            <p class="ro-eyebrow">SMA Negeri 1 Kota Malang · Operator</p>
            <h1 class="ro-title">Relasi Orang Tua – Anak</h1>
            <p class="ro-subtitle">Hubungkan akun orang tua dengan anaknya agar dapat melihat rapor yang sudah terbit.</p>
        </div>

        <button type="button" class="ro-btn-primary" data-hubung-baru data-mk-open="rot-hubung">+ Hubungkan</button>
    </header>

    @if (session('status'))
        <div class="mk-alert">{{ session('status') }}</div>
    @endif

    {{-- PERINGATAN SISWA BELUM TERHUBUNG --}}
    <div class="ro-alert {{ $stats['belum_terhubung'] > 0 ? '' : 'is-ok' }}">
        <span class="ro-alert-icon">{{ $stats['belum_terhubung'] > 0 ? '!' : '✓' }}</span>
        <div class="ro-alert-body">
            @if ($stats['belum_terhubung'] > 0)
                <p class="ro-alert-title">{{ $stats['belum_terhubung'] }} siswa belum memiliki akun orang tua terhubung.</p>
                <p class="ro-alert-text">Orang tua siswa tersebut belum dapat melihat rapor di portal (A4).</p>
            @else
                <p class="ro-alert-title">Semua siswa sudah memiliki akun orang tua terhubung.</p>
            @endif
        </div>
        <button type="button" class="ro-link" data-hubung-baru data-mk-open="rot-hubung">Lihat daftar →</button>
    </div>

    {{-- FILTER --}}
    <div class="ro-card ro-filter-card">
        <form method="GET" action="{{ route('relasi-orang-tua.index') }}" class="ro-filter">
            <x-dropdown name="status"   :value="$status"   :options="$opsiStatus"   autosubmit />
            <x-dropdown name="hubungan" :value="$hubungan" :options="$opsiHubungan" autosubmit />

            <div class="ro-search-wrap ro-search-main">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari orang tua atau siswa..." class="ro-input" data-search>
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <section class="ro-card ro-table-card">
        <div class="ro-table-head">
            <h2 class="ro-panel-title">Daftar Relasi</h2>
            <p class="ro-note">{{ $stats['ortu_terhubung'] }} orang tua terhubung</p>
        </div>

        <div class="ro-table-wrap">
            <table class="ro-table">
                <thead>
                    <tr>
                        <th>Nama Orang Tua/Wali</th>
                        <th>Username</th>
                        <th>Hubungan</th>
                        <th>Anak Terhubung</th>
                        <th>Status</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        @php
                            $anakIds = collect($r['anak'] ?? [])->pluck('id')->map(fn ($v) => (string) $v)->values()->all();
                        @endphp
                        <tr>
                            <td class="ro-bold ro-nowrap">{{ $r['nama'] ?? '-' }}</td>
                            <td class="ro-muted ro-nowrap">{{ $r['username'] ?? '-' }}</td>
                            <td class="ro-nowrap">{{ $labelHubungan[$r['hubungan'] ?? ''] ?? '-' }}</td>
                            <td>
                                <div class="ro-chips">
                                    @forelse ($r['anak'] ?? [] as $a)
                                        <span class="ro-chip-anak">{{ $a['nama'] ?? '-' }} · {{ $a['kelas'] ?? '-' }}</span>
                                    @empty
                                        <span class="ro-muted">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="ro-nowrap">
                                @if (($r['status'] ?? '') === 'terverifikasi')
                                    <span class="ro-chip ok">Terverifikasi</span>
                                @else
                                    <span class="ro-chip warn">Menunggu</span>
                                @endif
                            </td>
                            <td class="is-right ro-nowrap">
                                <div class="ro-actions">
                                    <button type="button" class="ro-icon-btn" aria-label="Ubah" title="Ubah"
                                            data-edit data-mk-open="rot-hubung"
                                            data-ortu-id="{{ $r['id'] }}"
                                            data-nama="{{ $r['nama'] ?? '' }}"
                                            data-hubungan="{{ $r['hubungan'] ?? 'ayah' }}"
                                            data-anak="{{ json_encode($anakIds) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    </button>

                                    <form method="POST" action="{{ route('relasi-orang-tua.destroy', $r['id']) }}"
                                          onsubmit="return confirm('Hapus semua relasi anak untuk {{ addslashes($r['nama'] ?? '') }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ro-icon-btn is-danger" aria-label="Hapus" title="Hapus">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="ro-empty">Belum ada relasi orang tua–anak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ro-foot">
            <span class="ro-note">ⓘ Satu akun orang tua dapat terhubung ke lebih dari satu anak.</span>

            <div class="ro-foot-right">
                <span class="ro-note">{{ $rows->count() }} dari {{ $totalFiltered }} relasi</span>

                @if ($lastPage > 1)
                    <nav class="ro-pager">
                        <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                           class="ro-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">‹</a>
                        @for ($i = 1; $i <= $lastPage; $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                               class="ro-page-btn {{ $i === $page ? 'is-active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                           class="ro-page-btn {{ $page >= $lastPage ? 'is-disabled' : '' }}">›</a>
                    </nav>
                @endif
            </div>
        </div>
    </section>

</div>

{{-- MODAL HUBUNGKAN / UBAH RELASI --}}
<div id="rot-hubung" class="mk-overlay" @if ($aktifHubung) data-auto-open @endif>
    <div class="mk-box ro-modal" role="dialog" aria-modal="true" aria-labelledby="rot-judul">
        <div class="mk-head">
            <div>
                <h3 id="rot-judul" class="mk-title">{{ $editId !== '' ? 'Ubah Relasi Orang Tua' : 'Hubungkan Orang Tua ke Siswa' }}</h3>
            </div>
            <button type="button" class="mk-close" data-mk-close aria-label="Tutup">×</button>
        </div>

        <form method="POST"
              action="{{ $editId !== '' ? route('relasi-orang-tua.update', $editId) : route('relasi-orang-tua.store') }}"
              data-form-hubung
              data-store-url="{{ route('relasi-orang-tua.store') }}"
              data-update-url="{{ route('relasi-orang-tua.update', '__ID__') }}">
            @csrf
            <input type="hidden" name="_modal" value="rot-hubung">
            <input type="hidden" name="_edit_id" value="{{ $editId }}" data-edit-id>
            <input type="hidden" name="_method" value="PUT" data-method @disabled($editId === '')>

            {{-- AKUN ORANG TUA --}}
            <div class="mk-field">
                <label>Akun orang tua <span class="ro-req">*</span></label>
                <div class="ro-combo" data-combo>
                    <input type="hidden" name="akun_ortu_id" value="{{ $akunTerpilih }}" data-combo-input>

                    <button type="button" class="ro-combo-trigger {{ $errors->has('akun_ortu_id') ? 'is-error' : '' }}"
                            data-combo-trigger @disabled($editId !== '')>
                        <span class="ro-combo-text" data-combo-text>{{ $akunNama !== '' ? $akunNama : 'Pilih akun orang tua' }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="ro-chev"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div class="ro-combo-panel" data-combo-panel hidden>
                        <div class="ro-search-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                            </svg>
                            <input type="search" class="ro-input" placeholder="Cari nama atau username..." data-combo-search>
                        </div>

                        <div class="ro-combo-list">
                            @foreach ($akunOrtu as $a)
                                <button type="button" class="ro-combo-item {{ (string) $a['id'] === $akunTerpilih ? 'is-selected' : '' }}"
                                        data-combo-item
                                        data-value="{{ $a['id'] }}"
                                        data-label="{{ $a['nama'] ?? '' }}"
                                        data-text="{{ mb_strtolower(($a['nama'] ?? '') . ' ' . ($a['username'] ?? '')) }}">
                                    <span>{{ $a['nama'] ?? '-' }}</span>
                                    <span class="ro-muted">{{ $a['username'] ?? '' }}</span>
                                </button>
                            @endforeach

                            <p class="ro-combo-empty" @if ($akunOrtu->isNotEmpty()) hidden @endif>Belum ada akun orang tua.</p>
                            <p class="ro-combo-empty" data-combo-nomatch hidden>Tidak ada yang cocok.</p>
                        </div>
                    </div>
                </div>
                @error('akun_ortu_id')
                    <p class="mk-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- PILIH ANAK --}}
            <div class="mk-field">
                <label>Pilih anak <span class="ro-req">*</span></label>

                <div class="ro-search-wrap ro-search-block">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                    <input type="search" class="ro-input" placeholder="Cari nama siswa atau NIS..." data-anak-search>
                </div>

                <div class="ro-anak-list">
                    @forelse ($siswaList as $s)
                        @php $checked = in_array((string) $s['id'], $idTerpilih, true); @endphp
                        <label class="ro-anak-item {{ $checked ? 'is-selected' : '' }}"
                               data-anak-item
                               data-text="{{ mb_strtolower(($s['nama'] ?? '') . ' ' . ($s['nis'] ?? '')) }}">
                            <input type="checkbox" name="siswa_ids[]" value="{{ $s['id'] }}" @checked($checked)>
                            <span class="ro-anak-body">
                                <span class="ro-anak-name">{{ $s['nama'] ?? '-' }}</span>
                                <span class="ro-anak-nis">NIS {{ $s['nis'] ?? '-' }} · {{ $s['kelas'] ?? '-' }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="ro-combo-empty">Belum ada data siswa.</p>
                    @endforelse
                    <p class="ro-combo-empty" data-anak-nomatch hidden>Tidak ada siswa yang cocok.</p>
                </div>
                @error('siswa_ids')
                    <p class="mk-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- HUBUNGAN --}}
            <div class="mk-field">
                <span class="ro-label">Hubungan</span>
                <div class="ro-seg">
                    @foreach (['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'] as $val => $lbl)
                        <label class="ro-seg-item">
                            <input type="radio" name="hubungan" value="{{ $val }}" @checked(old('hubungan', 'ayah') === $val)>
                            <span>{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="ro-note">ⓘ Orang tua hanya dapat melihat rapor yang sudah diterbitkan.</p>
                @error('hubungan')
                    <p class="mk-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mk-foot">
                <button type="button" class="mk-btn" data-mk-close>Batal</button>
                <button type="submit" class="mk-btn-primary">{{ $editId !== '' ? 'Simpan Perubahan' : 'Hubungkan' }}</button>
            </div>
        </form>
    </div>
</div>

@include('components.modal-kit')

<script>
(function () {
    var filterCari = document.querySelector('.ro-filter [data-search]');
    if (filterCari) {
        filterCari.addEventListener('search', function () { filterCari.form.submit(); });
    }

    var form  = document.querySelector('[data-form-hubung]');
    var modal = document.getElementById('rot-hubung');
    if (!form || !modal) return;

    var judul      = document.getElementById('rot-judul');
    var metodeEl   = form.querySelector('[data-method]');
    var editEl     = form.querySelector('[data-edit-id]');
    var submitBtn  = form.querySelector('button[type="submit"].mk-btn-primary');
    var combo      = modal.querySelector('[data-combo]');
    var comboIn    = combo.querySelector('[data-combo-input]');
    var comboTxt   = combo.querySelector('[data-combo-text]');
    var comboTrig  = combo.querySelector('[data-combo-trigger]');
    var comboPanel = combo.querySelector('[data-combo-panel]');
    var comboCari  = combo.querySelector('[data-combo-search]');
    var comboItems = combo.querySelectorAll('[data-combo-item]');
    var comboNo    = combo.querySelector('[data-combo-nomatch]');
    var anakCari   = modal.querySelector('[data-anak-search]');
    var anakItems  = modal.querySelectorAll('[data-anak-item]');
    var anakNo     = modal.querySelector('[data-anak-nomatch]');
    var storeUrl   = form.dataset.storeUrl;
    var updateUrl  = form.dataset.updateUrl;

    function cocokkan(input, items, noEl) {
        var q = input ? input.value.trim().toLowerCase() : '';
        var n = 0;
        items.forEach(function (it) {
            var ok = it.dataset.text.indexOf(q) !== -1;
            it.hidden = !ok;
            if (ok) n++;
        });
        if (noEl) noEl.hidden = n > 0 || items.length === 0;
    }

    function tutupCombo() { comboPanel.hidden = true; }

    comboTrig.addEventListener('click', function () {
        var buka = comboPanel.hidden;
        comboPanel.hidden = !buka;
        if (buka) {
            comboCari.value = '';
            cocokkan(comboCari, comboItems, comboNo);
            comboCari.focus();
        }
    });

    comboCari.addEventListener('input', function () { cocokkan(comboCari, comboItems, comboNo); });

    comboItems.forEach(function (it) {
        it.addEventListener('click', function () {
            comboIn.value = it.dataset.value;
            comboTxt.textContent = it.dataset.label;
            comboItems.forEach(function (x) { x.classList.toggle('is-selected', x === it); });
            tutupCombo();
        });
    });

    document.addEventListener('click', function (e) {
        if (!combo.contains(e.target)) tutupCombo();
    });

    if (anakCari) {
        anakCari.addEventListener('input', function () { cocokkan(anakCari, anakItems, anakNo); });
    }

    anakItems.forEach(function (lbl) {
        var cb = lbl.querySelector('input[type="checkbox"]');
        cb.addEventListener('change', function () { lbl.classList.toggle('is-selected', cb.checked); });
    });

    // Enter di kolom cari tidak boleh mengirim form
    [comboCari, anakCari].forEach(function (el) {
        if (!el) return;
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    });

    function setAkun(id, nama) {
        comboIn.value = id;
        comboTxt.textContent = nama || 'Pilih akun orang tua';
        comboItems.forEach(function (x) {
            x.classList.toggle('is-selected', x.dataset.value === String(id));
        });
    }

    function setAnak(ids) {
        anakItems.forEach(function (lbl) {
            var cb = lbl.querySelector('input[type="checkbox"]');
            cb.checked = ids.indexOf(cb.value) !== -1;
            lbl.classList.toggle('is-selected', cb.checked);
        });
    }

    function setHubungan(v) {
        var r = form.querySelector('input[name="hubungan"][value="' + v + '"]');
        if (r) r.checked = true;
    }

    function bersihkanCari() {
        if (anakCari) {
            anakCari.value = '';
            cocokkan(anakCari, anakItems, anakNo);
        }
        comboCari.value = '';
        cocokkan(comboCari, comboItems, comboNo);
        tutupCombo();
    }

    // Mode tambah
    document.querySelectorAll('[data-hubung-baru]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            judul.textContent = 'Hubungkan Orang Tua ke Siswa';
            form.action = storeUrl;
            metodeEl.disabled = true;
            editEl.value = '';
            submitBtn.textContent = 'Hubungkan';
            comboTrig.disabled = false;
            setAkun('', '');
            setAnak([]);
            setHubungan('ayah');
            bersihkanCari();
        });
    });

    // Mode ubah
    document.querySelectorAll('[data-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = btn.dataset;
            var anak = [];
            try { anak = JSON.parse(d.anak || '[]'); } catch (err) { anak = []; }

            judul.textContent = 'Ubah Relasi Orang Tua';
            form.action = updateUrl.replace('__ID__', encodeURIComponent(d.ortuId));
            metodeEl.disabled = false;
            metodeEl.value = 'PUT';
            editEl.value = d.ortuId;
            submitBtn.textContent = 'Simpan Perubahan';
            comboTrig.disabled = true;
            setAkun(d.ortuId, d.nama);
            setAnak(anak.map(String));
            setHubungan(d.hubungan || 'ayah');
            bersihkanCari();
        });
    });
})();
</script>
@endsection