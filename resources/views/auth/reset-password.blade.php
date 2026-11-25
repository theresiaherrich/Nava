
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIAKAD - Reset Password</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('auth/auth.css') }}">
</head>
<body class="auth-body">
<main class="shell">
    <section class="hero">
        <div class="logo">
            <img src="{{ asset('images/logo-siakad.png') }}" alt="Logo SIAKAD">
            <div class="logo-text">
                <span>SIAKAD</span>
                <small>SMA Negeri 1 Malang</small>
            </div>
        </div>
        <div class="hero-label">SISTEM INFORMASI AKADEMIK</div>
        <h1>Amankan akun sekolahmu</h1>
        <p>Buat password baru untuk kembali mengakses akun SIAKAD kamu.</p>
    </section>

    <section class="panel">
        <div class="panel-inner">
            <div class="avatar" aria-hidden="true">
                <svg viewBox="0 0 16 16">
                    <path d="M11 1a4 4 0 1 1-1.646 7.646L8 10v2H6v2H4l-1 1H1v-2l6.354-6.354A4 4 0 0 1 11 1m1 2a1 1 0 1 0 0 2a1 1 0 0 0 0-2"/>
                </svg>
            </div>

            <h2>Reset Password</h2>
            <p class="sub">Masukkan password baru untuk akunmu.</p>

            <form id="form-reset" novalidate>
                @csrf

                <div class="fg">
                    <label for="password">Password Baru</label>
                    <div class="field has-toggle">
                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="4" y="11" width="16" height="10" rx="2"/>
                            <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                        </svg>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            minlength="8"
                            required
                        >
                        <button type="button" class="toggle"
                            data-toggle="password" aria-label="Lihat password">
                            <svg class="on" viewBox="0 0 24 24">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="off" viewBox="0 0 24 24">
                                <path d="M17.9 17.9A10.9 10.9 0 0 1 12 19C5.6 19 2 12 2 12a18.5 18.5 0 0 1 5.1-5.9M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a18.6 18.6 0 0 1-2.2 3.2M1 1l22 22"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="fg">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <div class="field has-toggle">
                        <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="4" y="11" width="16" height="10" rx="2"/>
                            <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                        </svg>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            required
                        >
                        <button type="button" class="toggle"
                            data-toggle="password_confirmation"
                            aria-label="Lihat password">
                            <svg class="on" viewBox="0 0 24 24">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="off" viewBox="0 0 24 24">
                                <path d="M17.9 17.9A10.9 10.9 0 0 1 12 19C5.6 19 2 12 2 12a18.5 18.5 0 0 1 5.1-5.9M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a18.6 18.6 0 0 1-2.2 3.2M1 1l22 22"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="status" id="reset-status" role="status"></div>

                <button class="btn" id="reset-submit" type="submit">
                    Simpan Password Baru
                </button>
            </form>

            <p class="alt">
                Pastikan password baru tidak mudah ditebak.
            </p>
        </div>
    </section>
</main>

<script src="{{ asset('auth/auth.js') }}"></script>
<script>
const form = document.getElementById('form-reset');
const tombol = document.getElementById('reset-submit');
const statusBox = document.getElementById('reset-status');

const token = "{{ request()->route('token') }}";

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;

    statusBox.className = 'status';
    statusBox.textContent = '';

    if (password.length < 8) {
        tampilkanPesan('Password baru minimal 8 karakter.', false);
        return;
    }

    if (password !== confirmation) {
        tampilkanPesan('Konfirmasi password baru tidak cocok.', false);
        return;
    }

    tombol.disabled = true;
    tombol.textContent = 'Menyimpan...';

    try {
        const response = await fetch('/api/reset-password', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                token: token,
                password: password,
                password_confirmation: confirmation
            })
        });

        const hasil = await response.json();

        if (!response.ok) {
            tampilkanPesan(
                hasil.message || 'Reset password gagal. Silakan coba kembali.',
                false
            );
            return;
        }

        tampilkanPesan(
            'Password berhasil diperbarui. Silakan login kembali.',
            true
        );

        setTimeout(() => {
            window.location.href = '/login';
        }, 1500);

    } catch (error) {
        console.error('[Reset Password]', error);
        tampilkanPesan('Tidak dapat terhubung ke server.', false);
    } finally {
        tombol.disabled = false;
        tombol.textContent = 'Simpan Password Baru';
    }
});

function tampilkanPesan(pesan, berhasil) {
    statusBox.textContent = pesan;
    statusBox.className = berhasil
        ? 'status show'
        : 'status bad show';
}
</script>
</body>
</html>
