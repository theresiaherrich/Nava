require('dotenv').config();
const { normalize } = require('./phone');

const token = process.env.WA_SERVICE_TOKEN || '';
if (token.length < 24) {
  console.error('WA_SERVICE_TOKEN belum diisi atau kurang dari 24 karakter. Cek file .env');
  process.exit(1);
}

const allowed = new Set(
  (process.env.WA_ALLOWED_NUMBERS || '')
    .split(',')
    .map((n) => normalize(n.trim()))
    .filter(Boolean)
);
if (allowed.size === 0) {
  console.error('WA_ALLOWED_NUMBERS kosong atau formatnya salah. Cek file .env');
  process.exit(1);
}

module.exports = {
  port: Number(process.env.PORT || 3001),
  token,
  allowed,
  delayMin: Number(process.env.DELAY_MIN_MS || 3000),
  delayMax: Number(process.env.DELAY_MAX_MS || 8000),
  maxPerDay: Number(process.env.MAX_PER_DAY || 20),
};