<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - SIAKAD SMAN 1</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/portal-orang-tua.css', 'resources/css/admin.css'])
</head>
<body class="pt pt-admin-login">
    <form class="pt-admin-login__card" method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <div class="pt-admin-login__brand">
            <img
                src="{{ asset('images/logo-siakad.png') }}"
                alt="Logo SIAKAD"
                class="pt-admin-login__logo"
            >
        </div>
        <p class="eyebrow">SIAKAD SMA NEGERI 1</p>
        <h1>Login Administrator</h1>
        <p class="muted">Masuk untuk mengelola Portal Orang Tua.</p>

        @if ($errors->any())
            <div class="pt-admin-login__error" role="alert">{{ $errors->first() }}</div>
        @endif

        <label>Username
            <input name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
        </label>
        <label>Kata sandi
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button type="submit" class="pt-btn pt-btn--primary">Masuk</button>
    </form>
</body>
</html>
