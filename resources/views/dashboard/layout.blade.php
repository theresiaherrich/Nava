<!doctype html><html lang="id"><head><meta charset="UTF-8"><title>NAVA Dashboard</title></head>
<body style="font-family:Arial;padding:30px">
<h1>Dashboard {{ $judul }}</h1>
<p>Halo, {{ auth()->user()->name }}</p>
<p>Role: {{ auth()->user()->role }}</p>
<p>Subrole: {{ auth()->user()->sub_role ?? '-' }}</p>
<form method="POST" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
</body></html>

<!-- FILE: resources/views/dashboard/admin.blade.php -->


<!-- FILE: resources/views/dashboard/guru.blade.php -->


<!-- FILE: resources/views/dashboard/tendik.blade.php -->


<!-- FILE: resources/views/dashboard/siswa.blade.php -->
