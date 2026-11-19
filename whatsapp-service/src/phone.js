// Ubah 08xx / +62xx / 62xx menjadi 62xxxxxxxxxx. Return null kalau tidak valid.
function normalize(input) {
  let d = String(input || '').replace(/\D/g, '');
  if (d.startsWith('0')) d = '62' + d.slice(1);
  else if (d.startsWith('8')) d = '62' + d;
  if (!/^62\d{8,13}$/.test(d)) return null;
  return d;
}

// Samarkan nomor untuk log, contoh: 62812****789
function mask(d) {
  return d.slice(0, 5) + '****' + d.slice(-3);
}

module.exports = { normalize, mask };