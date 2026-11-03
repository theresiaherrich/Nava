{{--
    resources/views/portal-orang-tua/minat-bakat.blade.php
    (Desain halaman ini belum ada, disusun dari komponen Dashboard & Profil.)
    Variabel dari MinatBakatController@index:
    $siswa, $ortu, $riasec (skor, nama, kode, judul, diperbarui, oleh), $prodi, $tes, $bk, $kegiatan, $langkah
--}}

@extends('portal-orang-tua.layout')

@section('title', 'Minat-Bakat & Karir - Portal Orang Tua')

@section('portal')
@php $maks = max($riasec->skor); @endphp

<section class="pt-pagehead pt-pagehead--plain">
    <div>
        <nav class="pt-crumb" aria-label="Breadcrumb">
            <a href="{{ route('portal.dashboard') }}">Portal Orang Tua</a>
            <x-portal.icon name="chev-right" :size="10" /> <b>Minat-Bakat & Karir</b>
        </nav>
        <h1 class="pt-h1">Minat-Bakat & Karir</h1>
        <p class="pt-lead">Hasil asesmen minat, rekomendasi rumpun program studi, dan pendampingan karier untuk {{ $siswa->nama }}.</p>
        <div class="pt-tags">
            <span class="pt-chip pt-chip--soft pt-chip--sm">{{ $siswa->fase }} (Kelas XI)</span>
            <span class="pt-muted">Terakhir diperbarui {{ $riasec->diperbarui->locale('id')->translatedFormat('d F Y') }} oleh {{ $riasec->oleh }}</span>
        </div>
    </div>
    <div class="pt-pagehead__side">
        <a href="#" class="pt-btn pt-btn--primary" data-pt-soon><x-portal.icon name="calendar" :size="14" /> Jadwalkan Konseling</a>
    </div>
</section>

<section class="pt-grid pt-grid--2">
    {{-- Profil RIASEC --}}
    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2"><x-portal.icon name="bulb" :size="18" /> Profil Minat (RIASEC)</h2>
            <span class="pt-chip pt-chip--blue pt-chip--sm">Kode {{ $riasec->kode }}</span>
        </header>

        <div class="pt-panel">
            <span class="pt-eyebrow">Tipe dominan</span>
            <strong class="pt-reco__title">{{ $riasec->judul }} ({{ $riasec->kode }})</strong>
        </div>

        <ul class="pt-riasec">
            @foreach ($riasec->skor as $kode => $skor)
                <li class="{{ str_contains($riasec->kode, $kode) ? 'is-top' : '' }}">
                    <span class="pt-riasec__k">{{ $kode }}</span>
                    <span class="pt-riasec__n">{{ $riasec->nama[$kode] }}</span>
                    <div class="pt-bar"><i style="width: {{ $skor }}%"></i></div>
                    <b>{{ $skor }}</b>
                </li>
            @endforeach
        </ul>
        <p class="pt-note"><x-portal.icon name="info" :size="13" /> Skor 0–100. Dua skor tertinggi membentuk kode minat siswa.</p>
    </article>

    {{-- Status asesmen --}}
    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2"><x-portal.icon name="headset" :size="18" /> Asesmen & Bimbingan</h2>
        </header>

        <div class="pt-stack">
            <div class="pt-panel pt-panel--row">
                <span class="pt-iconbox pt-iconbox--white"><x-portal.icon name="bulb" :size="18" /></span>
                <div><strong>Tes Psikologi</strong><small>{{ $tes->penyelenggara }} · {{ $tes->tanggal->locale('id')->translatedFormat('d F Y') }}</small></div>
                <span class="pt-chip pt-chip--green pt-chip--sm">{{ $tes->status }}</span>
            </div>
            <div class="pt-panel pt-panel--row">
                <span class="pt-iconbox pt-iconbox--white"><x-portal.icon name="headset" :size="18" /></span>
                <div><strong>Konseling BK</strong><small>{{ $bk->nama }} · {{ $bk->tanggal->locale('id')->translatedFormat('d F Y') }}</small></div>
                <span class="pt-chip pt-chip--soft pt-chip--sm">{{ $bk->status }}</span>
            </div>
        </div>

        <div class="pt-panel pt-panel--mt">
            <span class="pt-eyebrow">Catatan Guru BK</span>
            <blockquote class="pt-quote pt-quote--plain">“{{ $bk->catatan }}”</blockquote>
        </div>
    </article>
</section>

{{-- Rekomendasi prodi --}}
<section>
    <header class="pt-sechead">
        <div><span class="pt-eyebrow">Rekomendasi</span><h2 class="pt-h2 pt-h2--lg">Rumpun Program Studi</h2></div>
    </header>
    <div class="pt-grid pt-grid--3">
        @foreach ($prodi as $p)
            <article class="pt-card pt-prodi {{ $loop->first ? 'is-top' : '' }}">
                <div class="pt-panel__top"><span class="pt-eyebrow">{{ $loop->first ? 'Paling sesuai' : 'Alternatif' }}</span><b class="pt-prodi__pct">{{ $p->cocok }}%</b></div>
                <strong class="pt-reco__title">{{ $p->rumpun }}</strong>
                <div class="pt-bar"><i style="width: {{ $p->cocok }}%"></i></div>
                <div class="pt-tags">@foreach ($p->prodi as $x)<span class="pt-chip pt-chip--soft pt-chip--sm">{{ $x }}</span>@endforeach</div>
                @if ($p->alasan)<p class="pt-note pt-note--plain">{{ $p->alasan }}</p>@endif
            </article>
        @endforeach
    </div>
</section>

<section class="pt-grid pt-grid--2">
    <article class="pt-card">
        <header class="pt-card__head"><h2 class="pt-h2"><x-portal.icon name="trophy" :size="18" /> Minat, Bakat & Prestasi</h2></header>
        <div class="pt-stack">
            @foreach ($kegiatan as $k)
                <div class="pt-panel pt-panel--row pt-panel--{{ $k->tipe }}">
                    <span class="pt-iconbox pt-iconbox--white"><x-portal.icon :name="$k->ikon" :size="18" /></span>
                    <div><strong>{{ $k->judul }}</strong><small>{{ $k->detail }}</small></div>
                    <span class="pt-chip pt-chip--{{ $k->tipe === 'emas' ? 'gold' : ($k->tipe === 'hijau' ? 'green' : 'soft') }} pt-chip--sm">{{ $k->badge }}</span>
                </div>
            @endforeach
        </div>
    </article>

    <article class="pt-card">
        <header class="pt-card__head"><h2 class="pt-h2"><x-portal.icon name="compass" :size="18" /> Peta Langkah Karier</h2></header>
        <ol class="pt-steps">
            @foreach ($langkah as $l)
                <li class="{{ $l->status === 'berjalan' ? 'is-now' : '' }}">
                    <i></i>
                    <div><strong>{{ $l->judul }}</strong><small>{{ $l->waktu }}</small></div>
                    @if ($l->status === 'berjalan')<span class="pt-chip pt-chip--blue pt-chip--xs">Sedang berjalan</span>@endif
                </li>
            @endforeach
        </ol>
    </article>
</section>
@endsection
