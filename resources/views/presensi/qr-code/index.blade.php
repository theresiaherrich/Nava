
@extends('layouts.app')

@section('title', 'Presensi QR Code')

@section('content')

<div class="presensi-wrapper">

    <!-- HEADER -->
    <div class="presensi-topbar">
        <button class="aturan-button" onclick="openAturan()">
            Lihat Aturan
        </button>

        <div class="student-profile">
            <div class="profile-avatar">S</div>
            <div>
                <strong>Siswa</strong>
                <small>Presensi Siswa</small>
            </div>
        </div>
    </div>

    <!-- KONTEN UTAMA -->
    <div class="presensi-content">

        <div class="presensi-grid">

            <!-- QR CODE -->
            <div class="content-card qr-card">
                <h3>QR Code</h3>

                <button class="qr-preview" onclick="openQR()" type="button"
                        aria-label="Perbesar QR Code">
                    <div class="qr-placeholder">
                        <svg viewBox="0 0 100 100" aria-hidden="true">
                            <rect x="5" y="5" width="28" height="28"/>
                            <rect x="11" y="11" width="16" height="16" class="white"/>
                            <rect x="15" y="15" width="8" height="8"/>

                            <rect x="67" y="5" width="28" height="28"/>
                            <rect x="73" y="11" width="16" height="16" class="white"/>
                            <rect x="77" y="15" width="8" height="8"/>

                            <rect x="5" y="67" width="28" height="28"/>
                            <rect x="11" y="73" width="16" height="16" class="white"/>
                            <rect x="15" y="77" width="8" height="8"/>

                            <path d="M40 5h8v8h-8zM52 5h8v15h-8z
                            M40 20h8v8h-8zM52 28h8v8h-8z
                            M40 40h8v8h-8zM52 40h8v8h-8z
                            M64 40h8v8h-8zM80 40h8v8h-8z
                            M40 52h8v8h-8zM56 52h8v8h-8z
                            M72 52h8v8h-8zM88 52h7v8h-7z
                            M40 64h8v8h-8zM52 64h8v8h-8z
                            M64 64h8v8h-8zM80 64h8v8h-8z
                            M40 80h8v8h-8zM56 80h8v8h-8z
                            M68 80h8v8h-8zM84 80h8v8h-8z
                            M40 92h8v3h-8zM56 92h8v3h-8z"/>
                        </svg>
                    </div>
                </button>

                <p class="qr-caption">Tekan QR Code untuk memperbesar</p>
            </div>

            <!-- FORM PRESENSI -->
            <div class="content-card form-card">
                <h3>Presensi</h3>

                <form id="formPresensi">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" id="nama" name="nama"
                               placeholder="Nama siswa" autocomplete="name">
                    </div>

                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" id="kelas" name="kelas"
                               placeholder="Kelas siswa">
                    </div>

                    <div class="form-group">
                        <label for="alasan">Alasan ketidakhadiran</label>
                        <textarea id="alasan" name="alasan"
                                  placeholder="Masukkan alasan jika tidak dapat hadir"></textarea>
                    </div>

                    <button type="submit" class="submit-button">
                        Submit
                    </button>
                </form>
            </div>

        </div>

        <!-- RIWAYAT PRESENSI -->
        <div class="content-card history-card">

            <div class="history-header">
                <h3>Riwayat Presensi</h3>

                <div class="search-box">
                    <input type="search" id="searchHistory"
                           placeholder="Cari riwayat presensi...">
                    <span>⌕</span>
                </div>
            </div>

            <div class="history-content" id="historyContent">
                <div class="empty-history">
                    <div class="empty-icon">▤</div>
                    <strong>Belum ada riwayat presensi</strong>
                    <p>Riwayat presensi akan ditampilkan setelah data tersedia.</p>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- MODAL ATURAN -->
