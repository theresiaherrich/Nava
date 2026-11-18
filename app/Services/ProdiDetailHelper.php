<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * ProdiDetailHelper — Informasi ringkas, relevan, dan terstruktur untuk program studi & karier.
 */
class ProdiDetailHelper
{
    /**
     * Ambil data ringkas dan relevan untuk program studi.
     */
    public static function getDetail(
        string $nama,
        ?string $rumpun = null,
        ?string $kodeRiasec = null,
        ?string $dbDeskripsi = null,
        mixed $dbProfesi = null
    ): array {
        $key = self::normalizeKey($nama);
        $catalog = self::getCatalog();

        $curated = $catalog[$key] ?? self::generateFallback($nama, $rumpun, $kodeRiasec, $dbDeskripsi);

        // Ambil profesi dari DB jika ada, gabungkan dengan katalog
        $profesiList = $curated['profesi_list'] ?? [];
        if ($dbProfesi instanceof Collection && $dbProfesi->isNotEmpty()) {
            foreach ($dbProfesi as $pf) {
                if (! in_array($pf->nama, $profesiList, true)) {
                    array_unshift($profesiList, $pf->nama);
                }
            }
        }
        $curated['profesi_list'] = array_values(array_unique($profesiList));

        return $curated;
    }

    /**
     * Normalisasi string nama prodi untuk pencocokan kunci katalog.
     */
    private static function normalizeKey(string $nama): string
    {
        $clean = strtolower(trim($nama));
        $clean = preg_replace('/^(s1|d4|sarjana|program studi)\s+/i', '', $clean);

        return trim($clean);
    }

