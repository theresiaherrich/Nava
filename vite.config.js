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
                'resources/css/rekap-nilai.css',
                'resources/js/rekap-nilai.js',
                'resources/css/verifikasi-nilai.css',
                'resources/js/verifikasi-nilai.js',
                'resources/css/rapor-saya.css',
                'resources/js/rapor-saya.js',
                'resources/css/nilai-per-mapel.css',
                'resources/js/nilai-per-mapel.js',
                'resources/css/riwayat-rapor.css',
                'resources/js/riwayat-rapor.js',
                'resources/css/beranda-akademik.css',
                'resources/js/beranda-akademik.js',
                'resources/css/dashboard-kurikulum.css',
                'resources/js/dashboard-kurikulum.js',
                'resources/css/dashboard-operator.css',
                'resources/js/dashboard-operator.js',
                'resources/css/akun-peran.css',
                'resources/js/akun-peran.js',
                'resources/css/data-siswa.css',
                'resources/js/data-siswa.js',
                'resources/css/data-guru.css',
                'resources/js/data-guru.js',
                'resources/css/data-kelas.css',
                'resources/js/data-kelas.js',
                'resources/css/relasi-orang-tua.css',
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