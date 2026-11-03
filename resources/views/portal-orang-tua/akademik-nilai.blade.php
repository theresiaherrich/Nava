{{--
    resources/views/portal-orang-tua/akademik-nilai.blade.php
    Variabel dari AkademikNilaiController@index:
    $siswa, $ortu, $nilai (koleksi: no, mapel, kelompok, kelompok_kelas, nilai, predikat, tp[label,kelas], capaian),
    $rataRata, $selisih, $semesterLabel, $periodeLabel, $diverifikasi, $disahkanPada, $catatan,
    $stabilitas, $tertinggi, $terendah, $portofolio, $target, $kktp, $q
--}}

@extends('portal-orang-tua.layout')

@section('title', 'Akademik & Nilai - Portal Orang Tua')

@section('portal')
@php
    $ring = max(0, min(100, $stabilitas->persen));
    $perlu = $nilai->filter(fn ($n) => $n->tp['kelas'] === 'perlu');
@endphp

{{-- ===== Header ===== --}}
<section class="pt-pagehead pt-pagehead--plain">
    <div>
        <div class="pt-tags">
            <span class="pt-chip pt-chip--soft pt-chip--sm">{{ $siswa->fase }} · Kurikulum Merdeka</span>
            <span class="pt-muted">NISN: {{ $siswa->nisn }}</span>
        </div>
        <h1 class="pt-h1">Akademik & Nilai Siswa</h1>
        <p class="pt-lead">Rekapitulasi pencapaian kurikulum, nilai mata pelajaran, dan laporan perkembangan belajar <a href="{{ route('portal.profil') }}" class="pt-link pt-link--strong">{{ $siswa->nama }}</a> (Kelas {{ str_replace(' - ', ' ', $siswa->kelas) }}).</p>
    </div>

    <div class="pt-pagehead__side pt-pagehead__side--left">
        <span class="pt-chip pt-chip--outline"><x-portal.icon name="calendar" :size="13" /> {{ $semesterLabel }}</span>
        <span class="pt-chip pt-chip--outline"><x-portal.icon name="file" :size="13" /> {{ $periodeLabel }}</span>
        <a href="{{ route('portal.rapor.unduh') }}" class="pt-btn pt-btn--primary"><x-portal.icon name="download" :size="14" /> Unduh Rapor PDF</a>
    </div>
</section>

{{-- ===== 3 kartu ===== --}}
<section class="pt-grid pt-grid--3">
    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Rata-rata Nilai Rapor</span><span class="pt-iconbox"><x-portal.icon name="chart" :size="18" /></span></header>
        <p class="pt-stat__value">{{ number_format($rataRata, 2) }} <small>/ 100</small></p>
        <div class="pt-stat__row">
            <span class="pt-chip pt-chip--soft pt-chip--sm"><x-portal.icon name="trending" :size="11" /> {{ $selisih >= 0 ? '+' : '' }}{{ number_format($selisih, 1) }} poin</span>
            <small class="pt-muted">vs Semester Genap X</small>
        </div>
    </article>

    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Beban & Struktur Mapel</span><span class="pt-iconbox"><x-portal.icon name="book" :size="18" /></span></header>
        <p class="pt-stat__value">{{ $nilai->count() }} <small>Mata Pelajaran</small></p>
        <div class="pt-tags">
            <span class="pt-chip pt-chip--soft pt-chip--sm">Kurikulum Merdeka</span>
            <span class="pt-chip pt-chip--soft pt-chip--sm">{{ $siswa->fase }} - MIPA</span>
        </div>
    </article>

    <article class="pt-card pt-stat">
        <header><span class="pt-eyebrow">Status Publikasi Rapor</span><span class="pt-iconbox"><x-portal.icon name="shield" :size="18" /></span></header>
        <p class="pt-stat__title"><i class="pt-dot pt-dot--blue"></i> {{ $diverifikasi ? 'Telah Diverifikasi' : 'Menunggu Verifikasi' }}</p>
        <div class="pt-stat__row">
            <small class="pt-muted">STS {{ $semesterLabel ? str_replace('Semester ', '', $semesterLabel) : '' }}</small>
            <a href="{{ route('portal.rapor.unduh') }}" class="pt-link pt-link--strong">Arsip Resmi <x-portal.icon name="arrow" :size="12" /></a>
        </div>
    </article>
</section>

