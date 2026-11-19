
{{-- 
    resources/views/portal-orang-tua/minat-bakat.blade.php
    Frontend asli dipertahankan.
    Data kosong ditampilkan sebagai keterangan kosong.
--}}

@extends('portal-orang-tua.layout')

@section('title', 'Minat-Bakat & Karir - Portal Orang Tua')

@section('portal')

@php
    // Pengamanan data agar tidak error ketika database kosong.
    $skorRiasec = (array) data_get($riasec, 'skor', []);
    $namaRiasec = (array) data_get($riasec, 'nama', []);
    $kodeRiasec = data_get($riasec, 'kode', '—');
    $maks = !empty($skorRiasec) ? max($skorRiasec) : 0;

    $daftarProdi = collect($prodi ?? []);
    $daftarKegiatan = collect($kegiatan ?? []);
    $daftarLangkah = collect($langkah ?? []);

    $tanggalRiasec = data_get($riasec, 'diperbarui');
    $penyelenggaraTes = data_get($tes, 'penyelenggara');
    $tanggalTes = data_get($tes, 'tanggal');
    $statusTes = data_get($tes, 'status');

    $namaBk = data_get($bk, 'nama');
    $tanggalBk = data_get($bk, 'tanggal');
    $statusBk = data_get($bk, 'status');
    $catatanBk = data_get($bk, 'catatan');
@endphp

<section class="pt-pagehead pt-pagehead--plain">
    <div>
        <nav class="pt-crumb" aria-label="Breadcrumb">
            <a href="{{ route('portal.dashboard') }}">Portal Orang Tua</a>
            <x-portal.icon name="chev-right" :size="10" />
            <b>Minat-Bakat & Karir</b>
        </nav>

        <h1 class="pt-h1">Minat-Bakat & Karir</h1>

        <p class="pt-lead">
            Hasil asesmen minat, rekomendasi rumpun program studi,
            dan pendampingan karier untuk {{ $siswa->nama ?? '—' }}.
        </p>

        <div class="pt-tags">
            <span class="pt-chip pt-chip--soft pt-chip--sm">
                {{ $siswa->fase ?? '—' }}
                (Kelas XI)
            </span>

            <span class="pt-muted">
                Terakhir diperbarui
                {{ $tanggalRiasec?->locale('id')->translatedFormat('d F Y') ?? '—' }}
                oleh {{ data_get($riasec, 'oleh') ?? '—' }}
            </span>
        </div>
    </div>

    <div class="pt-pagehead__side">
        <a href="#" class="pt-btn pt-btn--primary" data-pt-soon>
            <x-portal.icon name="calendar" :size="14" />
            Jadwalkan Konseling
        </a>
    </div>
</section>

<section class="pt-grid pt-grid--2">

    {{-- Profil RIASEC --}}
    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2">
                <x-portal.icon name="bulb" :size="18" />
                Profil Minat (RIASEC)
            </h2>

            <span class="pt-chip pt-chip--blue pt-chip--sm">
                Kode {{ $kodeRiasec }}
            </span>
        </header>

        @if ($riasec && !empty($skorRiasec))

            <div class="pt-panel">
                <span class="pt-eyebrow">Tipe dominan</span>

                <strong class="pt-reco__title">
                    {{ data_get($riasec, 'judul') ?? '—' }}
                    ({{ $kodeRiasec }})
                </strong>
            </div>

            <ul class="pt-riasec">
                @foreach ($skorRiasec as $kode => $skor)
                    <li class="{{ str_contains((string) $kodeRiasec, (string) $kode) ? 'is-top' : '' }}">

                        <span class="pt-riasec__k">
                            {{ $kode }}
                        </span>

                        <span class="pt-riasec__n">
                            {{ $namaRiasec[$kode] ?? $kode }}
                        </span>

                        <div class="pt-bar">
                            <i style="width: {{ min(100, max(0, (float) $skor)) }}%"></i>
                        </div>

                        <b>{{ $skor }}</b>
                    </li>
                @endforeach
            </ul>

            <p class="pt-note">
                <x-portal.icon name="info" :size="13" />
                Skor 0–100. Dua skor tertinggi membentuk kode minat siswa.
            </p>

        @else

            <div class="pt-panel">
                <span class="pt-eyebrow">Hasil asesmen</span>
                <strong class="pt-reco__title">
                    Belum ada hasil tes RIASEC.
                </strong>
            </div>

        @endif
    </article>

    {{-- Status asesmen --}}
    <article class="pt-card">
        <header class="pt-card__head">
            <h2 class="pt-h2">
                <x-portal.icon name="headset" :size="18" />
                Asesmen & Bimbingan
            </h2>
        </header>

        <div class="pt-stack">

            {{-- Tes Psikologi --}}
            <div class="pt-panel pt-panel--row">
                <span class="pt-iconbox pt-iconbox--white">
                    <x-portal.icon name="bulb" :size="18" />
                </span>

                <div>
                    <strong>Tes Psikologi</strong>

                    <small>
                        @if ($tes)
                            {{ $penyelenggaraTes ?? '—' }}
                            ·
                            {{ $tanggalTes?->locale('id')->translatedFormat('d F Y') ?? '—' }}
                        @else
                            Belum ada informasi tes psikologi.
                        @endif
                    </small>
                </div>

                <span class="pt-chip pt-chip--green pt-chip--sm">
                    {{ $statusTes ?? 'Belum tersedia' }}
                </span>
            </div>

            {{-- Konseling BK --}}
            <div class="pt-panel pt-panel--row">
                <span class="pt-iconbox pt-iconbox--white">
                    <x-portal.icon name="headset" :size="18" />
                </span>

                <div>
                    <strong>Konseling BK</strong>

                    <small>
                        @if ($bk)
                            {{ $namaBk ?? '—' }}
                            ·
                            {{ $tanggalBk?->locale('id')->translatedFormat('d F Y') ?? '—' }}
                        @else
                            Belum ada informasi konsultasi BK.
                        @endif
                    </small>
                </div>

                <span class="pt-chip pt-chip--soft pt-chip--sm">
                    {{ $statusBk ?? 'Belum tersedia' }}
                </span>
            </div>

        </div>

        <div class="pt-panel pt-panel--mt">
            <span class="pt-eyebrow">Catatan Guru BK</span>

            <blockquote class="pt-quote pt-quote--plain">
                @if ($bk && $catatanBk)
                    “{{ $catatanBk }}”
                @else
                    Belum ada catatan dari Guru BK.
                @endif
            </blockquote>
        </div>
    </article>

