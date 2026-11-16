<?php

return [
    // Masa berlaku token reset kata sandi (menit).
    'reset_token_ttl' => (int) env('SIAKAD_RESET_TTL_MENIT', 60),

    // Hanya untuk development: tampilkan token reset di respons API.
    // Di produksi harus false; token seharusnya dikirim lewat kanal notifikasi (mis. modul A4).
    'expose_reset_token' => (bool) env('SIAKAD_EXPOSE_RESET_TOKEN', false),
];
