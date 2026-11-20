require('dotenv').config();
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');

let status = 'INITIALIZING';

const client = new Client({
  authStrategy: new LocalAuth({ dataPath: './.wwebjs_auth' }),
  puppeteer: {
    headless: true,
    executablePath: process.env.CHROME_PATH || undefined,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  },
});

client.on('qr', (qr) => {
  status = 'QR_REQUIRED';
  console.log('Scan QR ini dengan WhatsApp nomor UJI (Perangkat tertaut > Tautkan perangkat):');
  qrcode.generate(qr, { small: true });
});
client.on('authenticated', () => console.log('Autentikasi berhasil'));
client.on('ready', () => {
  status = 'READY';
  console.log('WhatsApp siap dipakai');
});
client.on('auth_failure', (m) => {
  status = 'DISCONNECTED';
  console.error('Autentikasi gagal:', m);
});
client.on('disconnected', (reason) => {
  status = 'DISCONNECTED';
  console.error('Terputus:', reason);
});

function getStatus() {
  return status;
}

async function sendText(number, text) {
  const id = await client.getNumberId(number);

  if (!id) {
    const err = new Error('Nomor tidak terdaftar di WhatsApp');
    err.code = 'NOT_ON_WHATSAPP';
    throw err;
  }

  const msg = await client.sendMessage(id._serialized, text);
  return msg.id._serialized;
}

client.initialize();

module.exports = { getStatus, sendText };