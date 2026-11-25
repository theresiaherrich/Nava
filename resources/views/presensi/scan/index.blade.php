
@extends('layouts.app')

@section('title', 'Admin Presensi')

@push('styles')
<style>
* {
    box-sizing: border-box;
}

.scan-page {
    font-family: 'Poppins', sans-serif;
    padding: 12px;
    min-height: 100vh;
    background: #f3f5f9;
    color: #252538;
}

/* AREA KAMERA */
.camera-card {
    background: white;
    border: 1px solid #cbd2df;
    border-radius: 7px;
    padding: 10px;
    margin-bottom: 17px;
}

.camera-container {
    position: relative;
    width: 100%;
    height: 235px;
    border: 2px solid #078df0;
    border-radius: 9px;
    overflow: hidden;
    background: #202020;
}

.camera-container video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.camera-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    color: white;
    background: #333;
    font-size: 11px;
    text-align: center;
    padding: 15px;
}

.camera-placeholder i {
    font-size: 30px;
    margin-bottom: 8px;
}

.scan-frame {
    position: absolute;
    width: 95px;
    height: 95px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}

.scan-frame span {
    position: absolute;
    width: 29px;
    height: 29px;
    border-color: white;
    border-style: solid;
}

.top-left {
    top: 0;
    left: 0;
    border-width: 4px 0 0 4px;
}

.top-right {
    top: 0;
    right: 0;
    border-width: 4px 4px 0 0;
}

.bottom-left {
    bottom: 0;
    left: 0;
    border-width: 0 0 4px 4px;
}

.bottom-right {
    bottom: 0;
    right: 0;
    border-width: 0 4px 4px 0;
}

/* FORM MANUAL */
.manual-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 9px;
    color: #222;
}

.manual-card {
    background: white;
    border: 3px solid #078df0;
    border-radius: 8px;
    padding: 12px;
}

.manual-card h3 {
    font-size: 11px;
    font-weight: 600;
    margin: 0 0 7px;
}

.form-group {
    margin-bottom: 7px;
}

.form-group label {
    display: block;
    font-size: 9px;
    margin-bottom: 3px;
}

.form-group input,
.form-group select {
    width: 100%;
    height: 28px;
    border: 1px solid #54b7ff;
    border-radius: 5px;
    padding: 4px 8px;
    font-family: inherit;
    font-size: 9px;
    outline: none;
    background: white;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #078df0;
}

.form-note {
    color: #ed65a4;
    font-size: 8px;
    margin: 7px 0;
}

.manual-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 9px;
}

.area-status {
    color: #258b42;
    font-size: 8px;
}

.area-status i {
    margin-right: 5px;
}

.submit-btn {
    background: #e60083;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 7px 22px;
    font-family: inherit;
    font-size: 9px;
    cursor: pointer;
}

.submit-btn:hover {
    background: #c90070;
}

@media(max-width: 600px) {
    .scan-page {
        padding: 10px;
    }

    .camera-container {
        height: 210px;
    }
}
</style>
@endpush

@section('content')
<div class="scan-page">

    {{-- KAMERA SCAN QR CODE --}}
    <div class="camera-card">

        <div class="camera-container">

            <video id="scanCamera" autoplay playsinline muted></video>

            <div class="camera-placeholder" id="cameraPlaceholder">
                <i class="bi bi-camera-video"></i>
                <span>Mengaktifkan kamera...</span>
            </div>

            {{-- Bingkai QR Code --}}
            <div class="scan-frame">
                <span class="top-left"></span>
                <span class="top-right"></span>
                <span class="bottom-left"></span>
                <span class="bottom-right"></span>
            </div>

        </div>

    </div>

    {{-- PRESENSI MANUAL --}}
    <h2 class="manual-title">Presensi manual</h2>

    <div class="manual-card">

        <h3>Presensi</h3>

        <form id="formPresensi">

            <div class="form-group">
                <label for="nama">Nama</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Nama"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kelas">Kelas</label>
                <input
                    type="text"
                    id="kelas"
                    name="kelas"
                    placeholder="Kelas"
                    required
                >
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="">Pilih Status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <p class="form-note">
                Pastikan isi dengan benar!
            </p>

            <div class="manual-footer">

                <div class="area-status">
                    <i class="bi bi-geo-alt-fill"></i>
                    Area sesuai
                </div>

                <button type="submit" class="submit-btn">
                    Submit
                </button>

            </div>

        </form>

    </div>

</div>
@endsection

@push('scripts')
<script>
const video = document.getElementById('scanCamera');
const placeholder = document.getElementById('cameraPlaceholder');

let cameraStream = null;

async function startCamera() {
    try {
        if (!navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia) {
            throw new Error('Browser tidak mendukung kamera.');
        }

        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'environment'
            },
            audio: false
        });

        video.srcObject = cameraStream;

        placeholder.style.display = 'none';

    } catch (error) {
        placeholder.style.display = 'flex';
        placeholder.innerHTML = `
            <i class="bi bi-camera-video-off"></i>
            <span>Kamera tidak dapat diakses.<br>
            Silakan izinkan penggunaan kamera.</span>
        `;

        console.error(error);
    }
}

// Aktifkan kamera saat halaman dibuka
startCamera();

// Simulasi submit presensi
document.getElementById('formPresensi')
    .addEventListener('submit', function(e) {

        e.preventDefault();

        const nama = document.getElementById('nama').value;
        const kelas = document.getElementById('kelas').value;
        const status = document.getElementById('status').value;

        alert(
            'Presensi berhasil diinput!\n\n' +
            'Nama: ' + nama + '\n' +
            'Kelas: ' + kelas + '\n' +
            'Status: ' + status
        );

        this.reset();
    });

// Matikan kamera saat meninggalkan halaman
window.addEventListener('beforeunload', function() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => {
            track.stop();
        });
    }
});
</script>
@endpush
