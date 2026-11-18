import { strict as assert } from 'node:assert';
import { readFile } from 'node:fs/promises';
import { chromium } from 'playwright-core';

const base = process.env.NAVA_TEST_URL ?? 'http://127.0.0.1:8765';
assert(['127.0.0.1', 'localhost'].includes(new URL(base).hostname), 'Jalankan tes browser pada server demo lokal.');
const password = process.env.SEED_DEFAULT_PASSWORD ?? (process.env.NAVA_TEST_PASSWORD_FILE
    ? await readFile(process.env.NAVA_TEST_PASSWORD_FILE, 'utf8') : null);
assert(password, 'Atur SEED_DEFAULT_PASSWORD untuk database demo pengujian.');
const browser = await chromium.launch({ executablePath: process.env.NAVA_TEST_CHROME, headless: true });
const errors = [];

async function visit(page, path) {
    const response = await page.goto(base + path);
    assert(response.ok(), `${response.status()} saat membuka ${path}`);
    await page.waitForFunction(() => Boolean(window.Alpine));
}

async function login(username, path) {
    const page = await browser.newPage();
    page.on('pageerror', error => errors.push(`${page.url()} ${error.message}`));
    await visit(page, '/login');
    await page.locator('#username').fill(username);
    await page.locator('#password').fill(password);
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await page.waitForURL(base + path);
    return page;
}

async function call(page, path, { method = 'GET', body, status = 200, token } = {}) {
    const csrf = token ?? await page.locator('meta[name="csrf-token"]').getAttribute('content');
    const response = await page.request.fetch(base + '/api' + path, {
        method, data: body, headers: { 'X-CSRF-TOKEN': csrf }, timeout: 60000,
    });
    assert.equal(response.status(), status, `${method} ${path}: ${response.status() === status ? '' : await response.text()}`);
    return response.headers()['content-type'].includes('application/json')
        ? (await response.json()).data : response;
}

async function text(page, value) { await page.getByText(value, { exact: true }).filter({ visible: true }).first().waitFor(); }
async function switchRole(page, role, path) {
    await page.locator('#peran').selectOption(role);
    await page.getByRole('button', { name: 'Ganti peran', exact: true }).click();
    await page.waitForURL(base + path);
}

const send = (page, rombel, period, cls, force = false) => call(page, `/guru/rombel/${rombel}/paket/kirim`, {
    method: 'POST', body: { id_periode: period, id_kelas: cls, konfirmasi_belum_lengkap: force },
});
const verify = (page, paket, action, body = {}) => call(page, `/wali/paket/${paket}/${action}`, { method: 'POST', body });
const lock = (page, period, cls, status = 200) => call(page, `/kurikulum/periode/${period}/kelas/${cls}/kunci`, { method: 'POST', body: {}, status });

