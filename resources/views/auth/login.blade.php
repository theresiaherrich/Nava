<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login NAVA</title>
</head>
<body style="font-family:Arial;max-width:400px;margin:70px auto">
    <h1>Login SIAKAD NAVA</h1>
    
    <!-- Ganti login.process menjadi login.attempt -->
    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <label>Username <input name="username" value="{{ old('username') }}" required></label><br><br>
        <label>Password <input name="password" type="password" required></label><br><br>
        
        @error('username') 
            <p style="color:red" role="alert">{{ $message }}</p> 
        @enderror
        
        <button type="submit">Masuk</button>
    </form>
    
    <br>
    <a href="{{ route('password.request') }}">Lupa password?</a>
</body>
</html>