    /**
     * Katalog ringkas program studi.
     */
    public static function getCatalog(): array
    {
        return [
            'akuntansi' => [
                'nama' => 'Akuntansi',
                'rumpun' => 'Ekonomi & Bisnis',
                'jenjang_gelar' => 'S1 · S.Ak.',
                'kode_riasec' => 'CES',
                'deskripsi_ringkas' => 'Mempelajari pengelolaan dan analisis keuangan, sistem informasi akuntansi digital (ERP), audit independen, serta kepatuhan perpajakan modern untuk mendukung pengambilan keputusan strategis entitas bisnis dan publik.',
                'kesesuaian_singkat' => 'Selaras dengan karakter yang teliti dan terstruktur (C), berorientasi analisis bisnis (E), serta komunikatif dalam koordinasi tim (S).',
                'profesi_list' => ['Akuntan Publik / Auditor', 'Konsultan Pajak', 'Financial Analyst', 'Internal Auditor & Compliance'],
            ],

            'teknik informatika' => [
                'nama' => 'Teknik Informatika',
                'rumpun' => 'Teknologi Informasi',
                'jenjang_gelar' => 'S1 · S.Kom.',
                'kode_riasec' => 'IRC',
                'deskripsi_ringkas' => 'Mempelajari rekayasa perangkat lunak modern, struktur data algoritmis, komputasi awan (cloud), kecerdasan buatan (AI/ML), dan keamanan siber untuk merancang solusi sistem digital yang andal dan terukur.',
                'kesesuaian_singkat' => 'Cocok untuk kepribadian yang analitis memecahkan masalah logika (I), senang praktik teknis langsung (R), dan teliti pada arsitektur kode yang terstruktur (C).',
                'profesi_list' => ['Software Engineer', 'Cloud & DevOps Specialist', 'AI / Machine Learning Engineer', 'Cybersecurity Analyst'],
            ],

            'sains data' => [
                'nama' => 'Sains Data',
                'rumpun' => 'Teknologi Informasi',
                'jenjang_gelar' => 'S1 · S.Stat. / S.Kom.',
                'kode_riasec' => 'IRC',
                'deskripsi_ringkas' => 'Memadukan statistika terapan, machine learning, dan pengolahan big data untuk menggali pola analitis, visualisasi interaktif, serta model prediksi akurat bagi kebutuhan transformasi industri.',
                'kesesuaian_singkat' => 'Sangat pas bagi yang kritis meneliti pola data (I), terampil mengoperasikan perangkat komputasi (R), dan teliti dalam validasi dataset (C).',
                'profesi_list' => ['Data Scientist', 'Business Intelligence Analyst', 'Data Engineer', 'Quantitative Analyst'],
            ],

            'pendidikan dokter' => [
                'nama' => 'Pendidikan Dokter',
                'rumpun' => 'Kesehatan & Kedokteran',
                'jenjang_gelar' => 'S1 (S.Ked.) + Profesi (dr.)',
                'kode_riasec' => 'ISR',
                'deskripsi_ringkas' => 'Mempelajari ilmu biomedis mendalam, keterampilan diagnosis klinis, pencegahan dan penanganan penyakit, serta komunikasi terapeutik empatik untuk memberikan layanan kesehatan masyarakat yang holistik.',
                'kesesuaian_singkat' => 'Selaras dengan nalar analisis ilmiah patologi (I), kepedulian sosial tinggi menolong sesama (S), serta ketahanan fisik dalam tindakan klinis (R).',
                'profesi_list' => ['Dokter Layanan Primer', 'Dokter Spesialis', 'Peneliti Medis & Klinis', 'Konsultan Manajemen RS'],
            ],

            'arsitektur' => [
                'nama' => 'Arsitektur',
                'rumpun' => 'Teknik & Desain',
                'jenjang_gelar' => 'S1 · S.Ars.',
                'kode_riasec' => 'AIR',
                'deskripsi_ringkas' => 'Memadukan estetika desain visual ruang, kekuatan rekayasa struktur, dan prinsip keberlanjutan ramah lingkungan (green building) untuk merancang hunian serta kawasan perkotaan yang fungsional.',
                'kesesuaian_singkat' => 'Cocok untuk perancang berjiwa artistik visual (A), analitis terhadap tapak lingkungan (I), dan aplikatif dalam gambar teknik konstruksi nyata (R).',
                'profesi_list' => ['Arsitek Bangunan', 'Urban Designer / Tata Kota', 'BIM Architect / Manager', 'Interior Architect'],
            ],

            'psikologi' => [
                'nama' => 'Psikologi',
                'rumpun' => 'Sosial Humaniora',
                'jenjang_gelar' => 'S1 · S.Psi.',
                'kode_riasec' => 'SAI',
                'deskripsi_ringkas' => 'Mempelajari fungsi kognitif, dinamika emosi, kepribadian, dan pola interaksi perilaku manusia, serta penerapannya pada asesmen psikologis, konseling suportif, dan manajemen talenta SDM (HRD).',
                'kesesuaian_singkat' => 'Selaras dengan empati sosial tinggi mendampingi sesama (S), kepekaan intuisi humanistik (A), dan nalar investigatif riset perilaku ilmiah (I).',
                'profesi_list' => ['HR & People Operations', 'User Experience (UX) Researcher', 'Konselor Pendidikan / Karier', 'Psikolog Klinis (S2 Profesi)'],
            ],

            'hubungan internasional' => [
                'nama' => 'Hubungan Internasional',
                'rumpun' => 'Sosial Humaniora',
                'jenjang_gelar' => 'S1 · S.Hub.Int.',
                'kode_riasec' => 'EAS',
                'deskripsi_ringkas' => 'Mempelajari dinamika geopolitik global, diplomasi bilateral dan multilateral, hukum internasional, organisasi antarpemerintah, perdagangan dunia, serta resolusi konflik perdamaian lintas batas negara.',
                'kesesuaian_singkat' => 'Cocok bagi yang berjiwa kepemimpinan dan persuasif (E), menguasai retorika diplomasi multibudaya (A), dan berorientasi perdamaian global (S).',
                'profesi_list' => ['Diplomat / Pejabat Luar Negeri', 'International NGO Officer (PBB)', 'Analis Kebijakan & Risiko Geopolitik', 'Corporate Public Relations'],
            ],

            'manajemen' => [
                'nama' => 'Manajemen',
                'rumpun' => 'Ekonomi & Bisnis',
                'jenjang_gelar' => 'S1 · S.M.',
                'kode_riasec' => 'ESC',
                'deskripsi_ringkas' => 'Mempelajari perencanaan strategi bisnis, manajemen pemasaran digital, tata kelola keuangan korporat, rantai pasok (supply chain), serta kepemimpinan tim kerja di berbagai sektor industri.',
                'kesesuaian_singkat' => 'Selaras dengan naluri kepemimpinan bisnis (E), komunikasi koordinasi tim (S), serta kedisiplinan tata kelola operasional (C).',
                'profesi_list' => ['Management Trainee / Operations', 'Brand & Marketing Strategist', 'Business Development Manager', 'Wirausahawan / Founder'],
            ],

            'ilmu komunikasi' => [
                'nama' => 'Ilmu Komunikasi',
                'rumpun' => 'Sosial Humaniora',
                'jenjang_gelar' => 'S1 · S.I.Kom.',
                'kode_riasec' => 'AES',
                'deskripsi_ringkas' => 'Mempelajari strategi transmisi pesan melalui media digital, hubungan masyarakat (Public Relations), produksi konten multimedia, periklanan kreatif, dan manajemen media sosial.',
                'kesesuaian_singkat' => 'Pas bagi yang kreatif memproduksi narasi visual (A), persuasif dalam public speaking (E), dan luwes membangun relasi publik (S).',
                'profesi_list' => ['Public Relations (PR) Specialist', 'Social Media & Content Strategist', 'Jurnalis Multimedia / Produser', 'Brand Communication Planner'],
            ],

            'ilmu hukum' => [
                'nama' => 'Ilmu Hukum',
                'rumpun' => 'Ilmu Hukum',
                'jenjang_gelar' => 'S1 · S.H.',
                'kode_riasec' => 'ESI',
                'deskripsi_ringkas' => 'Mempelajari sistem hukum pidana, perdata, tata negara, serta hukum bisnis dan perancangan kontrak (legal drafting) guna menegakkan keadilan dan kepatuhan regulasi.',
                'kesesuaian_singkat' => 'Selaras dengan ketegasan argumentasi negosiasi (E), komitmen keadilan sosial (S), serta ketajaman analisis yuridis (I).',
                'profesi_list' => ['Corporate Legal Counsel', 'Advokat / Pengacara', 'Hakim / Jaksa', 'Legal Compliance Specialist'],
            ],

            'farmasi' => [
                'nama' => 'Farmasi',
                'rumpun' => 'Sains & Kesehatan',
                'jenjang_gelar' => 'S1 (S.Farm.) + Apoteker (Apt.)',
                'kode_riasec' => 'ISR',
                'deskripsi_ringkas' => 'Mempelajari kimia medisinal, formulasi dan peracikan sediaan obat, farmakologi klinis, serta pengawasan mutu sediaan farmasi dan kosmetika berstandar Cara Pembuatan Obat yang Baik (CPOB).',
                'kesesuaian_singkat' => 'Cocok untuk periset ilmiah laboratorium (I), teliti dalam pengujian presisi (R), dan peduli pada keselamatan edukasi obat pasien (S).',
                'profesi_list' => ['Apoteker Rumah Sakit / Apotek', 'R&D & Formulator Farmasi', 'Quality Control (QC) Mutu', 'Pengawas Regulasi BPOM'],
            ],
        ];
    }

