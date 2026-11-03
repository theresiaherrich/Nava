
@extends('layouts.app')

@section('title', 'Presensi')

@php
    $namaSiswa = 'Mutiara Bintang';
    $kelasSiswa = 'XI MIPA 2';

    $riwayat = [
        ['tanggal'=>'14 Oktober 2026','status'=>'Hadir','jam'=>'06.45','metode'=>'QR Code','keterangan'=>''],
        ['tanggal'=>'13 Oktober 2026','status'=>'Hadir','jam'=>'08.50','metode'=>'QR Code','keterangan'=>'Terlambat'],
        ['tanggal'=>'13 Oktober 2026','status'=>'Alpa','jam'=>'07.30','metode'=>'','keterangan'=>''],
        ['tanggal'=>'12 Oktober 2026','status'=>'Hadir','jam'=>'06.45','metode'=>'QR Code','keterangan'=>''],
        ['tanggal'=>'11 Oktober 2026','status'=>'Hadir','jam'=>'06.45','metode'=>'QR Code','keterangan'=>''],
    ];
@endphp

@push('styles')
<style>
.presensi-page {
    font-family:'Poppins',sans-serif;
    background:#f3f5f9;
    min-height:100vh;
    padding:12px;
    color:#222;
}
.top-bar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}
.btn-aturan {
    background:#e60083;
    color:white;
    border:0;
    border-radius:5px;
    padding:8px 18px;
    font-size:10px;
    cursor:pointer;
}
.presensi-grid {
    display:grid;
    grid-template-columns:160px 1fr;
    gap:9px;
    margin-bottom:9px;
}
.card {
    background:white;
    border:1px solid #cbd2df;
    border-radius:7px;
    padding:15px;
}
.card h3 {
    font-size:12px;
    font-weight:500;
    margin:0 0 12px;
}
.qr-card {
    text-align:center;
    display:flex;
    flex-direction:column;
    align-items:center;
}
.qr-box {
    width:115px;
    height:115px;
    border:1px solid #55b9f3;
    border-radius:7px;
    padding:7px;
}
.qr-box img {
    width:100%;
    height:100%;
}
.form-group {
    margin-bottom:8px;
}
.form-group label {
    display:block;
    font-size:10px;
    margin-bottom:4px;
}
.form-group input {
    width:100%;
    height:22px;
    border:1px solid #55b9f3;
    border-radius:7px;
    padding:4px 8px;
    font-family:inherit;
    font-size:9px;
    outline:none;
}
.form-group input:focus {
    border-color:#078edb;
}
.form-note {
    font-size:8px;
    color:#e60083;
    margin-top:3px;
}
.form-bottom {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:12px;
}
.lokasi {
    font-size:9px;
    color:#25843c;
}
.btn-submit {
    background:#e60083;
    color:white;
    border:0;
    border-radius:4px;
    padding:6px 20px;
    font-family:inherit;
    font-size:9px;
    cursor:pointer;
}
.history-card {
    padding:15px 17px;
}
.history-heading {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
}
.history-heading h3 {
    margin:0;
    font-size:12px;
}
.search-date {
    width:245px;
    height:25px;
    border:1px solid #cbd2df;
    border-radius:7px;
    padding:4px 9px;
    font-family:inherit;
    font-size:9px;
    outline:none;
}
.history-date {
    font-size:10px;
    margin:10px 0 5px;
}
.history-item {
    border:1px solid #55b9f3;
    border-radius:7px;
    min-height:19px;
    padding:3px 8px;
    margin:5px 0 5px 18px;
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    font-size:9px;
}
.history-item.today {
    background:#dff3ff;
}
.dot {
    width:6px;
    height:6px;
    border-radius:50%;
    background:#079bd0;
    display:inline-block;
}
.hadir {color:#25843c;}
.izin {color:#d99a00;}
.alpa {color:#e60083;}
.terlambat {color:#078edb;}
.empty {
    font-size:10px;
    color:#888;
    text-align:center;
    padding:15px;
}
.modal-aturan {
    display:none;
    position:fixed;
    inset:0;
    background:#0006;
    z-index:9999;
    align-items:center;
    justify-content:center;
    padding:15px;
}
.modal-aturan.show {display:flex;}
.modal-aturan-content {
    background:white;
    border-radius:8px;
    padding:20px;
    width:100%;
    max-width:350px;
    font-size:11px;
}
.modal-aturan-content h3 {
    font-size:14px;
    margin-top:0;
}
.modal-aturan-content button {
    background:#e60083;
    color:white;
    border:0;
    border-radius:5px;
    padding:6px 15px;
    float:right;
}
@media(max-width:550px) {
    .presensi-grid {grid-template-columns:1fr;}
    .qr-box {width:130px;height:130px;}
    .history-heading {align-items:flex-start;flex-direction:column;}
    .search-date {width:100%;}
}
</style>
@endpush

@section('content')
<div class="presensi-page">

    <div class="top-bar">
        <button class="btn-aturan" onclick="bukaAturan()">
            Lihat Aturan
        </button>
    </div>

    <div class="presensi-grid">

        <!-- QR CODE -->
        <div class="card qr-card">
            <h3>QR Code</h3>
            <div class="qr-box">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SISWA-001"
                    alt="QR Code Siswa">
            </div>
        </div>

        <!-- FORM PRESENSI -->
        <div class="card">
            <h3>Presensi</h3>

            <form id="formPresensi">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" value="{{ $namaSiswa }}" readonly>
                </div>

                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text" value="{{ $kelasSiswa }}" readonly>
                    <div class="form-note">
                        Pastikan isi dengan benar!
                    </div>
                </div>

                <div class="form-bottom">
                    <span class="lokasi" id="statusLokasi">
                        <span class="dot"></span> Memeriksa lokasi...
                    </span>

                    <button type="submit" class="btn-submit">
                        Submit
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- RIWAYAT PRESENSI -->
    <div class="card history-card">
        <div class="history-heading">
            <h3>Riwayat Presensi</h3>
            <input
                type="text"
                id="cariRiwayat"
                class="search-date"
                placeholder="Cari Hari Presensi...">
        </div>

        <div id="daftarRiwayat">
            @foreach($riwayat as $index => $item)

                @if($index === 0 || $riwayat[$index-1]['tanggal'] !== $item['tanggal'])
                    <div class="history-date">
                        {{ $item['tanggal'] }}
                    </div>
                @endif

                <div class="history-item {{ $index === 0 ? 'today' : '' }}"
                     data-cari="{{ strtolower($item['tanggal'].' '.$item['status'].' '.$item['jam'].' '.$item['keterangan']) }}">

                    <span class="{{ strtolower($item['status']) }}">
                        {{ $item['status'] }}
                    </span>

                    <span class="dot"></span>
                    <span>{{ $item['jam'] }}</span>

                    @if($item['keterangan'])
                        <span class="dot"></span>
                        <span class="terlambat">{{ $item['keterangan'] }}</span>
                    @endif

                    @if($item['metode'])
                        <span class="dot"></span>
                        <span>{{ $item['metode'] }}</span>
                    @endif
                </div>

            @endforeach
        </div>

        <div class="empty" id="tidakAda" style="display:none;">
            Riwayat presensi tidak ditemukan.
        </div>
    </div>

</div>

<!-- MODAL ATURAN -->
<div class="modal-aturan" id="modalAturan">
    <div class="modal-aturan-content">
        <h3>Aturan Presensi</h3>
        <ul>
            <li>Presensi dilakukan sesuai jadwal yang ditentukan.</li>
            <li>Pastikan lokasi perangkat sudah aktif.</li>
            <li>Presensi manual menggunakan validasi geofencing.</li>
            <li>Pastikan data presensi sudah benar sebelum Submit.</li>
        </ul>
        <button onclick="tutupAturan()">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bukaAturan() {
    document.getElementById('modalAturan').classList.add('show');
}

function tutupAturan() {
    document.getElementById('modalAturan').classList.remove('show');
}

// Pencarian riwayat
document.getElementById('cariRiwayat').addEventListener('input', function() {
    const keyword = this.value.toLowerCase();
    const items = document.querySelectorAll('.history-item');
    let jumlah = 0;

    items.forEach(item => {
        const cocok = item.dataset.cari.includes(keyword);
        item.style.display = cocok ? 'flex' : 'none';
        if(cocok) jumlah++;
    });

    document.getElementById('tidakAda').style.display =
        jumlah ? 'none' : 'block';
});

// Cek lokasi
const statusLokasi = document.getElementById('statusLokasi');

if(navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        () => {
            statusLokasi.innerHTML = '<span class="dot"></span> Area sesuai';
            statusLokasi.dataset.valid = 'true';
        },
        () => {
            statusLokasi.innerHTML = '<span class="dot"></span> Lokasi tidak tersedia';
            statusLokasi.dataset.valid = 'false';
        }
    );
} else {
    statusLokasi.textContent = 'Geolocation tidak didukung';
}

// Submit tampilan
document.getElementById('formPresensi').addEventListener('submit', function(e) {
    e.preventDefault();

    if(statusLokasi.dataset.valid !== 'true') {
        alert('Lokasi belum berhasil diverifikasi.');
        return;
    }

    alert('Tampilan berhasil disubmit. Hubungkan ke backend untuk menyimpan presensi.');
});
</script>
@endpush
