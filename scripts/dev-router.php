<?php

/*
|--------------------------------------------------------------------------
| Router server dev lokal (php -S) — hanya untuk development
|--------------------------------------------------------------------------
|
| Server bawaan PHP di Windows kadang memotong respons > 64 KB lalu menggantung
| ±20 detik. Respons PHP dikompres gzip (jadi jauh di bawah 64 KB); berkas statis
| (gambar, font, build Vite) tetap dilayani apa adanya oleh php -S.
|
| Jalankan dari folder public:  npm run serve
|   (= cd public && php -S 127.0.0.1:8100 ../scripts/dev-router.php)
|
*/

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');

if ($uri !== '/' && is_file(__DIR__.'/../public'.$uri)) {
    return false;
}

if (str_contains($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip')) {
    ob_start('ob_gzhandler');
}

require __DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
