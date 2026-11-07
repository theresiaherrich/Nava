@php($pageTitle = 'Riwayat Tes RIASEC')
@php($subtitle = 'Pantau perkembangan hasil minat dari waktu ke waktu.')
<x-layouts.admin title="{{ $pageTitle }} — SIAKAD A3">
<div class="a3x">
@if(session('success'))<div class="a3x-flash" role="status">{{ session('success') }}</div>@endif
@if(session('info'))<div class="a3x-notice" role="status">{{ session('info') }}</div>@endif
<div class="a3x-head"><div><p class="a3x-eyebrow">SMA NEGERI 1 MALANG · KELAS X</p><h1>{{ $pageTitle }}</h1><p>{{ $subtitle }}</p></div><span class="a3x-pill">{{ $class->nama }} · {{ $class->tahun_ajaran }}</span></div>
<section class="a3x-panel"><div class="a3x-section"><h3>Semua riwayat</h3><a href="{{ route('a3.x.instructions') }}">Ikuti Tes Baru</a></div><div class="a3x-table-wrap"><table class="a3x-table"><thead><tr><th>Tanggal tes</th><th>Kelas saat tes</th><th>Tahun ajaran</th><th>Kode dominan</th><th>Hasil</th></tr></thead><tbody>@forelse($history as $item)<tr><td>{{ \Carbon\Carbon::parse($item->waktu_selesai)->format('d M Y, H:i') }}</td><td>{{ $item->nama_kelas }}</td><td>{{ $item->tahun_ajaran }}</td><td><span class="a3x-pill">{{ $item->kode_holland ?? '—' }}</span></td><td>@if($item->tingkat === 'X')<a href="{{ route('a3.x.result', $item->id_sesi_tes) }}">Detail</a>@else<span class="a3x-muted">Detail tingkat lain belum didukung di fase ini</span>@endif</td></tr>@empty<tr><td colspan="5" class="a3x-empty">Belum ada riwayat tes tersimpan.</td></tr>@endforelse</tbody></table></div></section></div>
</x-layouts.admin>
