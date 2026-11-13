<h1>Dashboard NAVA</h1>
<p>{{ auth()->user()->name }}</p>
<p>{{ auth()->user()->getRoleNames()->implode(', ') }}</p>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Keluar</button>
</form>