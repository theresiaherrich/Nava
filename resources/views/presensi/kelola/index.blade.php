
@extends('layouts.app')

@section('title', 'Kelola Presensi')

@php
    $role = strtolower(request('role', 'wali_kelas'));

    $siswa = [
        [
            'nama' => 'Mutiara Bintang',
            'nisn' => '245150600',
            'kelas' => 'XI MIPA 2',
            'status' => 'Telat',
            'jam' => '07:28',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => 'Terlambat masuk',
            'jk' => 'Perempuan',
            'lahir' => 'Malang, 17 Agustus 2009',
            'alamat' => 'Jl. Semesta No. 09 Kec. Lowokwaru Kota Malang',
            'hp' => '088556677334',
        ],
        [
            'nama' => 'Bintang Mutiara',
            'nisn' => '245150601',
            'kelas' => 'XI MIPA 2',
            'status' => 'Telat',
            'jam' => '07:10',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
        [
            'nama' => 'Mutiara Lautan',
            'nisn' => '245150602',
            'kelas' => 'XI MIPA 2',
            'status' => 'Hadir',
            'jam' => '07:00',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
        [
            'nama' => 'Lautan Biru',
            'nisn' => '245150603',
            'kelas' => 'XI MIPA 2',
            'status' => 'Hadir',
            'jam' => '06:58',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
        [
            'nama' => 'Angkasa Raya',
            'nisn' => '245150604',
            'kelas' => 'XI MIPA 2',
            'status' => 'Hadir',
            'jam' => '06:56',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
        [
            'nama' => 'Putri Andromeda',
            'nisn' => '245150605',
            'kelas' => 'XI MIPA 2',
            'status' => 'Hadir',
            'jam' => '06:56',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
        [
            'nama' => 'Putra Mahkota',
            'nisn' => '245150606',
            'kelas' => 'XI MIPA 2',
            'status' => 'Hadir',
            'jam' => '06:56',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
        [
            'nama' => 'Peri Indah',
            'nisn' => '245150607',
            'kelas' => 'XI MIPA 2',
            'status' => 'Hadir',
            'jam' => '06:56',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
        [
            'nama' => 'Bintang Samudra',
            'nisn' => '245150608',
            'kelas' => 'XI MIPA 2',
            'status' => 'Hadir',
            'jam' => '06:56',
            'metode' => 'QR Code',
            'berkas' => '-',
            'keterangan' => '-',
        ],
    ];
@endphp

@push('styles')
<style>
    .kelola-page {
        font-family: 'Poppins', sans-serif;
        color: #292d38;
        padding: 14px;
        background: #f3f5f9;
        min-height: 100vh;
    }

    .kelola-page * {
        box-sizing: border-box;
    }

    /* HEADER */

    .kelola-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 20px;
    }

    .search-box {
        width: 345px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        height: 37px;
        border: 1px solid #b8c2d2;
        border-radius: 9px;
        padding: 0 38px 0 13px;
        font-size: 11px;
        outline: none;
        background: #fff;
        font-family: inherit;
    }

    .search-box i {
        position: absolute;
        right: 13px;
        top: 11px;
        color: #777;
    }

    .profile-info {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 10px;
    }

    .profile-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: #2457dc;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 600;
        font-size: 13px;
    }

    .profile-info small {
        display: block;
        color: #888;
        margin-top: 3px;
    }

    /* STATISTIK */

    .statistik-layout {
        display: grid;
        grid-template-columns: 175px 1fr 207px;
        gap: 10px;
        margin-bottom: 15px;
    }

    .panel {
        background: white;
        border: 1px solid #b9c4d5;
        border-radius: 9px;
        padding: 13px;
    }

    .chart-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 12px;
    }

    .donut {
        width: 118px;
        height: 118px;
        border-radius: 50%;
        background: conic-gradient(
            #1769c5 0% 85%,
            #ffe000 85% 93%,
            #e50079 93% 100%
        );
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .donut::before {
        content: '';
        position: absolute;
        width: 78px;
        height: 78px;
        border-radius: 50%;
        background: white;
    }

    .donut-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .donut-content strong {
        font-size: 19px;
        display: block;
    }

    .donut-content span {
        font-size: 10px;
        color: #777;
    }

    .chart-legend {
        display: flex;
        gap: 12px;
        font-size: 9px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .legend-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
    }

    .blue { background: #1769c5; }
    .yellow { background: #ffe000; }
    .pink { background: #e50079; }

    /* PROGRESS STATUS */

    .status-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 18px;
    }

    .progress-info {
        display: grid;
        grid-template-columns: 43px 50px 20px 55px 1fr;
        align-items: center;
        gap: 7px;
        font-size: 14px;
    }

    .progress-info .label-izin {
        color: #f0b900;
    }

    .progress-info .label-alpa {
        color: #e50079;
    }

    .progress-number {
        border: 1px solid #ff9a3d;
        border-radius: 8px;
        text-align: center;
        padding: 3px 5px;
        color: #f07819;
        background: #fff3e7;
        font-weight: 600;
    }

    .progress-number.alpa-number {
        border-color: #ff64a8;
        color: #e50079;
        background: #fff0f7;
    }

    .progress-total {
        font-size: 10px;
        color: #888;
        background: #eee;
        padding: 4px;
        border-radius: 6px;
        text-align: center;
    }

    .progress-bar {
        width: 100%;
        height: 24px;
        border-radius: 8px;
        background: #fff8dd;
        overflow: hidden;
        position: relative;
        margin-top: 8px;
    }

    .progress-bar.alpa-bar {
        background: #f9d7e9;
    }

    .progress-fill {
        height: 100%;
        border-radius: 8px;
        background: #ffca00;
    }

    .progress-fill.alpa-fill {
        background: #e50079;
    }

    .progress-percent {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        color: #e0a900;
    }

    .progress-percent.alpa-percent {
        color: #e50079;
    }

    /* FILTER TANGGAL */

    .tanggal-panel h4 {
        font-size: 12px;
        font-weight: 500;
        margin: 0 0 12px;
    }

    .tanggal-panel label {
        display: block;
        font-size: 10px;
        margin-bottom: 5px;
    }

    .tanggal-panel select {
        height: 32px;
        border: 1px solid #aeb9c8;
        border-radius: 9px;
        padding: 0 8px;
        font-family: inherit;
        font-size: 10px;
        color: #666;
        background: #f8f9fb;
        width: 100%;
    }

    .tanggal-row {
        display: grid;
        grid-template-columns: .7fr 1.3fr;
        gap: 10px;
        margin-bottom: 10px;
    }

    .btn-tanggal {
        width: 100%;
        margin-top: 12px;
        height: 27px;
        border: none;
        border-radius: 8px;
        background: #e50079;
        color: white;
        font-size: 10px;
        cursor: pointer;
        font-family: inherit;
    }

    /* TABEL */

    .table-panel {
        padding: 17px;
    }

    .table-tools {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-bottom: 12px;
    }

    .table-tools select {
        width: 155px;
        height: 36px;
        border: 1px solid #aeb8c6;
        border-radius: 22px;
        padding: 0 15px;
        background: #f8f9fb;
        font-family: inherit;
        font-size: 12px;
        color: #666;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .kelola-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
    }

    .kelola-table th {
        padding: 10px 8px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        border-bottom: 1px solid #dce0e6;
        white-space: nowrap;
    }

    .kelola-table td {
        padding: 6px 8px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 10px;
        color: #666;
        white-space: nowrap;
    }

    .kelola-table tbody tr:hover {
        background: #f8fbff;
    }

    .student-name {
        cursor: pointer;
    }

    .student-name:hover {
        color: #008ed0;
        text-decoration: underline;
    }

    .status-badge {
        display: inline-block;
        min-width: 60px;
        text-align: center;
        padding: 4px 9px;
        border-radius: 9px;
        font-size: 10px;
        border: 1px solid;
    }

    .status-hadir {
        background: #d8f0db;
        color: #328c45;
        border-color: #64ad70;
    }

    .status-telat {
        background: #d8efff;
        color: #168acb;
        border-color: #31a5e7;
    }

    .status-izin {
        background: #fff3d5;
        color: #bd8b00;
        border-color: #edc34d;
    }

    .status-sakit {
        background: #f1e8ff;
        color: #8155bd;
        border-color: #b69bdf;
    }

    .status-alpa {
        background: #ffe3ef;
        color: #d92b7a;
        border-color: #ee85b5;
    }

    .btn-edit {
        border: none;
        border-radius: 5px;
        background: #009ed8;
        color: white;
        width: 34px;
        height: 22px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-edit:hover {
        background: #0085b8;
    }

    .empty-message {
        text-align: center;
        padding: 25px;
        font-size: 12px;
        color: #999;
        display: none;
    }

    /* MODAL */

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(25, 30, 40, .48);
        align-items: center;
        justify-content: center;
        padding: 15px;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        width: 100%;
        max-width: 555px;
        max-height: 90vh;
        overflow-y: auto;
        background: white;
        border-radius: 9px;
        padding: 17px;
        position: relative;
        box-shadow: 0 10px 35px rgba(0,0,0,.15);
    }

    .modal-close {
        position: absolute;
        right: 13px;
        top: 10px;
        border: none;
        background: #e50079;
        color: white;
        width: 27px;
        height: 27px;
        border-radius: 5px;
        font-size: 17px;
        cursor: pointer;
        z-index: 2;
    }

    /* BIODATA MODAL */

    .biodata-layout {
        display: grid;
        grid-template-columns: 116px 1fr;
        gap: 22px;
        margin-top: 2px;
    }

    .student-photo {
        height: 155px;
        border-radius: 12px;
        background: linear-gradient(145deg, #254bfa, #426bff);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 35px;
        font-weight: 600;
    }

    .biodata-tabs {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 13px;
    }

    .biodata-tabs button {
        border: none;
        background: transparent;
        color: #e50079;
        font-family: inherit;
        font-size: 9px;
        padding: 8px 12px;
        cursor: pointer;
    }

    .biodata-tabs button.active {
        background: #e50079;
        color: white;
        border-radius: 7px;
        box-shadow: 0 2px 5px #ddd;
    }

    .biodata-tabs button:first-child {
        border: 1px solid #00a0df;
        color: #009bd7;
        border-radius: 7px;
    }

    .biodata-tabs button:first-child.active {
        background: #009bd7;
        color: white;
    }

    .biodata-field {
        margin-bottom: 8px;
    }

    .biodata-field label {
        display: block;
        font-size: 10px;
        margin-bottom: 4px;
    }

    .biodata-field input {
        width: 100%;
        height: 25px;
        border: 1px solid #45b5f3;
        border-radius: 12px;
        padding: 0 11px;
        font-family: inherit;
        font-size: 9px;
        outline: none;
    }

    .btn-biodata-edit {
        width: 100%;
        height: 30px;
        background: #e50079;
        color: white;
        border: none;
        border-radius: 20px;
        margin-top: 12px;
        font-family: inherit;
        font-size: 11px;
        cursor: pointer;
    }

    /* MODAL EDIT */

    .edit-modal-content {
        max-width: 460px;
    }

    .edit-modal-content h3 {
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 15px;
    }

    .edit-modal-content .form-group {
        margin-bottom: 10px;
    }

    .edit-modal-content label {
        display: block;
        font-size: 10px;
        margin-bottom: 4px;
    }

    .edit-modal-content input,
    .edit-modal-content select,
    .edit-modal-content textarea {
        width: 100%;
        height: 28px;
        border: 1px solid #45b5f3;
        border-radius: 11px;
        padding: 4px 10px;
        font-size: 10px;
        font-family: inherit;
        outline: none;
        background: white;
    }

    .edit-modal-content textarea {
        height: 48px;
        resize: vertical;
    }

    .edit-modal-content input:focus,
    .edit-modal-content select:focus,
    .edit-modal-content textarea:focus {
        border-color: #e50079;
    }

    .btn-save {
        width: 100%;
        height: 31px;
        border: none;
        background: #e50079;
        color: white;
        border-radius: 20px;
        margin-top: 10px;
        font-family: inherit;
        font-size: 11px;
        cursor: pointer;
    }

    .btn-save:hover,
    .btn-biodata-edit:hover {
        background: #c90069;
    }

    @media(max-width: 950px) {
        .statistik-layout {
            grid-template-columns: 1fr 1fr;
        }

        .tanggal-panel {
            grid-column: span 2;
        }
    }

    @media(max-width: 650px) {
        .kelola-page {
            padding: 10px;
        }

        .statistik-layout {
            grid-template-columns: 1fr;
        }

        .tanggal-panel {
            grid-column: auto;
        }

        .search-box {
            width: 100%;
        }

        .kelola-header {
            flex-wrap: wrap;
        }

        .biodata-layout {
            grid-template-columns: 1fr;
        }

        .student-photo {
            width: 100px;
            height: 110px;
        }

        .table-tools {
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }
</style>
@endpush

@section('content')

<div class="kelola-page">

    {{-- HEADER --}}
    <div class="kelola-header">

        <div class="search-box">
            <input type="text" id="searchSiswa"
                placeholder="Ketik nama, nisn, atau kelas siswa">
            <i>⌕</i>
        </div>

        <div class="profile-info">
            <div class="profile-avatar">M</div>
            <div>
                <strong>Mutiara Bintang</strong>
                <small>Wali Kelas</small>
            </div>
            <span style="margin-left:15px;">⌄</span>
        </div>

    </div>

    {{-- STATISTIK --}}
    <div class="statistik-layout">

        {{-- GRAFIK --}}
        <div class="panel chart-panel">

            <div class="donut">
                <div class="donut-content">
                    <strong id="persenHadir">85%</strong>
                    <span>Hadir</span>
                </div>
            </div>

            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-dot blue"></span> Hadir
                </div>
                <div class="legend-item">
                    <span class="legend-dot yellow"></span> Telat
                </div>
                <div class="legend-item">
                    <span class="legend-dot pink"></span> Alpa
                </div>
            </div>

        </div>

        {{-- STATISTIK IZIN DAN ALPA --}}
        <div class="panel status-panel">

            <div>
                <div class="progress-info">
                    <span class="label-izin">Izin</span>
                    <strong class="progress-number" id="jumlahIzin">0</strong>
                    <span>/</span>
                    <span class="progress-total" id="totalSiswa1">9</span>
                    <span style="color:#e0ae00;">Siswa</span>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" id="barIzin"
                         style="width:22%;"></div>
                    <span class="progress-percent" id="persenIzin">11%</span>
                </div>
            </div>

            <div>
                <div class="progress-info">
                    <span class="label-alpa">Alpa</span>
                    <strong class="progress-number alpa-number"
                        id="jumlahAlpa">0</strong>
                    <span>/</span>
                    <span class="progress-total" id="totalSiswa2">9</span>
                    <span style="color:#e50079;">Siswa</span>
                </div>

                <div class="progress-bar alpa-bar">
                    <div class="progress-fill alpa-fill" id="barAlpa"
                         style="width:10%;"></div>
                    <span class="progress-percent alpa-percent"
                        id="persenAlpa">4%</span>
                </div>
            </div>

        </div>

        {{-- FILTER TANGGAL --}}
        <div class="panel tanggal-panel">

            <h4>Tanggal Presensi</h4>

            <div class="tanggal-row">
                <div>
                    <label>Tanggal</label>
                    <select id="filterHari">
                        <option value="">Semua</option>
                        @for($i = 1; $i <= 31; $i++)
                            <option value="{{ sprintf('%02d', $i) }}">
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label>Bulan</label>
                    <select id="filterBulan">
                        <option value="">Semua</option>
                        @foreach([
                            '01'=>'Januari',
                            '02'=>'Februari',
                            '03'=>'Maret',
                            '04'=>'April',
                            '05'=>'Mei',
                            '06'=>'Juni',
                            '07'=>'Juli',
                            '08'=>'Agustus',
                            '09'=>'September',
                            '10'=>'Oktober',
                            '11'=>'November',
                            '12'=>'Desember'
                        ] as $angka => $bulan)
                            <option value="{{ $angka }}">{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label>Tahun</label>
                <select id="filterTahun">
                    <option value="">Semua Tahun</option>
                    <option value="2026" selected>2026</option>
                    <option value="2027">2027</option>
                </select>
            </div>

            <button type="button" class="btn-tanggal" id="btnFilterTanggal">
                Pilih Tanggal
            </button>

        </div>

    </div>

    {{-- TABEL DATA PRESENSI --}}
    <div class="panel table-panel">

        <div class="table-tools">

            <select id="filterStatus">
                <option value="">Semua Status</option>
                <option value="Hadir">Hadir</option>
                <option value="Telat">Telat</option>
                <option value="Izin">Izin</option>
                <option value="Sakit">Sakit</option>
                <option value="Alpa">Alpa</option>
            </select>

            <select id="urutanData">
                <option value="terbaru">Terbaru</option>
                <option value="terlama">Terlama</option>
            </select>

        </div>

        <div class="table-responsive">

            <table class="kelola-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NISN</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Jam</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="tabelSiswa">

                    @foreach($siswa as $index => $item)

                        <tr
                            data-nama="{{ $item['nama'] }}"
                            data-nisn="{{ $item['nisn'] }}"
                            data-kelas="{{ $item['kelas'] }}"
                            data-status="{{ $item['status'] }}"
                            data-tanggal="2026-11-03"
                            data-jam="{{ $item['jam'] }}"
                            data-metode="{{ $item['metode'] }}"
                            data-berkas="{{ $item['berkas'] }}"
                            data-keterangan="{{ $item['keterangan'] }}"
                        >

                            <td>{{ $index + 1 }}</td>

                            <td>
                                <span class="student-name"
                                    onclick="openBiodata(this.closest('tr'))">
                                    {{ $item['nama'] }}
                                </span>
                            </td>

                            <td>{{ $item['nisn'] }}</td>
                            <td>{{ $item['kelas'] }}</td>

                            <td>
                                <span class="status-badge status-{{ strtolower($item['status']) }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>

                            <td class="jam-cell">
                                {{ $item['jam'] }} WIB
                            </td>

                            <td class="metode-cell">
                                {{ $item['metode'] }}
                            </td>

                            <td>
                                <button type="button" class="btn-edit"
                                    onclick="openEdit(this.closest('tr'))"
                                    title="Edit Presensi">
                                    ✎
                                </button>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <div class="empty-message" id="emptyMessage">
                Data presensi tidak ditemukan.
            </div>

        </div>

    </div>

</div>

{{-- MODAL BIODATA SISWA --}}
<div class="modal-overlay" id="biodataModal">

    <div class="modal-content">

        <button class="modal-close" onclick="closeModal('biodataModal')">
            ×
        </button>

        <div class="biodata-layout">

            <div class="student-photo">
                M
            </div>

            <div>

                <div class="biodata-tabs">
                    <button type="button" class="active">Riwayat Presensi</button>
                    <button type="button">Biodata</button>
                    <button type="button">Orang Tua</button>
                    <button type="button">Kurikulum</button>
                </div>

                <div class="biodata-field">
                    <label>Nama</label>
                    <input type="text" id="bioNama" readonly>
                </div>

                <div class="biodata-field">
                    <label>NISN</label>
                    <input type="text" id="bioNisn" readonly>
                </div>

                <div class="biodata-field">
                    <label>Jenis Kelamin</label>
                    <input type="text" id="bioJK" readonly>
                </div>

                <div class="biodata-field">
                    <label>Tempat Tanggal Lahir</label>
                    <input type="text" id="bioLahir" readonly>
                </div>

                <div class="biodata-field">
                    <label>Alamat</label>
                    <input type="text" id="bioAlamat" readonly>
                </div>

                <div class="biodata-field">
                    <label>No Handphone</label>
                    <input type="text" id="bioHp" readonly>
                </div>

                <button class="btn-biodata-edit"
                    onclick="editDariBiodata()">
                    Edit ✎
                </button>

            </div>

        </div>

    </div>

</div>

{{-- MODAL EDIT PRESENSI --}}
<div class="modal-overlay" id="editModal">

    <div class="modal-content edit-modal-content">

        <button class="modal-close" onclick="closeModal('editModal')">
            ×
        </button>

        <h3>Edit Presensi Siswa</h3>

        <div class="form-group">
            <label>Nama</label>
            <input type="text" id="editNama" readonly>
        </div>

        <div class="form-group">
            <label>NISN</label>
            <input type="text" id="editNisn" readonly>
        </div>

        <div class="form-group">
            <label>Kelas</label>
            <input type="text" id="editKelas" readonly>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select id="editStatus">
                <option value="Hadir">Hadir</option>
                <option value="Telat">Telat</option>
                <option value="Izin">Izin</option>
                <option value="Sakit">Sakit</option>
                <option value="Alpa">Alpa</option>
            </select>
        </div>

        <div class="form-group">
            <label>Jam</label>
            <input type="time" id="editJam">
        </div>

        <div class="form-group">
            <label>Metode</label>
            <select id="editMetode">
                <option value="QR Code">QR Code</option>
                <option value="Manual">Manual</option>
            </select>
        </div>

        <div class="form-group">
            <label>Berkas Pendukung</label>
            <input type="text" id="editBerkas"
                placeholder="Nama berkas pendukung">
        </div>

        <div class="form-group">
            <label>Keterangan</label>
            <textarea id="editKeterangan"
                placeholder="Masukkan keterangan presensi"></textarea>
        </div>

        <button type="button" class="btn-save" onclick="simpanEdit()">
            Simpan Perubahan ✎
        </button>

    </div>

</div>

@endsection

@push('scripts')
<script>
    let selectedRow = null;

    // MEMBUKA MODAL BIODATA
    function openBiodata(row) {
        selectedRow = row;

        document.getElementById('bioNama').value =
            row.dataset.nama;

        document.getElementById('bioNisn').value =
            row.dataset.nisn;

        // Data contoh biodata
        document.getElementById('bioJK').value =
            'Perempuan';

        document.getElementById('bioLahir').value =
            'Malang, 17 Agustus 2009';

        document.getElementById('bioAlamat').value =
            'Jl. Semesta No. 09 Kec. Lowokwaru Kota Malang';

        document.getElementById('bioHp').value =
            '088556677334';

        document.getElementById('biodataModal').classList.add('active');
    }

    // MEMBUKA MODAL EDIT
    function openEdit(row) {
        selectedRow = row;

        document.getElementById('editNama').value =
            row.dataset.nama;

        document.getElementById('editNisn').value =
            row.dataset.nisn;

        document.getElementById('editKelas').value =
            row.dataset.kelas;

        document.getElementById('editStatus').value =
            row.dataset.status;

        document.getElementById('editJam').value =
            row.dataset.jam;

        document.getElementById('editMetode').value =
            row.dataset.metode;

        document.getElementById('editBerkas').value =
            row.dataset.berkas === '-' ? '' : row.dataset.berkas;

        document.getElementById('editKeterangan').value =
            row.dataset.keterangan === '-' ? '' : row.dataset.keterangan;

        document.getElementById('editModal').classList.add('active');
    }

    // EDIT DARI MODAL BIODATA
    function editDariBiodata() {
        if (!selectedRow) return;

        document.getElementById('biodataModal').classList.remove('active');

        openEdit(selectedRow);
    }

    // MENUTUP MODAL
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // SIMPAN EDIT TAMPILAN
    function simpanEdit() {
        if (!selectedRow) return;

        const status = document.getElementById('editStatus').value;
        const jam = document.getElementById('editJam').value;
        const metode = document.getElementById('editMetode').value;
        const berkas = document.getElementById('editBerkas').value.trim();
        const keterangan = document.getElementById('editKeterangan').value.trim();

        selectedRow.dataset.status = status;
        selectedRow.dataset.jam = jam;
        selectedRow.dataset.metode = metode;
        selectedRow.dataset.berkas = berkas || '-';
        selectedRow.dataset.keterangan = keterangan || '-';

        // Update status tabel
        const badge = selectedRow.querySelector('.status-badge');

        badge.textContent = status;
        badge.className = 'status-badge status-' + status.toLowerCase();

        // Update jam
        selectedRow.querySelector('.jam-cell').textContent =
            jam ? jam + ' WIB' : '-';

        // Update metode
        selectedRow.querySelector('.metode-cell').textContent =
            metode;

        updateStatistik();
        filterData();

        closeModal('editModal');

        alert('Perubahan berhasil diterapkan pada tampilan. Data belum tersimpan ke database.');
    }

    // MENGHITUNG STATISTIK
    function updateStatistik() {
        const allRows = [
            ...document.querySelectorAll('#tabelSiswa tr')
        ];

        const total = allRows.length;

        const count = status =>
            allRows.filter(row => row.dataset.status === status).length;

        const hadir = count('Hadir');
        const telat = count('Telat');
        const izin = count('Izin');
        const sakit = count('Sakit');
        const alpa = count('Alpa');

        const persenHadir = total
            ? Math.round((hadir + telat) / total * 100)
            : 0;

        document.getElementById('persenHadir').textContent =
            persenHadir + '%';

        document.querySelector('.donut').style.background =
            `conic-gradient(
                #1769c5 0% ${total ? hadir / total * 100 : 0}%,
                #ffe000 ${total ? hadir / total * 100 : 0}% ${total ? (hadir + telat) / total * 100 : 0}%,
                #e50079 ${total ? (hadir + telat) / total * 100 : 0}% 100%
            )`;

        document.getElementById('jumlahIzin').textContent =
            izin + sakit;

        document.getElementById('jumlahAlpa').textContent =
            alpa;

        document.getElementById('totalSiswa1').textContent = total;
        document.getElementById('totalSiswa2').textContent = total;

        const persenIzin = total
            ? Math.round((izin + sakit) / total * 100)
            : 0;

        const persenAlpa = total
            ? Math.round(alpa / total * 100)
            : 0;

        document.getElementById('persenIzin').textContent =
            persenIzin + '%';

        document.getElementById('persenAlpa').textContent =
            persenAlpa + '%';

        document.getElementById('barIzin').style.width =
            persenIzin + '%';

        document.getElementById('barAlpa').style.width =
            persenAlpa + '%';
    }

    // FILTER DAN PENCARIAN
    function filterData() {
        const keyword = document.getElementById('searchSiswa')
            .value.toLowerCase();

        const status = document.getElementById('filterStatus').value;

        const hari = document.getElementById('filterHari').value;
        const bulan = document.getElementById('filterBulan').value;
        const tahun = document.getElementById('filterTahun').value;

        let jumlah = 0;

        document.querySelectorAll('#tabelSiswa tr').forEach(row => {
            const nama = row.dataset.nama.toLowerCase();
            const nisn = row.dataset.nisn.toLowerCase();
            const kelas = row.dataset.kelas.toLowerCase();

            const tanggal = row.dataset.tanggal.split('-');

            const cocokKeyword =
                nama.includes(keyword) ||
                nisn.includes(keyword) ||
                kelas.includes(keyword);

            const cocokStatus =
                !status || row.dataset.status === status;

            const cocokHari =
                !hari || tanggal[2] === hari;

            const cocokBulan =
                !bulan || tanggal[1] === bulan;

            const cocokTahun =
                !tahun || tanggal[0] === tahun;

            const tampil =
                cocokKeyword &&
                cocokStatus &&
                cocokHari &&
                cocokBulan &&
                cocokTahun;

            row.style.display = tampil ? '' : 'none';

            if (tampil) jumlah++;
        });

        document.getElementById('emptyMessage').style.display =
            jumlah === 0 ? 'block' : 'none';
    }

    // URUTAN DATA
    document.getElementById('urutanData').addEventListener('change', function() {
        const tbody = document.getElementById('tabelSiswa');

        const rows = [...tbody.querySelectorAll('tr')];

        if (this.value === 'terbaru') {
            rows.reverse();
        }

        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
            tbody.appendChild(row);
        });
    });

    // EVENT FILTER
    [
        'searchSiswa',
        'filterStatus',
        'filterHari',
        'filterBulan',
        'filterTahun'
    ].forEach(id => {
        document.getElementById(id).addEventListener('input', filterData);
        document.getElementById(id).addEventListener('change', filterData);
    });

    document.getElementById('btnFilterTanggal')
        .addEventListener('click', filterData);

    // TUTUP MODAL KETIKA KLIK BAGIAN LUAR
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    // INISIALISASI
    updateStatistik();
</script>
@endpush
