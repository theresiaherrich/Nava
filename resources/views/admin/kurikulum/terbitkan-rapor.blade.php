@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }

    .tb-wrap { padding: 24px; max-width: 1280px; margin: 0 auto; color: #111827; font-size: 12px; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
    .tb-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: .08em; color: #0ea5e9; text-transform: uppercase; }
    .tb-title { font-size: 22px; font-weight: 700; margin-top: 2px; }
    .tb-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }

    .tb-notice { display: flex; gap: 12px; align-items: flex-start; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 14px; padding: 12px 16px; margin-top: 18px; }
    .tb-notice-icon { width: 28px; height: 28px; border-radius: 8px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 13px; }
    .tb-notice-title { font-weight: 700; color: #0c4a6e; }
    .tb-notice-text { color: #0369a1; margin-top: 2px; }

    .tb-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 10px; margin-top: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .tb-toolbar-left { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .tb-seg { display: inline-flex; background: #f3f4f6; border-radius: 10px; padding: 3px; }
    .tb-seg button { border: 0; background: transparent; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; color: #6b7280; cursor: pointer; }
    .tb-seg button.active { background: #fff; color: #0284c7; font-weight: 700; box-shadow: 0 1px 2px rgba(0,0,0,.08); }
    .tb-select { border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 10px; padding: 7px 10px; font-size: 11px; font-weight: 600; color: #374151; outline: none; }
    .tb-search { position: relative; }
    .tb-search input { width: 240px; border: 1px solid #e5e7eb; background: #f9fafb; border-radius: 10px; padding: 7px 12px 7px 32px; font-size: 11px; outline: none; }
    .tb-search input:focus { border-color: #38bdf8; background: #fff; box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
    .tb-search svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #9ca3af; }

    .tb-flow { display: flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 600; }
    .tb-flow span.arrow { color: #d1d5db; }

    .tb-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; margin-top: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .tb-table { width: 100%; border-collapse: collapse; }
    .tb-table th { text-align: left; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; padding: 12px 14px; background: #fafafa; border-bottom: 1px solid #e5e7eb; }
    .tb-table td { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; font-size: 11.5px; color: #374151; vertical-align: middle; }
    .tb-table tr:last-child td { border-bottom: 0; }
    .tb-table tbody tr:hover td { background: #fafafa; }
    .tb-table tr.is-selected td { background: #f0f9ff; }
    .tb-table .c { text-align: center; }
    .tb-table .r { text-align: right; }
    .tb-kelas { font-weight: 700; color: #111827; }

    .tb-check { width: 15px; height: 15px; accent-color: #0ea5e9; cursor: pointer; }
    .tb-check:disabled { cursor: not-allowed; opacity: .35; }

    .tb-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 10px; font-weight: 600; padding: 3px 9px; border-radius: 999px; border: 1px solid; white-space: nowrap; }
    .tb-pill.ok   { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .tb-pill.warn { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .tb-pill.gray { background: #f3f4f6; color: #6b7280; border-color: #e5e7eb; }
    .tb-pill.sky  { background: #f0f9ff; color: #0369a1; border-color: #bae6fd; }
    .tb-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .tb-btn { display: inline-flex; align-items: center; gap: 6px; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 10px; padding: 6px 11px; font-size: 11px; font-weight: 600; cursor: pointer; transition: background .15s, transform .05s; white-space: nowrap; }
    .tb-btn:hover { background: #f9fafb; }
    .tb-btn:active { transform: scale(.97); }
    .tb-btn:disabled { opacity: .45; cursor: not-allowed; }
    .tb-btn.primary { background: #0ea5e9; border-color: #0ea5e9; color: #fff; }
    .tb-btn.primary:hover { background: #0284c7; }
    .tb-btn.danger { color: #be123c; }
    .tb-btn.danger:hover { background: #fff1f2; }
    .tb-btn.ghost { border-color: transparent; background: transparent; color: #6b7280; }
    .tb-btn svg { width: 13px; height: 13px; }

    .tb-tip { position: relative; display: inline-flex; }
    .tb-tip[data-tip]:hover::after { content: attr(data-tip); position: absolute; right: 0; bottom: calc(100% + 6px); background: #1f2937; color: #fff; font-size: 10px; padding: 5px 8px; border-radius: 6px; white-space: nowrap; z-index: 5; }

    .tb-footer { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-top: 1px solid #f3f4f6; background: #fafafa; font-size: 11px; color: #6b7280; }
    .tb-pages { display: flex; gap: 4px; }
    .tb-pg { width: 26px; height: 26px; border-radius: 7px; border: 1px solid #e5e7eb; background: #fff; color: #4b5563; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .tb-pg:hover { background: #f9fafb; }
    .tb-pg:disabled { opacity: .4; cursor: not-allowed; }
    .tb-pg.active { background: #0ea5e9; border-color: #0ea5e9; color: #fff; font-weight: 700; }

    .tb-bulk { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); display: flex; align-items: center; gap: 14px; background: #1e293b; color: #fff; padding: 10px 12px 10px 18px; border-radius: 16px; box-shadow: 0 20px 40px -10px rgba(0,0,0,.35); z-index: 40; max-width: calc(100vw - 32px); }
    .tb-bulk-count { font-weight: 700; font-size: 12px; }
    .tb-bulk-list { font-size: 10.5px; color: #94a3b8; margin-top: 2px; }
    .tb-bulk .tb-btn { background: transparent; border-color: #475569; color: #e2e8f0; }
    .tb-bulk .tb-btn:hover { background: #334155; }
    .tb-bulk .tb-btn.primary { background: #0ea5e9; border-color: #0ea5e9; color: #fff; }
    .tb-bulk .tb-btn.primary:hover { background: #0284c7; }

    .tb-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.45); display: flex; align-items: center; justify-content: center; z-index: 50; padding: 16px; }
    .tb-modal { background: #fff; border-radius: 18px; width: 100%; max-width: 420px; padding: 22px; box-shadow: 0 25px 50px -12px rgba(0,0,0,.3); position: relative; }
    .tb-modal-close { position: absolute; top: 14px; right: 14px; border: 0; background: transparent; color: #9ca3af; font-size: 18px; cursor: pointer; line-height: 1; }
    .tb-modal-icon { width: 36px; height: 36px; border-radius: 10px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
    .tb-modal-title { font-size: 15px; font-weight: 700; }
    .tb-modal-text { font-size: 11.5px; color: #6b7280; margin-top: 4px; }
    .tb-box { border-radius: 10px; padding: 10px 12px; font-size: 11px; margin-top: 12px; display: flex; gap: 8px; align-items: flex-start; border: 1px solid; }
    .tb-box.ok { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .tb-box.warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .tb-box.info { background: #f8fafc; border-color: #e2e8f0; color: #334155; }
    .tb-check-row { display: flex; align-items: center; gap: 8px; margin-top: 12px; font-size: 11.5px; color: #374151; cursor: pointer; }
    .tb-modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }

    .tb-toast { position: fixed; bottom: 24px; right: 24px; background: #1e293b; color: #fff; padding: 11px 16px; border-radius: 12px; font-size: 12px; font-weight: 500; box-shadow: 0 10px 30px -5px rgba(0,0,0,.3); z-index: 60; display: flex; align-items: center; gap: 10px; }
    .tb-toast .ok-dot { width: 18px; height: 18px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; }
    .tb-toast .ok-dot svg { width: 11px; height: 11px; color: #fff; }

    @media (max-width: 900px) {
        .tb-search input { width: 180px; }
        .tb-flow { display: none; }
    }
</style>

<div class="tb-wrap" x-data="terbitRapor" x-cloak>

    {{-- HEADER --}}
    <div>
        <p class="tb-eyebrow">SMA Negeri 1 Kota Malang · Kurikulum</p>
        <h1 class="tb-title">Terbitkan Rapor</h1>
        <p class="tb-sub">Rapor yang diterbitkan dapat dilihat siswa dan orang tua di portal.</p>
    </div>

    {{-- NOTICE --}}
    <div class="tb-notice">
        <div class="tb-notice-icon">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
        </div>
        <div>
            <p class="tb-notice-title">Kunci nilai tidak otomatis menerbitkan rapor.</p>
            <p class="tb-notice-text">Terbitkan setelah PDF dibuat. Rapor yang terbit langsung muncul di Portal Orang Tua.</p>
        </div>
    </div>

    {{-- TOOLBAR --}}
    <div class="tb-toolbar">
        <div class="tb-toolbar-left">
            <div class="tb-seg">
                <button type="button" :class="tipe === 'sts' ? 'active' : ''" @click="gantiTipe('sts')">Rapor STS</button>
                <button type="button" :class="tipe === 'semester' ? 'active' : ''" @click="gantiTipe('semester')">Rapor Semester</button>
            </div>

            <select class="tb-select" x-model="tingkat" @change="page = 1">
                <option value="Kelas X">Tingkat: Kelas X</option>
            </select>

            <div class="tb-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Cari kelas..." x-model="search" @input="page = 1">
            </div>
        </div>

        <div class="tb-flow">
            <span class="tb-pill ok">Terkunci</span>
            <span class="arrow">→</span>
            <span class="tb-pill sky">PDF dibuat</span>
            <span class="arrow">→</span>
            <span class="tb-pill gray">✓ Terbit</span>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="tb-card">
        <table class="tb-table">
            <thead>
                <tr>
                    <th style="width:40px;" class="c">
                        <input type="checkbox" class="tb-check"
                               :checked="semuaHalamanTerpilih"
                               :disabled="!adaYangBisaTerbitHalaman"
                               @change="toggleHalaman($event.target.checked)">
                    </th>
                    <th style="width:90px;">Kelas</th>
                    <th>Wali Kelas</th>
                    <th style="width:120px;">Kunci</th>
                    <th style="width:130px;">PDF</th>
                    <th style="width:220px;">Status Terbit</th>
                    <th style="width:150px;" class="r">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="k in kelasHalaman" :key="k.id">
                    <tr :class="isTerpilih(k.id) ? 'is-selected' : ''">
                        <td class="c">
                            <input type="checkbox" class="tb-check"
                                   :checked="isTerpilih(k.id)"
                                   :disabled="!bisaTerbit(k)"
                                   @change="toggleKelas(k.id, $event.target.checked)">
                        </td>
                        <td><span class="tb-kelas" x-text="k.id"></span></td>
                        <td x-text="k.wali"></td>
                        <td>
                            <span :class="k.kunci === 'Terkunci' ? 'tb-pill ok' : (k.kunci === 'Terkoreksi' ? 'tb-pill warn' : 'tb-pill gray')">
                                <span x-show="k.kunci === 'Terkunci'">🔒</span>
                                <span x-show="k.kunci === 'Terkoreksi'">⚠</span>
                                <span x-text="k.kunci"></span>
                            </span>
                        </td>
                        <td>
                            <span :class="pdfSiap(k) ? 'tb-pill warn' : 'tb-pill gray'"
                                  x-text="k.pdf[tipe] === 0 ? 'Belum dibuat' : (k.pdf[tipe] + '/' + k.total + ' dibuat')"></span>
                        </td>
                        <td>
                            <span :class="k.terbit[tipe] ? 'tb-pill ok' : 'tb-pill gray'">
                                <span class="dot" x-show="k.terbit[tipe]"></span>
                                <span x-text="k.terbit[tipe] ? 'Terbit · ' + k.terbit[tipe] : 'Belum terbit'"></span>
                            </span>
                        </td>
                        <td class="r">
                            <template x-if="k.terbit[tipe]">
                                <button type="button" class="tb-btn danger" @click="bukaTarik(k.id)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 010 10H9m-6-10l4-4m-4 4l4 4"/></svg>
                                    Tarik terbitan
                                </button>
                            </template>
                            <template x-if="!k.terbit[tipe]">
                                <span class="tb-tip" :data-tip="alasanTidakBisa(k)">
                                    <button type="button" class="tb-btn" :disabled="!bisaTerbit(k)" @click="bukaTerbit([k.id])">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/></svg>
                                        Terbitkan
                                    </button>
                                </span>
                            </template>
                        </td>
                    </tr>
                </template>

                <tr x-show="kelasHalaman.length === 0">
                    <td colspan="7" style="text-align:center; padding:40px 14px; color:#9ca3af;">Kelas tidak ditemukan</td>
                </tr>
            </tbody>
        </table>

        {{-- FOOTER / PAGINASI --}}
        <div class="tb-footer">
            <span>
                <span x-text="dariMulai"></span>–<span x-text="dariSampai"></span> dari <span x-text="kelasCari.length"></span> kelas
            </span>
            <div class="tb-pages">
                <button type="button" class="tb-pg" @click="page > 1 && page--" :disabled="page === 1">&lsaquo;</button>
                <template x-for="p in totalHalaman" :key="p">
                    <button type="button" class="tb-pg" :class="page === p ? 'active' : ''" @click="page = p" x-text="p"></button>
                </template>
                <button type="button" class="tb-pg" @click="page < totalHalaman && page++" :disabled="page === totalHalaman">&rsaquo;</button>
            </div>
        </div>
    </div>

    {{-- BULK BAR --}}
    <div class="tb-bulk" x-show="terpilih.length > 0" x-transition>
        <div>
            <div class="tb-bulk-count">
                <span x-text="terpilih.length"></span> kelas dipilih
            </div>
            <div class="tb-bulk-list" x-text="terpilih.join(', ') + ' · Rapor ' + tipeLabel"></div>
        </div>
        <button type="button" class="tb-btn" @click="terpilih = []">Batal pilih</button>
        <button type="button" class="tb-btn primary" @click="bukaTerbit(terpilih)">Terbitkan yang dipilih</button>
    </div>

    {{-- MODAL KONFIRMASI --}}
    <div class="tb-overlay" x-show="modal.open" x-transition.opacity @click.self="tutupModal()" @keydown.escape.window="tutupModal()">
        <div class="tb-modal" @click.stop>
            <button type="button" class="tb-modal-close" @click="tutupModal()">&times;</button>

            <div class="tb-modal-icon" x-show="modal.mode === 'terbit'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5L6 9H2v6h4l5 4V5zM19 12a7 7 0 00-2-5m2 10a7 7 0 000-10"/></svg>
            </div>
            <div class="tb-modal-icon" style="background:#fff1f2;color:#be123c;" x-show="modal.mode === 'tarik'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 010 10H9m-6-10l4-4m-4 4l4 4"/></svg>
            </div>

            <h2 class="tb-modal-title" x-text="judulModal"></h2>
            <p class="tb-modal-text" x-show="modal.mode === 'terbit'">Rapor akan langsung dapat dilihat siswa dan orang tua di portal.</p>
            <p class="tb-modal-text" x-show="modal.mode === 'tarik'">Rapor akan ditarik dan tidak lagi tampil di Portal Orang Tua.</p>

            {{-- Ringkasan terbit --}}
            <template x-if="modal.mode === 'terbit'">
                <div>
                    <div class="tb-box ok">
                        <span>✓</span>
                        <span><span x-text="ringkasan.siswa"></span> siswa · <span x-text="ringkasan.pdf"></span> PDF siap</span>
                    </div>
                    <div class="tb-box warn" x-show="ringkasan.belumOrtu > 0">
                        <span>⚠</span>
                        <span><span x-text="ringkasan.belumOrtu"></span> siswa belum memiliki orang tua terhubung.</span>
                    </div>
                    <label class="tb-check-row">
                        <input type="checkbox" class="tb-check" x-model="modal.notif">
                        Kirim notifikasi ke portal siswa dan orang tua
                    </label>
                </div>
            </template>

            {{-- Ringkasan tarik --}}
            <template x-if="modal.mode === 'tarik'">
                <div class="tb-box info">
                    <span>i</span>
                    <span>Anda bisa menerbitkan ulang kapan saja setelah rapor diperbaiki.</span>
                </div>
            </template>

            <div class="tb-modal-actions">
                <button type="button" class="tb-btn" @click="tutupModal()">Batal</button>
                <button type="button" class="tb-btn primary" x-show="modal.mode === 'terbit'" @click="konfirmasi()" x-text="'Terbitkan ' + (modal.ids.length > 1 ? modal.ids.length + ' kelas' : '')"></button>
                <button type="button" class="tb-btn danger" x-show="modal.mode === 'tarik'" @click="konfirmasi()">Tarik terbitan</button>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <div class="tb-toast" x-show="showToast" x-transition.opacity>
        <span class="ok-dot">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </span>
        <span x-text="toastMsg"></span>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {

    const DATA_AWAL = [
        { id: 'X-A', wali: 'Andini Pratiwi, S.Pd.',   kunci: 'Terbuka',    total: 32, pdf: { sts: 0,  semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-B', wali: 'Siti Rahmawati, S.Pd.',   kunci: 'Terkunci',   total: 32, pdf: { sts: 32, semester: 0  }, terbit: { sts: '30 Okt 2026, 09.00', semester: null }, belumOrtu: 1 },
        { id: 'X-C', wali: 'Bayu Nugraha, S.Pd.',     kunci: 'Terbuka',    total: 32, pdf: { sts: 0,  semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-D', wali: 'Ratna Sari, M.Pd.',       kunci: 'Terbuka',    total: 32, pdf: { sts: 0,  semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-E', wali: 'Yusuf Hidayat, S.Pd.',    kunci: 'Terkoreksi', total: 32, pdf: { sts: 0,  semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-F', wali: 'Leetiet Wulandari, S.Pd.', kunci: 'Terbuka',   total: 32, pdf: { sts: 0,  semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-G', wali: 'Dadi Kurniawan, S.Pd.',   kunci: 'Terkunci',   total: 32, pdf: { sts: 32, semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-H', wali: 'Maria Ulfa, S.Pd.',       kunci: 'Terkunci',   total: 32, pdf: { sts: 32, semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-I', wali: 'Rizal Fahmi, S.Pd.',      kunci: 'Terbuka',    total: 32, pdf: { sts: 0,  semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
        { id: 'X-J', wali: 'Novi Andriani, S.Pd.',    kunci: 'Terkunci',   total: 32, pdf: { sts: 32, semester: 0  }, terbit: { sts: null, semester: null }, belumOrtu: 0 },
    ];

    Alpine.data('terbitRapor', () => ({
        tipe: 'sts',
        tingkat: 'Kelas X',
        search: '',
        page: 1,
        perHalaman: 10,
        kelas: JSON.parse(JSON.stringify(DATA_AWAL)),
        terpilih: [],

        modal: { open: false, mode: 'terbit', ids: [], notif: true },
        showToast: false,
        toastMsg: '',
        toastTimer: null,

        // ===== Getter =====
        get tipeLabel() { return this.tipe === 'sts' ? 'STS' : 'Semester'; },

        get kelasCari() {
            const q = this.search.trim().toLowerCase();
            return this.kelas.filter(k => !q || k.id.toLowerCase().includes(q) || k.wali.toLowerCase().includes(q));
        },
        get totalHalaman() { return Math.max(1, Math.ceil(this.kelasCari.length / this.perHalaman)); },
        get kelasHalaman() {
            const awal = (this.page - 1) * this.perHalaman;
            return this.kelasCari.slice(awal, awal + this.perHalaman);
        },
        get dariMulai() { return this.kelasCari.length ? (this.page - 1) * this.perHalaman + 1 : 0; },
        get dariSampai() { return Math.min(this.page * this.perHalaman, this.kelasCari.length); },

        get bisaTerbitHalaman() { return this.kelasHalaman.filter(k => this.bisaTerbit(k)); },
        get adaYangBisaTerbitHalaman() { return this.bisaTerbitHalaman.length > 0; },
        get semuaHalamanTerpilih() {
            return this.adaYangBisaTerbitHalaman && this.bisaTerbitHalaman.every(k => this.terpilih.includes(k.id));
        },

        get ringkasan() {
            const list = this.modal.ids.map(id => this.cariKelas(id)).filter(Boolean);
            return {
                siswa: list.reduce((a, k) => a + k.total, 0),
                pdf: list.reduce((a, k) => a + k.pdf[this.tipe], 0),
                belumOrtu: list.reduce((a, k) => a + k.belumOrtu, 0),
            };
        },
        get judulModal() {
            if (this.modal.mode === 'tarik') return 'Tarik terbitan rapor ' + this.tipeLabel + ' ' + this.modal.ids[0] + '?';
            if (this.modal.ids.length === 1) return 'Terbitkan rapor ' + this.tipeLabel + ' kelas ' + this.modal.ids[0] + '?';
            return 'Terbitkan rapor ' + this.tipeLabel + ' untuk ' + this.modal.ids.length + ' kelas?';
        },

        // ===== Helper =====
        cariKelas(id) { return this.kelas.find(k => k.id === id); },
        pdfSiap(k) { return k.pdf[this.tipe] === k.total; },
        bisaTerbit(k) { return k.kunci === 'Terkunci' && this.pdfSiap(k) && !k.terbit[this.tipe]; },
        alasanTidakBisa(k) {
            if (k.terbit[this.tipe]) return 'Sudah diterbitkan';
            if (k.kunci !== 'Terkunci') return 'Kunci nilai dulu';
            if (!this.pdfSiap(k)) return 'Buat PDF semua siswa dulu';
            return '';
        },
        isTerpilih(id) { return this.terpilih.includes(id); },

        toast(msg) {
            this.toastMsg = msg;
            this.showToast = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => this.showToast = false, 3000);
        },
        stampWaktu() {
            return new Date().toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },

        // ===== Aksi =====
        gantiTipe(t) {
            this.tipe = t;
            this.terpilih = [];
            this.page = 1;
        },

        toggleKelas(id, checked) {
            if (checked && !this.terpilih.includes(id)) this.terpilih.push(id);
            if (!checked) this.terpilih = this.terpilih.filter(x => x !== id);
        },

        toggleHalaman(checked) {
            const ids = this.bisaTerbitHalaman.map(k => k.id);
            if (checked) {
                this.terpilih = [...new Set([...this.terpilih, ...ids])];
            } else {
                this.terpilih = this.terpilih.filter(x => !ids.includes(x));
            }
        },

        bukaTerbit(ids) {
            const valid = ids.filter(id => {
                const k = this.cariKelas(id);
                return k && this.bisaTerbit(k);
            });
            if (!valid.length) {
                this.toast('Tidak ada kelas yang bisa diterbitkan.');
                return;
            }
            this.modal = { open: true, mode: 'terbit', ids: valid, notif: true };
        },

        bukaTarik(id) {
            this.modal = { open: true, mode: 'tarik', ids: [id], notif: false };
        },

        tutupModal() { this.modal.open = false; },

        konfirmasi() {
            const ids = [...this.modal.ids];
            const tipeSaatIni = this.tipe;
            const tipeLabel = this.tipeLabel;

            if (this.modal.mode === 'terbit') {
                const waktu = this.stampWaktu();
                ids.forEach(id => {
                    const k = this.cariKelas(id);
                    if (k) k.terbit[tipeSaatIni] = waktu;
                });
                this.terpilih = this.terpilih.filter(x => !ids.includes(x));
                this.toast(ids.length + ' kelas rapor ' + tipeLabel + ' berhasil diterbitkan.');
            } else {
                ids.forEach(id => {
                    const k = this.cariKelas(id);
                    if (k) k.terbit[tipeSaatIni] = null;
                });
                this.toast('Terbitan rapor ' + tipeLabel + ' ' + ids[0] + ' ditarik.');
            }

            this.modal.open = false;
        },
    }));
});
</script>
@endsection