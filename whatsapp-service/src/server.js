const express = require('express');
const crypto = require('crypto');
const config = require('./config');
const { normalize, mask } = require('./phone');
const templates = require('./templates');
const wa = require('./waClient');

const app = express();
app.use(express.json({ limit: '10kb' }));

const messages = new Map(); // idempotency_key -> info status pesan
let sentToday = 0;
let dayStamp = new Date().toDateString();

function checkDailyLimit() {
  const today = new Date().toDateString();
  if (today !== dayStamp) {
    dayStamp = today;
    sentToday = 0;
  }
  return sentToday < config.maxPerDay;
}

// Antrean serial + jeda acak antar pesan (anti-spam)
let chain = Promise.resolve();
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
function enqueue(task) {
  const run = chain.then(async () => {
    const ms = config.delayMin + Math.random() * (config.delayMax - config.delayMin);
    await sleep(ms);
    return task();
  });
  chain = run.catch(() => {});
  return run;
}

// Cek token (perbandingan aman)
function auth(req, res, next) {
  const given = Buffer.from(String(req.get('X-WA-Token') || ''));
  const real = Buffer.from(config.token);
  if (given.length !== real.length || !crypto.timingSafeEqual(given, real)) {
    return res.status(401).json({ error: 'TOKEN_INVALID' });
  }
  next();
}

app.get('/health', (req, res) => res.json({ ok: true }));

app.get('/api/status', auth, (req, res) => res.json({ status: wa.getStatus() }));

app.post('/api/messages', auth, (req, res) => {
  const { idempotency_key, to, template, data } = req.body || {};

  if (!idempotency_key || !to || !template) {
    return res.status(422).json({ error: 'FIELD_KURANG' });
  }
  if (!templates[template]) {
    return res.status(422).json({ error: 'TEMPLATE_TIDAK_DIKENAL' });
  }
  const number = normalize(to);
  if (!number) return res.status(422).json({ error: 'NOMOR_TIDAK_VALID' });

  if (!config.allowed.has(number)) {
    return res.status(403).json({ error: 'NOMOR_DI_LUAR_ALLOWLIST' });
  }
  if (messages.has(idempotency_key)) {
    return res.status(409).json({ error: 'DUPLIKAT', ...messages.get(idempotency_key) });
  }
  if (wa.getStatus() !== 'READY') {
    return res.status(503).json({ error: 'WA_BELUM_SIAP', status: wa.getStatus() });
  }
  if (!checkDailyLimit()) {
    return res.status(429).json({ error: 'BATAS_HARIAN_TERCAPAI' });
  }

  const text = templates[template](data || {});
  const info = { status: 'queued', to: mask(number) };
  messages.set(idempotency_key, info);
  sentToday += 1;

  enqueue(async () => {
    try {
      info.wa_message_id = await wa.sendText(number, text);
      info.status = 'sent';
      console.log(`Terkirim ke ${mask(number)} (${idempotency_key})`);
    } catch (e) {
      info.status = 'failed';
      info.error = e.code || e.message;
      console.error(`Gagal kirim ke ${mask(number)}: ${info.error}`);
    }
  });

  res.status(202).json({ status: 'accepted', ...info });
});

app.get('/api/messages/:key', auth, (req, res) => {
  const info = messages.get(req.params.key);
  if (!info) return res.status(404).json({ error: 'TIDAK_DITEMUKAN' });
  res.json(info);
});

// Hanya bisa diakses dari laptop sendiri
app.listen(config.port, '127.0.0.1', () => {
  console.log(`WhatsApp service jalan di http://127.0.0.1:${config.port}`);
});