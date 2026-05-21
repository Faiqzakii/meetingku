# MeetingKu

MeetingKu adalah aplikasi manajemen rapat berbasis CodeIgniter 4 untuk menjadwalkan rapat, mengelola pegawai/ruangan, menyimpan tautan Zoom/manual, serta mengirim notifikasi WhatsApp grup melalui worker dan gateway internal.

## Fitur Utama

- Kalender dan daftar rapat mendatang/semua rapat.
- CRUD pegawai dan ruangan, termasuk impor pegawai dari spreadsheet.
- Tautan Zoom/manual meeting dan pengiriman ulang tautan.
- WhatsApp admin page untuk status koneksi, QR/pairing code, API key, dan antrean pesan.
- Worker retry WhatsApp dengan backoff dan scheduler ringkasan harian 07:00 WITA.
- Docker Compose untuk PHP-FPM, Nginx reverse proxy, worker, scheduler, dan Node `wa-sender`.

## Stack

- PHP `^8.1`, CodeIgniter 4, Composer.
- PHPUnit 10 untuk test.
- MySQL/MariaDB lokal; PostgreSQL didukung untuk deploy container via env.
- UI server-rendered PHP views dengan Tailwind CDN, Bootstrap, Font Awesome, FullCalendar.
- Node.js service `wa-sender` memakai Baileys untuk koneksi WhatsApp internal.

## Struktur Penting

- `app/Controllers`, `app/Models`, `app/Views` — kode aplikasi utama.
- `app/Commands` — worker dan scheduler WhatsApp.
- `app/Database/Migrations` — migrasi tabel WhatsApp.
- `docker/`, `Dockerfile`, `docker-compose.yml` — deploy container.
- `wa-sender/` — service Node internal untuk WhatsApp.
- `writable/` — runtime logs/cache/session/uploads; tidak untuk commit.

## Setup Lokal

1. Install dependency PHP:

   ```bash
   composer install
   ```

2. Buat konfigurasi lokal:

   ```bash
   cp env .env
   ```

3. Atur minimal `.env`:

   ```ini
   CI_ENVIRONMENT = development
   app.baseURL = 'http://localhost:8080/'

   database.defaultGroup = default
   database.default.hostname = localhost
   database.default.database = meetingku
   database.default.username = root
   database.default.password =
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

4. Jalankan migrasi jika memakai schema baru:

   ```bash
   php spark migrate
   ```

5. Jalankan server lokal:

   ```bash
   php spark serve
   ```

   Buka `http://localhost:8080`.

## WhatsApp Gateway

Untuk fitur WhatsApp lokal, jalankan `wa-sender` terpisah atau gunakan Docker Compose.

Env penting:

```ini
WA_SENDER_URL=http://localhost:3001
WA_SENDER_SECRET=change-me
WA_DAILY_SUMMARY_GROUP_ID=120363425375670792@g.us
```

Command aplikasi:

```bash
php spark wa:worker --sleep=3 --limit=20
php spark wa:daily-summary
php spark wa:daily-summary-scheduler --time=07:00 --sleep=60
```

## Docker / Coolify

Compose memakai external network `coolify` dan env wajib dari platform deploy.

Env minimal:

```ini
APP_PROXY_FQDN=domain.example
APP_BASE_URL=https://domain.example
DB_HOST=postgres-host
DB_DATABASE=meetingku
DB_USERNAME=meetingku
DB_PASSWORD=secret
DB_DRIVER=Postgre
DB_PORT=5432
DB_SCHEMA=public
WA_SENDER_SECRET=secret-internal
WA_DAILY_SUMMARY_GROUP_ID=120363425375670792@g.us
```

Jalankan:

```bash
docker compose up -d --build
```

Service utama:

- `reverse-proxy` — Nginx exposed port 80.
- `meetingku-app` — PHP-FPM app.
- `meetingku-worker` — proses antrean WhatsApp.
- `meetingku-scheduler` — ringkasan harian.
- `wa-sender` — service internal WhatsApp.

## Import Data MySQL ke PostgreSQL

Gunakan saat aplikasi lama masih memakai MySQL dan deployment baru memakai PostgreSQL. Jalankan migrasi PostgreSQL dulu agar schema target tersedia, lalu import data dari dump MySQL.

1. Buat dump data dari server MySQL lama:

   ```bash
   mysqldump --no-create-info --skip-triggers --single-transaction \
     -u USER -p DATABASE_NAME > mysql-data.sql
   ```

2. Jalankan migrasi di PostgreSQL target:

   ```bash
   php spark migrate
   ```

3. Pastikan `.env` target PostgreSQL sudah benar. Command memakai konfigurasi database CodeIgniter dari `.env`:

   ```ini
   database.defaultGroup = default
   database.default.hostname = HOST
   database.default.database = meetingku
   database.default.username = meetingku_user
   database.default.password = PASSWORD
   database.default.DBDriver = Postgre
   database.default.port = 5432
   database.default.schema = public
   ```

4. Cek isi dump tanpa menulis ke DB:

   ```bash
   php spark db:import-mysql-dump \
     --file mysql-data.sql \
     --dry-run
   ```

5. Import ke PostgreSQL sesuai `.env`:

   ```bash
   php spark db:import-mysql-dump \
     --file mysql-data.sql \
     --truncate
   ```

   Jika ingin memakai group selain default:

   ```bash
   php spark db:import-mysql-dump --file mysql-data.sql --group production --truncate
   ```

Catatan:

- Command hanya membaca `INSERT` untuk tabel MeetingKu yang dikenal: `pegawai`, `ruangan`, `meeting`, `wa_settings`, `wa_api_keys`, `wa_message_queue`.
- `--truncate` menghapus isi tabel target dulu; jangan pakai jika target sudah berisi data baru.
- ID lama dipertahankan dan sequence PostgreSQL di-reset setelah import.
- Database target selalu dari konfigurasi CodeIgniter/`.env`; tidak perlu tulis password di command.
- Jangan commit file dump SQL; simpan sementara di server lalu hapus setelah import.

## Testing

```bash
composer test
```

Atau:

```bash
vendor/bin/phpunit -c phpunit.xml.dist
```

## Catatan Keamanan

- Jangan commit `.env`, session, log, cache, zip build, atau kredensial deploy.
- Public webroot harus `public/`, bukan root project.
- API key WhatsApp hanya untuk integrasi tepercaya; rotasi jika pernah terekspos.
- Pastikan `writable/` bisa ditulis runtime tetapi tidak dipublikasikan langsung.
