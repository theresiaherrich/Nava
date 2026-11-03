
@extends('layouts.app')

@section('title', 'Admin Presensi')

@push('styles')
<style>
    .admin-page {
        min-height: 100vh;
        padding: 20px;
        background: #f4f6fa;
        font-family: 'Poppins', sans-serif;
        color: #292331;
    }

    .camera-card {
        background: #fff;
        border: 1px solid #d9dce5;
        border-radius: 9px;
        padding: 10px;
        margin-bottom: 16px;
    }

    .camera-box {
        position: relative;
        width: 100%;
        height: 230px;
        overflow: hidden;
        border: 2px solid #2699e8;
        border-radius: 10px;
        background: #20232a;
    }

    .camera-box video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        background: #252a32;
        font-size: 13px;
        text-align: center;
        padding: 15px;
    }

    .scan-frame {
        position: absolute;
        width: 90px;
        height: 90px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 4px solid transparent;
        background:
            linear-gradient(transparent, transparent) padding-box,
            linear-gradient(white, white) border-box;
        clip-path: polygon(
            0 0, 30% 0, 30% 5%, 5% 5%,
            5% 30%, 0 30%,
            0 0,
            100% 0, 100% 30%, 95% 30%,
            95% 5%, 70% 5%, 70% 0,
            100% 0,
            100% 100%, 70% 100%, 70% 95%,
            95% 95%, 95% 70%, 100% 70%,
            100% 100%,
            0 100%, 0 70%, 5% 70%,
            5% 95%, 30% 95%, 30% 100%
        );
        pointer-events: none;
    }

    .camera-status {
        font-size: 10px;
        color: #888;
        margin: 8px 2px 0;
    }

    .manual-title {
        font-size: 16px;
        font-weight: 600;
        margin: 18px 0 10px;
    }

    .form-card {
        background: white;
        border: 1px solid #d5d9e2;
        border-radius: 8px;
        padding: 14px;
    }

    .form-card h3 {
        font-size: 12px;
        font-weight: 500;
        margin: 0 0 5px;
    }

    .form-group {
        margin-bottom: 7px;
    }

    .form-group label {
        display: block;
        font-size: 10px;
        margin-bottom: 3px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        height: 27px;
        padding: 4px 8px;
        border: 1px solid #72baff;
        border-radius: 5px;
        outline: none;
        font-family: inherit;
        font-size: 10px;
        background: white;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #1688df;
    }

    .form-note {
        color: #e45b91;
        font-size: 9px;
        margin: 8px 0 12px;
    }

    .form-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .location-status {
        color: #278347;
        font-size: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .submit-btn {
        border: none;
        border-radius: 4px;
        background: #df087c;
        color: white;
        padding: 8px 22px;
        font-family: inherit;
        font-size: 10px;
        cursor: pointer;
    }

    .submit-btn:hover {
        background: #bd086a;
    }

    @media (min-width: 1000px) {
        .camera-box {
            height: 310px;
        }
    }

    @media (max-width: 600px) {
        .admin-page {
            padding: 14px;
        }

        .camera-box {
            height: 220px;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-page">

    {{-- Kamera --}}
    <div class="camera-card">
        <div class="camera-box">
            <video id="cameraPreview" autoplay playsinline muted></video>

            <div class="camera-placeholder" id="cameraPlaceholder">
                Kamera belum diaktifkan
            </div>

            <div class="scan-frame"></div>
        </div>

        <p class="camera-status" id="cameraStatus">
            Menghubungkan kamera...
        </p>
    </div>

    {{-- Form Presensi Manual --}}
    <h2 class="manual-title">Presensi manual</h2>

    <div class="form-card">
        <h3>Presensi</h3>

        <form id="formPresensi">
            @csrf

            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama"
                    placeholder="Nama" required>
            </div>

            <div class="form-group">
                <label for="kelas">Kelas</label>
                <input type="text" id="kelas" name="kelas"
                    placeholder="Kelas" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="">Status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                </select>
            </div>

            <p class="form-note">
                Pastikan isi dengan benar!
            </p>

            <div class="form-bottom">
                <div class="location-status">
                    <span>📍</span>
                    <span id="locationStatus">Memeriksa lokasi...</span>
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
    // Mengaktifkan kamera
    const video = document.getElementById('cameraPreview');
    const placeholder = document.getElementById('cameraPlaceholder');
    const cameraStatus = document.getElementById('cameraStatus');

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment'
                },
                audio: false
            });

            video.srcObject = stream;
            placeholder.style.display = 'none';
            cameraStatus.textContent = 'Kamera aktif';

        } catch (error) {
            placeholder.textContent =
                'Kamera tidak dapat diakses. Periksa izin kamera pada browser.';
            cameraStatus.textContent = 'Kamera tidak aktif';
        }
    }

    startCamera();

    // Memeriksa lokasi perangkat
    const locationStatus = document.getElementById('locationStatus');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                locationStatus.textContent = 'Lokasi berhasil diperoleh';
            },
            function() {
                locationStatus.textContent = 'Lokasi tidak tersedia';
            }
        );
    } else {
        locationStatus.textContent = 'GPS tidak didukung';
    }

    // Simulasi pengiriman formulir
    document.getElementById('formPresensi')
        .addEventListener('submit', function(e) {
            e.preventDefault();

            const nama = document.getElementById('nama').value;
            const kelas = document.getElementById('kelas').value;
            const status = document.getElementById('status').value;

            if (!nama || !kelas || !status) {
                alert('Lengkapi semua data presensi!');
                return;
            }

            alert('Presensi berhasil diproses pada tampilan.');

            this.reset();
        });
</script>
@endpush
