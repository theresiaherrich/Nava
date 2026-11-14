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
    $periodeList   = $periodeList   ?? [];
    $hariIni       = $hariIni       ?? now()->toDateString();

    $sts = $sts ?? ['judul' => '-', 'tanggal' => '-', 'statusLabel' => '-'];
    $sas = $sas ?? [
        'judul'            => '-',
        'tanggal'          => '-',
        'statusLabel'      => '-',
        'tanggalBuka'      => '-',
        'bulanBuka'        => '',
        'hariSampaiDibuka' => 0,
    ];

    // Kalau controller belum mengirim data per semester, pakai key lama untuk semester aktif
    $semesterData = $semesterData ?? [
        $semesterAktif => [
            'tingkatData'  => $tingkatData,
            'tingkatAktif' => $tingkatAktif,
            'adaPeriode'   => true,
            'sts'          => $sts,
            'sas'          => $sas,
        ],
    ];

    // Semua tingkat yang ada (untuk pilihan di modal Tambah periode)
    $daftarTingkat = collect($semesterData)->flatMap(fn ($s) => array_keys($s['tingkatData'] ?? []))->unique()->values();
    if ($daftarTingkat->isEmpty()) {
        $daftarTingkat = collect(['X', 'XI', 'XII']);
    }

    // Data untuk periode-penguncian.js
    $dataJs = [
        'semesterTabs' => array_values($semesterTabs),
        'periode'      => $periodeList,
        'hariIni'      => $hariIni,
    ];

    // Status per kelas (harus sama dengan STATUS di periode-penguncian.js)
    $statusMap = [
        'siap'     => ['label' => 'Siap dikunci',         'class' => 'pk-status-siap'],
        'terkunci' => ['label' => 'Terkunci',             'class' => 'pk-status-kunci'],
        'menunggu' => ['label' => 'Belum lengkap',        'class' => 'pk-status-menunggu'],
        'koreksi'  => ['label' => 'Dibuka untuk koreksi', 'class' => 'pk-status-koreksi'],
        'belum'    => ['label' => 'Belum dibuka',         'class' => 'pk-status-belum'],
    ];

    // Badge status periode (harus sama dengan STATUS_PERIODE di periode-penguncian.js)
    $badgePeriode = [
        'terjadwal' => 'pk-badge-muted',
        'dibuka'    => 'pk-badge-open',
        'ditutup'   => 'pk-badge-closed',
    ];

    $iconLock     = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>';
    $iconUnlock   = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/></svg>';
    $iconCalendar = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>';
    $iconCalSm    = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>';
    $iconError    = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>';
@endphp

