/*
 * resources/js/periode-penguncian.js
 * Interaksi halaman Periode & Penguncian (simulasi frontend).
 *
 * Status kunci hanya disimpan di memori halaman.
 * Tidak ada request ke server dan tidak ada penyimpanan di browser.
 */

function initPeriodePenguncian() {
    const root = document.querySelector('[data-pk-page]');

    // Mencegah inisialisasi ganda.
    if (!root || root.dataset.pkReady === '1') {
        return;
    }
    root.dataset.pkReady = '1';

    const tabSemester = Array.from(root.querySelectorAll('.pk-semester-tab'));
    const tabTingkat = Array.from(root.querySelectorAll('.pk-tingkat-tab'));
    const panels = Array.from(root.querySelectorAll('[data-tingkat-panel]'));
    const btnKunci = root.querySelector('#pk-btn-kunci');
    const labelKunci = root.querySelector('#pk-label-kunci');
    const btnTambah = root.querySelector('#pk-btn-tambah');
    const btnJadwal = root.querySelector('#pk-btn-jadwal');
    const feedback = root.querySelector('#pk-feedback');

    // Tingkat yang sudah dikunci selama halaman terbuka.
    const terkunci = new Set();

    let tingkatAktif =
        tabTingkat.find((t) => t.getAttribute('aria-selected') === 'true')?.dataset.tingkat ?? 'X';

    function tampilkan(pesan, jenis = 'info') {
        feedback.textContent = pesan;
        feedback.className = `pk-feedback pk-feedback-${jenis}`;
    }

    function panelAktif() {
        return panels.find((p) => p.dataset.tingkatPanel === tingkatAktif);
    }

    /** Menyesuaikan tab, panel, dan tombol kunci dengan tingkat yang aktif. */
    function segarTingkat() {
        panels.forEach((p) => {
            p.hidden = p.dataset.tingkatPanel !== tingkatAktif;
        });

        tabTingkat.forEach((t) => {
            const aktif = t.dataset.tingkat === tingkatAktif;
            t.classList.toggle('is-active', aktif);
            t.setAttribute('aria-selected', String(aktif));
        });

        const sudahKunci = terkunci.has(tingkatAktif);
        labelKunci.textContent = sudahKunci
            ? `Nilai Kelas ${tingkatAktif} terkunci`
            : `Kunci nilai Kelas ${tingkatAktif}`;
        btnKunci.disabled = sudahKunci;
        btnKunci.classList.toggle('is-locked', sudahKunci);
    }

    function kunciTingkat() {
        if (terkunci.has(tingkatAktif)) {
            return;
        }

        const belumLengkap = Array.from(panelAktif().querySelectorAll('.pk-fill'))
            .filter((el) => Number(el.dataset.percent) < 100).length;

        if (belumLengkap > 0) {
            const lanjut = window.confirm(
                `${belumLengkap} kelas di Kelas ${tingkatAktif} belum 100% lengkap. Tetap kunci nilai?`
            );

            if (!lanjut) {
                tampilkan('Penguncian dibatalkan.', 'info');
                return;
            }
        }

        terkunci.add(tingkatAktif);
        segarTingkat();
        tampilkan(
            `Simulasi: Kelas ${tingkatAktif} dikunci di halaman ini. Belum tersimpan di server, sehingga guru belum benar-benar terkunci.`,
            'ok'
        );
    }

    tabSemester.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabSemester.forEach((t) => {
                const aktif = t === tab;
                t.classList.toggle('is-active', aktif);
                t.setAttribute('aria-selected', String(aktif));
            });

            if (tab.dataset.semester !== '0') {
                tampilkan('Data semester ini belum tersedia. Halaman masih menampilkan data dummy.', 'info');
            } else {
                tampilkan('');
            }
        });
    });

    tabTingkat.forEach((tab) => {
        tab.addEventListener('click', () => {
            tingkatAktif = tab.dataset.tingkat;
            segarTingkat();
            tampilkan('');
        });
    });

    btnKunci.addEventListener('click', kunciTingkat);

    btnTambah.addEventListener('click', () => {
        tampilkan('Fitur tambah periode belum terhubung ke backend (simulasi).', 'info');
    });

    btnJadwal.addEventListener('click', () => {
        tampilkan('Fitur ubah jadwal belum terhubung ke backend (simulasi).', 'info');
    });

    // Sinkronisasi awal: isi lebar bar sesuai persentase.
    root.querySelectorAll('.pk-fill').forEach((el) => {
        el.style.width = `${el.dataset.percent}%`;
    });

    segarTingkat();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPeriodePenguncian);
} else {
    initPeriodePenguncian();
}