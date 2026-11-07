@props(['title', 'description' => null])
<header class="mb-6"><p class="text-xs font-semibold uppercase tracking-wider text-sky-700 mb-2">SIAKAD / Modul A3</p><h1 class="text-2xl md:text-3xl font-bold text-[#0f1b33]">{{ $title }}</h1>@if($description)<p class="mt-2 text-sm text-slate-500">{{ $description }}</p>@endif</header>
