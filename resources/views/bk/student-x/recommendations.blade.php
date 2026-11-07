@php($pageTitle = 'Rekomendasi Mata Pelajaran')
@php($subtitle = 'Paket pilihan membantu mempertimbangkan minat, hasil akademik, dan konsultasi dengan Guru BK.')
<x-layouts.admin title="{{ $pageTitle }} — SIAKAD A3">
<div class="a3x">
@if(session('success'))<div class="a3x-flash" role="status">{{ session('success') }}</div>@endif
@if(session('info'))<div class="a3x-notice" role="status">{{ session('info') }}</div>@endif
<div class="a3x-head"><div><p class="a3x-eyebrow">SMA NEGERI 1 MALANG · KELAS X</p><h1>{{ $pageTitle }}</h1><p>{{ $subtitle }}</p></div><span class="a3x-pill">{{ $class->nama }} · {{ $class->tahun_ajaran }}</span></div>
<section class="a3x-panel"><div class="a3x-notice" role="status"><strong>Integrasi rekomendasi belum tersedia</strong><p>Sistem belum memiliki sumber data resmi yang menghubungkan hasil RIASEC, nilai akademik, paket mata pelajaran, tingkat kecocokan, dan alasan rekomendasi. Angka atau urutan paket tidak ditampilkan agar tidak disalahartikan sebagai rekomendasi nyata.</p></div><h3>Tiga paket rekomendasi</h3><div class="a3x-recs">@for($i=1;$i<=3;$i++)<article class="a3x-recommendation"><small>PAKET {{ $i }}</small><h3>Menunggu data rekomendasi</h3><p>Nama paket, mata pelajaran, alasan pemilihan, dan persentase kecocokan akan ditampilkan setelah terhubung ke sumber data sekolah.</p><span class="a3x-pill">Belum tersedia</span></article>@endfor</div><div class="a3x-notice"><strong>Bahasa asing lintas minat</strong><p>Pilihan bahasa asing menunggu daftar mata pelajaran yang disahkan sekolah.</p></div><a class="a3x-button" href="{{ route('a3.x.counseling') }}">Konsultasikan dengan Guru BK</a></section></div>
</x-layouts.admin>
