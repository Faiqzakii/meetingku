# MeetingKu

MeetingKu adalah aplikasi manajemen rapat berbasis CodeIgniter 4 untuk menjadwalkan rapat, mengelola pegawai/ruangan, menyimpan tautan Zoom/manual, serta mengirim notifikasi WhatsApp grup melalui worker dan layanan WAHA eksternal.

## Fitur Utama

- Kalender dan daftar rapat mendatang/semua rapat.
- CRUD pegawai dan ruangan, termasuk impor pegawai dari spreadsheet.
- Tautan Zoom/manual meeting dan pengiriman ulang tautan.
- WhatsApp admin page untuk status sesi WAHA, tautan dashboard WAHA, API key MeetingKu, dan antrean pesan.
- Worker retry WhatsApp dengan backoff dan scheduler pengingat agenda besok pukul 15:00 WITA.
- Docker Compose untuk PHP-FPM, Nginx reverse proxy, worker, dan scheduler; WAHA dikelola terpisah.

## Stack

- PHP `^8.1`, CodeIgniter 4, Composer.
- PHPUnit 10 untuk test.
- MySQL/MariaDB lokal; PostgreSQL didukung untuk deploy container via env.
- UI server-rendered PHP views dengan Tailwind CDN, Bootstrap, Font Awesome, FullCalendar.
- WAHA eksternal di `https://waha.kaltarastats.id` untuk pengiriman WhatsApp.

## Struktur Penting

- `app/Controllers`, `app/Models`, `app/Views` — kode aplikasi utama.
- `app/Commands` — worker dan scheduler WhatsApp.
- `app/Database/Migrations` — migrasi tabel WhatsApp.
- `docker/`, `Dockerfile`, `docker-compose.yml` — deploy container.
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

