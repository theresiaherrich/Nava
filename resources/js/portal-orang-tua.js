/* resources/js/portal-orang-tua.js
   Interaksi ringan Portal Orang Tua: toast, pencarian mapel, tab rekap, klik tanggal presensi. */

(function () {
    'use strict';

    var root = document.querySelector('.pt');
    if (!root) return;

    /* ---------- Toast ---------- */
    function showToast(msg, type) {
        var old = root.querySelector('.pt-toast[data-pt-js]');
        if (old) old.remove();
        var el = document.createElement('div');
        el.className = 'pt-toast pt-toast--' + (type || 'info');
        el.setAttribute('role', 'status');
        el.setAttribute('data-pt-js', '');
        el.textContent = msg;
        root.appendChild(el);
        autoHide(el);
    }
    function autoHide(el) {
        setTimeout(function () {
            el.classList.add('is-leaving');
            setTimeout(function () { el.remove(); }, 300);
        }, 3500);
    }
    var serverToast = root.querySelector('[data-pt-toast]');
    if (serverToast) autoHide(serverToast);

    // Tombol/tautan yang belum punya halaman tujuan
    root.addEventListener('click', function (e) {
        var soon = e.target.closest('[data-pt-soon]');
        if (!soon) return;
        e.preventDefault();
        showToast('Fitur ini belum tersedia.', 'info');
    });

    /* ---------- Pencarian mata pelajaran ---------- */
    var form  = root.querySelector('[data-pt-search-form]');
    var input = root.querySelector('[data-pt-search]');
    var rows  = root.querySelectorAll('[data-pt-searchable]');

    function filterRows() {
        var q = input.value.trim().toLowerCase();
        var shown = 0;
        rows.forEach(function (tr) {
            var ok = !q || tr.textContent.toLowerCase().indexOf(q) !== -1;
            tr.hidden = !ok;
            if (ok) shown++;
        });
        var empty = root.querySelector('.pt-empty');
        if (empty) empty.hidden = shown !== 0;
    }

    if (form && input) {
        if (rows.length) {
            // Di halaman Akademik & Nilai: saring tabel langsung
            input.addEventListener('input', filterRows);
            form.addEventListener('submit', function (e) { e.preventDefault(); filterRows(); });
            filterRows();
        }
        // Di halaman lain: submit biasa -> pindah ke Akademik & Nilai?q=...
    }

    /* ---------- Tab Rekap Nilai ---------- */
    root.querySelectorAll('[data-pt-tabs]').forEach(function (box) {
        var tabs   = box.querySelectorAll('[data-tab]');
        var panels = box.querySelectorAll('[data-panel]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) {
                    var on = t === tab;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                panels.forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== tab.getAttribute('data-tab'); });
            });
        });
    });

    /* ---------- Kalender presensi -> Detail Log RFID ---------- */
    var dataEl = document.getElementById('pt-log-data');
    var logBox = document.getElementById('pt-log');
    if (dataEl && logBox) {
        var data;
        try { data = JSON.parse(dataEl.textContent); } catch (err) { data = null; }

        var set = function (key, val) {
            var el = logBox.querySelector('[data-log="' + key + '"]');
            if (el) el.textContent = val;
        };

        if (data) {
            root.querySelectorAll('.pt-cal__cell[data-date]').forEach(function (cell) {
                cell.addEventListener('click', function () {
                    var d = data.log[cell.getAttribute('data-date')];
                    if (!d) return;

                    root.querySelectorAll('.pt-cal__cell.is-selected').forEach(function (c) { c.classList.remove('is-selected'); });
                    cell.classList.add('is-selected');

                    set('tanggal', d.tanggal_label);
                    set('status', data.label[d.status] || '-');
                    set('masuk', d.masuk ? d.masuk + ' WIB' : '-');
                    set('pulang', d.pulang ? d.pulang + ' WIB' : '-');
                    set('gerbang', d.gerbang || '-');
                    set('suhu', d.suhu || '-');
                    set('metode', d.metode || '-');
                    set('catatan', d.catatan || d.keterangan || '-');
                });
            });
        }
    }
})();
