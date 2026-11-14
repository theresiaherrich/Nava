
// ==========================================================
// AUTH.JS
// Login, Register Orang Tua, dan Lupa Password
// ==========================================================


// ---------- HELPER ----------

function setError(el, message) {
    if (!el) return false;

    const grup = el.closest('.fg');
    const msg = grup?.querySelector('.msg');

    el.classList.toggle('invalid', !!message);

    if (msg) {
        msg.textContent = message || '';
    }

    return !message;
}

const wajib = (el, label) => {
    if (!el) return false;

    return setError(
        el,
        el.value.trim() ? '' : `${label} wajib diisi`
    );
};

const telpOk = (value) => {
    const nomor = value.replace(/[\s-]/g, '');

    return /^(\+62|62|0)8\d{7,12}$/.test(nomor);
};

function status(el, message, bad = false) {
    if (!el) return;

    el.textContent = message;
    el.className = 'status show' + (bad ? ' bad' : '');
}


// ---------- REQUEST JSON ----------

async function requestJSON(url, payload) {
    let response;

    try {
        response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
    } catch (error) {
        throw new Error(
            'Tidak dapat terhubung ke server. Pastikan Laravel sedang berjalan.'
        );
    }

    // Baca respons sebagai teks agar respons kosong
    // atau HTML error tidak menyebabkan JSON parse error.
    const raw = await response.text();

    if (!raw.trim()) {
        throw new Error(
            `Server memberikan respons kosong (HTTP ${response.status}). Periksa backend Laravel.`
        );
    }

    let result;

    try {
        result = JSON.parse(raw);
    } catch (error) {
        console.error('Respons server bukan JSON:', raw);

        throw new Error(
            `Respons server tidak valid (HTTP ${response.status}). Periksa endpoint API Laravel.`
        );
    }

    if (!response.ok) {
        if (result.errors) {
            const pesanValidasi = Object.values(result.errors)
                .flat()
                .filter(Boolean)
                .join('\n');

            throw new Error(
                pesanValidasi ||
                result.message ||
                'Data yang dikirim tidak valid.'
            );
        }

        throw new Error(
            result.message ||
            result.error ||
            `Terjadi kesalahan server (HTTP ${response.status}).`
        );
    }

    return result;
}


// ---------- TOGGLE PASSWORD ----------

document.querySelectorAll('[data-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(
            button.dataset.toggle
        );

        if (!input) return;

        const tampil = input.type === 'password';

        input.type = tampil ? 'text' : 'password';

        button.classList.toggle('is-shown', tampil);

        button.setAttribute(
            'aria-label',
            tampil ? 'Sembunyikan password' : 'Lihat password'
        );
    });
});


// ---------- LOGIN ----------

const formLogin = document.getElementById('form-login');

if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();

        const f = e.target;

        const ok = [
            wajib(f.username, 'Username'),
            wajib(f.password, 'Password')
        ].every(Boolean);

        if (!ok) return;

        const btn = f.querySelector('.btn');

        if (btn) btn.disabled = true;

        try {
            const result = await requestJSON('/api/login', {
                username: f.username.value.trim(),
                password: f.password.value
            });

            const token = result.data?.token;
            const user = result.data?.user;

            if (!token || !user) {
                throw new Error(
                    'Respons login tidak lengkap. Periksa AuthController Laravel.'
                );
            }

            // Simpan token dan informasi pengguna.
            localStorage.setItem('siakad_token', token);

            localStorage.setItem(
                'siakad_user',
                JSON.stringify(user)
            );

            // Pengguna wajib mengganti password.
            if (user.must_change_password) {
                window.location.href = '/ganti-password';
                return;
            }

            // Pengalihan berdasarkan role.
            const halamanRole = {
                SISWA: '/presensi/siswa',
                GURU: '/presensi/guru',
                GURU_WALI_KELAS: '/presensi/guru',
                ADMIN: '/presensi/admin',
                BUDAYA_POSITIF: '/presensi/budaya-positif',
                KURIKULUM: '/presensi/kurikulum',
                ORANG_TUA: '/presensi/orang-tua'
            };

            window.location.href =
                halamanRole[user.role] || '/';

        } catch (err) {
            console.error('Login error:', err);

            status(
                document.getElementById('l-status'),
                err.message,
                true
            );

        } finally {
            if (btn) btn.disabled = false;
        }
    });
}


