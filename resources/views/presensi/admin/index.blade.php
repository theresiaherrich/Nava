{{-- resources/views/presensi/admin/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Presensi')

@push('styles')
    @vite('resources/css/presensi/admin.css')
@endpush

@section('content')
<div class="pa-page" id="presensiAdmin">

    {{-- HEADER: PENCARIAN DAN PROFIL ADMIN --}}
    <div class="pa-topbar">

        <label class="pa-search">
            <input type="search" id="paHeaderSearch" placeholder="Ketik nama, nisn, atau kelas siswa"
                   autocomplete="off" aria-label="Cari siswa" title="Tekan Enter untuk mengisi form presensi manual">
            <svg class="pa-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>
            </svg>
        </label>

        <div class="pa-profile">
            <button type="button" class="pa-profile-button" id="paProfileButton"
                    aria-expanded="false" aria-haspopup="true">
                <span class="pa-profile-avatar" aria-hidden="true">A</span>

                <span class="pa-profile-info">
                    <strong>Admin</strong>
                    <small>Admin • SMAN 1 Malang</small>
                </span>

                <svg class="pa-profile-chevron" width="17" height="17" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </button>

            <div class="pa-profile-dropdown" id="paProfileDropdown">
                {{-- tabel user hanya punya "username", bukan "name" --}}
                <p><strong>{{ auth()->user()->username ?? 'Admin' }}</strong></p>
                <p>Administrator</p>
            </div>
        </div>
    </div>

    <div class="pa-content">
        <div class="pa-main-grid">

            {{-- SCANNER QR CODE --}}
            <section class="pa-card pa-scanner-card">
                <div class="pa-reader-wrap">
                    <div id="paReader"></div>

                    <div class="pa-camera-placeholder" id="paCameraPlaceholder">
                        <span class="pa-camera-frame"></span>
                        <strong>Kamera belum diaktifkan</strong>
                        <small>Tekan tombol Mulai Scan untuk membuka kamera.</small>
                    </div>
                </div>

                <div class="pa-scanner-actions">
                    <button class="pa-button pa-button-primary" type="button" id="paStartScan">Mulai Scan</button>
                    <button class="pa-button pa-button-light" type="button" id="paStopScan" disabled>Hentikan</button>
                </div>

                <div class="pa-scan-result" id="paScanResult" role="status" aria-live="polite">
                    Belum ada QR yang dipindai.
                </div>
            </section>

            {{-- PRESENSI MANUAL --}}
            <section class="pa-card pa-manual-card">
                <div class="pa-section-heading">
                    <h2>Presensi manual</h2>
                </div>

                <form id="paManualForm" novalidate>
                    <h3 class="pa-form-title">Presensi</h3>

                    <input id="paNisn" name="nisn" type="hidden">

                    <div class="pa-field">
                        <label for="paStudentName">Nama</label>
                        <input id="paStudentName" name="nama" type="text" placeholder="Nama" autocomplete="off" required>
                    </div>

                    <div class="pa-field">
                        <label for="paClass">Kelas</label>
                        <input id="paClass" name="kelas" type="text" placeholder="Kelas" autocomplete="off" required>
                    </div>

                    <div class="pa-field">
                        <label for="paStatus">Status</label>
                        <select id="paStatus" name="status" required>
                            <option value="">Pilih status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Telat">Telat</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpa">Alpa</option>
                        </select>
                    </div>

                    {{-- UPLOAD FILE --}}
                    <div class="pa-field pa-upload-field">
                        <label for="paFile">File Pendukung</label>

                        <label for="paFile" class="pa-upload-box">
                            <span class="pa-upload-icon">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 15v5h14v-5"/>
                                </svg>
                            </span>
                            <span class="pa-upload-title">Upload File</span>
                            <small>Mendukung file .jpg, .png dan .pdf (maks. 5 MB)</small>
                        </label>

                        <input type="file" id="paFile" name="file_pendukung" accept=".jpg,.jpeg,.png,.pdf" hidden>
                        <span id="paFileName" class="pa-file-name"></span>
                    </div>

                    {{-- INFORMASI --}}
                    <div class="pa-form-bottom-info">
                        <span class="pa-warning-text">Pastikan isi dengan benar!</span>

                        <div class="pa-location-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <span>Area sesuai</span>
                        </div>
                    </div>

                    <button class="pa-button pa-button-submit" type="submit">Submit</button>
                </form>
            </section>

        </div>
    </div>

    {{-- NOTIFIKASI --}}
    <div class="pa-toast" id="paToast" role="status" aria-live="polite"></div>
</div>

{{-- JS dimuat di sini supaya tidak bergantung pada @stack di layout --}}
@vite('resources/js/presensi/admin.js')
@endsection