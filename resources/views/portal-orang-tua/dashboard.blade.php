{{--
    resources/views/portal-orang-tua/dashboard.blade.php
    Variabel dari PortalDashboardController@index:
    $siswa, $ortu, $sapaan, $sinkronisasi, $presensi, $hariIni, $rataRata, $predikat, $peringkat,
    $mapelUnggul, $riasec, $prodiTop, $tes, $bk, $pengumuman, $kktp
--}}

@extends('portal-orang-tua.layout')

@section('title', 'Dashboard - Portal Orang Tua')

@section('portal')
@php
    // Donut presensi (conic-gradient) dihitung dari data
    $segmen = [['#1565c0', $presensi->hadir], ['#334155', $presensi->sakit], ['#f5b800', $presensi->izin], ['#e11d77', $presensi->alpha]];
    $total  = max(1, array_sum(array_column($segmen, 1)));
    $acc    = 0; $stops = [];
    foreach ($segmen as [$warna, $n]) {
        if ($n <= 0) { continue; }
        $a = round($acc / $total * 100, 2); $acc += $n; $b = round($acc / $total * 100, 2);
        $stops[] = "{$warna} {$a}% {$b}%";
    }
    $donut = 'conic-gradient(' . implode(', ', $stops) . ')';
@endphp

{{-- ===== Hero ===== --}}
<section class="pt-card pt-hero">
    <div class="pt-hero__main">
        <span class="pt-chip pt-chip--blue pt-chip--caps"><i class="pt-dot"></i>Tahun Pelajaran {{ $siswa->tahun_ajaran }}</span>
        <h1 class="pt-hero__title">{{ $sapaan }}, <em>{{ $ortu->nama }}</em></h1>
        <p class="pt-hero__lead">Pantau perkembangan akademik, presensi harian, dan aktivitas pembelajaran ananda secara terpadu melalui Portal Orang Tua SMAN 1 Malang.</p>
        <span class="pt-sync"><x-portal.icon name="sync" :size="12" /> Sinkronisasi: Hari ini {{ $sinkronisasi->format('H.i') }} WIB</span>
    </div>

    <aside class="pt-student">
        <div class="pt-student__top">
            <x-portal.avatar :src="$siswa->foto" :initials="$siswa->inisial" class="pt-avatar--md" />
            <div>
                <strong>{{ $siswa->nama }}</strong>
                <span>NIS: {{ $siswa->nis }} • NISN: {{ $siswa->nisn }}</span>
            </div>
        </div>
        <div class="pt-student__grid">
            <div class="pt-mini"><small>Kelas</small><strong>{{ $siswa->kelas }}</strong></div>
            <div class="pt-mini"><small>Semester Aktif</small><strong>{{ $siswa->semester }} ({{ substr($siswa->tahun_ajaran, 2, 2) }}/{{ substr($siswa->tahun_ajaran, 7, 2) }})</strong></div>
        </div>
        <div class="pt-mini pt-mini--wide">
            <x-portal.icon name="user" :size="16" />
            <div><small>Wali Kelas</small><strong>{{ $siswa->wali_kelas->nama }}</strong></div>
        </div>
    </aside>
</section>

