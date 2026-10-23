# Changelog

All notable changes to MeetingKu will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Replaced the bundled Node/Baileys WhatsApp gateway with the existing external WAHA service at `https://waha.kaltarastats.id`. MeetingKu reads session status and sends text only; all session lifecycle/pairing actions now belong to the WAHA dashboard.
- App and worker use `WAHA_BASE_URL` (default above), required `WAHA_API_KEY` (plain token, not SHA-512 hash), and `WAHA_SESSION` (default `default`). Provider credentials stay server-side; public MeetingKu API authentication, message queue, retry/backoff, and database schema are unchanged. No database migration is required.
- Removed the bundled gateway source/service and Compose auth-volume declaration, while retaining existing app/proxy healthchecks and network topology. `/healthz` does not test WAHA connectivity.
- Normalize both string message IDs and WEBJS `id._serialized` responses before storing the provider ID, so successful WEBJS sends are not incorrectly retried.
- Removed the API Keys section and new-key flash display from the WhatsApp admin page; WAHA connection status now spans the page above the queue log. Backend API/key capabilities are unchanged.
- Refined the WAHA session card with a single semantic connection summary, responsive session details, and a keyboard-visible dashboard action. Working, starting, QR, stopped, failed, unknown, and status-fetch error states are distinct; queue and backend behavior are unchanged.
- Exclude the read-only `/healthz` route from CSRF cookie generation so internal HTTP probes work with production secure cookies enabled; form CSRF protection remains unchanged.

### Deployment migration
- Configure WAHA secrets in the deployment platform, ensure the named session already exists and is `WORKING` via the WAHA dashboard, then rebuild/recreate app and worker. The long-running worker must restart to load code/config; changed Compose environment requires recreation.
- Old Baileys auth/session data does not migrate; pair the account in WAHA. Stop/remove the old `wa-sender` orphan (`docker compose up -d --remove-orphans` for the same project), without deleting its auth volume/directories. Do not use `down -v` or volume pruning during cutover.
- Permit backend HTTPS access to WAHA with valid TLS, restrict API/dashboard access to trusted networks or source allowlists, and never expose the WAHA token to browser clients. See README for the full cutover procedure.


### Added
- **Client-side form validation with center alerts** (2026-06-15)
  - Global `showCenterAlert()` helper rendering modal alerts in viewport center
    (replaces server-only validation that produced opaque 303 redirects).
  - Auto form-guard on `<form class="js-validated">` enforcing `data-rule-*`
    attributes before submit; errors aggregated into a single dialog and
    focus jumps to the first invalid field.
  - Mirrors server rules on every form input so users get immediate feedback:
    - Meeting: `nama_keg` (3–100 char), `jumlah_peserta` (1–9999),
      `fasilitas_lainnya` (≤200 char) on both create and edit.
    - Pegawai: `nama` (3–100), `nip` (exactly 18 digits via regex),
      `username` (3–50), `password` (3–100), `no_hp` (digits/`+`/space, 8–20).
    - Ruangan: `nama_ruangan` (3–100).
    - Login: `username` (3–50), `password` (≥3).
  - Login page ships its own inline center-alert (it does not extend layout).

### Security
- **API Hardening** (2026-06-01)
  - Removed `plain_key` from database storage (only hash stored now)
  - API key authentication now header-only (`X-API-KEY`), removed query parameter fallback
  - Added rate limiting (60 requests/minute) to all API endpoints via Throttler filter
  - Sanitized error messages to prevent information leakage
  - Added failed authentication logging with IP address
  - Added `WaApiKeyService::generateMeetingKey()` for `mku_` prefixed keys

### Documentation
- Added comprehensive API documentation (`API.md`)
- Updated README.md with Public API section and security notes
- Updated AGENTS.md with API security guidelines
- Added CHANGELOG.md for version tracking

### Added
- CSRF exclusion for API routes (proper for API key authentication)
- Route group with throttle filter for all API endpoints

## [1.0.0] - Initial Release

### Features
- Meeting management (CRUD, calendar, upcoming/all views)
- Employee (pegawai) management with spreadsheet import
- Room (ruangan) management
- Zoom integration (automatic & manual link management)
- WhatsApp gateway integration via Node.js service
- WhatsApp API key management
- Message queue with retry mechanism
- Daily agenda scheduler (15:00 WITA)
- Docker Compose deployment (PHP-FPM, Nginx, worker, scheduler, wa-sender)
- PostgreSQL support (with MySQL import tool for migration)

### Technical
- CodeIgniter 4 framework (PHP 8.1+)
- PHPUnit 10 for testing
- Tailwind CSS + Bootstrap for UI
- FullCalendar for calendar views
- Baileys library for WhatsApp connection