<div class="modal-overlay" id="aturanModal">
    <div class="aturan-modal">

        <button class="modal-close" onclick="closeAturan()">×</button>

        <div class="aturan-tabs">
            <button class="tab-button active" onclick="showTab('presensi', this)">
                Aturan Presensi
            </button>

            <button class="tab-button" onclick="showTab('izin', this)">
                Aturan Izin
            </button>
        </div>

        <div class="aturan-list" id="tabPresensi">
            <div class="aturan-item">
                <span>1</span>
                <p>Siswa wajib melakukan presensi sesuai dengan ketentuan waktu yang berlaku.</p>
            </div>

            <div class="aturan-item">
                <span>2</span>
                <p>Pastikan QR Code dipindai sesuai petunjuk yang diberikan oleh sekolah.</p>
            </div>

            <div class="aturan-item">
                <span>3</span>
                <p>Pastikan data presensi sudah tercatat sebelum meninggalkan halaman.</p>
            </div>
        </div>

        <div class="aturan-list hidden" id="tabIzin">
            <div class="aturan-item">
                <span>1</span>
                <p>Jika izin dari rumah, harap kirim surat izin atau surat sakit kepada wali kelas melalui orang tua.</p>
            </div>

            <div class="aturan-item">
                <span>2</span>
                <p>Setelah itu bawa surat izin tersebut saat masuk dan menyerahkannya kepada wali kelas.</p>
            </div>

            <div class="aturan-item">
                <span>3</span>
                <p>Jika izin dari sekolah, harap mengajukan surat dispensasi ke Budaya Positif.</p>
            </div>

            <div class="aturan-item">
                <span>4</span>
                <p>Kemudian mintalah tanda tangan kepada wali kelas.</p>
            </div>

            <div class="aturan-item">
                <span>5</span>
                <p>Jika tanda tangan sudah lengkap, serahkan surat ke Budaya Positif dan dispensasi dimulai.</p>
            </div>

            <div class="aturan-item">
                <span>6</span>
                <p>Ketika dispensasi selesai, mohon melaporkan diri ke Budaya Positif.</p>
            </div>
        </div>

    </div>
</div>

<!-- MODAL QR CODE -->
<div class="modal-overlay" id="qrModal">
    <div class="qr-modal">
        <button class="modal-close" onclick="closeQR()">×</button>

        <div class="qr-large">
            <svg viewBox="0 0 100 100" aria-hidden="true">
                <rect x="5" y="5" width="28" height="28"/>
                <rect x="11" y="11" width="16" height="16" class="white"/>
                <rect x="15" y="15" width="8" height="8"/>

                <rect x="67" y="5" width="28" height="28"/>
                <rect x="73" y="11" width="16" height="16" class="white"/>
                <rect x="77" y="15" width="8" height="8"/>

                <rect x="5" y="67" width="28" height="28"/>
                <rect x="11" y="73" width="16" height="16" class="white"/>
                <rect x="15" y="77" width="8" height="8"/>

                <path d="M40 5h8v8h-8zM52 5h8v15h-8z
                M40 20h8v8h-8zM52 28h8v8h-8z
                M40 40h8v8h-8zM52 40h8v8h-8z
                M64 40h8v8h-8zM80 40h8v8h-8z
                M40 52h8v8h-8zM56 52h8v8h-8z
                M72 52h8v8h-8zM88 52h7v8h-7z
                M40 64h8v8h-8zM52 64h8v8h-8z
                M64 64h8v8h-8zM80 64h8v8h-8z
                M40 80h8v8h-8zM56 80h8v8h-8z
                M68 80h8v8h-8zM84 80h8v8h-8z
                M40 92h8v3h-8zM56 92h8v3h-8z"/>
            </svg>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.presensi-wrapper {
    min-height: 100vh;
    background: #f4f6f9;
    font-family: 'Poppins', sans-serif;
    color: #333b46;
    padding: 0 25px 30px;
}

.presensi-topbar {
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.aturan-button {
    border: 0;
    background: #d41480;
    color: white;
    padding: 10px 25px;
    border-radius: 5px;
    font-family: inherit;
    font-size: 12px;
    cursor: pointer;
    box-shadow: 0 2px 5px #00000018;
}

.aturan-button:hover,
.submit-button:hover {
    background: #b90e6d;
}

.student-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11px;
}

.student-profile small {
    display: block;
    color: #7d8793;
    font-size: 9px;
}

.profile-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #2876c8;
    color: white;
    display: grid;
    place-items: center;
    font-weight: 600;
}

.presensi-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.presensi-grid {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 12px;
}

.content-card {
    background: #fff;
    border: 1px solid #dce2e8;
    border-radius: 9px;
    padding: 20px;
}

.content-card h3 {
    margin: 0 0 15px;
    font-size: 14px;
    font-weight: 500;
}

.qr-card {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.qr-card h3 {
    align-self: flex-start;
}

.qr-preview {
    border: 1px solid #5da9e9;
    background: white;
    border-radius: 7px;
    width: 100%;
    max-width: 230px;
    min-height: 190px;
    display: grid;
    place-items: center;
    cursor: pointer;
    padding: 15px;
}

.qr-placeholder {
    width: 150px;
    height: 150px;
    display: grid;
    place-items: center;
}

.qr-placeholder svg,
.qr-large svg {
    width: 100%;
    height: 100%;
    fill: #101010;
}

.qr-placeholder .white,
.qr-large .white {
    fill: white;
}

.qr-caption {
    color: #84909e;
    font-size: 10px;
    margin: 10px 0 0;
}

.form-card form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-size: 11px;
    font-weight: 500;
}