MeetingKu memakai WAHA eksternal di `https://waha.kaltarastats.id`; tidak menjalankan gateway WhatsApp sendiri. Pengelolaan sesi **hanya** dilakukan melalui [dashboard WAHA](https://waha.kaltarastats.id/dashboard), bukan melalui MeetingKu.

Env backend (lokal di `.env`, deployment di secret/env platform):

```ini
WAHA_BASE_URL=https://waha.kaltarastats.id
WAHA_API_KEY=replace-with-plain-waha-api-token
WAHA_SESSION=default
WA_DAILY_SUMMARY_GROUP_ID=120363425375670792@g.us
```

`WAHA_API_KEY` wajib berupa **token asli/plain**, bukan hash SHA-512 dari konfigurasi WAHA. `WAHA_BASE_URL` dan `WAHA_SESSION` memiliki default seperti contoh. Key ini berbeda dari API key MeetingKu (`mku_`/`mkwa_`) dan hanya dikirim backend sebagai header `X-Api-Key` ke WAHA; jangan menaruhnya di UI, URL, atau request publik MeetingKu.

Sebelum menjalankan worker, buat/pilih sesi dengan nama persis `WAHA_SESSION` di dashboard WAHA, hubungkan akun WhatsApp di sana, dan pastikan statusnya **`WORKING`**. MeetingKu hanya membaca status dan mengirim teks: tidak membuat, memulai, menghapus, memasangkan, atau me-restart sesi. Halaman WhatsApp MeetingKu menyediakan status dan tautan dashboard; jika base URL diubah, tautan menggunakan `<WAHA_BASE_URL>/dashboard`.

Pastikan host/container app dan worker dapat menjangkau WAHA melalui HTTPS dengan sertifikat TLS valid. Batasi akses API/dashboard WAHA pada jaringan tepercaya/VPN atau allowlist sumber deployment; jangan mengekspos token ke browser. Pertahankan verifikasi TLS dan autentikasi WAHA.

Command aplikasi:

```bash
php spark wa:worker --sleep=3 --limit=20
php spark wa:daily-summary
php spark wa:daily-summary-scheduler --time=15:00 --sleep=60
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
WAHA_BASE_URL=https://waha.kaltarastats.id
WAHA_API_KEY=replace-with-plain-waha-api-token
WAHA_SESSION=default
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

### Migrasi dari gateway lama ke WAHA

1. Siapkan sesi WAHA melalui dashboard hingga `WORKING`. Auth/session Baileys lama **tidak dimigrasikan**; lakukan pairing/login baru di WAHA.
2. Ganti `WA_SENDER_URL`/`WA_SENDER_SECRET` dengan konfigurasi `WAHA_*` di secret/env platform. Terapkan nilai yang sama pada app dan worker. Tidak ada migrasi database; antrean, retry/backoff, dan API publik MeetingKu tetap dipakai.
3. Rebuild/recreate app dan worker agar kode dan environment baru dimuat. Worker merupakan proses panjang; restart/recreate wajib setelah perubahan konfigurasi (restart saja tidak menerapkan environment Compose yang berubah):

   ```bash
   docker compose up -d --build --force-recreate meetingku-app meetingku-worker
   docker compose up -d --remove-orphans
   ```

   Perintah kedua menghentikan dan menghapus container orphan `wa-sender` dari project Compose yang sama, **tanpa menghapus volume auth lama**. Jika gateway dahulu dijalankan terpisah/di project lain, hentikan container tersebut secara eksplisit di platform deployment agar tidak terus berjalan. Jangan gunakan `down -v`, `docker volume prune`, atau hapus direktori auth/session lama saat cutover; simpan untuk kebutuhan operasional/rollback.
4. Pastikan gateway lama tidak berjalan, halaman WhatsApp menampilkan status WAHA, dan worker memproses antrean yang ada. Compose tidak lagi mendeklarasikan service gateway atau volume auth, tetapi volume lama tetap ada hingga dihapus manual oleh operator.


### Healthcheck Coolify

Compose mendeklarasikan healthcheck untuk `reverse-proxy` dan `meetingku-app`:

- `GET /healthz` pada port `80` mengembalikan HTTP `200` dengan body `ok` setelah request melewati Nginx, PHP-FPM, dan bootstrap CodeIgniter. Endpoint publik ini tidak memerlukan login dan tidak memeriksa database atau koneksi WhatsApp.
- `reverse-proxy` memanggil endpoint tersebut setiap 30 detik, dengan timeout 5 detik, masa startup 30 detik, dan 3 kegagalan sebelum `unhealthy`.
- `meetingku-app` memeriksa listener PHP-FPM di port `9000`; reverse proxy menunggu status `healthy` sebelum mulai. Pemeriksaan HTTP pada reverse proxy tetap menjadi bukti bahwa aplikasi dapat melayani request.
- Worker dan scheduler menonaktifkan healthcheck PHP-FPM bawaan image karena keduanya menjalankan proses CLI, bukan FPM. Statusnya tetap berbasis proses; healthcheck ini tidak mengklaim kesehatan pemrosesan antrean.

Setelah perubahan, rebuild dan recreate service di Coolify agar konfigurasi healthcheck diterapkan. Jika menggunakan pengaturan HTTP healthcheck Coolify, pilih method `GET`, path `/healthz`, port `80`, dan expected status `200` pada service `reverse-proxy`. Jangan arahkan probe HTTP ke port FastCGI `9000`.

Verifikasi di server deployment:

```bash
docker compose config --quiet
docker compose up -d --build --force-recreate
docker compose ps
docker compose exec reverse-proxy wget -q -O - http://127.0.0.1/healthz
```

Output probe yang diharapkan: `ok`. `unhealthy` tidak otomatis me-restart container; restart policy `unless-stopped` berlaku saat proses container berhenti.

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

## Public API

MeetingKu menyediakan REST API untuk integrasi eksternal:

- **Autentikasi:** Header `X-API-KEY` (wajib, tidak bisa via query parameter)
- **Rate Limiting:** 60 request per menit per IP
- **Endpoints:** CRUD meetings, rooms, WhatsApp messages

Dokumentasi lengkap: [API.md](API.md)

Contoh quick start:
```bash
curl -H "X-API-KEY: mku_your_key_here" \
  https://meetingku.example.com/api/meetings
```

## Catatan Keamanan

- Jangan commit `.env`, session, log, cache, zip build, atau kredensial deploy.
- Public webroot harus `public/`, bukan root project.
- API key WhatsApp dan Meeting hanya untuk integrasi tepercaya; rotasi jika pernah terekspos.
- API key **hanya** via header `X-API-KEY`, tidak bisa via URL query parameter.
- Pastikan `writable/` bisa ditulis runtime tetapi tidak dipublikasikan langsung.
- Failed API auth attempts di-log dengan IP address.