// ---------- REGISTER ORANG TUA ----------

const formReg = document.getElementById('form-register');

if (formReg) {
    formReg.addEventListener('submit', async (e) => {
        e.preventDefault();

        const f = e.target;

        const gender = f.querySelector(
            'input[name="gender"]:checked'
        );

        const msgGender = f.querySelector('fieldset .msg');

        if (msgGender) {
            msgGender.textContent = gender
                ? ''
                : 'Pilih jenis kelamin';
        }

        // Validasi seluruh input.
        const checks = [
            wajib(f.username, 'Username'),

            setError(
                f.password,
                f.password.value.length >= 8
                    ? ''
                    : 'Password minimal 8 karakter'
            ),

            wajib(f.nama, 'Nama lengkap'),

            !!gender,

            setError(
                f.telepon,
                telpOk(f.telepon.value)
                    ? ''
                    : 'Nomor telepon tidak valid'
            ),

            wajib(f.alamat, 'Alamat'),

            setError(
                f.hubungan,
                f.hubungan.value
                    ? ''
                    : 'Pilih hubungan dengan siswa'
            ),

            wajib(f.username_siswa, 'Username siswa'),

            setError(
                f.nisn,
                /^\d{10}$/.test(f.nisn.value.trim())
                    ? ''
                    : 'NISN harus terdiri dari 10 digit angka'
            )
        ];

        if (!checks.every(Boolean)) return;

        // Payload tetap mengikuti struktur form Nava.
        const payload = {
            username: f.username.value.trim(),
            password: f.password.value,
            nama: f.nama.value.trim(),
            jenis_kelamin: gender.value,
            telepon: f.telepon.value.trim(),
            alamat: f.alamat.value.trim(),
            hubungan: f.hubungan.value,
            username_siswa: f.username_siswa.value.trim(),
            nisn: f.nisn.value.trim()
        };

        const btn = f.querySelector('.btn');

        if (btn) btn.disabled = true;

        try {
            const result = await requestJSON(
                '/api/register',
                payload
            );

            status(
                document.getElementById('r-status'),
                result.message ||
                'Pendaftaran berhasil. Menunggu verifikasi petugas sekolah.'
            );

            // Form dikosongkan hanya jika server menyatakan berhasil.
            f.reset();

            // Bersihkan tampilan validasi.
            f.querySelectorAll('.invalid').forEach(el => {
                el.classList.remove('invalid');
            });

            f.querySelectorAll('.msg').forEach(el => {
                el.textContent = '';
            });

        } catch (err) {
            console.error('Register error:', err);

            status(
                document.getElementById('r-status'),
                err.message,
                true
            );

        } finally {
            if (btn) btn.disabled = false;
        }
    });
}


// ---------- LUPA PASSWORD ----------

const formLupa = document.getElementById('form-lupa');

if (formLupa) {
    formLupa.addEventListener('submit', async (e) => {
        e.preventDefault();

        const f = e.target;

        const ok = [
            wajib(f.username, 'Username'),

            setError(
                f.telepon,
                telpOk(f.telepon.value)
                    ? ''
                    : 'Nomor telepon tidak valid'
            ),

            setError(
                f.nisn,
                /^\d{10}$/.test(f.nisn.value.trim())
                    ? ''
                    : 'NISN harus terdiri dari 10 digit angka'
            )
        ].every(Boolean);

        if (!ok) return;

        const btn = f.querySelector('.btn');

        if (btn) btn.disabled = true;

        try {
            const result = await requestJSON(
                '/api/lupa-password',
                {
                    username: f.username.value.trim(),
                    telepon: f.telepon.value.trim(),
                    nisn: f.nisn.value.trim()
                }
            );

            status(
                document.getElementById('f-status'),
                result.message ||
                'Permintaan berhasil dikirim.'
            );

        } catch (err) {
            console.error('Lupa password error:', err);

            status(
                document.getElementById('f-status'),
                err.message,
                true
            );

        } finally {
            if (btn) btn.disabled = false;
        }
    });
}