{{-- ===== 3 kartu statistik ===== --}}
<section class="pt-grid pt-grid--3">
    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Tingkat Kehadiran</span><span class="pt-iconbox"><x-portal.icon name="cal-check" :size="18" /></span></header>
        <p class="pt-stat__value">{{ number_format($presensi->persen_hadir, 1) }}%
            <span class="pt-chip pt-chip--blue pt-chip--sm"><x-portal.icon name="trending" :size="10" />+{{ number_format($presensi->tren, 1) }}%</span></p>
        <div class="pt-bar"><i style="width: {{ $presensi->persen_hadir }}%"></i></div>
        <p class="pt-stat__note">Hadir <b>{{ $presensi->hadir }}</b> dari <b>{{ $presensi->efektif }} hari</b> efektif (Semester {{ $siswa->semester }} berjalan).</p>
    </article>

    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Rata-rata Nilai</span><span class="pt-iconbox"><x-portal.icon name="star" :size="18" /></span></header>
        <p class="pt-stat__value">{{ number_format($rataRata, 2) }}
            <span class="pt-chip pt-chip--blue pt-chip--sm">Predikat {{ $predikat }}</span></p>
        <div class="pt-bar"><i style="width: {{ min(100, $rataRata) }}%"></i></div>
        <p class="pt-stat__note">Peringkat <b>{{ $peringkat['posisi'] }}</b> dari <b>{{ $peringkat['dari'] }} siswa</b> di kelas {{ str_replace(' - ', ' ', $siswa->kelas) }}.</p>
    </article>

    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Profil Minat-Bakat</span><span class="pt-iconbox"><x-portal.icon name="bulb" :size="18" /></span></header>
        <p class="pt-stat__title">{{ $riasec->judul }} ({{ $riasec->kode }})</p>
        <div class="pt-tags">
            @foreach ($riasec->tag as $t)
                <span class="pt-chip pt-chip--soft pt-chip--sm">{{ $t }}</span>
            @endforeach
        </div>
        <p class="pt-stat__note">Terakhir diperbarui: <b>{{ $riasec->diperbarui->locale('id')->translatedFormat('d M Y') }}</b> oleh {{ $riasec->oleh }}.</p>
    </article>
</section>

{{-- ===== Presensi + Akademik ===== --}}
<section class="pt-grid pt-grid--2">
    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2"><x-portal.icon name="cal-check" :size="18" /> Ringkasan Presensi</h2>
            <span class="pt-chip pt-chip--blue pt-chip--sm">{{ $presensi->efektif }} Hari Efektif</span>
        </header>

        <div class="pt-presensi">
            <div class="pt-donut" style="background: {{ $donut }}"><div><b>{{ $presensi->hadir }}</b><small>HADIR</small></div></div>
            <div class="pt-tiles">
                <div class="pt-tile"><small><i class="pt-dot" style="background:#1565c0"></i>Hadir</small><strong>{{ $presensi->hadir }} Hari</strong></div>
                <div class="pt-tile"><small><i class="pt-dot" style="background:#334155"></i>Sakit</small><strong>{{ $presensi->sakit }} Hari</strong></div>
                <div class="pt-tile"><small><i class="pt-dot" style="background:#f5b800"></i>Izin</small><strong>{{ $presensi->izin }} Hari</strong></div>
                <div class="pt-tile"><small><i class="pt-dot" style="background:#e11d77"></i>Alpha</small><strong>{{ $presensi->alpha }} Hari</strong></div>
            </div>
        </div>

        <div class="pt-strip">
            <span class="pt-iconbox pt-iconbox--white"><x-portal.icon name="door" :size="16" /></span>
            <div><small>Kehadiran Hari Ini</small><strong>Pukul {{ $hariIni->jam }} WIB • {{ $hariIni->gerbang }}</strong></div>
            <span class="pt-chip pt-chip--white pt-chip--sm">{{ $hariIni->label }}</span>
        </div>

        <div class="pt-card__foot">
            <a href="{{ route('portal.presensi') }}" class="pt-btn pt-btn--soft">Lihat Presensi Lengkap <x-portal.icon name="arrow" :size="14" /></a>
        </div>
    </article>

    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2"><x-portal.icon name="book" :size="18" /> Ringkasan Akademik</h2>
            <span class="pt-chip pt-chip--soft pt-chip--sm"><x-portal.icon name="check" :size="11" /> STS Ganjil Selesai</span>
        </header>

        <p class="pt-eyebrow pt-eyebrow--mt">Mata pelajaran nilai unggul</p>
        <ol class="pt-rank">
            @foreach ($mapelUnggul as $m)
                <li>
                    <span class="pt-rank__no">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div><strong>{{ $m->mapel }}</strong><small>{{ $m->guru }}</small></div>
                    <div class="pt-rank__val"><b>{{ $m->nilai }}</b><small>KKTP: {{ $kktp }}</small></div>
                </li>
            @endforeach
        </ol>

        <div class="pt-card__foot">
            <a href="{{ route('portal.akademik') }}" class="pt-btn pt-btn--primary">Lihat Rapor & Nilai <x-portal.icon name="chart" :size="14" /></a>
        </div>
    </article>
