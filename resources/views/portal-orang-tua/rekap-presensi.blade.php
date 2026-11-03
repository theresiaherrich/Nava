{{--
    resources/views/portal-orang-tua/rekap-presensi.blade.php
    Variabel dari RekapPresensiController@index:
    $siswa, $ortu, $ringkasan, $sinkronisasi, $bulan (Carbon), $bulanPrev, $bulanNext, $bisaMaju,
    $minggu (array minggu -> 7 sel), $log (tanggal -> detail), $terpilih (Y-m-d), $labelStatus
--}}

@extends('portal-orang-tua.layout')

@section('title', 'Rekap Presensi - Portal Orang Tua')

@section('portal')
@php
    $detail = $log[$terpilih] ?? null;
    $hariNama = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
@endphp

{{-- ===== Header ===== --}}
<section class="pt-pagehead pt-pagehead--plain">
    <div>
        <nav class="pt-crumb" aria-label="Breadcrumb">
            <a href="{{ route('portal.dashboard') }}">Portal Orang Tua</a>
            <x-portal.icon name="chev-right" :size="10" /> <b>Presensi</b>
        </nav>
        <h1 class="pt-h1">Presensi Siswa</h1>
        <p class="pt-lead">Monitoring catatan kehadiran harian, ketepatan waktu, dan rekapitulasi presensi real-time {{ $siswa->nama }} ({{ str_replace(' - ', ' ', $siswa->kelas) }}).</p>
        <div class="pt-tags">
            <span class="pt-chip pt-chip--blue pt-chip--caps pt-chip--sm">Sistem presensi RFID terintegrasi</span>
            <span class="pt-sync"><i class="pt-dot"></i> Sinkronisasi pukul {{ $sinkronisasi->format('H:i') }} WIB</span>
        </div>
    </div>

    <div class="pt-pagehead__side">
        <div class="pt-card pt-chipcard">
            <x-portal.avatar :initials="$siswa->inisial" class="pt-avatar--sm pt-avatar--soft" />
            <div><strong>{{ $siswa->nama_pendek }}</strong><small>NISN: {{ $siswa->nisn }} • No. Absen: {{ str_pad($siswa->no_presensi, 2, '0', STR_PAD_LEFT) }}</small></div>
        </div>
        <a href="{{ route('portal.presensi.unduh') }}" class="pt-btn pt-btn--primary"><x-portal.icon name="download" :size="14" /> Unduh Rekap (PDF)</a>
    </div>
</section>

{{-- ===== 4 kartu ringkasan ===== --}}
<section class="pt-grid pt-grid--4">
    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Total Hadir</span><span class="pt-iconbox pt-iconbox--round"><x-portal.icon name="check" :size="16" /></span></header>
        <p class="pt-stat__value">{{ $ringkasan->hadir }} <small>Hari</small>
            <span class="pt-chip pt-chip--blue pt-chip--sm">{{ number_format($ringkasan->persen_hadir, 1) }}%</span></p>
        <div class="pt-strip pt-strip--sm pt-strip--split">
            <span><i class="pt-dot"></i> Tepat Waktu: <b>{{ $ringkasan->tepat_waktu }}</b></span>
            <span><i class="pt-dot pt-dot--light"></i> Toleransi: <b>{{ $ringkasan->toleransi }}</b></span>
        </div>
    </article>

    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Sakit (Kondisi Medis)</span><span class="pt-iconbox"><x-portal.icon name="medkit" :size="16" /></span></header>
        <p class="pt-stat__value">{{ $ringkasan->sakit }} <small>Hari</small><span class="pt-stat__pct">{{ $ringkasan->persen_sakit }}%</span></p>
        <div class="pt-strip pt-strip--sm"><x-portal.icon name="check" :size="13" /> {{ $ringkasan->ket_sakit }}</div>
    </article>

    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Izin Kedinasan / Lomba</span><span class="pt-iconbox"><x-portal.icon name="cal-check" :size="16" /></span></header>
        <p class="pt-stat__value">{{ $ringkasan->izin }} <small>Hari</small><span class="pt-stat__pct">{{ $ringkasan->persen_izin }}%</span></p>
        <div class="pt-strip pt-strip--sm"><x-portal.icon name="trophy" :size="13" /> {{ $ringkasan->ket_izin }}</div>
    </article>

    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Tanpa Keterangan (Alpha)</span><span class="pt-iconbox"><x-portal.icon name="shield" :size="16" /></span></header>
        <p class="pt-stat__value">{{ $ringkasan->alpha }} <small>Hari</small>
            @if ($ringkasan->alpha === 0)<span class="pt-chip pt-chip--soft pt-chip--sm">Nihil</span>@endif</p>
        <div class="pt-strip pt-strip--sm"><x-portal.icon name="thumbs" :size="13" /> {{ $ringkasan->alpha === 0 ? 'Disiplin Terjaga Baik' : 'Perlu perhatian wali murid' }}</div>
    </article>
</section>

