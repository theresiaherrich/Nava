@extends('layouts.app')

@section('title', 'Presensi Siswa')

@push('styles')
    @vite('resources/css/presensi/siswa.css')
@endpush

@php
    $aturanPresensi = [
        'Presensi dibuka mulai pukul 06.00 WIB.',
        'Presensi pukul 06.00 sampai 07.00 WIB tercatat Hadir.',
        'Presensi pukul 07.01 sampai 07.30 WIB tercatat Terlambat.',
        'Mulai pukul 07.31 WIB, siswa yang belum melakukan presensi otomatis tercatat Alpa oleh sistem.',
        'Presensi dilakukan ketika berada di area sekolah dengan radius maksimal 50 meter dari titik tengah lapangan.',
        'Pastikan GPS aktif dan akurasi lokasi maksimal 50 meter.',
        'Setiap siswa hanya dapat melakukan presensi satu kali dalam sehari.',
    ];

    $izinRumah = [
        'Jika izin dari rumah, harap kirim surat izin atau surat sakit kepada wali kelas melalui orang tua.',
        'Setelah itu bawa surat izin tersebut saat masuk dan menyerahkannya kepada wali kelas.',
    ];

    $izinSekolah = [
        'Jika izin dari sekolah, harap mengajukan surat dispensasi ke Budaya Positif.',
        'Kemudian mintalah tanda tangan kepada wali kelas.',
        'Jika tanda tangan sudah lengkap, serahkan surat ke Budaya Positif dan dispensasi dimulai.',
        'Ketika dispensasi selesai, mohon melaporkan diri ke Budaya Positif.',
    ];
@endphp

@section('content')

<div class="ps-page">

    {{-- PROFIL SISWA --}}
    <header class="ps-header">
        <div class="ps-profile">
            <div class="ps-avatar" id="psAvatar">M</div>

            <div class="ps-profile-text">
                <strong id="psNamaHeader">Memuat...</strong>
                <span id="psKelasHeader">&nbsp;</span>
            </div>

            <svg
                class="ps-chevron"
                viewBox="0 0 24 24"
                width="16"
                height="16"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </div>
    </header>

    <main class="ps-container">

        <div class="ps-main-grid">

            {{-- QR CODE --}}
            <section class="ps-card ps-qr-card">

                <h3>QR Code</h3>

                <button
                    type="button"
                    class="ps-qr-preview"
                    data-open-modal="psQrModal"
                    aria-label="Perbesar QR Code"
                >
                    <img
                        id="psQrImage"
                        src=""
                        alt="QR Code siswa"
                        hidden
                    >

                    <span id="psQrLoading">
                        Menyiapkan QR Code...
                    </span>
                </button>

            </section>

            {{-- FORM PRESENSI --}}
            <section class="ps-card ps-form-card">

                <h3>Presensi</h3>

                <form id="psAttendanceForm" novalidate>

                    <div class="ps-field">
                        <label for="psNama">Nama</label>

                        <input
                            type="text"
                            id="psNama"
                            placeholder="Nama"
                            autocomplete="off"
                        >
                    </div>

                    <div class="ps-field">
                        <label for="psKelas">Kelas</label>

                        <input
                            type="text"
                            id="psKelas"
                            placeholder="Kelas"
                            autocomplete="off"
                        >
                    </div>

                    <p class="ps-hint">
                        Pastikan data nama dan kelas sudah benar!
                    </p>

                    <div class="ps-form-footer">

                        {{-- STATUS LOKASI --}}
                        <div
                            class="ps-location"
                            id="psLocation"
                            data-state="checking"
                            role="status"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                width="16"
                                height="16"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>
                            </svg>

                            <span id="psLocationText">
                                Memeriksa lokasi...
                            </span>
                        </div>

                        <button
                            type="submit"
                            class="ps-submit"
                            id="psSubmit"
                            disabled
                        >
                            Submit
                        </button>

                    </div>

                    <p
                        class="ps-feedback"
                        id="psFeedback"
                        role="status"
                        aria-live="polite"
                    ></p>

                </form>

            </section>

        </div>

        {{-- RIWAYAT PRESENSI --}}
        <section class="ps-card ps-history-card">

            <div class="ps-history-heading">

                <h3>Riwayat Presensi</h3>

                <div class="ps-search">

                    <input
                        type="date"
                        id="psSearch"
                        aria-label="Cari tanggal presensi"
                        title="Cari tanggal presensi"
                    >

                    <button
                        type="button"
                        class="ps-search-reset"
                        id="psSearchReset"
                        hidden
                    >
                        Semua
                    </button>

                </div>

            </div>

            <div id="psHistory" class="ps-history">
                <p class="ps-empty">
                    Memuat riwayat...
                </p>
            </div>

        </section>

    </main>

</div>

{{-- MODAL QR CODE --}}
<div
    class="ps-modal"
    id="psQrModal"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-label="QR Code"
>
    <div class="ps-modal-box ps-modal-box--qr">

        <button
            type="button"
            class="ps-close"
            data-close-modal="psQrModal"
            aria-label="Tutup"
        >
            &times;
        </button>

        <div class="ps-qr-frame">
            <img
                id="psQrLarge"
                src=""
                alt="QR Code besar"
            >
        </div>

    </div>
</div>

{{-- MODAL ATURAN PRESENSI DAN IZIN --}}
<div
    class="ps-modal"
    id="psRulesModal"
    aria-hidden="true"
    role="dialog"
    aria-modal="true"
    aria-label="Panduan presensi"
>
    <div class="ps-modal-box ps-modal-box--rules">

        <button
            type="button"
            class="ps-close"
            data-close-modal="psRulesModal"
            aria-label="Tutup"
        >
            &times;
        </button>

        {{-- TAB --}}
        <div class="ps-tabs" role="tablist">

            <button
                type="button"
                class="ps-tab"
                role="tab"
                data-tab="psPanelPresensi"
                aria-selected="false"
            >
                Aturan Presensi
            </button>

            <button
                type="button"
                class="ps-tab is-active"
                role="tab"
                data-tab="psPanelIzin"
                aria-selected="true"
            >
                Aturan Izin
            </button>

        </div>

        {{-- PANEL ATURAN PRESENSI --}}
        <div
            class="ps-panel"
            id="psPanelPresensi"
            role="tabpanel"
            hidden
        >
            <ol class="ps-steps">

                @foreach ($aturanPresensi as $i => $teks)
                    <li>
                        <span class="ps-num">
                            {{ $i + 1 }}
                        </span>

                        <p>{{ $teks }}</p>
                    </li>
                @endforeach

            </ol>
        </div>

        {{-- PANEL ATURAN IZIN --}}
        <div
            class="ps-panel"
            id="psPanelIzin"
            role="tabpanel"
        >

            <ol class="ps-steps">

                @foreach ($izinRumah as $i => $teks)
                    <li>
                        <span class="ps-num">
                            {{ $i + 1 }}
                        </span>

                        <p>{{ $teks }}</p>
                    </li>
                @endforeach

            </ol>

            <hr class="ps-divider">

            <ol class="ps-steps">

                @foreach ($izinSekolah as $i => $teks)
                    <li>
                        <span class="ps-num">
                            {{ $i + 1 }}
                        </span>

                        <p>{{ $teks }}</p>
                    </li>
                @endforeach

            </ol>

        </div>

    </div>
</div>

@vite('resources/js/presensi/siswa.js')

@endsection