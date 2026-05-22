# Repository Guidelines

## Project Structure & Module Organization
- CodeIgniter 4 app lives in `app/` (`Controllers`, `Models`, `Views`, `Config`, `Commands`, `Database/Migrations`).
- Public webroot is `public/`; configure web server/Nginx to point here only.
- Runtime state belongs in `writable/` and `public/writable/`; do not commit logs/cache/session/uploads.
- WhatsApp gateway code is split between PHP (`app/Commands`, `app/Libraries`, `app/Models`) and Node service `wa-sender/`.
- Deploy assets live in `Dockerfile`, `docker-compose.yml`, and `docker/nginx/`.
- Tests live in `tests/`; project-specific unit tests are under `tests/unit/`.

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
- Keep Node gateway code in `wa-sender/src/`; avoid coupling UI directly to Baileys internals.

## Configuration & Secrets
- Use `env` as template; local secrets go in `.env` only.
- Required deploy env includes `APP_BASE_URL`, DB settings, `WA_SENDER_SECRET`, and optional `WA_DAILY_SUMMARY_GROUP_ID`.
- Do not commit `.env`, generated sessions, logs, cache, upload contents, zip archives, or local tool context.
- Database config supports `database.defaultGroup`, `database.*.schema`, and development overrides.

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
- Treat WhatsApp API keys and sender secrets as credentials; rotate after exposure.
- Never expose `wa-sender` publicly; it should stay on an internal network behind PHP app auth.
