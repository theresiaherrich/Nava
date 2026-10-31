<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIAKAD SMAN 1')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/sidebar.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body style="display:flex; margin:0; min-height:100vh;">

    <x-sidebar />

    <main style="flex:1; min-width:0;">
        @yield('content')
    </main>

</body>
</html>