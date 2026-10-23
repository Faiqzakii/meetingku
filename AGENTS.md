# Repository Guidelines

## Project Structure & Module Organization
- CodeIgniter 4 app lives in `app/` (`Controllers`, `Models`, `Views`, `Config`, `Commands`, `Database/Migrations`).
- Public webroot is `public/`; configure web server/Nginx to point here only.
- Runtime state belongs in `writable/` and `public/writable/`; do not commit logs/cache/session/uploads.
- WhatsApp queue/worker and external WAHA adapter live in PHP (`app/Commands`, `app/Libraries`, `app/Models`); no bundled gateway is deployed.
- Deploy assets live in `Dockerfile`, `docker-compose.yml`, and `docker/nginx/`.
- Tests live in `tests/`; project-specific unit tests are under `tests/unit/`.
- API documentation in `API.md`; README.md for general overview; AGENTS.md for dev guidelines.

## Build, Test, and Development Commands
- `composer install` — install PHP dependencies.
- `php spark serve` — run local app at `http://localhost:8080`.
- `php spark migrate` — apply database migrations.
- `composer test` or `vendor/bin/phpunit -c phpunit.xml.dist` — run PHPUnit suite.
- `php spark wa:worker --sleep=3 --limit=20` — process WhatsApp queue.
- `php spark wa:daily-summary-scheduler --time=15:00 --sleep=60` — run scheduler that sends tomorrow's agenda at 15:00.
- `docker compose up -d --build` — run container stack.

## Coding Style & Naming Conventions
- Target PHP `^8.1`; follow PSR-12 with 4-space indentation.
- Use PSR-4 namespaces: `App\` for `app/`, `Config\` for `app/Config/`.
- Classes use `PascalCase`; methods and variables use `camelCase`.
- Keep views in `app/Views/`; prefer small controller methods and model/library helpers for reusable logic.
- Keep WAHA integration in `App\Libraries\WahaClient`; session management belongs only in the external WAHA dashboard. Do not add automatic session creation/start/delete or browser-side provider calls.

## Configuration & Secrets
- Use `env` as template; local secrets go in `.env` only.
- Required deploy env includes `APP_BASE_URL`, DB settings, and `WAHA_API_KEY` (plain WAHA token, not SHA-512 hash). `WAHA_BASE_URL` defaults to `https://waha.kaltarastats.id`, `WAHA_SESSION` to `default`; `WA_DAILY_SUMMARY_GROUP_ID` is optional. App and worker need the same WAHA settings.
- Do not commit `.env`, generated sessions, logs, cache, upload contents, zip archives, or local tool context.
- Database config supports `database.defaultGroup`, `database.*.schema`, and development overrides.
- WAHA session must already exist and be `WORKING`; connect it through `<WAHA_BASE_URL>/dashboard`. Old Baileys auth is not migrated. Recreate/restart the long-running worker after code/config changes; recreate it when Compose environment changes.
- During cutover stop/remove the old gateway orphan (`docker compose up -d --remove-orphans` for the same project), but preserve old auth volumes/directories. Never use `down -v` or volume pruning as a migration step. No database migration is required for this provider switch.

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
- Treat MeetingKu API keys and `WAHA_API_KEY` as credentials; rotate after exposure. Provider credentials must remain backend-only and never appear in views, URLs, logs, or public API requests/responses.
- Use valid TLS for WAHA and restrict API/dashboard access to trusted networks/VPN or deployment source allowlists. `/healthz` intentionally does not check WAHA connectivity; inspect the WhatsApp status page separately.
- API keys (Meeting & WhatsApp) only accepted via `X-API-KEY` header, never query parameters.
- API routes have rate limiting (60 req/min) via Throttler filter.
- Failed API auth attempts are logged with IP address.
- Error responses use generic messages; never expose internal details to clients.
