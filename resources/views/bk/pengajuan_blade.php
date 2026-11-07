@extends('a3.layouts.app')
@section('title', 'Antrean Pengajuan BK')
@section('content')
<div class="eyebrow">A3 · GURU BK</div><div class="page-heading"><div><h1>Antrean Pengajuan Bimbingan</h1><p>Tinjau pengajuan siswa dan tindak lanjuti dengan arahan atau jadwal.</p></div></div>
<article class="panel"><div class="filter-row"><input type="search" placeholder="Cari nama siswa..."><select><option>Semua status</option><option>Menunggu</option><option>Diproses</option><option>Dijadwalkan</option><option>Selesai</option></select></div><div class="table-wrap"><table><thead><tr><th>Siswa</th><th>Kelas</th><th>Topik</th><th>Tanggal</th><th>Status</th><th>Tindak lanjut</th></tr></thead><tbody><tr><td colspan="6" class="table-empty">Belum ada data antrean yang terhubung.</td></tr></tbody></table></div></article>
@endsection
