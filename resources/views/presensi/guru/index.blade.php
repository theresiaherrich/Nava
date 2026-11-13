
@extends('layouts.app')

@php
    $pageTitle = $pageTitle ?? 'Kelola Presensi';
    $rolePresensi = $rolePresensi ?? 'guru';
    $namaProfil = $namaProfil ?? 'Nama Guru';
    $labelProfil = $labelProfil ?? 'Guru Wali Kelas';
    $detailProfil = $detailProfil ?? 'XI MIPA 2';
@endphp

@section('title', $pageTitle)

@push('styles')
    @vite('resources/css/presensi/guru.css')
@endpush

@section('content')
<div class="gw-page"
    data-role="{{ $rolePresensi }}"
    data-profile-name="{{ $namaProfil }}"
    data-profile-label="{{ $labelProfil }}"
    data-profile-detail="{{ $detailProfil }}">

    {{-- Header: pencarian + profil --}}
    <header class="gw-header">
        <label class="gw-search">
            <input type="search" id="gwSearch"
                placeholder="Ketik nama, NISN, atau kelas siswa"
                aria-label="Cari siswa" autocomplete="off">

            <svg viewBox="0 0 24 24" width="15" height="15"
                fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/>
                <path d="m20 20-3.5-3.5"/>
            </svg>
        </label>

        <div class="gw-profile">
            <div class="gw-avatar" id="gwGuruAvatar">
                {{ strtoupper(substr($namaProfil, 0, 1)) }}
            </div>

            <div class="gw-profile-text">
                <strong id="gwGuruNama">{{ $namaProfil }}</strong>
                <span id="gwGuruKelas">{{ $labelProfil }}</span>
            </div>

            <svg class="gw-chevron" viewBox="0 0 24 24"
                width="16" height="16" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </div>
    </header>

    <main class="gw-container">

        {{-- Ringkasan --}}
        <section class="gw-stats">

            <div class="gw-card gw-chart-card">
                <div class="gw-donut" id="gwDonut">
                    <div class="gw-donut-center">
                        <strong id="gwPersen">0%</strong>
                        <span>Kehadiran</span>
                    </div>
                </div>

                <ul class="gw-legend">
                    <li><i class="gw-dot gw-dot--hadir"></i>Hadir</li>
                    <li><i class="gw-dot gw-dot--telat"></i>Terlambat</li>
                    <li><i class="gw-dot gw-dot--alpa"></i>Alpa</li>
                </ul>
            </div>

            {{-- Izin --}}
            <div class="gw-card gw-bars">
                <div class="gw-bar-row is-izin">
                    <div class="gw-bar-head">
                        <strong>Izin</strong>
                        <span class="gw-chip" id="gwIzinJumlah">0</span>
                        <span class="gw-slash">/</span>
                        <span class="gw-chip gw-chip--gray" id="gwIzinTotal">0</span>
                        <strong>Siswa</strong>
                    </div>

                    <div class="gw-bar">
                        <div class="gw-bar-track">
                            <div class="gw-bar-fill" id="gwIzinBar"></div>
                        </div>
                        <span class="gw-bar-pct" id="gwIzinPersen">0%</span>
                    </div>
                </div>

                {{-- Alpa --}}
                <div class="gw-bar-row is-alpa">
                    <div class="gw-bar-head">
                        <strong>Alpa</strong>
                        <span class="gw-chip" id="gwAlpaJumlah">0</span>
                        <span class="gw-slash">/</span>
                        <span class="gw-chip gw-chip--gray" id="gwAlpaTotal">0</span>
                        <strong>Siswa</strong>
                    </div>

                    <div class="gw-bar">
                        <div class="gw-bar-track">
                            <div class="gw-bar-fill" id="gwAlpaBar"></div>
                        </div>
                        <span class="gw-bar-pct" id="gwAlpaPersen">0%</span>
                    </div>
                </div>
            </div>

            {{-- Filter tanggal --}}
            <div class="gw-card gw-date-card">
                <div class="gw-date-head">
                    <h3>Tanggal Presensi</h3>
                    <button type="button" class="gw-range-toggle"
                        id="gwRentang" aria-pressed="false">
                        Rentang
                    </button>
                </div>

                <div id="gwModeSatu">
                    <div class="gw-date-row">
                        <div class="gw-date-field">
                            <label for="gwTanggal">Tanggal</label>
                            <select id="gwTanggal">
                                @for ($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="gw-date-field">
                            <label for="gwBulan">Bulan</label>
                            <select id="gwBulan">
                                @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bulan)
                                    <option value="{{ $i + 1 }}">{{ $bulan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="gw-date-field">
                        <label for="gwTahun">Tahun</label>
                        <select id="gwTahun">
                            @foreach ([2025, 2026, 2027] as $tahun)
                                <option value="{{ $tahun }}">{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="gwModeRentang" hidden>
                    <div class="gw-date-field">
                        <label for="gwAwal">Tanggal awal</label>
                        <div class="gw-datebox">
                            <span id="gwAwalLabel"></span>
                            <input type="date" id="gwAwal"
                                aria-label="Tanggal awal">
                        </div>
                    </div>

                    <div class="gw-date-field">
                        <label for="gwAkhir">Tanggal akhir</label>
                        <div class="gw-datebox">
                            <span id="gwAkhirLabel"></span>
                            <input type="date" id="gwAkhir"
                                aria-label="Tanggal akhir">
                        </div>
                    </div>
                </div>

                <button type="button" class="gw-btn" id="gwPilihTanggal">
                    Pilih Tanggal
                </button>

                <p class="gw-date-msg" id="gwDateMsg" role="status"></p>
            </div>
        </section>

        {{-- Tab Presensi / Rekap --}}
        <div class="gw-subtabs" id="gwSubtabs"
            role="tablist" hidden>
            <button type="button" class="gw-subtab is-active"
                role="tab" data-view="presensi" aria-selected="true">
                Presensi
            </button>

            <button type="button" class="gw-subtab"
                role="tab" data-view="rekap" aria-selected="false">
                Rekap
            </button>
        </div>

        <section class="gw-card gw-table-card">
            <div class="gw-toolbar">
                <span class="gw-date-chip" id="gwChip"></span>

                <div class="gw-toolbar-filters" id="gwFilterPresensi">
                    <select id="gwStatusFilter"
                        class="gw-pill-select" aria-label="Filter status">
                        <option value="">Semua</option>
                        <option value="Hadir">Hadir</option>
                        <option value="Terlambat">Terlambat</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>

                    <select id="gwUrut" class="gw-pill-select"
                        aria-label="Urutkan">
                        <option value="terbaru">Terbaru</option>
                        <option value="terlama">Terlama</option>
                    </select>
                </div>

                <div class="gw-toolbar-filters" id="gwFilterRekap" hidden>
                    <select id="gwRekapFilter"
                        class="gw-pill-select" aria-label="Filter rekap">
                        <option value="">Semua</option>
                        <option value="rendah">Rekap di bawah 75%</option>
                        <option value="aman">Rekap 75% ke atas</option>
                    </select>
                </div>
            </div>

            {{-- Tabel Presensi --}}
            <div class="gw-table-wrap" id="gwViewPresensi">
                <table class="gw-table" id="gwTabelPresensi">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th class="gw-col-tgl">Tanggal</th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Jam</th>
                            <th>Metode</th>
                            <th><span class="gw-sr">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody id="gwBody"></tbody>
                </table>
            </div>

            {{-- Tabel Rekap --}}
            <div class="gw-table-wrap" id="gwViewRekap" hidden>
                <table class="gw-table gw-rekap">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpa</th>
                            <th>Rekap</th>
                        </tr>
                    </thead>
                    <tbody id="gwRekapBody"></tbody>
                </table>
            </div>

            <p class="gw-empty" id="gwEmpty" hidden>
                Data presensi tidak ditemukan.
            </p>
        </section>
    </main>
</div>

{{-- Modal detail siswa --}}
<div class="gw-modal" id="gwModal"
    aria-hidden="true" role="dialog" aria-modal="true"
    aria-label="Detail siswa">

    <div class="gw-modal-box">
        <button type="button" class="gw-close"
            data-gw-close aria-label="Tutup">
            &times;
        </button>

        <div class="gw-modal-layout">
            <div class="gw-photo" id="gwFoto" aria-hidden="true">S</div>

            <div class="gw-modal-main">
                <div class="gw-tabs" role="tablist">
                    <button type="button" class="gw-tab is-active"
                        role="tab" data-tab="history">
                        Riwayat Presensi
                    </button>

                    <button type="button" class="gw-tab"
                        role="tab" data-tab="biodata">
                        Biodata
                    </button>

                    <button type="button" class="gw-tab"
                        role="tab" data-tab="parent">
                        Orang Tua
                    </button>

                    <button type="button" class="gw-tab"
                        role="tab" data-tab="curriculum">
                        Kurikulum
                    </button>
                </div>

                {{-- Riwayat presensi --}}
                <div class="gw-panel" data-panel="history">
                    <div class="gw-field">
                        <label for="gwHNama">Nama</label>
                        <input id="gwHNama" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwHNisn">NISN</label>
                        <input id="gwHNisn" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwHKelas">Kelas</label>
                        <input id="gwHKelas" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwHStatus">Status</label>
                        <select id="gwHStatus" disabled>
                            <option>Hadir</option>
                            <option>Terlambat</option>
                            <option>Izin</option>
                            <option>Sakit</option>
                            <option>Alpa</option>
                        </select>
                    </div>

                    <div class="gw-field">
                        <label for="gwHJam">Jam</label>
                        <input id="gwHJam" readonly placeholder="06.25 WIB">
                    </div>

                    <div class="gw-field">
                        <label for="gwHMetode">Metode</label>
                        <select id="gwHMetode" disabled>
                            <option>QR Code</option>
                            <option>Manual</option>
                            <option>-</option>
                        </select>
                    </div>

                    <div class="gw-field">
                        <label for="gwHBerkas">Berkas Pendukung</label>
                        <input id="gwHBerkas" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwHKet">Keterangan</label>
                        <input id="gwHKet" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwHAlasan">Alasan Perubahan</label>
                        <input id="gwHAlasan" readonly
                            placeholder="Wajib diisi saat mengubah status">
                    </div>

                    <button type="button" class="gw-edit-btn"
                        id="gwEditHistory">
                        <span>Edit</span>
                        <svg viewBox="0 0 24 24" width="14" height="14"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/>
                            <path d="m15 5 4 4"/>
                        </svg>
                    </button>
                </div>

                {{-- Biodata siswa --}}
                <div class="gw-panel" data-panel="biodata" hidden>
                    <div class="gw-field">
                        <label for="gwBNama">Nama</label>
                        <input id="gwBNama" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwBNisn">NISN</label>
                        <input id="gwBNisn" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwBGender">Jenis Kelamin</label>
                        <select id="gwBGender" disabled>
                            <option>Laki-laki</option>
                            <option>Perempuan</option>
                        </select>
                    </div>

                    <div class="gw-field">
                        <label for="gwBTtl">Tempat Tanggal Lahir</label>
                        <input id="gwBTtl" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwBAlamat">Alamat</label>
                        <input id="gwBAlamat" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwBHp">No Handphone</label>
                        <input id="gwBHp" readonly inputmode="tel">
                    </div>
                </div>

                {{-- Data orang tua --}}
                <div class="gw-panel" data-panel="parent" hidden>
                    <div class="gw-field">
                        <label for="gwPAyah">Nama Ayah</label>
                        <input id="gwPAyah" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwPIbu">Nama Ibu</label>
                        <input id="gwPIbu" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwPHp">No Handphone Orang Tua</label>
                        <input id="gwPHp" readonly>
                    </div>
                </div>

                {{-- Data kurikulum --}}
                <div class="gw-panel" data-panel="curriculum" hidden>
                    <div class="gw-field">
                        <label for="gwKKelas">Kelas</label>
                        <input id="gwKKelas" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwKTahun">Tahun Ajaran</label>
                        <input id="gwKTahun" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwKSemester">Semester</label>
                        <input id="gwKSemester" readonly>
                    </div>

                    <div class="gw-field">
                        <label for="gwKKurikulum">Kurikulum</label>
                        <input id="gwKKurikulum" readonly>
                    </div>
                </div>

                <p class="gw-msg" id="gwMsg"
                    role="status" aria-live="polite"></p>
            </div>
        </div>
    </div>
</div>

@vite('resources/js/presensi/guru.js')
@endsection
