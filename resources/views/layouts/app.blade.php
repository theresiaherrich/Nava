<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIAKAD NAVA' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">

    <div class="min-h-screen">
        {{-- Nanti tempat @include('layouts.partials.header') & sidebar --}}
        
        <main class="py-6">
            @yield('content')
        </main>
    </div>

</body>
</html>