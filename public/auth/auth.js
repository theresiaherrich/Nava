// Dipakai di halaman login, register, dan lupa password. Tiap bagian hanya jalan kalau formnya ada di halaman.
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

// Tombol ikon mata untuk melihat / menyembunyikan password
document.querySelectorAll('[data-toggle]').forEach((b) => b.addEventListener('click', () => {
  const input = document.getElementById(b.dataset.toggle);
  const tampil = input.type === 'password';
  input.type = tampil ? 'text' : 'password';
  b.classList.toggle('is-shown', tampil);
  b.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Lihat password');
}));

// ---------- helper ----------
function setError(el, text) {
  const grup = el.closest('.fg');
  const msg = grup && grup.querySelector('.msg');
  el.classList.toggle('invalid', !!text);
  if (msg) msg.textContent = text || '';
  return !text;
}
const wajib = (el, label) => setError(el, el.value.trim() ? '' : `${label} wajib diisi`);
const telpOk = (v) => /^(\+62|62|0)8\d{7,12}$/.test(v.replace(/[\s-]/g, ''));
function status(el, text, bad) { el.textContent = text; el.className = 'status show' + (bad ? ' bad' : ''); }

// ---------- Login ----------
const formLogin = document.getElementById('form-login');
if (formLogin) formLogin.addEventListener('submit', async (e) => {
  e.preventDefault();
  const f = e.target;
  const ok = [wajib(f.username, 'Username'), wajib(f.password, 'Password')].every(Boolean);
  if (!ok) return;

  const btn = f.querySelector('.btn'); btn.disabled = true;
  try {
    // TODO (backend): sesuaikan URL dengan route login dari tim backend
    // const res = await fetch('/login', { method: 'POST',
    //   headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
    //   body: JSON.stringify({ username: f.username.value.trim(), password: f.password.value }) });
    // const data = await res.json();
    // if (!res.ok) throw new Error(data.message || 'Username atau password salah');
    // Kolom user.must_change_password bernilai 1 untuk akun baru: arahkan ke halaman ganti password
    // window.location.href = data.must_change_password ? '/ganti-password' : (data.redirect || '/dashboard');
    status(document.getElementById('l-status'), 'Form valid. Menunggu route login dari backend.');
  } catch (err) { status(document.getElementById('l-status'), err.message, true); }
  finally { btn.disabled = false; }
});

// ---------- Register orang tua ----------
const formReg = document.getElementById('form-register');
if (formReg) formReg.addEventListener('submit', async (e) => {
  e.preventDefault();
  const f = e.target;
  const gender = f.querySelector('input[name="gender"]:checked');
  const msgGender = f.querySelector('fieldset .msg');
  msgGender.textContent = gender ? '' : 'Pilih jenis kelamin';

  const checks = [
    wajib(f.username, 'Username'),
    setError(f.password, f.password.value.length >= 8 ? '' : 'Password minimal 8 karakter'),
    wajib(f.nama, 'Nama lengkap'),
    !!gender,
    setError(f.telepon, telpOk(f.telepon.value) ? '' : 'Nomor telepon tidak valid'),
    wajib(f.alamat, 'Alamat'),
    setError(f.hubungan, f.hubungan.value ? '' : 'Pilih hubungan'),
    wajib(f.username_siswa, 'Username siswa'),
    setError(f.nisn, /^\d{10}$/.test(f.nisn.value) ? '' : 'NISN harus 10 digit angka'),
  ];
  if (!checks.every(Boolean)) return;

  // Nama field mengikuti tabel orang_tua / user: jenis_kelamin = 'L' atau 'P', hubungan = 'Ayah' | 'Ibu' | 'Wali'
  const payload = {
    username: f.username.value.trim(), password: f.password.value, nama: f.nama.value.trim(),
    jenis_kelamin: gender.value, telepon: f.telepon.value.trim(), alamat: f.alamat.value.trim(),
    hubungan: f.hubungan.value,
    username_siswa: f.username_siswa.value.trim(), nisn: f.nisn.value,
  };

  const btn = f.querySelector('.btn'); btn.disabled = true;
  try {
    // TODO (backend): sesuaikan URL dan nama field dengan tim backend.
    // Backend mencocokkan username siswa + NISN lalu menyambungkan ortu ke siswa.
    // Kemungkinan galat dari backend yang perlu ditampilkan lewat data.message:
    //  - username sudah dipakai
    //  - username siswa dan NISN tidak cocok
    //  - siswa itu sudah punya akun dengan hubungan yang sama (satu Ayah, satu Ibu, satu Wali per siswa)
    // const res = await fetch('/register', { method: 'POST',
    //   headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
    //   body: JSON.stringify(payload) });
    // const data = await res.json();
    // if (!res.ok) throw new Error(data.message || 'Data siswa tidak cocok');
    // orang_tua.status_verifikasi awalnya 'pending': tampilkan pesan, jangan langsung masuk
    // status(document.getElementById('r-status'), 'Pendaftaran berhasil. Akunmu menunggu verifikasi petugas sekolah.');
    console.log('payload register ortu:', { ...payload, password: '***' });
    status(document.getElementById('r-status'), 'Form valid. Menunggu route register dari backend.');
  } catch (err) { status(document.getElementById('r-status'), err.message, true); }
  finally { btn.disabled = false; }
});

// ---------- Lupa password (permintaan reset ke petugas sekolah) ----------
const formLupa = document.getElementById('form-lupa');
if (formLupa) formLupa.addEventListener('submit', async (e) => {
  e.preventDefault();
  const f = e.target;
  const ok = [
    wajib(f.username, 'Username'),
    setError(f.telepon, telpOk(f.telepon.value) ? '' : 'Nomor telepon tidak valid'),
    setError(f.nisn, /^\d{10}$/.test(f.nisn.value) ? '' : 'NISN harus 10 digit angka'),
  ].every(Boolean);
  if (!ok) return;

  const btn = f.querySelector('.btn'); btn.disabled = true;
  try {
    // TODO (backend): tabel user tidak punya email, jadi reset dilakukan petugas setelah mencocokkan
    // username, nomor telepon di tabel orang_tua, dan NISN anak. Tanyakan ke PIC alurnya.
    // const res = await fetch('/lupa-password', { method: 'POST',
    //   headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
    //   body: JSON.stringify({ username: f.username.value.trim(), telepon: f.telepon.value.trim(), nisn: f.nisn.value }) });
    // const data = await res.json();
    // if (!res.ok) throw new Error(data.message || 'Data tidak cocok');
    // status(document.getElementById('f-status'), 'Permintaan terkirim. Petugas akan menghubungi nomor teleponmu.');
    status(document.getElementById('f-status'), 'Form valid. Menunggu route lupa password dari backend.');
  } catch (err) { status(document.getElementById('f-status'), err.message, true); }
  finally { btn.disabled = false; }
});