# Meetingku — Meeting Room Scheduling (CodeIgniter 4)

Meetingku is a simple meeting room scheduling application built with CodeIgniter 4. It supports authentication, role‑based admin actions, room and employee management, and meeting creation with calendar and upcoming views. Optional WhatsApp notification integration is available via Saungwa.

• Demo routes: `/` or `/meeting/calendar` (calendar), `/upcoming` (upcoming), `/auth/login` (login)

## Features

- Authentication: login/logout with hashed passwords; admin role
- Meetings: create/update/delete, status workflow (pending/approved/rejected/cancelled)
- Views: calendar, today/upcoming, and admin all‑meetings by date range
- Rooms (Ruangan): CRUD with type Online/Offline/Hybrid; prevent deletion if approved meetings exist
- Employees (Pegawai): CRUD, password hashing, Excel import, downloadable template
- CLI: `php spark auth:create-admin` to bootstrap the first admin
- Notifications: optional Saungwa API integration via environment variables

## Tech Stack

- PHP 8.1+, CodeIgniter 4.x
- Dependencies: `phpoffice/phpspreadsheet`, `vlucas/phpdotenv`
- Testing: PHPUnit 10 (coverage to `build/logs/`)

## Project Structure

- Source: `app/` (controllers, models, views, config)
- Public webroot: `public/` (configure your server to point here)
- Tests: `tests/` (helpers under `tests/_support/`)
- Runtime: `writable/` (logs, cache, uploads)
- Composer deps: `vendor/`; sample DB: `meetingku.sql`

## Requirements

- PHP 8.1+ with extensions: intl, mbstring, json, curl, mysqlnd
- A MySQL-compatible database (migrations use ENUM)

## Setup

1) Install dependencies

- `composer install`

2) Configure environment

- Copy `env` to `.env` and set at minimum:
  - `app.baseURL` (e.g., `http://localhost:8080`)
  - Database: `database.default.*` (hostname, database, username, password, DBDriver)
  - Optional Saungwa:
    - `saungwa.enabled=true`
    - `saungwa.url=https://app.saungwa.com/api/create-message`
    - `saungwa.appkey=...`
    - `saungwa.authkey=...`
    - `saungwa.to=...`
    - `saungwa.template_id=...`
    - `saungwa.template_id_update=...`

3) Prepare writable directories

- Ensure `writable/` and `build/` are writable by the web/PHP user (coverage writes to `build/logs/`).

## Database

You can start with the provided sample or run migrations.

- Option A: import sample
  - Import `meetingku.sql` into your database.

- Option B: run migrations
  - `php spark migrate`
  - Creates tables: `pegawai`, `ruangan`, `meeting` with proper FKs and timestamps.

## Bootstrapping Admin

Create the initial admin account via CLI:

- `php spark auth:create-admin`

The command prompts for username, NIP (18 digits), password, and full name, then persists an admin user.

## Running the App

- Local dev server: `php spark serve` then open `http://localhost:8080`
- Web server: set document root to `public/` (not the project root)
- Default routes:
  - `/` → `MeetingController::calendar`
  - `/upcoming` → upcoming list
  - `/auth/login` → login form

## Key Routes

- Auth
  - `GET /auth/login`, `POST /auth/login`, `GET /auth/logout`
- Meeting
  - `GET /meeting/calendar`, `GET /meeting/upcoming`, `GET /meeting/all` (admin)
  - `POST /meeting/create`
  - `GET /meeting/edit/{id}`
  - `POST /meeting/update/{id}`
  - `POST /meeting/delete/{id}`
  - `POST /meeting/status/{id}` (admin)
- Pegawai
  - `GET /pegawai` (auth)
  - `POST /pegawai/create` (admin)
  - `GET /pegawai/edit/{id}` (admin)
  - `PUT /pegawai/update/{id}` or `POST /pegawai/update/{id}` (admin)
  - `POST /pegawai/delete/{id}` (admin)
  - `POST /pegawai/import` (admin). Upload `.xlsx` matching the template
  - `GET /pegawai/downloadTemplate` (download Excel template)
- Ruangan
  - `GET /ruangan` (auth)
  - `POST /ruangan/create` (admin)
  - `GET /ruangan/edit/{id}` (admin)
  - `POST /ruangan/update/{id}` (admin)
  - `POST /ruangan/delete/{id}` (admin)

## Testing

- Run all tests: `composer test` or `vendor/bin/phpunit -c phpunit.xml.dist`
- Coverage, JUnit, and TestDox output to `build/logs/`

## Excel Import Notes

- Template columns: Nama, NIP, Username, Password, Admin (Ya/Tidak)
- Download template at `GET /pegawai/downloadTemplate`
- Import file at `POST /pegawai/import` (admin, `.xlsx` only)

## Configuration & Security

- Keep secrets in `.env` and never commit it
- Set `app.baseURL` to your public URL
- Configure your web server to use `public/` as document root
- Ensure `writable/` is writable and review `writable/logs/` during development
- Consider enabling CSRF in `Config\Security` and adjusting as needed

## License

MIT. See `LICENSE`.

## Contributing

- PHP `^8.1`, PSR‑12, 4‑space indentation
- Namespaces: `App\` (app/), `Config\` (app/Config/)
- Run `composer test` and ensure passing tests before PRs
