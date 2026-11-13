<h1>Set Password Baru</h1>
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input name="email" type="email" value="{{ old('email', $email) }}" required>
    <input name="password" type="password" required>
    <input name="password_confirmation" type="password" required>
    <button type="submit">Reset Password</button>
</form>
@if($errors->any()) <p>{{ $errors->first() }}</p> @endif