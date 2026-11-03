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

    $sts = $sts ?? ['judul' => '-', 'tanggal' => '-', 'statusLabel' => '-'];
    $sas = $sas ?? [
        'judul'            => '-',
        'tanggal'          => '-',
        'statusLabel'      => '-',
        'tanggalBuka'      => '-',
        'bulanBuka'        => '',
        'hariSampaiDibuka' => 0,
    ];

    $dataTingkatAktif = $tingkatData[$tingkatAktif] ?? null;
@endphp

<div class="pk-page" data-pk-page>

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
                        <span>Kelengkapan per kelas</span>
                        <strong>{{ $data['keseluruhan'] }}% keseluruhan</strong>
                    </div>

                    <ul class="pk-kelas-list">
                        @foreach ($data['kelas'] as $k)
                            <li class="pk-kelas-row">
                                <span class="pk-kelas-kode">{{ $k['kode'] }}</span>
                                <span class="pk-track">
                                    <span class="pk-fill" data-percent="{{ $k['persen'] }}"></span>
                                </span>
                                <span class="pk-persen">{{ $k['persen'] }}%</span>
                                <span class="pk-badge {{ $k['persen'] === 100 ? 'pk-badge-ok' : 'pk-badge-proses' }}">
                                    {{ $k['persen'] === 100 ? 'Lengkap' : 'Proses' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="pk-panel">
                    <p class="pk-desc">Belum ada data kelas.</p>
                </div>
            @endforelse

            <footer class="pk-card-foot">
                <p class="pk-foot-note">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                    Setelah dikunci, guru tidak dapat mengubah nilai.
                </p>
                <button type="button" class="pk-btn-primary" id="pk-btn-kunci" @disabled(! $tingkatAktif)>
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"/>
                        <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                    <span id="pk-label-kunci">Kunci nilai {{ $tingkatAktif ? 'Kelas ' . $tingkatAktif : '' }}</span>
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
</div>
@endsection