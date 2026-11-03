{{--
    resources/views/portal-orang-tua/profil-siswa.blade.php
    Variabel dari ProfilSiswaController@index:
    $siswa, $ortu, $walimurid, $kegiatan, $sinkronisasi
--}}

@extends('portal-orang-tua.layout')

@section('title', 'Profil Siswa - Portal Orang Tua')

@section('portal')
{{-- ===== Header halaman ===== --}}
<section class="pt-card pt-pagehead">
    <div>
        <nav class="pt-crumb" aria-label="Breadcrumb">
            <a href="{{ route('portal.dashboard') }}">Portal Orang Tua</a>
            <x-portal.icon name="chev-right" :size="10" /> <b>Profile Siswa</b>
        </nav>
        <h1 class="pt-h1 pt-h1--md">Profil Lengkap Siswa
            <span class="pt-chip pt-chip--soft pt-chip--sm"><x-portal.icon name="check" :size="11" /> Terverifikasi DAPODIK</span></h1>
    </div>
    <span class="pt-chip pt-chip--outline pt-chip--sm"><x-portal.icon name="clock" :size="12" /> Sinkronisasi: {{ $sinkronisasi->locale('id')->translatedFormat('d M Y, H.i') }} WIB</span>
</section>

{{-- ===== Identitas ===== --}}
<section class="pt-card pt-identity">
    <div class="pt-identity__photo">
        <x-portal.avatar :src="$siswa->foto" :initials="$siswa->inisial" class="pt-avatar--xl" />
        <span class="pt-status"><i class="pt-dot pt-dot--green"></i>Aktif</span>
    </div>

    <div class="pt-identity__body">
        <div class="pt-tags">
            <span class="pt-chip pt-chip--green pt-chip--sm"><i class="pt-dot pt-dot--green"></i>{{ $siswa->status }}</span>
            <span class="pt-chip pt-chip--soft pt-chip--sm"><x-portal.icon name="trophy" :size="11" /> {{ $siswa->jalur }}</span>
            <span class="pt-chip pt-chip--soft pt-chip--sm">Angkatan {{ $siswa->angkatan }}</span>
        </div>
        <h1 class="pt-h1 pt-h1--lg">{{ $siswa->nama }}</h1>
        <p class="pt-identity__class">Kelas {{ $siswa->kelas }} • {{ $siswa->peminatan }}</p>

        <div class="pt-grid pt-grid--4 pt-grid--tight">
            <div class="pt-mini"><small>NIS Sekolah</small><strong>{{ $siswa->nis }}</strong></div>
            <div class="pt-mini"><small>NISN Kemendikbud</small><strong>{{ $siswa->nisn }}</strong></div>
            <div class="pt-mini"><small>Nomor Presensi</small><strong>No. {{ str_pad($siswa->no_presensi, 2, '0', STR_PAD_LEFT) }}</strong></div>
            <div class="pt-mini"><small>Tahun Ajaran</small><strong>{{ $siswa->tahun_ajaran }}</strong></div>
        </div>

        <div class="pt-identity__actions">
            <a href="#" class="pt-btn pt-btn--primary" data-pt-soon><x-portal.icon name="id-card" :size="15" /> Cetak Kartu Pelajar Digital</a>
            <a href="#" class="pt-btn pt-btn--ghost" data-pt-soon><x-portal.icon name="edit" :size="15" /> Laporkan Perubahan Data</a>
        </div>
    </div>
</section>

{{-- ===== Akademik & Data pribadi ===== --}}
<section class="pt-grid pt-grid--2">
    <article class="pt-card">
        <header class="pt-card__head">
            <div class="pt-titleicon">
                <span class="pt-iconbox pt-iconbox--lg"><x-portal.icon name="cap" :size="20" /></span>
                <div><h2 class="pt-h2">Informasi Akademik & Kelas</h2><small>Rombongan belajar resmi TP {{ $siswa->tahun_ajaran }}</small></div>
            </div>
        </header>

        <dl class="pt-dl">
            <div><dt><x-portal.icon name="building" :size="14" /> Tingkat & Kelas</dt><dd>{{ $siswa->rombel_label }}</dd></div>
            <div><dt><x-portal.icon name="book" :size="14" /> Kurikulum Terapan</dt><dd>{{ $siswa->kurikulum }}</dd></div>
            <div><dt><x-portal.icon name="door" :size="14" /> Ruang Pembelajaran</dt><dd>{{ $siswa->ruang }}</dd></div>
        </dl>

        <div class="pt-panel">
            <div class="pt-panel__top">
                <span class="pt-eyebrow">Wali kelas terdaftar</span>
                @if ($siswa->wali_kelas->tersedia)
                    <span class="pt-chip pt-chip--green pt-chip--sm">Tersedia di Jam Kerja</span>
                @endif
            </div>
            <div class="pt-person">
                <x-portal.avatar :initials="$siswa->wali_kelas->inisial" class="pt-avatar--md pt-avatar--blue" />
                <div><strong>{{ $siswa->wali_kelas->nama }}</strong><small>NIP: {{ $siswa->wali_kelas->nip }}</small></div>
                <a href="#" class="pt-btn pt-btn--primary pt-btn--sm" data-pt-soon><x-portal.icon name="message" :size="14" /> Hubungi</a>
            </div>
        </div>
    </article>

    <article class="pt-card">
        <header class="pt-card__head">
            <div class="pt-titleicon">
                <span class="pt-iconbox pt-iconbox--lg"><x-portal.icon name="id-card" :size="20" /></span>
                <div><h2 class="pt-h2">Data Pribadi & Domisili</h2><small>Informasi identitas sipil peserta didik</small></div>
            </div>
            <span class="pt-chip pt-chip--soft pt-chip--sm">KK Kota Malang</span>
        </header>

        <dl class="pt-dl">
            <div><dt><x-portal.icon name="calendar" :size="14" /> Tempat, Tanggal Lahir</dt>
                 <dd>{{ $siswa->tempat_lahir }}, {{ $siswa->tanggal_lahir->locale('id')->translatedFormat('d F Y') }} ({{ $siswa->umur }} Thn)</dd></div>
            <div><dt><x-portal.icon name="users" :size="14" /> Jenis Kelamin</dt><dd>{{ $siswa->jenis_kelamin }}</dd></div>
            <div><dt><x-portal.icon name="building" :size="14" /> Agama</dt><dd>{{ $siswa->agama }}</dd></div>
        </dl>

        <div class="pt-panel">
            <span class="pt-eyebrow"><x-portal.icon name="pin" :size="13" /> Alamat domisili terdata</span>
            <p class="pt-address">{{ $siswa->alamat }}</p>
            <div class="pt-panel__top">
                <span class="pt-chip pt-chip--green pt-chip--sm"><x-portal.icon name="check" :size="11" /> Jarak ke Sekolah: {{ $siswa->jarak_sekolah }}</span>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($siswa->alamat) }}" target="_blank" rel="noopener" class="pt-link">Lihat Titik Peta <x-portal.icon name="arrow" :size="12" /></a>
            </div>
        </div>
    </article>
