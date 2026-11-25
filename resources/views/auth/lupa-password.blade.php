<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SIAKAD – Lupa Password</title>
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
    <h1>Kelola sistem sekolahmu dengan satu kali klik</h1>
    <p>Sistem terpadu untuk siswa, guru, bahkan orang tua untuk memantau perkembangan sekolah dengan transparan dan mudah.</p>
  </section>

  <section class="panel">
    <div class="panel-inner">
      <div class="avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg></div>
      <h2>Lupa Password?</h2>
      <p class="sub">Masukkan username dan NISN atau NIP yang terdaftar untuk memverifikasi akun dan membuat password baru.</p>
      <form id="form-lupa" novalidate>
        @csrf
        <div class="fg"><label for="f-user">Username</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><input id="f-user" name="username" autocomplete="username" placeholder="Username akunmu"></div>
          <div class="msg"></div></div>
        <div class="fg">
          <label for="f-nisn">NISN / NIP</label>
          <div class="field">
              <svg class="ico" viewBox="0 0 24 24" aria-hidden="true">
                  <rect x="3" y="5" width="18" height="14" rx="2"/>
                  <circle cx="9" cy="11" r="2"/>
                  <path d="M6 16c.5-1.5 1.7-2 3-2s2.5.5 3 2M14 10h4M14 14h4"/>
              </svg>
              <input
                  id="f-nisn"
                  name="nisn"
                  maxlength="30"
                  placeholder="Masukkan NISN atau NIP"
              >
          </div>
          <div class="msg"></div>
      </div>
        <div class="status" role="status" id="f-status"></div>
        <button class="btn" type="submit">Ajukan Reset Password</button>
      </form>
      <p class="alt">Ingat password? <a href="{{ route('login') }}">Kembali ke Login</a></p>
    </div>
  </section>
</main>
<script src="{{ asset('auth/auth.js') }}"></script>
</body>
</html>