{{-- ===== Catatan sekolah ===== --}}
<section>
    <header class="pt-sechead">
        <div><span class="pt-eyebrow">Umpan balik resmi</span><h2 class="pt-h2 pt-h2--lg">Catatan Sekolah untuk Orang Tua</h2></div>
        <span class="pt-chip pt-chip--soft pt-chip--sm">{{ count($catatan) }} Catatan Masuk</span>
    </header>

    <div class="pt-grid pt-grid--2">
        @foreach ($catatan as $c)
            <article class="pt-card pt-note-card">
                <div class="pt-person">
                    <x-portal.avatar :initials="$c->inisial" class="pt-avatar--md pt-avatar--blue" />
                    <div>
                        <span class="pt-chip pt-chip--soft pt-chip--xs">{{ $c->peran }}</span>
                        <strong>{{ $c->nama }}</strong>
                        <small>NIP. {{ $c->nip }} · Tanggal: {{ $c->tanggal->locale('id')->translatedFormat('d F Y') }}</small>
                    </div>
                </div>
                <blockquote class="pt-quote">“{{ $c->isi }}”</blockquote>
                <footer class="pt-note-card__foot">
                    <span class="pt-note pt-note--plain pt-note--blue"><x-portal.icon name="check" :size="13" /> Terbaca oleh Wali Siswa</span>
                    <a href="#" class="pt-btn pt-btn--soft pt-btn--sm" data-pt-soon><x-portal.icon :name="$c->aksi_ikon" :size="13" /> {{ $c->aksi }}</a>
                </footer>
            </article>
        @endforeach
    </div>
</section>

{{-- ===== Analisis + Portofolio ===== --}}
<section class="pt-grid pt-grid--2">
    <article class="pt-card">
        <header class="pt-card__head">
            <div><span class="pt-eyebrow">Analisis rentang</span><h2 class="pt-h2 pt-h2--lg">Capaian Akademik Ekstrem</h2></div>
            <span class="pt-chip pt-chip--soft pt-chip--sm">Top 5% Kelas</span>
        </header>

        <div class="pt-panel pt-stability">
            <div class="pt-ring" style="--p: {{ $ring }}"><div><b>{{ $stabilitas->persen }}%</b><small>Predikat {{ $stabilitas->predikat }}</small></div></div>
            <div>
                <strong>Stabilitas Belajar {{ $stabilitas->persen >= 85 ? 'Sangat Tinggi' : 'Cukup Baik' }}</strong>
                <p>Dari {{ $stabilitas->total }} mata pelajaran, {{ $stabilitas->optimal }} di antaranya tuntas di atas kriteria optimal (≥ {{ $stabilitas->ambang }}) dan ketercapaian tujuan pembelajaran prima.</p>
            </div>
        </div>

        <div class="pt-grid pt-grid--2 pt-grid--tight">
            <div class="pt-panel pt-extreme">
                <div class="pt-panel__top"><span class="pt-chip pt-chip--white pt-chip--xs"><x-portal.icon name="star" :size="10" /> Nilai Tertinggi</span><b class="pt-pill">{{ $tertinggi->predikat }}</b></div>
                <strong>{{ $tertinggi->mapel }}</strong>
                <p class="pt-extreme__val">{{ $tertinggi->nilai }} <small>Skor STS</small></p>
            </div>
            <div class="pt-panel pt-extreme">
                <div class="pt-panel__top"><span class="pt-chip pt-chip--white pt-chip--xs"><x-portal.icon name="target" :size="10" /> Titik Evaluasi</span><b class="pt-pill">{{ $terendah->predikat }}</b></div>
                <strong>{{ $terendah->mapel }}</strong>
                <p class="pt-extreme__val">{{ $terendah->nilai }} <small>Skor STS</small></p>
            </div>
        </div>
    </article>

    <article class="pt-card">
        <header class="pt-card__head">
            <div><span class="pt-eyebrow">Refleksi belajar siswa</span><h2 class="pt-h2 pt-h2--lg">Aktivitas & Portofolio Pembelajaran</h2></div>
            <small class="pt-muted">Tahun Ajaran {{ $siswa->tahun_ajaran }}</small>
        </header>

        <div class="pt-grid pt-grid--2 pt-grid--tight">
            @foreach ($portofolio as $p)
                @php $ada = file_exists(public_path($p->foto)); @endphp
                <figure class="pt-photo">
                    <div class="pt-photo__img {{ $ada ? '' : 'is-empty' }}" @if ($ada) style="background-image:url('{{ asset($p->foto) }}')" @endif>
                        @unless ($ada)<x-portal.icon name="flask" :size="28" />@endunless
                        <figcaption>{{ $p->judul }}</figcaption>
                    </div>
                    <p>{{ $p->teks }}</p>
                </figure>
            @endforeach
        </div>

        <div class="pt-strip pt-strip--target">
            <x-portal.icon name="trophy" :size="16" />
            <span>Target Akademik Semester: <b>{{ $target['teks'] }}</b></span>
            <span class="pt-chip pt-chip--white pt-chip--sm">{{ $target['status'] }}</span>
        </div>
    </article>