</section>

{{-- Rekomendasi prodi --}}
<section>
    <header class="pt-sechead">
        <div>
            <span class="pt-eyebrow">Rekomendasi</span>
            <h2 class="pt-h2 pt-h2--lg">Rumpun Program Studi</h2>
        </div>
    </header>

    <div class="pt-grid pt-grid--3">

        @forelse ($daftarProdi as $p)

            <article class="pt-card pt-prodi {{ $loop->first ? 'is-top' : '' }}">

                <div class="pt-panel__top">
                    <span class="pt-eyebrow">
                        {{ $loop->first ? 'Paling sesuai' : 'Alternatif' }}
                    </span>

                    <b class="pt-prodi__pct">
                        {{ $p->cocok ?? '—' }}%
                    </b>
                </div>

                <strong class="pt-reco__title">
                    {{ $p->rumpun ?? '—' }}
                </strong>

                <div class="pt-bar">
                    <i style="width: {{ min(100, max(0, (float) ($p->cocok ?? 0))) }}%"></i>
                </div>

                <div class="pt-tags">
                    @foreach (($p->prodi ?? []) as $x)
                        <span class="pt-chip pt-chip--soft pt-chip--sm">
                            {{ $x }}
                        </span>
                    @endforeach
                </div>

                @if (!empty($p->alasan))
                    <p class="pt-note pt-note--plain">
                        {{ $p->alasan }}
                    </p>
                @endif

            </article>

        @empty

            <article class="pt-card">
                <p class="pt-note pt-note--plain">
                    Belum ada rekomendasi program studi.
                </p>
            </article>

        @endforelse

    </div>
</section>

<section class="pt-grid pt-grid--2">

    {{-- Minat, bakat dan prestasi --}}
    <article class="pt-card">

        <header class="pt-card__head">
            <h2 class="pt-h2">
                <x-portal.icon name="trophy" :size="18" />
                Minat, Bakat & Prestasi
            </h2>
        </header>

        <div class="pt-stack">

            @forelse ($daftarKegiatan as $k)

                @php
                    $tipe = in_array($k->tipe ?? '', ['emas', 'hijau'])
                        ? $k->tipe
                        : 'soft';

                    $warnaChip = $tipe === 'emas'
                        ? 'gold'
                        : ($tipe === 'hijau' ? 'green' : 'soft');
                @endphp

                <div class="pt-panel pt-panel--row pt-panel--{{ $tipe }}">

                    <span class="pt-iconbox pt-iconbox--white">
                        <x-portal.icon :name="$k->ikon ?? 'trophy'" :size="18" />
                    </span>

                    <div>
                        <strong>{{ $k->judul ?? '—' }}</strong>
                        <small>{{ $k->detail ?? '—' }}</small>
                    </div>

                    <span class="pt-chip pt-chip--{{ $warnaChip }} pt-chip--sm">
                        {{ $k->badge ?? '—' }}
                    </span>

                </div>

            @empty

                <div class="pt-panel">
                    <small>Belum ada data kegiatan atau prestasi siswa.</small>
                </div>

            @endforelse

        </div>
    </article>

    {{-- Peta langkah karier --}}
    <article class="pt-card">

        <header class="pt-card__head">
            <h2 class="pt-h2">
                <x-portal.icon name="compass" :size="18" />
                Peta Langkah Karier
            </h2>
        </header>

        <ol class="pt-steps">

            @forelse ($daftarLangkah as $l)

                <li class="{{ ($l->status ?? '') === 'berjalan' ? 'is-now' : '' }}">

                    <i></i>

                    <div>
                        <strong>{{ $l->judul ?? '—' }}</strong>
                        <small>{{ $l->waktu ?? '—' }}</small>
                    </div>

                    @if (($l->status ?? '') === 'berjalan')
                        <span class="pt-chip pt-chip--blue pt-chip--xs">
                            Sedang berjalan
                        </span>
                    @endif

                </li>

            @empty

                <li>
                    <i></i>
                    <div>
                        <strong>Belum ada data langkah karier.</strong>
                        <small>Informasi belum tersedia.</small>
                    </div>
                </li>

            @endforelse

        </ol>
    </article>

</section>

@endsection
