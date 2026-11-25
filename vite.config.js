import { globSync } from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fontsource } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Setiap file CSS/JS halaman di resources/{css,js} otomatis menjadi entry Vite,
// jadi daftar input tidak bisa lagi menyebut file yang tidak ada (atau lupa didaftarkan).
// Subfolder resources/{css,js}/presensi/ milik modul A1 (Presensi).
const input = [
    ...globSync('resources/css/*.css'),
    ...globSync('resources/js/*.js'),
    ...globSync('resources/css/presensi/*.css'),
    ...globSync('resources/js/presensi/*.js'),
].map((file) => file.replaceAll('\\', '/')).sort();

export default defineConfig({
    plugins: [
        laravel({
            input,
            refresh: true,
            fonts: [
                // Satu font untuk seluruh halaman, sesuai desain. Diambil dari paket npm
                // @fontsource/plus-jakarta-sans (tanpa CDN).
                fontsource('Plus Jakarta Sans', {
                    weights: [400, 500, 600, 700, 800],
                    // Tanpa paket opsional "fontaine"; fallback cukup dari daftar --font-sans.
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
