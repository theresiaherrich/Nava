
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Status Verifikasi - SIAKAD SMAN 1</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/portal-orang-tua.css'
    ])
</head>

<body class="pt-auth">

    <div class="pt-auth-card">

        <div class="pt-auth-logo">
            <img src="{{ asset('images/logo-siakad.png') }}"
                 alt="Logo SIAKAD">
        </div>

        <p class="eyebrow">SIAKAD SMA NEGERI 1</p>

        <h1>Status Verifikasi</h1>

        <p class="muted">
            Halo, {{ session('ortu_username') }}.
            Berikut status pengajuan hubungan orang tua dan siswa.
        </p>

        @if(session('success'))
            <div class="pt-auth-success">
                {{ session('success') }}
            </div>
        @endif

        @foreach($relations as $relation)

            <div class="pt-auth-relation">

                <strong>{{ $relation->nama_siswa }}</strong>

                <p>NIS: {{ $relation->nis }}</p>

                <p>
                    {{ $relation->nama }}
                    ({{ $relation->hubungan }})
                </p>

                @if($relation->status_verifikasi === 'verified')
                    <span class="pt-auth-status verified">
                        Terverifikasi
                    </span>

                @elseif($relation->status_verifikasi === 'rejected')
                    <span class="pt-auth-status rejected">
                        Ditolak
                    </span>

                @else
                    <span class="pt-auth-status pending">
                        Menunggu Verifikasi Admin
                    </span>
                @endif

            </div>

        @endforeach

        @if($relations->contains('status_verifikasi', 'verified'))

            <a href="{{ route('portal.dashboard') }}"
               class="pt-auth-button">
                Masuk ke Portal Orang Tua
            </a>

        @endif

        <form method="POST" action="{{ route('ortu.logout') }}">
            @csrf

            <button type="submit" class="pt-auth-secondary">
                Keluar
            </button>
        </form>

    </div>

</body>
</html>
