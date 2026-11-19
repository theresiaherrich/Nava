module.exports = {
  uji: (d) => `[UJI NAVA] ${d.pesan || 'Pesan uji dari WhatsApp Service.'}`,
  presensi: (d) =>
    `Yth. Bapak/Ibu ${d.nama_ortu}, ananda ${d.nama_siswa} tercatat ${d.status} pada ${d.tanggal}. ` +
    `Detail lengkap dapat dilihat di Portal Orang Tua.`,

  presensi_mingguan: (d) =>
    `Yth. Bapak/Ibu ${d.nama_ortu}, rekap kehadiran ananda ${d.nama_siswa} (${d.periode}): ` +
    `Hadir ${d.hadir}, Terlambat ${d.terlambat}, Sakit ${d.sakit}, Izin ${d.izin}, Alpa ${d.alpa}. ` +
    `Detail lengkap dapat dilihat di Portal Orang Tua.`,

  nilai: (d) =>
    `Yth. Bapak/Ibu ${d.nama_ortu}, nilai ${d.jenis} semester ${d.semester} ananda ${d.nama_siswa} ` +
    `sudah tersedia di Portal Orang Tua.`,

  tes_minat: (d) =>
    `Yth. Bapak/Ibu ${d.nama_ortu}, hasil tes minat bakat dan ${d.hasil} ananda ${d.nama_siswa} ` +
    `sudah tersedia di Portal Orang Tua.`,
};