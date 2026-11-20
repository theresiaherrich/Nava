
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Orang Tua - SIAKAD SMAN 1</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/portal-orang-tua.css'
    ])
</head>

<body class="pt-auth">

    <form class="pt-auth-card"
          method="POST"
          action="{{ route('ortu.login.submit') }}">

        @csrf

        <div class="pt-auth-logo">
            <img src="{{ asset('images/logo-siakad.png') }}"
                 alt="Logo SIAKAD">
        </div>

        <p class="eyebrow">SIAKAD SMA NEGERI 1</p>

        <h1>Portal Orang Tua</h1>

        <p class="muted">
            Masuk untuk memantau perkembangan akademik anak.
        </p>

        @if(session('success'))
            <div class="pt-auth-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="pt-auth-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="pt-ortu-login">
        <label>Username
            <input type="text"
                   name="username"
                   value="{{ old('username') }}"
                   autocomplete="username"
                   required autofocus>
        </label>

        <label>Kata sandi
            <input type="password"
                   name="password"
                   autocomplete="current-password"
                   required>
        </label>

        <button type="submit" class="pt-auth-button">
            Masuk ke Portal
        </button>

        <p class="pt-auth-footer">
            Belum memiliki akun?
            <a href="{{ route('ortu.register') }}">
                Daftar sekarang
            </a>
        </p>
<!-- 
        <a href="{{ route('admin.login') }}" class="pt-auth-admin-link">
            Login Administrator
        </a> -->
        </div>

    </form>

</body>
</html>