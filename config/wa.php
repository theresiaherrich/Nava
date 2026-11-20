<?php
return [
    'url'            => env('WA_SERVICE_URL', 'http://127.0.0.1:3001'),
    'token'          => env('WA_SERVICE_TOKEN'),
    'hanya_verified' => env('WA_HANYA_VERIFIED', false),
    'jendela_hari'   => (int) env('WA_JENDELA_HARI', 7),
    'aktif' => env('WA_AKTIF', false),
];