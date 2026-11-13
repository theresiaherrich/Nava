<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SIAKAD – Login</title>
<link rel="icon" href="{{ asset('favicon.ico') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('auth/auth.css') }}">
</head>
<body class="auth-body">
<main class="shell">
  <section class="hero">
    <div class="logo"><img src="{{ asset('images/logo-siakad.png') }}" alt="">SIAKAD<small>SMA Negeri 1 Malang</small></div>
    <h1>Kelola sistem sekolahmu dengan satu kali klik</h1>
    <p>Sistem terpadu untuk siswa, guru, bahkan orang tua untuk memantau perkembangan sekolah dengan transparan dan mudah.</p>
  </section>

  <section class="panel">
    <div class="panel-inner">
      <div class="avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg></div>
      <h2>Hai, Silakan Login<br>Terlebih Dahulu!</h2>
      <p class="sub">Masuk untuk dapat memantau perkembangan sekolah.</p>
      <form id="form-login" novalidate>
        @csrf
        <div class="fg"><label for="l-user">Username</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><input id="l-user" name="username" autocomplete="username" placeholder="Username"></div>
          <div class="msg"></div></div>
        <div class="fg"><label for="l-pass">Password</label>
          <div class="field has-toggle"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg><input id="l-pass" name="password" type="password" autocomplete="current-password" placeholder="Password"><button type="button" class="toggle" data-toggle="l-pass" aria-label="Lihat password"><svg class="on" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="off" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.9 17.9A10.9 10.9 0 0 1 12 19C5.6 19 2 12 2 12a18.5 18.5 0 0 1 5.1-5.9M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a18.6 18.6 0 0 1-2.2 3.2M1 1l22 22"/></svg></button></div>
          <div class="msg"></div></div>
        <a class="link" href="{{ Route::has('password.request') ? route('password.request') : '#' }}">Lupa password?</a>
        <div class="status" role="status" id="l-status"></div>
        <button class="btn" type="submit">Login</button>
      </form>
      <p class="alt">Belum punya akun? <a href="{{ route('register') }}">Register Sekarang</a></p>
    </div>
  </section>
</main>
<script src="{{ asset('auth/auth.js') }}"></script>
</body>
</html>