</section>

{{-- ===== Wali murid & Prestasi ===== --}}
<section class="pt-grid pt-grid--2">
    <article class="pt-card">
        <header class="pt-card__head">
            <div class="pt-titleicon">
                <span class="pt-iconbox pt-iconbox--lg"><x-portal.icon name="users" :size="20" /></span>
                <div><h2 class="pt-h2">Wali Murid Terhubung</h2><small>Hubungan keluarga & kontak notifikasi instan</small></div>
            </div>
            <span class="pt-chip pt-chip--green pt-chip--sm"><i class="pt-dot pt-dot--green"></i>Akun Aktif Sesi Ini</span>
        </header>

        <div class="pt-stack">
            @foreach ($walimurid as $w)
                <div class="pt-panel pt-panel--row">
                    <x-portal.avatar :initials="$w->inisial" class="pt-avatar--md {{ $w->utama ? 'pt-avatar--blue' : 'pt-avatar--slate' }}" />
                    <div>
                        <strong>{{ $w->nama }} <span class="pt-chip pt-chip--soft pt-chip--xs">{{ $w->hubungan }}</span></strong>
                        <small>{{ $w->keterangan }}</small>
                    </div>
                    @if ($w->utama)<span class="pt-check"><x-portal.icon name="check" :size="20" /></span>@endif
                </div>
                @if ($w->wa)
                    <div class="pt-panel pt-panel--row pt-panel--flat">
                        <x-portal.icon name="message" :size="18" />
                        <div><small>Nomor WhatsApp Siaga Notifikasi</small><strong>{{ $w->wa }}</strong></div>
                        <span class="pt-chip pt-chip--green pt-chip--sm">Aktif Auto-SMS</span>
                    </div>
                @endif
            @endforeach
        </div>

        <p class="pt-note"><x-portal.icon name="info" :size="13" /> Data keluarga diverifikasi oleh Subbag Kesiswaan dan Bimbingan Konseling (BK).</p>
    </article>

    <article class="pt-card">
        <header class="pt-card__head">
            <div class="pt-titleicon">
                <span class="pt-iconbox pt-iconbox--lg pt-iconbox--dark"><x-portal.icon name="trophy" :size="20" /></span>
                <div><h2 class="pt-h2">Minat, Bakat & Prestasi</h2><small>Ekstrakurikuler dan catatan pencapaian siswa</small></div>
            </div>
        </header>

        <div class="pt-stack">
            @foreach ($kegiatan as $k)
                <div class="pt-panel pt-panel--row pt-panel--{{ $k->tipe }}">
                    <span class="pt-iconbox pt-iconbox--white"><x-portal.icon :name="$k->ikon" :size="18" /></span>
                    <div>
                        <strong>{{ $k->judul }}</strong>
                        <small>{{ $k->detail }}</small>
                    </div>
                    <span class="pt-chip pt-chip--{{ $k->tipe === 'emas' ? 'gold' : ($k->tipe === 'hijau' ? 'green' : 'soft') }} pt-chip--sm">{{ $k->badge }}</span>
                </div>
            @endforeach
        </div>
    </article>
</section>

<p class="pt-privacy">
    <x-portal.icon name="shield" :size="16" />
    <span>Halaman ini dilindungi oleh Protokol Privasi Portal SMAN 1 Malang. Hanya wali murid sah yang berhak mengakses berkas digital ini.</span>
    <a href="#" class="pt-link" data-pt-soon>Kebijakan Privasi Data</a>
    <a href="#" class="pt-link" data-pt-soon>Pusat Bantuan</a>
</p>
@endsection
