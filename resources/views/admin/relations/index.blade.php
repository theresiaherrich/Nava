@extends('admin.layouts.app')
@section('title','Relasi Orang Tua dan Siswa')
@section('content')
<div class="page-heading"><div><p class="eyebrow">Integrasi Akun SIAKAD</p><h1>Relasi Orang Tua dan Siswa</h1><p class="muted">Hubungkan akun orang tua dengan data siswa untuk sinkronisasi portal mandiri.</p></div><div class="summary-pill"><small>Relasi terverifikasi</small><b>{{ $verifiedCount }}</b></div></div>
<div class="toolbar"><form class="filter-form" method="GET"><input name="q" value="{{ request('q') }}" placeholder="⌕ Cari nama orang tua, siswa, atau NIS..."><select name="tingkat"><option value="">Semua Kelas</option>@foreach(['X','XI','XII'] as $level)<option value="{{ $level }}" @selected(request('tingkat')===$level)>Kelas {{ $level }}</option>@endforeach</select><select name="status"><option value="">Semua Status</option><option value="verified">Terverifikasi</option><option value="pending">Menunggu</option><option value="rejected">Ditolak</option></select><button class="btn btn-soft">Filter</button></form><button class="btn btn-primary" onclick="document.getElementById('create-relation').showModal()">＋ Tambah Relasi</button></div>

<div class="table-panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>NAMA ORANG TUA</th>
                    <th>AKUN PORTAL</th>
                    <th>NAMA SISWA</th>
                    <th>NIS</th>
                    <th>KELAS</th>
                    <th>STATUS VERIFIKASI</th>
                    <th>AKSI</th>
                </tr>
            </thead>

            <tbody>
                @forelse($relations as $rel)
                    <tr>
                        {{-- Identitas orang tua --}}
                        <td>
                            <div class="person-cell">
                                <span class="initials">
                                    {{ strtoupper(substr($rel->nama_orang_tua, 0, 2)) }}
                                </span>

                                <div>
                                    <b>{{ $rel->nama_orang_tua }}</b>
                                    <small>
                                        {{ $rel->hubungan }}
                                        ·
                                        {{ $rel->no_telepon ?: 'Nomor belum tersedia' }}
                                    </small>
                                </div>
                            </div>
                        </td>

                        {{-- Informasi akun --}}
                        <td>
                            @if($rel->id_user && $rel->username_akun)
                                <div>
                                    <span class="status-chip good">
                                        ● Terhubung
                                    </span>
                                    <small>{{ $rel->username_akun }}</small>
                                </div>
                            @else
                                <span class="status-chip pending">
                                    ● Belum terhubung
                                </span>
                            @endif
                        </td>

                        {{-- Identitas siswa --}}
                        <td>{{ $rel->nama_siswa }}</td>

                        <td>{{ $rel->nis }}</td>

                        {{-- Kelas --}}
                        <td>
                            <span class="class-chip">
                                {{ $rel->nama_kelas ?? 'Belum ada kelas aktif' }}
                            </span>
                        </td>

                        {{-- Status verifikasi --}}
                        <td>
                            <span class="status-chip
                                {{ $rel->status_verifikasi === 'verified'
                                    ? 'good'
                                    : ($rel->status_verifikasi === 'rejected'
                                        ? 'bad'
                                        : 'pending') }}">
                                ● {{ ucfirst($rel->status_verifikasi) }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <details class="action-menu">
                                <summary>•••</summary>

                                @foreach([
                                    'verified' => 'Setujui',
                                    'rejected' => 'Tolak',
                                    'pending' => 'Jadikan pending'
                                ] as $status => $label)

                                    <form method="POST"
                                        action="{{ route('admin.relations.verify', [$rel->id_siswa, $rel->hubungan]) }}">
                                        @csrf
                                        @method('PUT')

                                        <input type="hidden"
                                            name="status"
                                            value="{{ $status }}">

                                        <button class="text-action">
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endforeach

                                <form method="POST"
                                    action="{{ route('admin.relations.destroy', [$rel->id_siswa, $rel->hubungan]) }}"
                                    onsubmit="return confirm('Hapus relasi ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button class="text-action danger-text">
                                        Hapus relasi
                                    </button>
                                </form>
                            </details>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            Belum ada data relasi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-row">
        <span>{{ $relations->total() }} relasi ditemukan</span>
        {{ $relations->links() }}
    </div>
</div>

<dialog id="create-relation" class="modal"><form method="POST" action="{{ route('admin.relations.store') }}">@csrf<div class="modal-head"><h2>Tambah Relasi Orang Tua</h2><button type="button" class="close-modal" onclick="this.closest('dialog').close()">×</button></div><label>Siswa<select name="id_siswa" required>@foreach($students as $student)<option value="{{ $student->id_siswa }}">{{ $student->nama }} — NIS {{ $student->nis }}</option>@endforeach</select></label><label>Hubungan<select name="hubungan"><option>Ayah</option><option>Ibu</option><option>Wali</option></select></label><label>Nama orang tua/wali<input name="nama" required maxlength="120"></label><label>Nomor WhatsApp<input name="no_telepon" maxlength="25"></label><label>Jenis kelamin<select name="jenis_kelamin"><option value="">Tidak diisi</option><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></label><label>Alamat<textarea name="alamat" rows="2"></textarea></label><p class="hint">Relasi baru otomatis berstatus pending dan harus diverifikasi.</p><button class="btn btn-primary btn-full">Simpan Relasi</button></form></dialog>
@endsection
