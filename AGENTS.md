# Repository Guidelines

## Project Structure & Module Organization
- Source code in `app/` (controllers, models, views, config).
- Public webroot is `public/` (index.php, assets). Point your server here.
- Tests in `tests/` with helpers in `tests/_support/`.
- Runtime files in `writable/` (logs, cache, uploads). Do not commit contents.
- Composer dependencies in `vendor/`; sample database in `meetingku.sql`.

## Build, Test, and Development Commands
- `composer install` — install PHP dependencies.
- `composer update` — update dependencies to allowed versions.
- `php spark serve` — run local dev server at `http://localhost:8080`.
- `composer test` or `vendor/bin/phpunit -c phpunit.xml.dist` — run tests and produce coverage in `build/logs/` (HTML, Clover, JUnit).
- Optional: `php spark migrate` — run database migrations if migrations are defined.

## Coding Style & Naming Conventions
- Target PHP `^8.1`. Follow PSR-12; use 4-space indentation.
- PSR-4 namespaces: `App\` in `app/`, `Config\` in `app/Config/`.
- Classes `PascalCase` (e.g., `App\Controllers\Home`, `App\Models\UserModel`); methods `camelCase`.
- Views live in `app/Views/` and are referenced from controllers; keep filenames consistent and descriptive.
- Keep secrets and environment-specific values in `.env`, not in code.

## Testing Guidelines
- PHPUnit 10 configured via `phpunit.xml.dist`; tests reside under `tests/` and end with `*Test.php`.
- Use `Tests\Support\` utilities from `tests/_support/` when helpful.
- Coverage reports and logs write to `build/logs/`; ensure the project can create directories under `build/`.
- Run `composer test` and ensure passing tests before opening a PR.

## Commit & Pull Request Guidelines
- Use clear, imperative subjects (e.g., `Add meeting export controller`).
- Group related changes; avoid unrelated edits and noisy formatting-only diffs.
- PRs should include: concise description, linked issue (if any), setup/testing notes, and screenshots for UI changes.
- For DB changes, include migration steps and rollback notes.

## Security & Configuration Tips
- Copy `env` to `.env`; set `app.baseURL` and database credentials. Never commit `.env`.
- Configure your web server to use `public/` as the document root.
- Enable and respect CSRF protections (`Config\Security`); validate all inputs.
- Verify permissions for `writable/`; review logs in `writable/logs/` during development.
