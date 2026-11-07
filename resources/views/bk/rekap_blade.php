@extends('a3.layouts.app')
@section('title', 'Rekap Tes RIASEC')
@section('content')
<div class="eyebrow">A3 · GURU BK</div><div class="page-heading"><div><h1>Rekap Hasil Tes RIASEC</h1><p>Pantau hasil asesmen siswa berdasarkan nama, NIS, kelas, dan periode.</p></div><button class="btn btn-outline" type="button" disabled>Ekspor rekap</button></div>
<article class="panel"><div class="filter-row"><input type="search" placeholder="Cari nama atau NIS..."><select><option>Semua kelas</option><option>X</option><option>XI</option><option>XII</option></select><select><option>Semua status</option><option>Sudah tes</option><option>Belum tes</option></select></div><div class="table-wrap"><table><thead><tr><th>Nama Siswa</th><th>NIS</th><th>Kelas</th><th>Tanggal Tes</th><th>Profil Dominan</th><th>Aksi</th></tr></thead><tbody><tr><td colspan="6" class="table-empty">Data siswa akan dimuat dari backend.</td></tr></tbody></table></div></article>
@endsection
