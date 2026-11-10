-- DATA AWAL A3 (jalankan SETELAH struktur A3 baru ada). Aman dijalankan sekali.
USE nava;

-- Dimensi (lewati bila sudah terisi)
INSERT INTO dimensi_riasec (kode, nama, deskripsi)
SELECT * FROM (
  SELECT 'R','Realistic','Menyukai aktivitas praktis, menggunakan alat, mesin, dan bekerja di luar ruangan.' UNION ALL
  SELECT 'I','Investigative','Tertarik pada penelitian, analisis, pemecahan masalah, dan eksplorasi ilmiah.' UNION ALL
  SELECT 'A','Artistic','Menyukai ekspresi kreatif melalui seni, desain, bahasa, dan gagasan orisinal.' UNION ALL
  SELECT 'S','Social','Senang membantu, mengajar, berkomunikasi, dan bekerja bersama orang lain.' UNION ALL
  SELECT 'E','Enterprising','Tertarik memimpin, memengaruhi, berwirausaha, dan mencapai target.' UNION ALL
  SELECT 'C','Conventional','Menyukai keteraturan, data, prosedur, administrasi, dan pekerjaan terstruktur.'
) x WHERE NOT EXISTS (SELECT 1 FROM dimensi_riasec);

-- 12 soal: 2 per dimensi (satu bank untuk semua jenjang)
INSERT INTO soal_riasec (id_dimensi, teks)
SELECT d.id_dimensi, s.teks FROM (
  SELECT 'R' k,'Saya senang memperbaiki peralatan elektronik yang rusak.' teks UNION ALL
  SELECT 'R','Saya tertarik merakit, memperbaiki, atau bekerja dengan alat dan mesin.' UNION ALL
  SELECT 'I','Saya tertarik mencari penyebab terjadinya suatu fenomena alam.' UNION ALL
  SELECT 'I','Saya senang memecahkan soal atau teka-teki yang membutuhkan analisis.' UNION ALL
  SELECT 'A','Saya suka membuat karya visual dengan gaya yang unik.' UNION ALL
  SELECT 'A','Saya senang menulis cerita, puisi, atau mendesain tampilan kreatif.' UNION ALL
  SELECT 'S','Saya merasa puas ketika dapat membantu teman menyelesaikan masalah.' UNION ALL
  SELECT 'S','Saya senang mengajari orang lain memahami sesuatu.' UNION ALL
  SELECT 'E','Saya percaya diri memimpin diskusi atau kegiatan kelompok.' UNION ALL
  SELECT 'E','Saya senang meyakinkan orang lain terhadap gagasan saya.' UNION ALL
  SELECT 'C','Saya suka mengatur data atau catatan agar rapi dan teratur.' UNION ALL
  SELECT 'C','Saya nyaman mengikuti prosedur dan jadwal yang jelas.'
) s JOIN dimensi_riasec d ON d.kode = s.k;

-- Program studi (langsung tervalidasi supaya rekomendasi langsung muncul)
INSERT INTO program_studi (nama, kategori, deskripsi, kode_holland, status_validasi, waktu_validasi) VALUES
('Psikologi','Sosial & Humaniora','Mempelajari perilaku, proses mental, dan interaksi manusia secara ilmiah.','ISA','tervalidasi',NOW()),
('Kedokteran','Kesehatan','Ilmu kesehatan, diagnosis, penanganan, dan pencegahan penyakit.','ISR','tervalidasi',NOW()),
('Ilmu Komunikasi','Sosial & Humaniora','Komunikasi strategis, media, dan penyampaian gagasan kepada publik.','ASE','tervalidasi',NOW()),
('Teknik Informatika','Sains & Teknologi','Rekayasa perangkat lunak, sistem komputasi, dan teknologi digital.','IRC','tervalidasi',NOW()),
('Manajemen','Ekonomi & Bisnis','Pengelolaan organisasi, bisnis, sumber daya, dan strategi.','ESC','tervalidasi',NOW()),
('Desain Komunikasi Visual','Seni & Desain','Komunikasi kreatif melalui media visual dan teknologi desain.','AER','tervalidasi',NOW());

-- Profesi dan program studi terkait
INSERT INTO profesi (nama, deskripsi) VALUES
('Psikolog Klinis','Memberikan asesmen dan intervensi terkait kesehatan mental.'),
('Dokter Umum','Memberikan layanan medis primer kepada pasien.'),
('Software Engineer','Merancang dan mengembangkan sistem perangkat lunak.'),
('UI/UX Designer','Merancang pengalaman dan antarmuka produk digital.'),
('Business Analyst','Menganalisis proses dan kebutuhan strategis bisnis.'),
('PR Specialist','Mengelola citra dan komunikasi organisasi kepada publik.');

INSERT INTO profesi_prodi (id_profesi, id_prodi)
SELECT pf.id_profesi, ps.id_prodi FROM (
  SELECT 'Psikolog Klinis' a,'Psikologi' b UNION ALL SELECT 'Dokter Umum','Kedokteran' UNION ALL
  SELECT 'Software Engineer','Teknik Informatika' UNION ALL SELECT 'UI/UX Designer','Desain Komunikasi Visual' UNION ALL
  SELECT 'UI/UX Designer','Teknik Informatika' UNION ALL SELECT 'Business Analyst','Manajemen' UNION ALL
  SELECT 'PR Specialist','Ilmu Komunikasi'
) m JOIN profesi pf ON pf.nama = m.a JOIN program_studi ps ON ps.nama = m.b;

-- Mapel pilihan: mapel (A2) dibuat bila belum ada, lalu profilnya langsung tervalidasi
INSERT IGNORE INTO mapel (id_dimensi, kode, nama)
SELECT d.id_dimensi, x.kode, x.nama FROM (
  SELECT 'BIO' kode,'Biologi Tingkat Lanjut' nama,'I' dim UNION ALL SELECT 'KIM','Kimia','I' UNION ALL
  SELECT 'FIS','Fisika','I' UNION ALL SELECT 'MTL','Matematika Tingkat Lanjut','I' UNION ALL
  SELECT 'INF','Informatika','I' UNION ALL SELECT 'GEO','Geografi','I' UNION ALL
  SELECT 'ANT','Antropologi','S' UNION ALL SELECT 'IGL','Bahasa Inggris Tingkat Lanjut','A' UNION ALL
  SELECT 'IDL','Bahasa Indonesia Tingkat Lanjut','A'
) x JOIN dimensi_riasec d ON d.kode = x.dim;

INSERT INTO profil_mapel (id_mapel, rumpun, kode_holland, status_validasi, waktu_validasi)
SELECT m.id_mapel, x.rumpun, x.holland, 'tervalidasi', NOW() FROM (
  SELECT 'BIO' kode,'MIPA' rumpun,'ISA' holland UNION ALL SELECT 'KIM','MIPA','IRC' UNION ALL
  SELECT 'FIS','MIPA','IRC' UNION ALL SELECT 'MTL','MIPA','ICR' UNION ALL
  SELECT 'INF','Teknologi','IRC' UNION ALL SELECT 'GEO','Sosial','IRS' UNION ALL
  SELECT 'ANT','Sosial','SAI' UNION ALL SELECT 'IGL','Bahasa','ASE' UNION ALL SELECT 'IDL','Bahasa','ASC'
) x JOIN mapel m ON m.kode = x.kode;
