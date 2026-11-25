@extends('admin.layouts.app')
@section('title','Notifikasi WhatsApp')
@section('content')
<div class="page-heading notif-page-heading">
    <div>
        <p class="eyebrow notif-eyebrow">INTEGRASI WHATSAPP</p>
        <h1 class="notif-title">Notifikasi WhatsApp</h1>
        <p class="muted notif-subtitle">Riwayat pesan yang dikirim ke orang tua. Pesan dikirim otomatis oleh penjadwal.</p>

        <div class="notif-status-chips" style="margin-top: 16px;">
            <span class="status-chip {{ $statusService === 'READY' ? 'good' : ($statusService === null ? 'bad' : 'pending') }}">
                ● WhatsApp: {{ $statusService === null ? 'service tidak terjangkau' : $statusService }}
            </span>
            <span class="status-chip {{ $waAktif ? 'good' : 'pending' }}">
                ● Pengiriman otomatis {{ $waAktif ? 'aktif' : 'nonaktif' }}
            </span>
        </div>
    </div>

    <div class="notif-summary-cards">
        <div class="notif-summary-card">
            <span class="notif-summary-card__label">Diterima</span>
            <strong class="notif-summary-card__value notif-summary-card__value--blue">{{ $diterima }}</strong>
        </div>
        <div class="notif-summary-card">
            <span class="notif-summary-card__label">Gagal</span>
            <strong class="notif-summary-card__value">{{ $failed }}</strong>
        </div>
    </div>
</div>

@if(session('wa_pesan'))
    <p class="hint" style="margin-bottom: 12px;">{{ session('wa_pesan') }}</p>
@endif
@if(session('wa_error'))
    <p class="hint" style="margin-bottom: 12px;"><span class="status-chip bad">● {{ session('wa_error') }}</span></p>
@endif

<div class="notif-toolbar">
    <form class="notif-filter-form" method="GET">
        <div class="notif-search-input">
            <svg class="notif-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama siswa..." autocomplete="off">
        </div>

        <div class="notif-select-wrap">
            <select name="status">
                <option value="">Semua Status</option>
                @foreach(['diterima' => 'Diterima', 'gagal' => 'Gagal', 'diproses' => 'Diproses'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="notif-btn notif-btn-filter">Filter</button>
    </form>

    <div class="notif-actions">
        <form method="POST" action="{{ route('admin.notifications.rekap') }}">
            @csrf
            <button class="notif-btn notif-btn-outline" @disabled(!$waAktif || !$waConfigured)>Kirim Rekap Mingguan</button>
        </form>
        <form method="POST" action="{{ route('admin.notifications.send') }}">
            @csrf
            <button class="notif-btn notif-btn-solid" @disabled(!$waAktif || !$waConfigured)>Kirim sekarang</button>
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