@php($pageTitle = 'Tes Minat RIASEC')
@php($subtitle = 'Kenali kecenderungan minat melalui jawaban yang jujur.')
<x-layouts.admin title="{{ $pageTitle }} — SIAKAD A3">
<div class="a3x">
@if(session('success'))<div class="a3x-flash" role="status">{{ session('success') }}</div>@endif
@if(session('info'))<div class="a3x-notice" role="status">{{ session('info') }}</div>@endif
<div class="a3x-head"><div><p class="a3x-eyebrow">SMA NEGERI 1 MALANG · KELAS X</p><h1>{{ $pageTitle }}</h1><p>{{ $subtitle }}</p></div><span class="a3x-pill">{{ $class->nama }} · {{ $class->tahun_ajaran }}</span></div>
<div class="a3x-columns"><section class="a3x-panel"><span class="a3x-pill">PETUNJUK TES</span><h2>Kenali minat dan potensimu</h2><p>RIASEC terdiri dari Realistic, Investigative, Artistic, Social, Enterprising, dan Conventional. Pilih jawaban sesuai ketertarikan pribadimu.</p><ol class="a3x-list"><li>Baca setiap pernyataan dengan saksama.</li><li>Nilai minatmu dari 1 (sangat tidak tertarik) hingga 5 (sangat tertarik).</li><li>Tidak ada jawaban benar atau salah.</li><li>Semua pertanyaan harus dijawab sebelum dikirim.</li></ol><a class="a3x-button" href="{{ route('a3.x.questions') }}">Mulai Tes</a></section><aside class="a3x-panel a3x-panel-soft"><h3>Skala jawaban</h3>@foreach(['Sangat tidak tertarik','Tidak tertarik','Netral','Tertarik','Sangat tertarik'] as $label)<div class="a3x-scale"><b>{{ $loop->iteration }}</b><span>{{ $label }}</span></div>@endforeach</aside></div></div>
</x-layouts.admin>