.form-group input,
.form-group textarea {
    border: 1px solid #91c4ee;
    border-radius: 6px;
    padding: 9px 11px;
    font-family: inherit;
    font-size: 11px;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #d41480;
    box-shadow: 0 0 0 2px #d4148015;
}

.form-group textarea {
    resize: vertical;
    min-height: 55px;
}

.submit-button {
    align-self: flex-end;
    background: #d41480;
    color: white;
    border: 0;
    border-radius: 5px;
    padding: 9px 28px;
    font-family: inherit;
    font-size: 11px;
    cursor: pointer;
}

.history-card {
    min-height: 210px;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.search-box {
    display: flex;
    align-items: center;
    border: 1px solid #dce2e8;
    border-radius: 6px;
    padding: 0 10px;
    width: 240px;
}

.search-box input {
    width: 100%;
    border: 0;
    outline: 0;
    padding: 8px 0;
    font-family: inherit;
    font-size: 10px;
}

.search-box span {
    color: #84909e;
    font-size: 19px;
}

.empty-history {
    min-height: 130px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #84909e;
}

.empty-icon {
    font-size: 28px;
    margin-bottom: 8px;
}

.empty-history strong {
    color: #596575;
    font-size: 12px;
    font-weight: 500;
}

.empty-history p {
    font-size: 10px;
}

/* MODAL */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .48);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal-overlay.show {
    display: flex;
}

.aturan-modal {
    background: white;
    border-radius: 9px;
    width: 100%;
    max-width: 470px;
    max-height: 85vh;
    overflow-y: auto;
    position: relative;
    padding: 45px 23px 25px;
    box-sizing: border-box;
}

.modal-close {
    position: absolute;
    right: -12px;
    top: -12px;
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 8px;
    background: #df087e;
    color: white;
    font-size: 25px;
    cursor: pointer;
    z-index: 2;
    box-shadow: 0 3px 8px #0002;
}

.aturan-modal > .modal-close {
    position: fixed;
    top: auto;
    right: auto;
    transform: translate(435px, -220px);
}

.aturan-tabs {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
}

.tab-button {
    border: 1px solid #df087e;
    color: #333;
    background: white;
    border-radius: 8px;
    padding: 9px 18px;
    font-family: inherit;
    font-size: 11px;
    cursor: pointer;
}

.tab-button.active {
    background: #df087e;
    color: white;
}

.aturan-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.aturan-item {
    display: flex;
    align-items: center;
    gap: 13px;
}

.aturan-item span {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    background: #247bd1;
    color: white;
    display: grid;
    place-items: center;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 600;
}

.aturan-item p {
    flex: 1;
    border: 1px solid #6caaf0;
    border-radius: 8px;
    padding: 9px;
    margin: 0;
    font-size: 10px;
    line-height: 1.6;
}

.hidden {
    display: none !important;
}

.qr-modal {
    position: relative;
    background: white;
    padding: 25px;
    border-radius: 9px;
    width: 100%;
    max-width: 390px;
    box-sizing: border-box;
}

.qr-modal .modal-close {
    top: -13px;
    right: -13px;
}

.qr-large {
    width: 100%;
    border: 1px solid #8bc5f3;
    border-radius: 7px;
    padding: 15px;
    box-sizing: border-box;
}

@media (max-width: 800px) {
    .presensi-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 550px) {
    .presensi-wrapper {
        padding: 0 13px 20px;
    }

    .content-card {
        padding: 15px;
    }

    .history-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .search-box {
        width: 100%;
        box-sizing: border-box;
    }

    .aturan-tabs {
        gap: 8px;
    }

    .aturan-modal {
        padding: 40px 15px 20px;
    }

    .aturan-modal > .modal-close {
        position: absolute;
        top: 5px;
        right: 5px;
        transform: none;
    }
}
</style>
@endpush

<script>
function openAturan() {
    document.getElementById('aturanModal').classList.add('show');
}

function closeAturan() {
    document.getElementById('aturanModal').classList.remove('show');
}

function openQR() {
    document.getElementById('qrModal').classList.add('show');
}

function closeQR() {
    document.getElementById('qrModal').classList.remove('show');
}

function showTab(tab, button) {
    document.getElementById('tabPresensi').classList.toggle('hidden', tab !== 'presensi');
    document.getElementById('tabIzin').classList.toggle('hidden', tab !== 'izin');

    document.querySelectorAll('.tab-button').forEach(function(item) {
        item.classList.remove('active');
    });

    button.classList.add('active');
}

document.querySelectorAll('.modal-overlay').forEach(function(modal) {
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAturan();
        closeQR();
    }
});

document.getElementById('formPresensi').addEventListener('submit', function(event) {
    event.preventDefault();
    alert('Form belum terhubung dengan backend. Data presensi belum disimpan.');
});
</script>
