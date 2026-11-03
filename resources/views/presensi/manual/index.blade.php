
@extends('layouts.app')

@section('title', 'Admin Presensi')

@push('styles')
<style>
    .manual-page {
        font-family: 'Poppins', sans-serif;
        padding: 10px;
        background: #f3f5f9;
        min-height: 100vh;
        color: #252538;
    }

    /* KAMERA */
    .camera-card {
        background: #fff;
        border: 1px solid #cbd2df;
        border-radius: 7px;
        padding: 10px;
        margin-bottom: 20px;
        width: 100%;
        box-sizing: border-box;
    }

    .camera-container {
        position: relative;
        width: 100%;
        height: 293px;
        border: 3px solid #078df0;
        border-radius: 13px;
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
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 13px;
        background: #333;
        z-index: 1;
    }

    .scan-frame {
        position: absolute;
        width: 118px;
        height: 118px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
    }

    .scan-frame span {
        position: absolute;
        width: 35px;
        height: 35px;
        border-color: white;
        border-style: solid;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,.3));
    }

    .scan-frame .top-left {
        top: 0;
        left: 0;
        border-width: 5px 0 0 5px;
        border-radius: 5px 0 0 0;
    }

    .scan-frame .top-right {
        top: 0;
        right: 0;
        border-width: 5px 5px 0 0;
        border-radius: 0 5px 0 0;
    }

    .scan-frame .bottom-left {
        bottom: 0;
        left: 0;
        border-width: 0 0 5px 5px;
        border-radius: 0 0 0 5px;
    }

    .scan-frame .bottom-right {
        bottom: 0;
        right: 0;
        border-width: 0 5px 5px 0;
        border-radius: 0 0 5px 0;
    }

    /* JUDUL FORM */
    .manual-title {
        font-size: 14px;
        font-weight: 500;
        margin: 0 0 9px;
        color: #111;
    }

    /* FORM */
    .manual-form-card {
        background: #fff;
        border: 1px solid #cbd2df;
        border-radius: 7px;
        padding: 13px 16px;
        width: 100%;
        box-sizing: border-box;
    }

    .manual-form-card h3 {
        font-size: 12px;
        font-weight: 500;
        margin: 0 0 8px;
        color: #222;
    }

    .form-group {
        margin-bottom: 7px;
    }

    .form-group label {
        display: block;
        font-size: 10px;
        font-weight: 400;
        margin-bottom: 3px;
        color: #333;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        height: 20px;
        padding: 2px 7px;
        border: 1px solid #168ff0;
        border-radius: 7px;
        outline: none;
        background: #fff;
        font-family: inherit;
        font-size: 9px;
        color: #555;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #0784e8;
        box-shadow: 0 0 0 2px rgba(7, 132, 232, .1);
    }

    .form-group input::placeholder {
        color: #777;
    }

    .form-note {
        color: #ed70aa;
        font-size: 8px;
        margin-top: 2px;
    }

    /* LOKASI DAN TOMBOL */
    .form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 10px;
    }

    .location-status {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 9px;
        color: #23843b;
    }

    .location-status i {
        font-size: 11px;
    }

    .btn-submit {
        background: #e60083;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 20px;
        min-width: 66px;
        font-family: inherit;
        font-size: 9px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(230, 0, 131, .2);
    }

    .btn-submit:hover {
        background: #c90070;
    }

    .alert-success {
        display: none;
        padding: 8px;
        margin-bottom: 10px;
        background: #eaf8ef;
        color: #25834a;
        font-size: 11px;
        border-radius: 5px;
    }

    @media (max-width: 768px) {
        .manual-page {
            padding: 10px;
        }

        .camera-container {
            height: 250px;
        }
    }

    @media (max-width: 480px) {
        .camera-container {
            height: 210px;
        }

        .scan-frame {
            width: 100px;
            height: 100px;
        }
    }
</style>
@endpush

@section('content')

<div class="manual-page">

    <!-- AREA KAMERA -->
    <div class="camera-card">
        <div class="camera-container">

            <video id="cameraPreview" autoplay playsinline muted></video>

            <div class="camera-placeholder" id="cameraPlaceholder">
                Menghubungkan kamera...
            </div>

            <!-- Bingkai pemindai -->
            <div class="scan-frame">
                <span class="top-left"></span>
                <span class="top-right"></span>
                <span class="bottom-left"></span>
                <span class="bottom-right"></span>
            </div>

        </div>
    </div>

    <!-- JUDUL -->
    <h2 class="manual-title">Presensi manual</h2>

    <!-- FORM PRESENSI -->
    <div class="manual-form-card">

        <h3>Presensi</h3>

        <div class="alert-success" id="successAlert">
            Presensi berhasil disiapkan!
        </div>

        <form id="manualForm">

            <!-- NAMA -->
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

            <!-- KELAS -->
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

            <!-- STATUS -->
            <div class="form-group">
                <label for="status">Status</label>

                <select id="status" name="status" required>
                    <option value="" selected disabled>Status</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                </select>

                <div class="form-note">
                    Pastikan isi dengan benar!
                </div>
            </div>

            <!-- FOOTER -->
            <div class="form-footer">

                <div class="location-status">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="locationText">Area sesuai</span>
                </div>

                <button type="submit" class="btn-submit">
                    Submit
                </button>

            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')
<script>
    // AKSES KAMERA
    const video = document.getElementById('cameraPreview');
    const placeholder = document.getElementById('cameraPlaceholder');

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user'
                },
                audio: false
            });

            video.srcObject = stream;

            placeholder.style.display = 'none';

        } catch (error) {
            placeholder.textContent =
                'Kamera tidak tersedia atau izin kamera belum diberikan';

            console.error('Kamera:', error);
        }
    }

    if (navigator.mediaDevices &&
        navigator.mediaDevices.getUserMedia) {
        startCamera();
    } else {
        placeholder.textContent = 'Browser tidak mendukung akses kamera';
    }

    // SUBMIT FORM
    document.getElementById('manualForm')
        .addEventListener('submit', function(e) {

            e.preventDefault();

            const nama = document.getElementById('nama').value;
            const kelas = document.getElementById('kelas').value;
            const status = document.getElementById('status').value;

            if (!nama || !kelas || !status) {
                return;
            }

            const alert = document.getElementById('successAlert');

            alert.style.display = 'block';

            alert.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        });
</script>
@endpush