try {
    const guest = await browser.newPage();
    await visit(guest, '/guru/input-nilai');
    assert(guest.url().endsWith('/login'));
    await call(guest, '/auth/me', { status: 401 });
    await call(guest, '/auth/login', { method: 'POST', token: 'tidak-valid', body: { username: 'budi', password }, status: 419 });
    const kurikulum = await login('kurikulum', '/kurikulum/dashboard');
    const guru = await login('budi', '/guru/dashboard');
    const wali = await login('sari', '/wali-kelas/dashboard');
    const siswa = await login('andi', '/siswa/beranda');
    const ortu = await login('hendra', '/orang-tua/beranda');
    const citra = await login('citra', '/ganti-sandi');
    await call(citra, '/portal/nilai', { status: 403 });
    const newPassword = password + 'B2!ubah';
    await citra.locator('[name="password_lama"]').fill(password);
    await citra.locator('[name="password_baru"]').fill(newPassword);
    await citra.locator('[name="password_baru_confirmation"]').fill(newPassword);
    await citra.getByRole('button', { name: 'Simpan kata sandi' }).click();
    await citra.waitForURL(base + '/siswa/beranda');
    await call(siswa, '/guru/rombel', { status: 403 });
    console.log('PASS login, pergantian sandi awal, 401/403 dan CSRF 419');

    const periode = await call(kurikulum, '/kurikulum/periode');
    const sts = periode.find(p => p.jenis === 'STS').id_periode;
    const sas = periode.find(p => p.jenis === 'SAS').id_periode;
    const kelas = await call(kurikulum, '/master/kelas');
    const xb = kelas.find(k => k.nama === 'X-B').id_kelas;
    const xa = kelas.find(k => k.nama === 'X-A').id_kelas;
    const rombel = await call(guru, '/guru/rombel');
    const mtk = rombel.find(r => r.mapel.kode === 'MTK').id_rombel;
    const pai = rombel.find(r => r.mapel.kode === 'PAI').id_rombel;
    const tpUrl = `/guru/tp?rombel=${mtk}`;
    await visit(guru, tpUrl);
    await guru.getByRole('button', { name: 'Tambah TP', exact: true }).filter({ visible: true }).first().click();
    await guru.locator('#tp-deskripsi').fill('Merumuskan model matematika dari persoalan sehari-hari');
    await guru.getByRole('button', { name: 'Simpan TP', exact: true }).click();
    await text(guru, 'Merumuskan model matematika dari persoalan sehari-hari');
    await visit(guru, tpUrl);
    await text(guru, 'Merumuskan model matematika dari persoalan sehari-hari');
    const tp = (await call(guru, `/guru/rombel/${mtk}/tp`)).tp;
    assert.equal(tp.length, 5);
    const paiTp = (await call(guru, `/guru/rombel/${pai}/tp`)).tp[0];
    // Lengkapi batas TP dengan rombel Matematika, lalu hapus TP tambahan yang belum dinilai.
    const tambahan = [];
    for (let i = 6; i <= 10; i++) {
        tambahan.push(await call(guru, `/guru/rombel/${mtk}/tp`, { method: 'POST', body: { deskripsi: 'TP pengujian kuota ' + i }, status: 201 }));
    }
    await call(guru, `/guru/rombel/${mtk}/tp`, { method: 'POST', body: { deskripsi: 'TP kesebelas ditolak' }, status: 422 });
    for (const t of tambahan.reverse()) await call(guru, '/guru/tp/' + t.id_tp, { method: 'DELETE' });
    console.log('PASS tambah TP pada Blade, refresh, dan batas 10');

    const gradeUrl = `/guru/input-nilai?rombel=${mtk}&periode=sts`;
    await visit(guru, gradeUrl);
    const andiCell = guru.getByRole('textbox', { name: 'Nilai TP1 Andi Pratama', exact: true });
    await andiCell.fill('85');
    await guru.waitForResponse(response => response.url().endsWith('/nilai') && response.request().method() === 'PUT' && response.ok());
    await visit(guru, gradeUrl);
    assert.equal(Number(await andiCell.inputValue()), 85);
    await andiCell.fill('85.50');
    await guru.waitForResponse(response => response.url().endsWith('/nilai') && response.request().method() === 'PUT' && response.ok());
    await guru.getByRole('button', { name: 'Kirim X-B', exact: true }).click();
    await guru.getByRole('button', { name: 'Kirim Nilai', exact: true }).filter({ visible: true }).click();
    await guru.waitForLoadState('domcontentloaded');
    await text(guru, 'Terkirim');
    await send(guru, pai, sts, xb);
    const incomplete = await send(guru, mtk, sts, xa, true);
    const waliXA = await login('wali_xa', '/wali-kelas/dashboard');
    await verify(waliXA, incomplete.paket.id_paket_nilai, 'setujui');
    await send(guru, pai, sts, xa);
    const xaPaket = await call(waliXA, '/wali/paket?id_periode=' + sts);
    await verify(waliXA, xaPaket.find(p => p.mapel !== 'Matematika' && p.status === 'terkirim').id_paket_nilai, 'setujui');
    assert(paiTp);

    await switchRole(wali, 'GURU', '/guru/dashboard');
    const sariRombel = await call(wali, '/guru/rombel');
    for (const r of sariRombel) await send(wali, r.id_rombel, sts, xb);
    const bin = sariRombel.find(r => r.mapel.kode === 'BIN').id_rombel;
    await send(wali, bin, sts, xa);
    const xaBin = (await call(waliXA, '/wali/paket?id_periode=' + sts)).find(p => p.mapel === 'Bahasa Indonesia');
    await verify(waliXA, xaBin.id_paket_nilai, 'setujui');
    await lock(kurikulum, sts, xa, 422);
    await switchRole(wali, 'WALI_KELAS', '/wali-kelas/dashboard');
    const paketXB = () => call(wali, '/wali/paket?id_periode=' + sts);
    const paketMtk = (await paketXB()).find(p => p.mapel === 'Matematika');
    await visit(wali, '/wali-kelas/verifikasi-nilai');
    await wali.getByLabel('Periode').selectOption(String(sts));
    await wali.getByRole('button', { name: 'Lihat nilai' }).nth((await paketXB()).findIndex(p => p.id_paket_nilai === paketMtk.id_paket_nilai)).click();
    await wali.getByLabel('Catatan verifikasi').fill('Mohon periksa ulang nilai Matematika.');
    await wali.getByRole('button', { name: 'Minta revisi' }).click();
    await text(wali, 'Permintaan revisi dikirim ke guru.');
    await send(guru, mtk, sts, xb);
    for (const p of await paketXB()) await verify(wali, p.id_paket_nilai, 'setujui');
    await visit(kurikulum, '/kurikulum/periode-penguncian');
    await kurikulum.getByLabel('Periode', { exact: true }).selectOption(String(sts));
    await kurikulum.getByRole('button', { name: 'Kunci nilai kelas' }).filter({ visible: true }).nth(1).click();
    await text(kurikulum, 'Nilai kelas X-B dikunci (4 mapel).');
    const grid = await call(guru, `/guru/rombel/${mtk}/nilai?id_periode=${sts}`);
    const andi = grid.peserta.find(p => p.siswa.nama === 'Andi Pratama');
    assert.equal(andi.ringkasan.nilai_sts, 81.75);
    const saveBody = { id_periode: sts, items: [{ id_siswa_rombel: andi.id_siswa_rombel, id_tp: tp[0].id_tp, nilai: 90 }] };
    await call(guru, `/guru/rombel/${mtk}/nilai`, { method: 'PUT', body: saveBody, status: 422 });
    console.log('PASS autosave desimal, refresh, revisi, verifikasi, penguncian, dan penolakan edit/incomplete');

    const createReports = async id => {
        const body = { id_kelas: xb, id_periode: id };
        const list = await call(wali, `/wali/rapor?id_kelas=${xb}&id_periode=${id}`);
        assert(list.status_nilai.dikunci);
        const full = await call(wali, '/wali/rapor/cetak-kelas', { method: 'POST', body: { ...body, format: 'pdf' } });
        assert((await full.body()).subarray(0, 4).equals(Buffer.from('%PDF')));
        const versions = (await call(wali, `/wali/rapor?id_kelas=${xb}&id_periode=${id}`)).siswa;
        const rapor = versions.find(s => s.siswa.nis === '1001').versi[0].id_rapor;
        const other = versions.find(s => s.siswa.nis === '1002').versi[0].id_rapor;
        await call(siswa, `/portal/rapor/${rapor}/unduh`, { status: 403 });
        await visit(kurikulum, '/kurikulum/cetak-rapor');
        await kurikulum.getByLabel('Kelas', { exact: true }).selectOption(String(xb));
        await kurikulum.getByLabel('Periode', { exact: true }).selectOption(String(id));
        await kurikulum.getByRole('button', { name: 'Terbitkan rapor kelas' }).click();
        await text(kurikulum, '10 rapor diterbitkan.');
        const preview = await call(wali, `/wali/rapor/${rapor}/unduh?pratinjau=1`);
        assert(preview.headers()['content-disposition'].startsWith('inline'));
        const report = await call(siswa, `/portal/rapor/${rapor}/unduh`);
        assert((await report.body()).equals(await preview.body()));
        await call(siswa, `/portal/rapor/${other}/unduh`, { status: 403 });
        await call(ortu, `/portal/rapor/${other}/unduh`, { status: 403 });
        assert((await (await call(ortu, `/portal/rapor/${rapor}/unduh`)).body()).equals(await report.body()));
        return rapor;
    };
    await createReports(sts);
    const dkn = await call(wali, `/wali/dkn/ekspor?id_kelas=${xb}&id_periode=${sts}&format=xlsx`);
    assert((await dkn.body()).subarray(0, 2).equals(Buffer.from('PK')));
    console.log('PASS rapor STS, pratinjau, unduh kelas dan siswa, DKN Excel, akses rapor sendiri/anak');

    await visit(kurikulum, '/kurikulum/periode-penguncian');
    await kurikulum.getByLabel('Periode', { exact: true }).selectOption(String(sas));
    const dateWib = timestamp => new Date(timestamp + 7 * 3600000).toISOString().slice(0, 16);
    await kurikulum.getByLabel('Mulai (WIB)').fill(dateWib(Date.now() - 3600000));
    await kurikulum.getByLabel('Selesai (WIB)').fill(dateWib(Date.now() + 3600000));
    await kurikulum.getByRole('button', { name: 'Simpan jadwal' }).click();
    await text(kurikulum, 'Jadwal periode disimpan.');
    const sasGrid = await call(guru, `/guru/rombel/${mtk}/nilai?id_periode=${sas}`);
    const last = sasGrid.tp.at(-1);
    assert(last.tp_sas);
    await call(guru, `/guru/rombel/${mtk}/nilai`, { method: 'PUT', body: {
        id_periode: sas, items: sasGrid.peserta.filter(p => p.kelas.nama === 'X-B').map(p => ({ id_siswa_rombel: p.id_siswa_rombel, id_tp: last.id_tp, nilai: 100 })),
    } });
    const kesiswaan = await login('kesiswaan', '/kesiswaan/dashboard');
    await visit(kesiswaan, '/kesiswaan/ekskul');
    const ekskul = (await call(kesiswaan, '/kesiswaan/ekskul')).find(e => e.nama === 'Pramuka');
    await kesiswaan.getByLabel('Ekstrakurikuler', { exact: true }).selectOption(String(ekskul.id_ekskul));
    await kesiswaan.getByLabel('Predikat Andi Pratama').selectOption('SB');
    await kesiswaan.getByRole('button', { name: 'Simpan predikat' }).click();
    await text(kesiswaan, 'Predikat tersimpan.');
    await send(guru, mtk, sas, xb);
    await send(guru, pai, sas, xb);
    await switchRole(wali, 'GURU', '/guru/dashboard');
    for (const r of sariRombel) await send(wali, r.id_rombel, sas, xb);
    await switchRole(wali, 'WALI_KELAS', '/wali-kelas/dashboard');
    for (const p of await call(wali, '/wali/paket?id_periode=' + sas)) await verify(wali, p.id_paket_nilai, 'setujui');
    await kurikulum.getByRole('button', { name: 'Tutup periode', exact: true }).click();
    await text(kurikulum, 'Periode ditutup.');
    await lock(kurikulum, sas, xb);
    await createReports(sas);
    await visit(siswa, '/siswa/nilai');
    await text(siswa, 'Matematika');
    await visit(siswa, '/siswa/nilai/' + mtk);
    const detail = await call(siswa, '/portal/nilai/' + mtk);
    assert.equal(detail.ringkasan.nilai_sas, 100);
    assert.equal(detail.ringkasan.nilai_akhir, 87.9);
    await visit(siswa, '/siswa/rapor');
    await text(siswa, 'Rapor Semester');
    const [downloaded] = await Promise.all([
        siswa.waitForEvent('download'),
        siswa.getByText('Rapor Semester', { exact: true }).locator('../..').getByRole('link', { name: 'Unduh PDF' }).click(),
    ]);
    assert((await readFile(await downloaded.path())).subarray(0, 4).equals(Buffer.from('%PDF')));
    await siswa.getByRole('button', { name: 'Keluar', exact: true }).click();
    await siswa.waitForURL(base + '/login');
    await call(siswa, '/auth/me', { status: 401 });
    console.log('PASS SAS, rata-rata TP, ekskul, portal siswa dan logout');
    assert.deepEqual(errors, [], 'Galat JavaScript di browser');
} finally {
    if (errors.length) console.error(errors);
    await browser.close();
}
