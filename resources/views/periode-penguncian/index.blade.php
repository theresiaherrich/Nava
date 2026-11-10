@extends('layouts.app')
 
@section('title', 'Periode & Penguncian')
 
@section('content')
@vite(['resources/css/periode-penguncian.css', 'resources/js/periode-penguncian.js'])
 
@php
    $namaSekolah   = 'SMA Negeri 1 Kota Malang';
    $semesterTabs  = $semesterTabs  ?? [];
    $semesterAktif = $semesterAktif ?? 0;
    $tingkatData   = $tingkatData   ?? [];
    $tingkatAktif  = $tingkatAktif  ?? array_key_first($tingkatData);
    $mapelList     = $mapelList     ?? [];
 
    $sts = $sts ?? ['judul' => '-', 'tanggal' => '-', 'statusLabel' => '-'];
    $sas = $sas ?? [
        'judul'            => '-',
        'tanggal'          => '-',
        'statusLabel'      => '-',
        'tanggalBuka'      => '-',
        'bulanBuka'        => '',
        'hariSampaiDibuka' => 0,
    ];
 
    // Kalau tingkat dari URL tidak ada di data, pakai tingkat pertama
    if (! array_key_exists($tingkatAktif, $tingkatData)) {
        $tingkatAktif = array_key_first($tingkatData);
    }
 
    // Label periode untuk modal, mis. "STS Ganjil" dan "STS Ganjil 2026/2027"
    $semesterLabel  = $semesterTabs[$semesterAktif] ?? '';
    $jenisSemester  = str_contains(strtolower($semesterLabel), 'genap') ? 'Genap' : 'Ganjil';
    $tahunAjaran    = trim(preg_replace('/^Semester\s+\S+\s*/i', '', $semesterLabel));
    $periodeLabel   = 'STS ' . $jenisSemester;
    $periodeLengkap = trim($periodeLabel . ' ' . $tahunAjaran);
 
    // Status per kelas (harus sama dengan STATUS di periode-penguncian.js)
    $statusMap = [
        'siap'     => ['label' => 'Siap dikunci',         'class' => 'pk-status-siap'],
        'terkunci' => ['label' => 'Terkunci',             'class' => 'pk-status-kunci'],
        'menunggu' => ['label' => 'Belum lengkap',        'class' => 'pk-status-menunggu'],
        'koreksi'  => ['label' => 'Dibuka untuk koreksi', 'class' => 'pk-status-koreksi'],
    ];
 
    $iconLock   = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>';
    $iconUnlock = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>';
@endphp
 