<div class="pk-page" data-pk-page>

    {{-- Data untuk JS. TODO (backend): nanti bisa diganti fetch ke API --}}
    <script type="application/json" id="pk-data">@json($dataJs)</script>

    <header class="pk-header">
        <div>
            <p class="pk-eyebrow">{{ $namaSekolah }}</p>
            <h1 class="pk-title">Periode &amp; Penguncian</h1>
            <p class="pk-desc">Atur jadwal pengisian dan kunci nilai sebelum rapor diterbitkan.</p>
        </div>
        <button type="button" class="pk-btn-outline pk-btn-icon" id="pk-btn-tambah" data-action="tambah">
            <span aria-hidden="true">+</span> Tambah periode
        </button>
    </header>

    {{-- Tab semester --}}
    <div class="pk-semester-tabs" role="tablist" aria-label="Pilih semester">
        @forelse ($semesterTabs as $i => $semester)
            <button type="button"
                    role="tab"
                    id="pk-semester-tab-{{ $i }}"
                    aria-controls="pk-semester-{{ $i }}"
                    class="pk-semester-tab{{ $i === $semesterAktif ? ' is-active' : '' }}"
                    aria-selected="{{ $i === $semesterAktif ? 'true' : 'false' }}"
                    tabindex="{{ $i === $semesterAktif ? '0' : '-1' }}"
                    data-semester="{{ $i }}">{{ $semester }}</button>
        @empty
            <span class="pk-desc">Belum ada semester.</span>
        @endforelse
    </div>

    @foreach ($semesterData as $si => $sem)
        @php
            $semTingkatData  = $sem['tingkatData'] ?? [];
            $semTingkatAktif = $sem['tingkatAktif'] ?? array_key_first($semTingkatData);
            if (! array_key_exists($semTingkatAktif, $semTingkatData)) {
                $semTingkatAktif = array_key_first($semTingkatData);
            }
            $semSts   = $sem['sts'] ?? $sts;
            $semSas   = $sem['sas'] ?? $sas;
            $semLabel = $semesterTabs[$si] ?? '';
            $prefix   = 'pk-s' . $si;
        @endphp

        <section class="pk-semester-panel"
                 id="pk-semester-{{ $si }}"
                 role="tabpanel"
                 aria-labelledby="pk-semester-tab-{{ $si }}"
                 data-semester-panel="{{ $si }}"
                 @if ($si !== $semesterAktif) hidden @endif>

            {{-- Empty state: semester belum punya periode --}}
            <div class="pk-empty" data-semester-empty @if ($sem['adaPeriode'] ?? true) hidden @endif>
                <span class="pk-empty-icon" aria-hidden="true">{!! $iconCalendar !!}</span>
                <h2 class="pk-empty-title">Belum ada periode di {{ $semLabel }}</h2>
                <p class="pk-empty-text">
                    Tambahkan periode STS atau SAS agar guru bisa mulai mengisi nilai sesuai jadwal.
                </p>
                <button type="button" class="pk-btn-primary" data-action="tambah" data-semester="{{ $si }}">
                    <span aria-hidden="true">+</span> Tambah periode
                </button>
            </div>

            <div class="pk-grid" data-semester-grid @unless ($sem['adaPeriode'] ?? true) hidden @endunless>

                {{-- Card STS --}}
                <section class="pk-card" data-periode-card="STS" aria-labelledby="{{ $prefix }}-sts-title">
                    <div class="pk-card-head">
                        <span class="pk-icon-box" aria-hidden="true">{!! $iconCalendar !!}</span>
                        <div class="pk-card-head-text">
                            <h2 class="pk-card-title" id="{{ $prefix }}-sts-title">{{ $semSts['judul'] }}</h2>
                            <p class="pk-card-sub" data-periode-tanggal>{{ $semSts['tanggal'] }}</p>
                        </div>
                        <div class="pk-card-head-actions">
                            <span class="pk-badge {{ $badgePeriode[$semSts['status'] ?? ''] ?? 'pk-badge-open' }}"
                                  data-periode-badge
                                  @if (($semSts['ada'] ?? true) === false) hidden @endif>{{ $semSts['statusLabel'] }}</span>
                            <button type="button"
                                    class="pk-btn-outline pk-btn-sm"
                                    data-action="jadwal"
                                    data-jenis="STS"
                                    aria-label="Ubah jadwal STS Kelas {{ $semTingkatAktif }}">
                                {!! $iconCalSm !!} <span data-jadwal-text>Ubah jadwal</span>
                            </button>
                        </div>
                    </div>

                    {{-- Pilihan tingkat kelas --}}
                    <div class="pk-tingkat-tabs" role="tablist" aria-label="Pilih tingkat kelas">
                        @foreach ($semTingkatData as $tingkat => $data)
                            <button type="button"
                                    role="tab"
                                    id="{{ $prefix }}-tab-{{ $tingkat }}"
                                    aria-controls="{{ $prefix }}-panel-{{ $tingkat }}"
                                    class="pk-tingkat-tab{{ $tingkat === $semTingkatAktif ? ' is-active' : '' }}"
                                    aria-selected="{{ $tingkat === $semTingkatAktif ? 'true' : 'false' }}"
                                    tabindex="{{ $tingkat === $semTingkatAktif ? '0' : '-1' }}"
                                    data-tingkat="{{ $tingkat }}">Kelas {{ $tingkat }}</button>
                        @endforeach
                    </div>

                    {{-- Panel per tingkat. Hanya satu yang tampil. --}}
                    @forelse ($semTingkatData as $tingkat => $data)
                        <div class="pk-panel"
                             id="{{ $prefix }}-panel-{{ $tingkat }}"
                             role="tabpanel"
                             aria-labelledby="{{ $prefix }}-tab-{{ $tingkat }}"
                             data-tingkat-panel="{{ $tingkat }}"
                             @if ($tingkat !== $semTingkatAktif) hidden @endif>
                            <div class="pk-panel-head">
                                <span>Status per kelas</span>
                                <strong>{{ $data['keseluruhan'] }}% keseluruhan</strong>
                            </div>

                            <div class="pk-table-wrap">
                                <table class="pk-table">
                                    <caption class="pk-sr-only">Status nilai per kelas, Kelas {{ $tingkat }}, {{ $semLabel }}</caption>
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
                                                <td class="pk-td-wali">{{ $k['wali'] ?? '-' }}</td>
                                                <td class="pk-td-progress">
                                                    <div class="pk-progress">
                                                        <span class="pk-track"
                                                              role="progressbar"
                                                              aria-label="Kelengkapan nilai {{ $k['kode'] }}"
                                                              aria-valuemin="0"
                                                              aria-valuemax="100"
                                                              aria-valuenow="{{ $k['persen'] }}">
                                                            <span class="pk-fill" data-percent="{{ $k['persen'] }}"></span>
                                                        </span>
                                                        <span class="pk-progress-text">{{ $progress }}</span>
                                                    </div>
                                                </td>
                                                <td class="pk-td-status">
                                                    <span class="pk-status {{ $st['class'] }}" data-status-badge>{{ $st['label'] }}</span>
                                                    @if (! empty($k['catatan']))
                                                        <span class="pk-status-note" data-status-note>{{ $k['catatan'] }}</span>
                                                    @endif
                                                </td>
                                                <td class="pk-td-aksi" data-aksi>
                                                    @if ($statusKey === 'siap')
                                                        <button type="button" class="pk-btn-primary pk-btn-sm" data-action="kunci"
                                                                aria-label="Kunci nilai kelas {{ $k['kode'] }}">
                                                            {!! $iconLock !!} Kunci
                                                        </button>
                                                    @elseif ($statusKey === 'terkunci')
                                                        <button type="button" class="pk-btn-outline pk-btn-sm" data-action="koreksi"
                                                                aria-label="Buka koreksi nilai kelas {{ $k['kode'] }}">
                                                            {!! $iconUnlock !!} Buka koreksi
                                                        </button>
                                                    @elseif ($statusKey === 'belum')
                                                        <span class="pk-aksi-text">periode belum dibuka</span>
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
                        <button type="button" class="pk-btn-primary" data-action="kunci-semua" disabled>
                            {!! $iconLock !!}
                            <span data-kunci-label>Kunci semua yang siap</span>
                        </button>
                    </footer>
                </section>

                {{-- Card SAS --}}
                <section class="pk-card" data-periode-card="SAS" aria-labelledby="{{ $prefix }}-sas-title">
                    <div class="pk-card-head">
                        <span class="pk-icon-box pk-icon-sas" aria-hidden="true">{!! $iconCalendar !!}</span>
                        <div class="pk-card-head-text">
                            <h2 class="pk-card-title" id="{{ $prefix }}-sas-title">{{ $semSas['judul'] }}</h2>
                            <p class="pk-card-sub" data-periode-tanggal>{{ $semSas['tanggal'] }}</p>
                        </div>
                        <span class="pk-badge {{ $badgePeriode[$semSas['status'] ?? ''] ?? 'pk-badge-muted' }}"
                              data-periode-badge
                              @if (($semSas['ada'] ?? true) === false) hidden @endif>{{ $semSas['statusLabel'] }}</span>
                    </div>

                    <div class="pk-sas-body">
                        <div class="pk-date-box" data-date-box aria-hidden="true">
                            <span class="pk-date-day" data-date-day>{{ $semSas['tanggalBuka'] }}</span>
                            <span class="pk-date-month" data-date-month>{{ $semSas['bulanBuka'] }}</span>
                        </div>
                        <div>
                            <p class="pk-sas-title" data-sas-title>Dibuka dalam {{ $semSas['hariSampaiDibuka'] }} hari</p>
                            <p class="pk-sas-desc" data-sas-desc>Guru dapat mulai mengisi nilai SAS mulai tanggal ini.</p>
                        </div>
                    </div>

                    <div class="pk-sas-foot">
                        <button type="button"
                                class="pk-btn-outline pk-btn-block pk-btn-icon"
                                data-action="jadwal"
                                data-jenis="SAS"
                                aria-label="Ubah jadwal SAS Kelas {{ $semTingkatAktif }}">
                            {!! $iconCalSm !!} <span data-jadwal-text>Ubah jadwal</span>
                        </button>
                    </div>
                </section>
            </div>
        </section>
    @endforeach

    {{-- Notifikasi (toast) --}}
    <div class="pk-toasts" id="pk-toasts" role="status" aria-live="polite"></div>

    {{-- ============ MODAL: KONFIRMASI KUNCI NILAI ============ --}}
    <dialog class="pk-modal" id="pk-modal-kunci" aria-labelledby="pk-modal-kunci-title" aria-describedby="pk-modal-kunci-desc">
        <form method="dialog" class="pk-modal-box">
            <button type="button" data-close class="pk-modal-close" aria-label="Tutup">&times;</button>

            <span class="pk-modal-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            </span>

            <h2 class="pk-modal-title" id="pk-modal-kunci-title">
                Kunci nilai <span data-kunci-jumlah>0</span> kelas?
            </h2>
            <p class="pk-modal-text" id="pk-modal-kunci-desc">
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
                <button type="button" data-close class="pk-btn-outline">Batal</button>
                <button type="submit" value="kunci" class="pk-btn-primary" data-autofocus>
                    {!! $iconLock !!} Ya, kunci nilai
                </button>
            </div>
        </form>
    </dialog>

    {{-- ============ MODAL: BUKA KOREKSI NILAI ============ --}}
    <dialog class="pk-modal pk-modal-lg" id="pk-modal-koreksi" aria-labelledby="pk-modal-koreksi-title">
        <form method="dialog" class="pk-modal-box" novalidate>
            <button type="button" data-close class="pk-modal-close" aria-label="Tutup">&times;</button>

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
                    <select id="pk-mapel-select" class="pk-chip-select" data-autofocus>
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
                    {!! $iconError !!}
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
                <button type="button" data-close class="pk-btn-outline">Batal</button>
                <button type="submit" value="buka" class="pk-btn-primary" id="pk-btn-buka-koreksi" disabled>
                    {!! $iconUnlock !!} Buka Koreksi
                </button>
            </div>
        </form>
    </dialog>

    {{-- ============ MODAL: TAMBAH PERIODE ============ --}}
    <dialog class="pk-modal pk-modal-lg" id="pk-modal-tambah" aria-labelledby="pk-modal-tambah-title" aria-describedby="pk-modal-tambah-desc">
        <form method="dialog" class="pk-modal-box" novalidate>
            <button type="button" data-close class="pk-modal-close" aria-label="Tutup">&times;</button>

            <span class="pk-modal-icon" aria-hidden="true">{!! $iconCalendar !!}</span>

            <h2 class="pk-modal-title" id="pk-modal-tambah-title">Tambah periode</h2>
            <p class="pk-modal-text" id="pk-modal-tambah-desc">
                Atur jadwal pengisian nilai untuk satu atau beberapa tingkat sekaligus.
            </p>

            <div class="pk-field">
                <label class="pk-label" for="pk-tambah-semester">
                    Semester <span class="pk-req" aria-hidden="true">*</span>
                </label>
                <select id="pk-tambah-semester" name="semester" class="pk-input"
                        aria-describedby="pk-tambah-semester-error" required data-autofocus>
                    <option value="">Pilih semester…</option>
                    @foreach ($semesterTabs as $i => $semester)
                        <option value="{{ $i }}">{{ $semester }}</option>
                    @endforeach
                </select>
                <p class="pk-error" id="pk-tambah-semester-error" hidden>{!! $iconError !!} <span data-error-text></span></p>
            </div>

            <fieldset class="pk-field pk-fieldset" aria-describedby="pk-tambah-jenis-error">
                <legend class="pk-label">Jenis periode <span class="pk-req" aria-hidden="true">*</span></legend>
                <div class="pk-options">
                    <label class="pk-option">
                        <input type="radio" name="jenis" value="STS" required>
                        <span><strong>STS</strong> Sumatif Tengah Semester</span>
                    </label>
                    <label class="pk-option">
                        <input type="radio" name="jenis" value="SAS" required>
                        <span><strong>SAS</strong> Sumatif Akhir Semester</span>
                    </label>
                </div>
                <p class="pk-error" id="pk-tambah-jenis-error" hidden>{!! $iconError !!} <span data-error-text></span></p>
            </fieldset>

            <fieldset class="pk-field pk-fieldset" aria-describedby="pk-tambah-tingkat-hint pk-tambah-tingkat-error">
                <legend class="pk-label">Tingkat <span class="pk-req" aria-hidden="true">*</span></legend>
                <p class="pk-hint" id="pk-tambah-tingkat-hint">Boleh pilih lebih dari satu.</p>
                <div class="pk-options pk-options-tingkat">
                    @foreach ($daftarTingkat as $tingkat)
                        <label class="pk-option pk-option-sm">
                            <input type="checkbox" name="tingkat" value="{{ $tingkat }}">
                            <span>Kelas {{ $tingkat }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="pk-error" id="pk-tambah-tingkat-error" hidden>{!! $iconError !!} <span data-error-text></span></p>
            </fieldset>

            <div class="pk-field-row">
                <div class="pk-field">
                    <label class="pk-label" for="pk-tambah-buka">
                        Tanggal buka <span class="pk-req" aria-hidden="true">*</span>
                    </label>
                    <input type="date" id="pk-tambah-buka" name="buka" class="pk-input"
                           aria-describedby="pk-tambah-buka-error" required>
                    <p class="pk-error" id="pk-tambah-buka-error" hidden>{!! $iconError !!} <span data-error-text></span></p>
                </div>
                <div class="pk-field">
                    <label class="pk-label" for="pk-tambah-tutup">
                        Tanggal tutup <span class="pk-req" aria-hidden="true">*</span>
                    </label>
                    <input type="date" id="pk-tambah-tutup" name="tutup" class="pk-input"
                           aria-describedby="pk-tambah-tutup-error" required>
                    <p class="pk-error" id="pk-tambah-tutup-error" hidden>{!! $iconError !!} <span data-error-text></span></p>
                </div>
            </div>

            <div class="pk-modal-actions">
                <button type="button" data-close class="pk-btn-outline">Batal</button>
                <button type="submit" value="simpan" class="pk-btn-primary">
                    <span aria-hidden="true">+</span> Simpan periode
                </button>
            </div>
        </form>
    </dialog>

    {{-- ============ MODAL: UBAH JADWAL ============ --}}
    <dialog class="pk-modal" id="pk-modal-jadwal" aria-labelledby="pk-modal-jadwal-title">
        <form method="dialog" class="pk-modal-box" novalidate>
            <button type="button" data-close class="pk-modal-close" aria-label="Tutup">&times;</button>

            <span class="pk-modal-icon" aria-hidden="true">{!! $iconCalendar !!}</span>

            <h2 class="pk-modal-title" id="pk-modal-jadwal-title">
                Ubah jadwal <span data-jadwal-jenis>STS</span>
            </h2>
            <p class="pk-modal-pill" data-jadwal-info>-</p>

            <div class="pk-field-row">
                <div class="pk-field">
                    <label class="pk-label" for="pk-jadwal-buka">
                        Tanggal buka <span class="pk-req" aria-hidden="true">*</span>
                    </label>
                    <input type="date" id="pk-jadwal-buka" name="buka" class="pk-input"
                           aria-describedby="pk-jadwal-buka-error" required data-autofocus>
                    <p class="pk-error" id="pk-jadwal-buka-error" hidden>{!! $iconError !!} <span data-error-text></span></p>
                </div>
                <div class="pk-field">
                    <label class="pk-label" for="pk-jadwal-tutup">
                        Tanggal tutup <span class="pk-req" aria-hidden="true">*</span>
                    </label>
                    <input type="date" id="pk-jadwal-tutup" name="tutup" class="pk-input"
                           aria-describedby="pk-jadwal-tutup-error" required>
                    <p class="pk-error" id="pk-jadwal-tutup-error" hidden>{!! $iconError !!} <span data-error-text></span></p>
                </div>
            </div>

            <div class="pk-alert" data-jadwal-warning hidden>
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3 2 20h20L12 3z"/><path d="M12 10v4M12 17h.01"/></svg>
                <span>Periode ini sedang dibuka. Perubahan jadwal langsung berlaku untuk semua guru di tingkat ini.</span>
            </div>

            <div class="pk-modal-actions">
                <button type="button" data-close class="pk-btn-outline">Batal</button>
                <button type="submit" value="simpan" class="pk-btn-primary">Simpan jadwal</button>
            </div>
        </form>
    </dialog>
</div>
@endsection
