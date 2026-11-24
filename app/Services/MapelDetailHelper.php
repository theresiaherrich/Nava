<?php

namespace App\Services;

/**
 * MapelDetailHelper — Menyediakan deskripsi ringkas, relevan, dan terstruktur untuk mata pelajaran pilihan.
 */
class MapelDetailHelper
{
    /**
     * Katalog deskripsi ringkas mata pelajaran pilihan SMA.
     */
    public const KATALOG_MAPEL = [
        'ekonomi terapan' => [
            'nama' => 'Ekonomi Terapan',
            'rumpun' => 'Sosial Humaniora',
            'deskripsi' => 'Mempelajari dinamika pasar modal, akuntansi manajerial, dan kewirausahaan modern.',
        ],
        'sosiologi & riset masyarakat' => [
            'nama' => 'Sosiologi & Riset Masyarakat',
            'rumpun' => 'Sosial Humaniora',
            'deskripsi' => 'Mempelajari dinamika sosial, metode riset kemasyarakatan, dan kebijakan publik.',
        ],
        'sosiologi' => [
            'nama' => 'Sosiologi',
            'rumpun' => 'Sosial Humaniora',
            'deskripsi' => 'Mempelajari interaksi sosial, dinamika kelompok, fenomena kultural, dan struktur masyarakat.',
        ],
        'desain komunikasi visual' => [
            'nama' => 'Desain Komunikasi Visual',
            'rumpun' => 'Seni & Budaya',
            'deskripsi' => 'Mempelajari prinsip desain visual, tipografi digital, ilustrasi, dan komunikasi kreatif.',
        ],
        'geografi spasial & sig' => [
            'nama' => 'Geografi Spasial & SIG',
            'rumpun' => 'Ilmu Bumi & Spasial',
            'deskripsi' => 'Mempelajari sistem informasi geografis, pemetaan digital, dan mitigasi bencana wilayah.',
        ],
        'geografi' => [
            'nama' => 'Geografi',
            'rumpun' => 'Ilmu Bumi & Spasial',
            'deskripsi' => 'Mempelajari fenomena geosfer, tata ruang wilayah, dinamika kependudukan, dan lingkungan hidup.',
        ],
        'fisika lanjut' => [
            'nama' => 'Fisika Lanjut',
            'rumpun' => 'Sains & Teknologi',
            'deskripsi' => 'Mempelajari hukum alam, mekanika lanjutan, termodinamika, dan fenomena energi.',
        ],
        'fisika' => [
            'nama' => 'Fisika',
            'rumpun' => 'Sains & Teknologi',
            'deskripsi' => 'Mempelajari konsep gerak, gelombang, kelistrikan, optik, dan hukum-hukum fundamental alam.',
        ],
        'informatika & pemrograman' => [
            'nama' => 'Informatika & Pemrograman',
            'rumpun' => 'Teknologi Informasi',
            'deskripsi' => 'Mempelajari algoritma terapan, rekayasa perangkat lunak, dan kecerdasan buatan.',
        ],
        'informatika' => [
            'nama' => 'Informatika',
            'rumpun' => 'Teknologi Informasi',
            'deskripsi' => 'Mempelajari berpikir komputasional, pemrograman dasar, struktur data, dan analisis data.',
        ],
        'biologi & rekayasa hayati' => [
            'nama' => 'Biologi & Rekayasa Hayati',
            'rumpun' => 'Sains Hayati',
            'deskripsi' => 'Mempelajari keanekaragaman hayati, bioteknologi terapan, genetika, dan ekologi lingkungan.',
        ],
        'biologi' => [
            'nama' => 'Biologi',
            'rumpun' => 'Sains Hayati',
            'deskripsi' => 'Mempelajari struktur makhluk hidup, fisiologi seluler, genetika, dan keseimbangan ekosistem.',
        ],
        'kimia terapan' => [
            'nama' => 'Kimia Terapan',
            'rumpun' => 'Sains Terapan',
            'deskripsi' => 'Mempelajari reaksi kimia industri, material modern, dan dasar-dasar farmakologi.',
        ],
        'kimia' => [
            'nama' => 'Kimia',
            'rumpun' => 'Sains Terapan',
            'deskripsi' => 'Mempelajari struktur atom, ikatan molekul, hukum termokimia, dan kinetika reaksi.',
        ],
        'matematika tingkat lanjut' => [
            'nama' => 'Matematika Tingkat Lanjut',
            'rumpun' => 'MIPA',
            'deskripsi' => 'Mempelajari kalkulus diferensial-integral, aljabar vektor, matriks, dan pemodelan matematis.',
        ],
        'bahasa inggris tingkat lanjut' => [
            'nama' => 'Bahasa Inggris Tingkat Lanjut',
            'rumpun' => 'Bahasa & Budaya',
            'deskripsi' => 'Mempelajari komunikasi internasional, analisis wacana kritis, dan penulisan teks analitik.',
        ],
        'bahasa dan sastra indonesia' => [
            'nama' => 'Bahasa dan Sastra Indonesia',
            'rumpun' => 'Bahasa & Budaya',
            'deskripsi' => 'Mempelajari linguistik terapan, penulisan kreatif, jurnalistik, dan apresiasi karya sastra.',
        ],
        'antropologi' => [
            'nama' => 'Antropologi',
            'rumpun' => 'Sosial Humaniora',
            'deskripsi' => 'Mempelajari keragaman budaya masyarakat, etnografi, dan dinamika kebudayaan nusantara.',
        ],
    ];

    /**
     * Dapatkan deskripsi ringkas mapel berdasarkan nama.
     */
    public static function getDeskripsi(string $nama, ?string $dbDeskripsi = null, ?string $rumpun = null): string
    {
        if (! empty($dbDeskripsi) && trim($dbDeskripsi) !== '') {
            return trim($dbDeskripsi);
        }

        $key = self::normalizeKey($nama);
        if (isset(self::KATALOG_MAPEL[$key])) {
            return self::KATALOG_MAPEL[$key]['deskripsi'];
        }

        // Cek pencocokan parsial kata kunci
        foreach (self::KATALOG_MAPEL as $k => $item) {
            if (str_contains($key, $k) || str_contains($k, $key)) {
                return $item['deskripsi'];
            }
        }

        $cleanRumpun = $rumpun ? ' rumpun ' . $rumpun : '';

        return 'Mempelajari landasan konsep' . $cleanRumpun . ' serta penerapan keterampilan praktis di bidangnya.';
    }

    /**
     * Normalisasi nama mapel untuk pencocokan kunci.
     */
    private static function normalizeKey(string $nama): string
    {
        $clean = strtolower(trim($nama));
        $clean = str_replace(['mata pelajaran', 'mapel'], '', $clean);

        return trim($clean);
    }
}
