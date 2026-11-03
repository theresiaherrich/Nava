<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIAKAD SMAN 1 Malang - BK' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    @vite(['resources/css/app.css', 'resources/css/bk/bk.css'])
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar Universal (CSS .sb) -->
        <x-sidebar />

        <!-- Area Konten Utama -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar Universal -->
            <x-topbar />

            <!-- Main Content Slot -->
            <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>