</section>

{{-- ===== Minat-Bakat + Pengumuman ===== --}}
<section class="pt-grid pt-grid--2">
    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2"><x-portal.icon name="compass" :size="18" /> Minat-Bakat & Karier</h2>
            <span class="pt-chip pt-chip--soft pt-chip--sm">{{ $siswa->fase }} (Kelas XI)</span>
        </header>

        <div class="pt-reco">
            <div class="pt-reco__top"><small>Rekomendasi rumpun program studi</small><b>Kesesuaian {{ $prodiTop->cocok }}%</b></div>
            <strong class="pt-reco__title">{{ $prodiTop->rumpun }}</strong>
            <div class="pt-tags">
                @foreach ($prodiTop->prodi as $p)<span class="pt-chip pt-chip--white pt-chip--sm">{{ $p }}</span>@endforeach
            </div>
        </div>

        <div class="pt-grid pt-grid--2 pt-grid--tight">
            <div class="pt-strip pt-strip--sm"><span class="pt-iconbox pt-iconbox--sm"><x-portal.icon name="bulb" :size="16" /></span>
                <div><small>Tes Psikologi</small><strong>{{ $tes->status }}</strong></div></div>
            <div class="pt-strip pt-strip--sm"><span class="pt-iconbox pt-iconbox--sm"><x-portal.icon name="headset" :size="16" /></span>
                <div><small>Konseling BK</small><strong>{{ $bk->status }}</strong></div></div>
        </div>

        <div class="pt-card__foot">
            <a href="{{ route('portal.minat-bakat') }}" class="pt-btn pt-btn--soft">Lihat Detail Asesmen <x-portal.icon name="external" :size="14" /></a>
        </div>
    </article>

    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2"><x-portal.icon name="megaphone" :size="18" /> Pengumuman Resmi Sekolah</h2>
            <span class="pt-chip pt-chip--soft pt-chip--sm">{{ count($pengumuman) }} Info Baru</span>
        </header>

        <div class="pt-news">
            @foreach ($pengumuman as $p)
                <a href="#" class="pt-news__item" data-pt-soon>
                    <span class="pt-iconbox pt-iconbox--sm"><x-portal.icon :name="$p->ikon" :size="16" /></span>
                    <div>
                        <div class="pt-news__meta"><b>{{ $p->kategori }}</b><span>{{ $p->tanggal->locale('id')->translatedFormat('d M Y') }}</span></div>
                        <strong>{{ $p->judul }}</strong>
                        <p>{{ $p->ringkas }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pt-card__foot">
            <a href="#" class="pt-btn pt-btn--soft" data-pt-soon>Semua Pengumuman <x-portal.icon name="list" :size="14" /></a>
        </div>
    </article>
</section>

{{-- ===== CTA konsultasi ===== --}}
<section class="pt-card pt-cta">
    <span class="pt-iconbox pt-iconbox--lg"><x-portal.icon name="headset" :size="22" /></span>
    <div>
        <h2 class="pt-h2">Butuh Konsultasi dengan Wali Kelas atau BK?</h2>
        <p>Layanan pendampingan siswa dan janji temu temu daring/luring dapat diajukan langsung melalui portal ini.</p>
    </div>
    <div class="pt-cta__btns">
        <a href="#" class="pt-btn pt-btn--soft" data-pt-soon><x-portal.icon name="message" :size="14" /> Hubungi Wali Kelas</a>
        <a href="#" class="pt-btn pt-btn--primary" data-pt-soon><x-portal.icon name="calendar" :size="14" /> Jadwalkan Konseling</a>
    </div>
</section>
@endsection