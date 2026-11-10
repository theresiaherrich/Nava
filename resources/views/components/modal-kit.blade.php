<style>
    .mk-overlay { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 23, 42, .4); font-family: "Plus Jakarta Sans", "Segoe UI", system-ui, sans-serif; color: #0f172a; }
    .mk-overlay.is-open { display: flex; }
    .mk-overlay [hidden] { display: none !important; }
    .mk-overlay * { box-sizing: border-box; }
    .mk-box { width: 100%; max-width: 560px; max-height: calc(100vh - 32px); overflow-y: auto; background: #fff; border-radius: 14px; padding: 20px 22px; box-shadow: 0 20px 40px rgba(15, 23, 42, .18); }
    .mk-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
    .mk-title { font-size: 15px; font-weight: 700; margin: 0 0 2px; }
    .mk-desc { font-size: 10px; color: #94a3b8; margin: 0; }
    .mk-close { border: 0; background: none; font-size: 20px; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0; }
    .mk-close:hover { color: #475569; }

    .mk-steps { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 11px; color: #64748b; }
    .mk-step { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .mk-step.is-active { color: #0369a1; font-weight: 600; }
    .mk-step.is-done { color: #15803d; font-weight: 600; }
    .mk-num { width: 20px; height: 20px; border-radius: 50%; display: inline-grid; place-items: center; font-size: 10px; font-weight: 700; background: #f1f5f9; color: #64748b; }
    .mk-step.is-active .mk-num { background: #0ea5e9; color: #fff; }
    .mk-step.is-done .mk-num { background: #16a34a; color: #fff; }
    .mk-line { flex: 1; height: 1px; background: #e2e8f0; min-width: 16px; }

    .mk-drop { border: 1.5px dashed #7dd3fc; background: #f8fdff; border-radius: 12px; padding: 26px 16px; text-align: center; cursor: pointer; transition: background .15s, border-color .15s; }
    .mk-drop:hover, .mk-drop.is-over { border-color: #0ea5e9; background: #e0f2fe; }
    .mk-drop:focus-visible { outline: none; box-shadow: 0 0 0 3px #bae6fd; }
    .mk-drop.is-invalid { border-color: #fca5a5; background: #fef2f2; }
    .mk-drop-icon { width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 50%; background: #e0f2fe; color: #0369a1; display: grid; place-items: center; font-size: 20px; font-weight: 700; }
    .mk-drop-title { font-size: 13px; font-weight: 600; margin: 0; }
    .mk-drop-text { font-size: 11px; color: #64748b; margin: 4px 0 0; }
    .mk-drop-link { color: #0369a1; font-weight: 600; text-decoration: underline; }

    .mk-file { display: flex; align-items: center; gap: 12px; border: 1px solid #e8edf3; border-radius: 10px; padding: 10px 12px; }
    .mk-file-icon { width: 36px; height: 36px; border-radius: 8px; background: #dcfce7; color: #15803d; display: grid; place-items: center; font-weight: 700; font-size: 10px; flex-shrink: 0; }
    .mk-file-body { flex: 1; min-width: 0; }
    .mk-file-name { font-size: 12px; font-weight: 600; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .mk-error { font-size: 10px; color: #dc2626; margin: 6px 0 0; }
    .mk-alert { margin-bottom: 14px; padding: 10px 14px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #15803d; font-size: 12px; }

    .mk-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 14px; }
    .mk-full { grid-column: 1 / -1; }
    .mk-field label { display: block; font-size: 11px; font-weight: 600; color: #334155; margin-bottom: 5px; }
    .mk-input, .mk-select { width: 100%; font-family: inherit; font-size: 12px; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: #0f172a; outline: none; }
    .mk-input:focus, .mk-select:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px #e0f2fe; }
    .mk-input.is-error, .mk-select.is-error { border-color: #f87171; }

    .mk-foot { display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: 18px; }
    .mk-foot-left { margin-right: auto; }
    .mk-link { font-size: 11px; font-weight: 600; color: #0369a1; text-decoration: none; }
    .mk-link:hover { text-decoration: underline; }

    .mk-btn, .mk-btn-primary { font-family: inherit; font-size: 12px; font-weight: 500; padding: 8px 14px; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .mk-btn { background: #fff; border: 1px solid #e2e8f0; color: #0f172a; }
    .mk-btn:hover { background: #f8fafc; }
    .mk-btn-primary { background: #0ea5e9; border: 1px solid #0ea5e9; color: #fff; font-weight: 600; }
    .mk-btn-primary:hover { background: #0284c7; }
    .mk-btn-primary:disabled { background: #e2e8f0; border-color: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
    .mk-btn-sm { padding: 5px 10px; font-size: 11px; }
</style>

<script>
(function () {
    var MAKS = 5 * 1024 * 1024;
    var POLA = /\.(xlsx|xls|csv)$/i;

    function buka(el) {
        if (!el) return;
        el.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        var f = el.querySelector('.mk-input, .mk-select');
        if (f) f.focus();
    }

    function tutup(el) {
        if (!el) return;
        el.classList.remove('is-open');
        if (!document.querySelector('.mk-overlay.is-open')) {
            document.body.style.overflow = '';
        }
    }

    function ukuran(b) {
        return b >= 1048576
            ? (b / 1048576).toFixed(1) + ' MB'
            : Math.max(1, Math.round(b / 1024)) + ' KB';
    }

    function setStep(wrap, n) {
        wrap.querySelectorAll('.mk-step').forEach(function (s, i) {
            s.classList.toggle('is-done', i + 1 < n);
            s.classList.toggle('is-active', i + 1 === n);
        });
    }

    // Buka dan tutup modal
    document.addEventListener('click', function (e) {
        var bukaBtn = e.target.closest('[data-mk-open]');
        if (bukaBtn) {
            e.preventDefault();
            buka(document.getElementById(bukaBtn.getAttribute('data-mk-open')));
            return;
        }
        var tutupBtn = e.target.closest('[data-mk-close]');
        if (tutupBtn) {
            tutup(tutupBtn.closest('.mk-overlay'));
            return;
        }
        if (e.target.classList && e.target.classList.contains('mk-overlay')) {
            tutup(e.target);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.mk-overlay.is-open').forEach(tutup);
        }
    });

    // Modal yang harus terbuka lagi karena ada error validasi
    document.querySelectorAll('[data-auto-open]').forEach(buka);

    // Area drag and drop untuk impor
    document.querySelectorAll('[data-dropzone]').forEach(function (zona) {
        var wrap   = zona.closest('.mk-overlay');
        var input  = wrap.querySelector('[data-file-input]');
        var info   = wrap.querySelector('[data-file-info]');
        var pesan  = wrap.querySelector('[data-drop-msg]');
        var submit = wrap.querySelector('[data-submit-impor]');

        function pilih() { input.click(); }

        function gagal(teks) {
            input.value = '';
            pesan.textContent = teks;
            pesan.hidden = false;
            zona.classList.add('is-invalid');
            zona.hidden = false;
            info.hidden = true;
            submit.disabled = true;
            setStep(wrap, 1);
        }

        function proses() {
            var f = input.files && input.files[0];
            pesan.hidden = true;
            zona.classList.remove('is-invalid');

            if (!f) {
                info.hidden = true;
                zona.hidden = false;
                submit.disabled = true;
                setStep(wrap, 1);
                return;
            }
            if (!POLA.test(f.name)) return gagal('Format harus .xlsx, .xls, atau .csv.');
            if (f.size > MAKS) return gagal('Ukuran berkas maksimal 5 MB.');

            wrap.querySelector('[data-file-name]').textContent = f.name;
            wrap.querySelector('[data-file-size]').textContent = ukuran(f.size);
            zona.hidden = true;
            info.hidden = false;
            submit.disabled = false;
            setStep(wrap, 2);
        }

        zona.addEventListener('click', pilih);
        zona.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                pilih();
            }
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            zona.addEventListener(ev, function (e) {
                e.preventDefault();
                zona.classList.add('is-over');
            });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            zona.addEventListener(ev, function (e) {
                e.preventDefault();
                zona.classList.remove('is-over');
            });
        });
        zona.addEventListener('drop', function (e) {
            var files = e.dataTransfer && e.dataTransfer.files;
            if (files && files.length) {
                input.files = files;
                proses();
            }
        });

        input.addEventListener('change', proses);
        wrap.querySelector('[data-ganti-berkas]').addEventListener('click', pilih);
        wrap.querySelector('[data-hapus-berkas]').addEventListener('click', function () {
            input.value = '';
            proses();
        });

        // Cegah kirim ganda
        wrap.querySelector('form').addEventListener('submit', function () {
            submit.disabled = true;
            submit.textContent = 'Mengunggah...';
        });
    });
})();
</script>