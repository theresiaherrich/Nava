
{{-- resources/views/presensi/budaya-positif/index.blade.php --}}

@php
    $halaman = $halaman ?? 'dashboard';
    $peran = $peran ?? 'Budaya Positif';

    $isDashboard = $halaman === 'dashboard';
@endphp

{{-- Dashboard menggunakan template Guru.
     Admin Presensi tetap menggunakan layouts.app. --}}
@extends(
    $isDashboard ? 'presensi.guru.index' : 'layouts.app',
    $isDashboard ? [
        'pageTitle' => 'Dashboard',
        'rolePresensi' => 'budaya-positif',
        'namaProfil' => $peran,
        'labelProfil' => 'Budaya Positif',
        'detailProfil' => 'SMAN 1 Malang',
    ] : []
)

@if (!$isDashboard)

@section('title', 'Admin Presensi')

@push('styles')
    @vite('resources/css/presensi/budaya-positif.css')
@endpush

@section('content')

<div class="bp-container" data-halaman="admin">

    {{-- HEADER --}}
    <header class="bp-topbar">
        <label class="bp-search">
            <svg viewBox="0 0 24 24" width="13" height="13"
                fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/>
                <path d="m20 20-3.5-3.5"/>
            </svg>

            <input type="search" id="bpSearch"
                placeholder="Ketik nama, nisn, atau kelas siswa"
                aria-label="Cari siswa" autocomplete="off">
        </label>

        <div class="bp-profile">
            <div class="bp-avatar" aria-hidden="true">
                {{ strtoupper(substr($peran, 0, 1)) }}
            </div>

            <div>
                <strong>{{ $peran }}</strong>
                <small>SMAN 1 Malang</small>
            </div>

            <svg class="bp-chevron" viewBox="0 0 24 24"
                width="14" height="14" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </div>
    </header>

    {{-- ADMIN PRESENSI --}}
    <section class="bp-view active" id="bp-admin">

        {{-- SCAN QR --}}
        <div class="bp-scanner-card">
            <div class="bp-reader-wrap">

                <div id="bpQrReader"></div>

                {{-- Bingkai pemindai --}}
                <div class="bp-scan-frame" id="bpScanFrame"
                    aria-hidden="true" hidden>
                    <i></i>
                    <i></i>
                    <i></i>
                    <i></i>
                </div>

                {{-- Placeholder kamera --}}
                <div class="bp-camera-placeholder"
                    id="bpCameraPlaceholder">

                    <svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>

                    <p>Membuka kamera…</p>

                    <span>
                        Izinkan akses kamera jika diminta.
                    </span>

                    <button type="button"
                        id="bpStartCamera" hidden>
                        Coba Lagi
                    </button>
                </div>
            </div>

            <div class="bp-scan-info" id="bpScanInfo"
                role="status" aria-live="polite">
                Arahkan QR Code siswa ke area pemindaian.
            </div>
        </div>

        {{-- PRESENSI MANUAL --}}
        <div class="bp-manual-section">
            <h3>Presensi manual</h3>

            <div class="bp-manual-card">
                <h4>Presensi</h4>

                <form id="bpManualForm" novalidate>

                    <label for="bpName">Nama</label>
                    <input id="bpName" type="text"
                        placeholder="Nama"
                        autocomplete="off" required>

                    <label for="bpClass">Kelas</label>
                    <input id="bpClass" type="text"
                        placeholder="Kelas"
                        autocomplete="off" required>

                    <label for="bpManualStatus">Status</label>
                    <select id="bpManualStatus" required>
                        <option value="">Status</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Telat">Telat</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>

                    <small class="bp-validation"
                        id="bpFormMsg"
                        role="status"
                        aria-live="polite">
                        Pastikan isi dengan benar!
                    </small>

                    <div class="bp-manual-footer">

                        <span>
                            <svg viewBox="0 0 24 24"
                                width="12" height="12"
                                fill="currentColor"
                                aria-hidden="true">
                                <path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>
                            </svg>
                            Area sesuai
                        </span>

                        <button type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>

    </section>
</div>

@vite('resources/js/presensi/budaya-positif.js')

@endsection

@endif
