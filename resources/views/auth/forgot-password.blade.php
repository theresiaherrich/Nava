<h1>Lupa Password</h1>
@if(session('status')) <p>{{ session('status') }}</p> @endif
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <input name="email" type="email" required placeholder="Email sekolah">
    <button type="submit">Kirim tautan reset</button>
</form>