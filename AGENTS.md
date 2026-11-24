# Repository Guidelines

## Project Structure & Module Organization
- CodeIgniter 4 app lives in `app/` (`Controllers`, `Models`, `Views`, `Config`, `Commands`, `Database/Migrations`).
- Public webroot is `public/`; configure web server/Nginx to point here only.
- Runtime state belongs in `writable/` and `public/writable/`; do not commit logs/cache/session/uploads.
- WhatsApp queue/worker and external GOWA adapter live in PHP (`app/Commands`, `app/Libraries`, `app/Models`); no bundled gateway is deployed.
- Deploy assets live in `Dockerfile`, `docker-compose.yml`, and `docker/nginx/`.
- Tests live in `tests/`; project-specific unit tests are under `tests/unit/`.
- `API.md` is a retirement notice, not an endpoint reference; README.md covers operation and breaking upgrade/rollback steps; AGENTS.md covers dev guidelines.

## Build, Test, and Development Commands
- `composer install` — install PHP dependencies.
- `php spark serve` — run local app at `http://localhost:8080`.
- `php spark migrate` — apply database migrations.
- `composer test` or `vendor/bin/phpunit -c phpunit.xml.dist` — run PHPUnit suite.
- `php spark wa:worker --sleep 3 --limit 20` — process WhatsApp queue.
- `php spark wa:daily-summary-scheduler --time 15:00 --sleep 60` — enqueue tomorrow's agenda once per day at or after 15:00.
- `docker compose up -d --build` — run container stack.

## Coding Style & Naming Conventions
- Target PHP `^8.2`; follow PSR-12 with 4-space indentation.
- Use PSR-4 namespaces: `App\` for `app/`, `Config\` for `app/Config/`.
- Classes use `PascalCase`; methods and variables use `camelCase`.
- Keep views in `app/Views/`; prefer small controller methods and model/library helpers for reusable logic.
- Keep GOWA integration in `App\Libraries\GowaClient`; device/session management belongs only in the external GOWA dashboard. Do not add automatic device/session creation/start/delete or browser-side provider calls. Every provider request must explicitly select `GOWA_DEVICE_ID` with `X-Device-Id`.
- MeetingKu has no inbound meeting/rooms/WhatsApp API or API-key management. Do not restore removed routes, key services, filters, or unauthenticated replacements. Meeting/room CRUD uses the authenticated UI; internal notifications and the WhatsApp queue/worker/scheduler remain active.

## Configuration & Secrets
- Use `env` as template; local secrets go in `.env` only.
- Required deploy env includes `APP_BASE_URL`, DB settings, and `GOWA_USERNAME`/`GOWA_PASSWORD` (HTTP Basic Auth). `GOWA_BASE_URL` defaults to `https://gowa.kaltarastats.id`, `GOWA_DEVICE_ID` to case-sensitive `Meetingku`; `WA_DAILY_SUMMARY_GROUP_ID` is optional. App and worker need the same GOWA settings. `WAHA_*` is no longer used.
- Do not commit `.env`, generated sessions, logs, cache, upload contents, zip archives, or local tool context.
- Database config supports `database.defaultGroup`, `database.*.schema`, and development overrides.
- GOWA device must already exist and have both `is_connected` and `is_logged_in` true; connect it through `<GOWA_BASE_URL>/`. Old WAHA/Baileys auth is not migrated. Recreate/restart the long-running worker after code/config changes; recreate it when Compose environment changes.
- For the earlier provider cutover only, stop/remove an existing old `wa-sender` orphan after reviewing the orphan list; preserve old auth volumes/directories. If already retired, no further container deletion is needed. All four Compose services (proxy, app, worker, scheduler) remain necessary. Never use `down -v` or volume pruning as a migration step.
- API removal requires `php spark migrate`: `2026-10-23-000001_RemoveWhatsappApiKeys.php` drops the queue key FK/column and `wa_api_keys`, preserving queue/history and `wa_settings`. Back up the full database before upgrade, quiesce writers/worker/scheduler during cutover, and recreate services with the new code afterward. Historical migrations must remain unchanged.
- Migration rollback restores only empty key schema and a nullable queue key FK/column, not credentials or prior associations. These are unrecoverable without a pre-upgrade backup. Full rollback needs matching old code plus that backup; reconcile already-sent messages before restarting the restored queue. See README for deployment steps.
- Audit remediation requires forward migrations for Zoom token rotation, WhatsApp queue durability, and the meeting index. Quiesce writers before rotating tokens; never restore the exposed links. Reconcile uncertain WhatsApp deliveries before requeueing. See README for the upgrade and rollback procedure.

## Testing Guidelines
- Add focused tests for library/worker behavior under `tests/unit/`.
- Keep tests deterministic; avoid real WhatsApp/network calls in PHPUnit.
- Run `composer test` before commit when PHP code changes.
- For Docker changes, at minimum run `docker compose config`.

## Commit & PR Guidelines
- Use concise imperative subjects or Conventional Commits, e.g. `feat: add whatsapp worker`.
- Group related changes; avoid mixing unrelated UI, infra, and DB work unless part of one feature slice.
- PRs should include summary, setup/migration notes, verification commands, and screenshots for UI changes.
- For DB changes, include migration/rollback notes and required env values.

## Security Notes
- Public document root must remain `public/`.
- Validate all request input; keep CSRF protections enabled for forms.
- Treat `GOWA_USERNAME`/`GOWA_PASSWORD` as outbound provider credentials; rotate after exposure. They are not inbound MeetingKu API keys. Keep them backend-only and never expose them in views, URLs, logs, or browser requests/responses. Old database backups may contain removed MeetingKu key credentials and remain sensitive.
- Use valid TLS for GOWA and restrict API/dashboard access to trusted networks/VPN or deployment source allowlists. `/healthz` intentionally does not check GOWA connectivity; inspect the WhatsApp status page separately.
- Error responses use generic messages; never expose internal details to clients.