    /**
     * Fallback cerdas untuk prodi yang tidak ada dalam daftar katalog.
     */
    private static function generateFallback(
        string $nama,
        ?string $rumpun,
        ?string $kodeRiasec,
        ?string $dbDeskripsi
    ): array {
        $cleanRumpun = $rumpun ?: 'Program Studi Perguruan Tinggi';
        $deskripsi = $dbDeskripsi
            ? $dbDeskripsi . ' Membekali mahasiswa dengan pemahaman teori fundamental, metodologi riset, dan keahlian praktis terapan di bidangnya.'
            : 'Mempelajari landasan keilmuan ' . $cleanRumpun . ', metodologi riset ilmiah, serta penerapan keterampilan praktis untuk memecahkan persoalan industri dan masyarakat.';

        $kodeStr = strtoupper($kodeRiasec ?? 'IRC');

        return [
            'nama' => $nama,
            'rumpun' => $cleanRumpun,
            'jenjang_gelar' => 'Sarjana (S1)',
            'kode_riasec' => $kodeStr,
            'deskripsi_ringkas' => $deskripsi,
            'kesesuaian_singkat' => 'Kombinasi minat RIASEC (' . $kodeStr . ') mendukung ketekunan dan potensi eksplorasi terbaikmu di bidang keilmuan ini.',
            'profesi_list' => [
                'Praktisi / Spesialis ' . $nama,
                'Analis & Konsultan Bidang Terkait',
                'Peneliti / Akademisi',
            ],
        ];
    }
}
