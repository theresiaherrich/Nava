@extends('admin.layouts.app')
@section('title','Notifikasi WhatsApp')
@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Integrasi WhatsApp</p>
        <h1>Notifikasi WhatsApp</h1>
        <p class="muted">Riwayat pesan yang dikirim ke orang tua. Pesan dikirim otomatis oleh penjadwal.</p>
    </div>
    <div style="display:flex;gap:12px;">
        <div class="summary-pill"><small>Diterima</small><b>{{ $diterima }}</b></div>
        <div class="summary-pill"><small>Gagal</small><b>{{ $failed }}</b></div>
    </div>
</div>

@if(session('wa_pesan'))
    <p class="hint">{{ session('wa_pesan') }}</p>
@endif
@if(session('wa_error'))
    <p class="hint"><span class="status-chip bad">● {{ session('wa_error') }}</span></p>
@endif

<div class="toolbar">
    <form class="filter-form" method="GET">
        <input name="q" value="{{ request('q') }}" placeholder="⌕ Cari nama siswa...">
        <select name="status">
            <option value="">Semua Status</option>
            @foreach(['diterima' => 'Diterima', 'gagal' => 'Gagal', 'diproses' => 'Diproses'] as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-soft">Filter</button>
    </form>

    <div style="display:flex;gap:12px;align-items:center;">
        <span class="status-chip {{ $statusService === 'READY' ? 'good' : ($statusService === null ? 'bad' : 'pending') }}">
            ● WhatsApp:
            {{ $statusService === null ? 'service tidak terjangkau' : $statusService }}
        </span>
        <span class="status-chip {{ $waAktif ? 'good' : 'pending' }}">
            ● Pengiriman otomatis {{ $waAktif ? 'aktif' : 'nonaktif' }}
        </span>
        <form method="POST" action="{{ route('admin.notifications.send') }}">
            @csrf
            <button class="btn btn-primary" @disabled(!$waAktif || !$waConfigured)>Kirim sekarang</button>
        </form>
        <form method="POST" action="{{ route('admin.notifications.rekap') }}">
            @csrf
            <button class="btn btn-primary" @disabled(!$waAktif || !$waConfigured)>Kirim Rekap Mingguan</button>
        </form>
    </div>
</div>

<div class="table-panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>WAKTU</th>
                    <th>SISWA</th>
                    <th>PENERIMA</th>
                    <th>JENIS</th>
                    <th>STATUS</th>
                    <th>KETERANGAN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->nama_siswa ?? '-' }}</td>
                        <td>
                            <b>{{ $log->hubungan ?? '-' }}</b>
                            <small>{{ $log->nomor_samar }}</small>
                        </td>
                        <td><span class="class-chip">{{ ucfirst($log->template) }}</span></td>
                        <td>
                            <span class="status-chip {{ $log->status === 'diterima' ? 'good' : ($log->status === 'gagal' ? 'bad' : 'pending') }}">
                                ● {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td>{{ $log->error ?: '-' }}</td>
                        <td>
                            @if($log->status === 'gagal' && str_starts_with($log->kunci, 'presensi-'))
                                <form method="POST" action="{{ route('admin.notifications.retry', $log->id) }}">
                                    @csrf
                                    <button class="btn btn-soft">Kirim ulang</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">Belum ada riwayat pengiriman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-row">
        <span>{{ $logs->total() }} pesan ditemukan</span>
        {{ $logs->links() }}
    </div>
</div>
@endsection