<div class="pk-page"
     data-pk-page
     data-periode-label="{{ $periodeLabel }}"
     data-periode-lengkap="{{ $periodeLengkap }}">
 
    <header class="pk-header">
        <div>
            <p class="pk-eyebrow">{{ $namaSekolah }}</p>
            <h1 class="pk-title">Periode &amp; Penguncian</h1>
            <p class="pk-desc">Atur jadwal pengisian dan kunci nilai sebelum rapor diterbitkan.</p>
        </div>
        <button type="button" class="pk-btn-outline" id="pk-btn-tambah">
            <span aria-hidden="true">+</span> Tambah periode
        </button>
    </header>
 
    {{-- Tab semester --}}
    <nav class="pk-semester-tabs" role="tablist" aria-label="Pilih semester">
        @forelse ($semesterTabs as $i => $semester)
            <button type="button"
                    role="tab"
                    class="pk-semester-tab{{ $i === $semesterAktif ? ' is-active' : '' }}"
                    aria-selected="{{ $i === $semesterAktif ? 'true' : 'false' }}"
                    data-semester="{{ $i }}">{{ $semester }}</button>
        @empty
            <span class="pk-desc">Belum ada semester.</span>
        @endforelse
    </nav>
 
    <div class="pk-grid">
 
        {{-- Card STS --}}
        <section class="pk-card">
            <div class="pk-card-head">
                <span class="pk-icon-box" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="17" rx="2"/>
                        <path d="M3 9h18M8 2v4M16 2v4"/>
                    </svg>
                </span>
                <div class="pk-card-head-text">
                    <h2 class="pk-card-title">{{ $sts['judul'] }}</h2>
                    <p class="pk-card-sub">{{ $sts['tanggal'] }}</p>
                </div>
                <span class="pk-badge pk-badge-open">{{ $sts['statusLabel'] }}</span>
            </div>
 
            {{-- Pilihan tingkat kelas --}}
            <div class="pk-tingkat-tabs" role="tablist" aria-label="Pilih tingkat kelas">
                @foreach ($tingkatData as $tingkat => $data)
                    <button type="button"
                            role="tab"
                            class="pk-tingkat-tab{{ $tingkat === $tingkatAktif ? ' is-active' : '' }}"
                            aria-selected="{{ $tingkat === $tingkatAktif ? 'true' : 'false' }}"
                            data-tingkat="{{ $tingkat }}">Kelas {{ $tingkat }}</button>
                @endforeach
            </div>
 
            {{-- Panel per tingkat. Hanya satu yang tampil. --}}
            @forelse ($tingkatData as $tingkat => $data)
                <div class="pk-panel"
                     data-tingkat-panel="{{ $tingkat }}"
                     @if ($tingkat !== $tingkatAktif) hidden @endif>
                    <div class="pk-panel-head">
                        <span>Status per kelas</span>
                        <strong>{{ $data['keseluruhan'] }}% keseluruhan</strong>
                    </div>
 
                    <div class="pk-table-wrap">
                        <table class="pk-table">
                            <thead>
                                <tr>
                                    <th scope="col">Kelas</th>
                                    <th scope="col">Wali Kelas</th>
                                    <th scope="col">Kelengkapan</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="pk-th-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['kelas'] as $k)
                                    @php
                                        $statusKey = $k['status'] ?? ($k['persen'] === 100 ? 'siap' : 'menunggu');
                                        $st        = $statusMap[$statusKey] ?? $statusMap['menunggu'];
                                        $progress  = isset($k['terisi'], $k['total'])
                                            ? $k['terisi'] . '/' . $k['total']
                                            : $k['persen'] . '%';
                                    @endphp
                                    <tr data-kelas-row
                                        data-kelas="{{ $k['kode'] }}"
                                        data-status="{{ $statusKey }}"
                                        data-dikunci="{{ $k['dikunci'] ?? '' }}">
                                        <td class="pk-kelas-kode">{{ $k['kode'] }}</td>
                                        <td>{{ $k['wali'] ?? '-' }}</td>
                                        <td>
                                            <div class="pk-progress">
                                                <span class="pk-track">
                                                    <span class="pk-fill" data-percent="{{ $k['persen'] }}"></span>
                                                </span>
                                                <span class="pk-progress-text">{{ $progress }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="pk-status {{ $st['class'] }}" data-status-badge>{{ $st['label'] }}</span>
                                            @if (! empty($k['catatan']))
                                                <span class="pk-status-note" data-status-note>{{ $k['catatan'] }}</span>
                                            @endif
                                        </td>
                                        <td class="pk-td-aksi" data-aksi>
                                            @if ($statusKey === 'siap')
                                                <button type="button" class="pk-btn-primary pk-btn-sm" data-action="kunci">
                                                    {!! $iconLock !!} Kunci
                                                </button>
                                            @elseif ($statusKey === 'terkunci')
                                                <button type="button" class="pk-btn-outline pk-btn-sm" data-action="koreksi">
                                                    {!! $iconUnlock !!} Buka koreksi
                                                </button>
                                            @else
                                                <span class="pk-aksi-text">menunggu guru</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="pk-panel">
                    <p class="pk-desc">Belum ada data kelas.</p>
                </div>
            @endforelse
 
            <footer class="pk-card-foot">
                <p class="pk-foot-note">
                    {!! $iconLock !!}
                    Penguncian tidak otomatis menerbitkan rapor.
                </p>
                <button type="button" class="pk-btn-primary" id="pk-btn-kunci-semua" disabled>
                    {!! $iconLock !!}
                    <span id="pk-label-kunci">Kunci semua yang siap</span>
                </button>
            </footer>
        </section>
 
        {{-- Card SAS --}}
        <section class="pk-card">
            <div class="pk-card-head">
                <span class="pk-icon-box pk-icon-sas" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="17" rx="2"/>
                        <path d="M3 9h18M8 2v4M16 2v4"/>
                    </svg>
                </span>
                <div class="pk-card-head-text">
                    <h2 class="pk-card-title">{{ $sas['judul'] }}</h2>
                    <p class="pk-card-sub">{{ $sas['tanggal'] }}</p>
                </div>
                <span class="pk-badge pk-badge-muted">{{ $sas['statusLabel'] }}</span>
            </div>
 
            <div class="pk-sas-body">
                <div class="pk-date-box" aria-hidden="true">
                    <span class="pk-date-day">{{ $sas['tanggalBuka'] }}</span>
                    <span class="pk-date-month">{{ $sas['bulanBuka'] }}</span>
                </div>
                <div>
                    <p class="pk-sas-title">Dibuka dalam {{ $sas['hariSampaiDibuka'] }} hari</p>
                    <p class="pk-sas-desc">Guru dapat mulai mengisi nilai SAS sesuai jadwal yang ditentukan.</p>
                </div>
            </div>
 
            <div class="pk-sas-foot">
                <button type="button" class="pk-btn-outline pk-btn-block" id="pk-btn-jadwal">Ubah jadwal</button>
            </div>
        </section>
    </div>
 
    <p class="pk-feedback" id="pk-feedback" role="status"></p>
 
    {{-- ============ MODAL: KONFIRMASI KUNCI NILAI ============ --}}
    <dialog class="pk-modal" id="pk-modal-kunci" aria-labelledby="pk-modal-kunci-title">
        <form method="dialog" class="pk-modal-box">
            <button type="submit" value="batal" class="pk-modal-close" aria-label="Tutup">&times;</button>
 
            <span class="pk-modal-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            </span>
 
            <h2 class="pk-modal-title" id="pk-modal-kunci-title">
                Kunci nilai <span data-kunci-jumlah>0</span> kelas?
            </h2>
            <p class="pk-modal-text">
                Pastikan seluruh nilai telah diverifikasi. Setelah dikunci, perubahan hanya dapat
                dilakukan oleh Admin Kurikulum melalui koreksi beralasan.
            </p>
 
            <dl class="pk-modal-info">
                <div>
                    <dt>Kelas</dt>
                    <dd data-kunci-kelas>-</dd>
                </div>
                <div>
                    <dt>Kelengkapan</dt>
                    <dd class="pk-text-ok">100% nilai disetujui</dd>
                </div>
            </dl>
 
            <div class="pk-modal-actions">
                <button type="submit" value="batal" class="pk-btn-outline">Batal</button>
                <button type="submit" value="kunci" class="pk-btn-primary">
                    {!! $iconLock !!} Ya, kunci nilai
                </button>
            </div>
        </form>
    </dialog>
 
    {{-- ============ MODAL: BUKA KOREKSI NILAI ============ --}}
    <dialog class="pk-modal pk-modal-lg" id="pk-modal-koreksi" aria-labelledby="pk-modal-koreksi-title">
        <form method="dialog" class="pk-modal-box" novalidate>
            <button type="submit" value="batal" class="pk-modal-close" aria-label="Tutup">&times;</button>
 
            <span class="pk-modal-icon pk-modal-icon-koreksi" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>
            </span>
 
            <h2 class="pk-modal-title" id="pk-modal-koreksi-title">Buka koreksi nilai</h2>
            <p class="pk-modal-pill" data-koreksi-info>-</p>
 
            <div class="pk-field">
                <label class="pk-label" for="pk-mapel-select">
                    Mata pelajaran yang dikoreksi <span class="pk-req" aria-hidden="true">*</span>
                </label>
                <div class="pk-chips" data-mapel-chips>
                    <select id="pk-mapel-select" class="pk-chip-select">
                        <option value="">Tambah mapel…</option>
                        @foreach ($mapelList as $mapel)
                            <option value="{{ $mapel }}">{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
 
            <div class="pk-field">
                <label class="pk-label" for="pk-alasan">
                    Alasan koreksi <span class="pk-req" aria-hidden="true">*</span>
                </label>
                <textarea id="pk-alasan"
                          class="pk-input"
                          rows="3"
                          placeholder="Jelaskan kesalahan yang dikoreksi…"
                          aria-describedby="pk-alasan-error"
                          required></textarea>
                <p class="pk-error" id="pk-alasan-error" hidden>
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    Alasan koreksi wajib diisi.
                </p>
            </div>
 
            <div class="pk-field">
                <label class="pk-label" for="pk-batas">Batas waktu koreksi</label>
                <input type="date" id="pk-batas" class="pk-input">
            </div>
 
            <div class="pk-alert">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3 2 20h20L12 3z"/><path d="M12 10v4M12 17h.01"/></svg>
                <span>Koreksi akan membuat versi rapor baru. Rapor versi lama tetap tersimpan di arsip.</span>
            </div>
 
            <div class="pk-modal-actions">
                <button type="submit" value="batal" class="pk-btn-outline">Batal</button>
                <button type="submit" value="buka" class="pk-btn-primary" id="pk-btn-buka-koreksi" disabled>
                    {!! $iconUnlock !!} Buka Koreksi
                </button>
            </div>
        </form>
    </dialog>
</div>
@endsection
 