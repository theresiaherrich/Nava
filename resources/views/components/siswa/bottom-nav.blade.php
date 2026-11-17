{{-- Navigasi bawah portal siswa di layar kecil (desain 7.01). Sidebar tetap dipakai di layar lebar. --}}
@php
    $menu = [
        ['Beranda', 'home', 'beranda-akademik.index'],
        ['Rapor', 'file-text', 'rapor-saya.index'],
        ['Nilai', 'table', 'nilai-per-mapel.*'],
        ['Riwayat', 'clock', 'riwayat-rapor.index'],
    ];
@endphp
<nav aria-label="Navigasi siswa" class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] md:hidden">
    <ul class="grid grid-cols-4">
        @foreach ($menu as [$label, $ikon, $route])
            @php $aktif = request()->routeIs($route); @endphp
            <li>
                <a href="{{ route(str_replace('.*', '.index', $route)) }}" @if ($aktif) aria-current="page" @endif
                   @class(['flex flex-col items-center gap-1 py-2.5 text-xs', 'font-semibold text-link' => $aktif, 'text-muted hover:text-ink' => ! $aktif])>
                    <x-ui.icon :name="$ikon" :size="20" />{{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
