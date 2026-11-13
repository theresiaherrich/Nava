import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/sidebar.css',
                'resources/css/presensi/siswa.css',
                'resources/js/presensi/siswa.js',
                'resources/css/presensi/guru.css',
                'resources/js/presensi/guru.js',
                'resources/css/presensi/admin.css',
                'resources/js/presensi/admin.js',
                'resources/css/presensi/budaya-positif.css',
                'resources/js/presensi/budaya-positif.js',
                'resources/css/rekap-nilai.css',
                'resources/js/rekap-nilai.js',
                'resources/css/verifikasi-nilai.css',
                'resources/js/verifikasi-nilai.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
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