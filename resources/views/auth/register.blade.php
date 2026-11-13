<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SIAKAD – Register</title>
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
      <h2>Hai, Silakan Register<br>Terlebih Dahulu!</h2>
      <p class="sub">Pendaftaran khusus orang tua / wali siswa.</p>
      <form id="form-register" novalidate>
        @csrf
        <div class="fg"><label for="r-user">Username</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><input id="r-user" name="username" autocomplete="username" placeholder="Username"></div>
          <div class="msg"></div></div>
        <div class="fg"><label for="r-pass">Password</label>
          <div class="field has-toggle"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg><input id="r-pass" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter"><button type="button" class="toggle" data-toggle="r-pass" aria-label="Lihat password"><svg class="on" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="off" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.9 17.9A10.9 10.9 0 0 1 12 19C5.6 19 2 12 2 12a18.5 18.5 0 0 1 5.1-5.9M9.9 5.2A10 10 0 0 1 12 5c6.4 0 10 7 10 7a18.6 18.6 0 0 1-2.2 3.2M1 1l22 22"/></svg></button></div>
          <div class="msg"></div></div>
        <div class="fg"><label for="r-nama">Nama lengkap</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><input id="r-nama" name="nama" autocomplete="name" placeholder="Nama lengkap"></div>
          <div class="msg"></div></div>
        <fieldset class="fg"><legend>Jenis kelamin</legend>
          <div class="radios"><label><input type="radio" name="gender" value="L"> Laki-laki</label><label><input type="radio" name="gender" value="P"> Perempuan</label></div>
          <div class="msg"></div></fieldset>
        <div class="fg"><label for="r-hp">Nomor telepon</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg><input id="r-hp" name="telepon" type="tel" inputmode="tel" autocomplete="tel" maxlength="25" placeholder="08xxxxxxxxxx"></div>
          <div class="msg"></div></div>
        <div class="fg"><label for="r-alamat">Alamat</label>
          <div class="field is-area"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/></svg><textarea id="r-alamat" name="alamat" autocomplete="street-address" placeholder="Alamat lengkap"></textarea></div>
          <div class="msg"></div></div>
        <div class="fg"><label for="r-hub">Hubungan dengan siswa</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2 21a7 7 0 0 1 14 0M16 4.5a3.5 3.5 0 0 1 0 7M22 21a7 7 0 0 0-4-6.3"/></svg><select id="r-hub" name="hubungan"><option value="">Pilih hubungan</option><option>Ayah</option><option>Ibu</option><option>Wali</option></select></div>
          <div class="msg"></div></div>

        <div class="group">
          <h3>Verifikasi anak</h3>
          <p>Dipakai untuk menghubungkan akunmu ke data siswa yang benar. Anak lain bisa ditambah nanti dari dashboard.</p>
          <div class="fg"><label for="r-suser">Username siswa</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg><input id="r-suser" name="username_siswa" placeholder="Username siswa"></div>
          <div class="msg"></div></div>
          <div class="fg"><label for="r-nisn">NISN siswa</label>
          <div class="field"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.5-1.5 1.7-2 3-2s2.5.5 3 2M14 10h4M14 14h4"/></svg><input id="r-nisn" name="nisn" inputmode="numeric" maxlength="10" placeholder="10 digit NISN"></div>
          <div class="msg"></div></div>
        </div>
        <div class="status" role="status" id="r-status"></div>
        <button class="btn" type="submit">Register</button>
      </form>
      <p class="alt">Sudah punya akun? <a href="{{ route('login') }}">Login Sekarang</a></p>
    </div>
  </section>
</main>
<script src="{{ asset('auth/auth.js') }}"></script>
</body>
</html>