@props(['name', 'size' => 16, 'stroke' => 1.8])

@php
    // Ikon garis 24x24 (gaya Lucide). Tambahkan path baru di sini bila perlu.
    $paths = [
        'alert' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
        'archive' => '<rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M10 12h4"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'check-circle' => '<circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'clipboard' => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2M9 12h6M9 16h4"/>',
        'copy' => '<rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'lock' => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'pencil' => '<path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/><path d="m15 5 4 4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
        'trash' => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/>',
        'undo' => '<path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'shrink-0']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
     fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
