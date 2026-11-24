# Changelog

All notable changes to MeetingKu will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- Restrict Zoom host links to the booking owner and administrators, rotate unexpired host tokens, and prevent shared Zoom sessions from being changed or deleted by one booking.
- Remove legacy session archives, dumps, and logs from the release. Upgrade CodeIgniter, PhpSpreadsheet, and PHPUnit and require PHP 8.2.
- Commit one WhatsApp claim before sending, quarantine uncertain outcomes for reconciliation, and make daily summary catch-up atomic and idempotent.
- Reject replayed meeting form tokens. Bound calendar and list queries and keep CSV/print exports independent of pagination.
- Bound employee XLSX imports; scope calendar assets, verify pinned CDN assets with SRI, and remove retired views and styles.
- Show full room names in a responsive table instead of truncating them in cards. Keep room search, type filters, and actions available.

### Changed — GOWA provider (2026-11-06)
- Replaced the WAHA adapter with external GOWA at `https://gowa.kaltarastats.id`, using case-sensitive device ID `Meetingku` through `X-Device-Id` on status and send requests. Device management/pairing remains in the external dashboard.
- App and worker now require backend-only `GOWA_USERNAME`/`GOWA_PASSWORD` for HTTP Basic Auth; `GOWA_BASE_URL` and `GOWA_DEVICE_ID` have the defaults above. `WAHA_*` settings are no longer read. Rebuild/recreate app and worker with the new environment.
- Read connection/login flags from `/app/status`, send `phone`/`message` to `/send/message`, and map `results.message_id` to queue history. Preserve group JIDs and convert legacy personal `@c.us` recipients to `@s.whatsapp.net`.
- Queue/history/retry/scheduler behavior is retained. This provider change needs no database migration; the separate inbound API/key removal below still requires its migration if not yet applied. Added deterministic adapter tests without network sends.

### Breaking removal — inbound API and MeetingKu keys (2026-10-23)
- Removed inbound meeting/rooms and WhatsApp message/queue APIs, API-key creation/revocation/management, and their dedicated authentication/throttling code. Removed routes are not replaced by unauthenticated endpoints. `API.md` is now a retirement notice; external callers must stop using the old API.
- Retained authenticated meeting/room CRUD UI, Zoom, internal notifications, WhatsApp status/dashboard UI, queue/history/retry, worker, scheduler, and the `/healthz` secure-cookie fix. Retained backend outbound `WAHA_API_KEY`; it is unrelated to the removed MeetingKu keys.
- Added forward migration `2026-10-23-000001_RemoveWhatsappApiKeys.php`: drop the queue `api_key_id` foreign key and column before dropping `wa_api_keys`. Existing queue/history rows and `wa_settings` remain. Internal producers and the MySQL dump importer no longer use/import API keys or queue key associations.
- **Upgrade:** take and validate a full database backup before deployment; quiesce writes, worker, and scheduler, then run `php spark migrate` with the new code and recreate services. Key credentials and queue-key associations are destroyed and unrecoverable without the pre-upgrade backup.
- **Rollback:** migration `down()` restores empty key schema and a nullable queue key FK/column only, not credentials/associations or removed API code. Full recovery requires matching old code and the pre-upgrade database backup. Reconcile already-sent messages before restarting a restored queue to avoid duplicate sends. See README for the full procedure.
- All four Compose services remain necessary; this removal requires no further container/volume deletion. The only previously retired service is the old `wa-sender` orphan; preserve old auth/session volumes and never use `down -v` or volume pruning for cutover.

The entries below record earlier changes in this development cycle. Their statements that API/key behavior or schema was unchanged and no database migration was needed describe the earlier WAHA provider/UI cutover only; they are superseded by the breaking removal above. Historical API hardening notes remain for reference, not current setup instructions.

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
