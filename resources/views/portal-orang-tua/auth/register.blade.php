
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Orang Tua - SIAKAD SMAN 1</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/portal-orang-tua.css'
    ])
</head>

<body class="pt-auth">

    <form class="pt-auth-card"
          method="POST"
          action="{{ route('ortu.register.submit') }}">

        @csrf

        <div class="pt-auth-logo">
            <img src="{{ asset('images/logo-siakad.png') }}"
                 alt="Logo SIAKAD">
        </div>

        <p class="eyebrow">SIAKAD SMA NEGERI 1</p>

        <h1>Registrasi Orang Tua</h1>

        <p class="muted">
            Daftarkan akun untuk mengakses Portal Orang Tua.
        </p>

        @if($errors->any())
            <div class="pt-auth-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="pt-ortu-register">
        <label>NIS Anak
            <input type="text"
                   name="nis"
                   value="{{ old('nis') }}"
                   placeholder="Masukkan NIS siswa"
                   required>
        </label>

        <label>Nama Orang Tua / Wali
            <input type="text"
                   name="nama"
                   value="{{ old('nama') }}"
                   placeholder="Nama sesuai data sekolah"
                   required>
        </label>

        <label>Hubungan dengan Siswa
            <select name="hubungan" required>
                <option value="">Pilih hubungan</option>

                <option value="Ayah"
                    @selected(old('hubungan') === 'Ayah')>
                    Ayah
                </option>

                <option value="Ibu"
                    @selected(old('hubungan') === 'Ibu')>
                    Ibu
                </option>

                <option value="Wali"
                    @selected(old('hubungan') === 'Wali')>
                    Wali
                </option>
            </select>
        </label>

        <label>Nomor WhatsApp
            <input type="tel"
                   name="no_telepon"
                   value="{{ old('no_telepon') }}"
                   placeholder="08xxxxxxxxxx"
                   required>
        </label>

        <label>Username
            <input type="text"
                   name="username"
                   value="{{ old('username') }}"
                   autocomplete="username"
                   required>
        </label>

        <label>Kata Sandi
            <input type="password"
                   name="password"
                   autocomplete="new-password"
                   minlength="8"
                   required>
        </label>

        <label>Konfirmasi Kata Sandi
            <input type="password"
                   name="password_confirmation"
                   autocomplete="new-password"
                   minlength="8"
                   required>
        </label>

        <button type="submit" class="pt-auth-button">
            Daftar Akun
        </button>

        <p class="pt-auth-footer">
            Sudah memiliki akun?
            <a href="{{ route('ortu.login') }}">
                Login sekarang
            </a>
        </p>
        </div>

    </form>

</body>
</html>