{{-- ===== Kalender + Detail log ===== --}}
<section class="pt-grid pt-grid--cal">
    <article class="pt-card pt-cal">
        <header class="pt-card__head">
            <div class="pt-titleicon">
                <span class="pt-iconbox pt-iconbox--lg"><x-portal.icon name="calendar" :size="20" /></span>
                <div><h2 class="pt-h2">Kalender Kehadiran</h2><small>Klik salah satu tanggal untuk memuat rincian inspeksi log.</small></div>
            </div>
            <div class="pt-monthnav">
                <a href="{{ route('portal.presensi', ['bulan' => $bulanPrev]) }}" aria-label="Bulan sebelumnya"><x-portal.icon name="chev-left" :size="14" /></a>
                <span>{{ $bulan->locale('id')->translatedFormat('F Y') }}</span>
                @if ($bisaMaju)
                    <a href="{{ route('portal.presensi', ['bulan' => $bulanNext]) }}" aria-label="Bulan berikutnya"><x-portal.icon name="chev-right" :size="14" /></a>
                @else
                    <span class="is-disabled" aria-hidden="true"><x-portal.icon name="chev-right" :size="14" /></span>
                @endif
            </div>
        </header>

        <div class="pt-cal__scroll">
            <div class="pt-cal__grid" role="grid" aria-label="Kalender kehadiran {{ $bulan->locale('id')->translatedFormat('F Y') }}">
                @foreach ($hariNama as $i => $h)
                    <div class="pt-cal__dow {{ $i === 6 ? 'is-sun' : '' }}">{{ $h }}</div>
                @endforeach

                @foreach ($minggu as $pekan)
                    @foreach ($pekan as $sel)
                        @if (! $sel->dalam_bulan)
                            <div class="pt-cal__cell is-out"><span class="pt-cal__num">{{ $sel->hari }}</span></div>
                        @else
                            <button type="button"
                                    class="pt-cal__cell pt-st--{{ $sel->status }} {{ $sel->tanggal === $terpilih ? 'is-selected' : '' }}"
                                    data-date="{{ $sel->tanggal }}"
                                    @if ($sel->libur || $sel->status === 'belum') disabled @endif
                                    aria-label="{{ $sel->hari }} {{ $bulan->locale('id')->translatedFormat('F') }}: {{ $labelStatus[$sel->status] ?? '' }}">
                                <span class="pt-cal__num">{{ $sel->hari }}</span>
                                @if ($sel->libur)
                                    <span class="pt-cal__off">Libur</span>
                                @elseif ($sel->status !== 'belum')
                                    <i class="pt-cal__dot"></i>
                                @endif
                            </button>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>

        <ul class="pt-legend">
            <li><i class="pt-cal__dot pt-lg--hadir"></i> Hadir Tepat Waktu</li>
            <li><i class="pt-cal__dot pt-lg--toleransi"></i> Terlambat Toleransi</li>
            <li><i class="pt-cal__dot pt-lg--izin"></i> Izin</li>
            <li><i class="pt-cal__dot pt-lg--sakit"></i> Sakit</li>
            <li><i class="pt-cal__dot pt-lg--alpha"></i> Alpha</li>
        </ul>
        <p class="pt-note pt-note--plain">Gerbang ditutup pukul 06.55 WIB</p>
    </article>

    <aside class="pt-card pt-log" id="pt-log">
        <header class="pt-card__head">
            <h2 class="pt-h2"><x-portal.icon name="radio" :size="18" /> Detail Log RFID</h2>
            <span class="pt-chip pt-chip--soft pt-chip--sm" data-log="status">{{ $labelStatus[$detail['status'] ?? 'belum'] ?? '-' }}</span>
        </header>

        <div class="pt-log__date">
            <small>Tanggal Terpilih:</small>
            <strong data-log="tanggal">{{ $detail['tanggal_label'] ?? '-' }}</strong>
        </div>

        <div class="pt-grid pt-grid--2 pt-grid--tight">
            <div class="pt-mini"><small><x-portal.icon name="arrow" :size="11" /> Masuk</small><strong data-log="masuk">{{ ($detail['masuk'] ?? null) ? $detail['masuk'] . ' WIB' : '-' }}</strong><em>Batas masuk 06.55</em></div>
            <div class="pt-mini"><small><x-portal.icon name="door" :size="11" /> Kepulangan</small><strong data-log="pulang">{{ ($detail['pulang'] ?? null) ? $detail['pulang'] . ' WIB' : '-' }}</strong><em>Sesuai jadwal KBM</em></div>
        </div>

        <dl class="pt-dl pt-dl--plain">
            <div><dt><x-portal.icon name="door" :size="14" /> Titik Gerbang:</dt><dd data-log="gerbang">{{ $detail['gerbang'] ?? '-' }}</dd></div>
            <div><dt><x-portal.icon name="thermo" :size="14" /> Sensor Suhu:</dt><dd data-log="suhu">{{ $detail['suhu'] ?? '-' }}</dd></div>
            <div><dt><x-portal.icon name="radio" :size="14" /> Metode Autentikasi:</dt><dd data-log="metode">{{ $detail['metode'] ?? '-' }}</dd></div>
        </dl>

        <div class="pt-panel">
            <span class="pt-eyebrow">Catatan aktivitas / guru piket</span>
            <p data-log="catatan">{{ ($detail['catatan'] ?? null) ?: ($detail['keterangan'] ?? '-') }}</p>
        </div>

        <footer class="pt-log__foot">
            <span class="pt-note pt-note--plain"><x-portal.icon name="shield" :size="13" /> Terverifikasi Kesiswaan</span>
            <a href="#" class="pt-btn pt-btn--soft pt-btn--sm" data-pt-soon>Kirim Catatan Guru</a>
        </footer>
    </aside>
</section>

{{-- Data log untuk JS (klik tanggal tanpa reload) --}}
<script type="application/json" id="pt-log-data">@json(['log' => $log, 'label' => $labelStatus])</script>
@endsection
