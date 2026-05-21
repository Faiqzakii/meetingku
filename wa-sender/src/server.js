import express from 'express';
import makeWASocket, { DisconnectReason, fetchLatestBaileysVersion, useMultiFileAuthState } from '@whiskeysockets/baileys';
import pino from 'pino';
import QRCode from 'qrcode';
import { rm } from 'fs/promises';

const app = express();
const port = Number(process.env.PORT || 3001);
const secret = process.env.INTERNAL_WA_SECRET || '';
const authDir = process.env.WA_AUTH_DIR || './auth_info_baileys/default';
const logger = pino({ level: process.env.LOG_LEVEL || 'info' });

let sock = null;
let state = 'disconnected';
let lastQr = null;

app.use(express.json({ limit: '1mb' }));

function requireSecret(req, res, next) {
  if (!secret || req.get('X-INTERNAL-WA-SECRET') !== secret) {
    return res.status(401).json({ success: false, error: 'unauthorized' });
  }
  next();
}

async function ensureSession() {
  if (sock) return sock;

  const auth = await useMultiFileAuthState(authDir);
  const { version } = await fetchLatestBaileysVersion();
  sock = makeWASocket({
    version,
    auth: auth.state,
    printQRInTerminal: false,
    logger: pino({ level: 'silent' }),
    browser: ['MeetingKU', 'Chrome', '1.0.0'],
  });

  sock.ev.on('creds.update', auth.saveCreds);
  sock.ev.on('connection.update', async (update) => {
    if (update.qr) {
      lastQr = update.qr;
      state = 'qr_ready';
    }
    if (update.connection === 'open') {
      state = 'connected';
      lastQr = null;
    }
    if (update.connection === 'close') {
      const code = update.lastDisconnect?.error?.output?.statusCode;
      state = 'disconnected';
      sock = null;
      if (code !== DisconnectReason.loggedOut) {
        setTimeout(() => ensureSession().catch((err) => logger.error(err)), 3000);
      }
    }
  });

  return sock;
}

function normalizeJid(to) {
  if (to.endsWith('@g.us') || to.endsWith('@s.whatsapp.net')) return to;
  return `${to.replace(/\D/g, '')}@s.whatsapp.net`;
}

app.get('/health', (_req, res) => res.json({ ok: true, state }));

app.get('/status', requireSecret, async (_req, res) => {
  await ensureSession();
  const qr = lastQr ? await QRCode.toDataURL(lastQr, { margin: 1, width: 260 }) : null;
  res.json({ success: true, connected: state === 'connected', state, qr });
});

app.post('/pairing-code', requireSecret, async (req, res) => {
  const phoneNumber = String(req.body.phoneNumber || '').replace(/\D/g, '');
  if (!phoneNumber) return res.status(422).json({ success: false, error: 'phoneNumber required' });
  const session = await ensureSession();
  const pairingCode = await session.requestPairingCode(phoneNumber);
  res.json({ success: true, pairingCode });
});

app.post('/send-message', requireSecret, async (req, res) => {
  const to = String(req.body.to || '');
  const message = String(req.body.message || '');
  if (!to || !message) return res.status(422).json({ success: false, error: 'to and message required' });
  const session = await ensureSession();
  if (state !== 'connected') return res.status(409).json({ success: false, error: 'whatsapp not connected' });
  const result = await session.sendMessage(normalizeJid(to), { text: message });
  res.json({ success: true, messageId: result?.key?.id || null });
});

app.post('/logout', requireSecret, async (_req, res) => {
  if (sock) await sock.logout();
  sock = null;
  state = 'disconnected';
  lastQr = null;
  res.json({ success: true });
});

app.post('/reset-session', requireSecret, async (_req, res) => {
  if (sock) {
    try { await sock.logout(); } catch (_) {}
  }
  sock = null;
  state = 'disconnected';
  lastQr = null;
  await rm(authDir, { recursive: true, force: true });
  await ensureSession();
  res.json({ success: true });
});

app.listen(port, '0.0.0.0', () => {
  logger.info(`WA sender listening on ${port}`);
  ensureSession().catch((err) => logger.error(err));
});