</section>

{{-- ===== Rekap nilai ===== --}}
<section class="pt-card pt-rekap" data-pt-tabs>
    <header class="pt-card__head">
        <div><span class="pt-eyebrow">Buku rapor digital</span><h2 class="pt-h2 pt-h2--lg">Rekap Nilai Mata Pelajaran</h2></div>
        <div class="pt-tabs" role="tablist">
            <button type="button" class="is-active" role="tab" data-tab="sts">Rekap Nilai STS</button>
            <button type="button" role="tab" data-tab="catatan">Catatan Capaian Belajar</button>
        </div>
    </header>

    <div data-panel="sts">
        <div class="pt-table-wrap">
            <table class="pt-table">
                <thead>
                    <tr>
                        <th class="is-c">No</th><th>Mata Pelajaran</th><th>Kelompok</th>
                        <th class="is-c">Nilai Akhir</th><th class="is-c">Predikat</th><th class="is-c">Ketercapaian TP</th>
                        <th>Capaian Kompetensi Singkat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($nilai as $n)
                        <tr data-pt-searchable class="{{ $n->kelompok_kelas === 'mipa' && $n->mapel === $tertinggi->mapel ? 'is-top' : '' }}">
                            <td class="is-c pt-muted">{{ $n->no }}</td>
                            <td><strong>{{ $n->mapel }}</strong></td>
                            <td><span class="pt-chip pt-chip--{{ $n->kelompok_kelas === 'mipa' ? 'dark' : 'gray' }} pt-chip--xs">{{ $n->kelompok }}</span></td>
                            <td class="is-c pt-score {{ $n->nilai < $kktp ? 'is-low' : '' }}">{{ $n->nilai }}</td>
                            <td class="is-c"><b class="pt-pill">{{ $n->predikat }}</b></td>
                            <td class="is-c"><span class="pt-tp pt-tp--{{ $n->tp['kelas'] }}">{{ $n->tp['label'] }}</span></td>
                            <td class="pt-table__desc">{{ $n->capaian }}</td>
                        </tr>
                    @endforeach
                    <tr class="pt-empty" hidden><td colspan="7">Tidak ada mata pelajaran yang cocok dengan pencarian.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div data-panel="catatan" hidden>
        <div class="pt-grid pt-grid--2">
            <div class="pt-panel">
                <span class="pt-eyebrow">Sangat Baik</span>
                <ul class="pt-bullets">
                    @foreach ($nilai->where('tp.kelas', 'sangat-baik') as $n)<li><b>{{ $n->mapel }}</b> — {{ $n->capaian }}</li>@endforeach
                </ul>
            </div>
            <div class="pt-panel">
                <span class="pt-eyebrow">Baik</span>
                <ul class="pt-bullets">
                    @foreach ($nilai->where('tp.kelas', 'baik') as $n)<li><b>{{ $n->mapel }}</b> — {{ $n->capaian }}</li>@endforeach
                </ul>
                @if ($perlu->isNotEmpty())
                    <span class="pt-eyebrow pt-eyebrow--mt">Perlu Penguatan</span>
                    <ul class="pt-bullets">@foreach ($perlu as $n)<li><b>{{ $n->mapel }}</b> — {{ $n->capaian }}</li>@endforeach</ul>
                @endif
            </div>
        </div>
    </div>

    <footer class="pt-rekap__foot">
        <p class="pt-note pt-note--plain"><x-portal.icon name="info" :size="14" /> Nilai telah disahkan oleh Dewan Pendidik SMAN 1 Malang per {{ $disahkanPada->locale('id')->translatedFormat('d F Y') }}.</p>
        <div class="pt-rekap__btns">
            <a href="{{ route('portal.rapor.unduh') }}" class="pt-btn pt-btn--ghost"><x-portal.icon name="file" :size="14" /> Unduh Transkrip Nilai Sementara</a>
            <a href="{{ route('portal.rapor.unduh') }}" class="pt-btn pt-btn--primary"><x-portal.icon name="download" :size="14" /> Unduh Lembar Hasil Belajar STS (PDF)</a>
        </div>
    </footer>
</section>
@endsection
