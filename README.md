# MeetingKu

MeetingKu adalah aplikasi manajemen rapat berbasis CodeIgniter 4 untuk menjadwalkan rapat, mengelola pegawai/ruangan, menyimpan tautan Zoom/manual, serta mengirim notifikasi WhatsApp grup melalui worker dan layanan GOWA eksternal.

## Fitur Utama

- Kalender dan daftar rapat mendatang/semua rapat.
- CRUD pegawai dan ruangan, termasuk impor pegawai dari spreadsheet.
- Tautan Zoom/manual meeting dan pengiriman ulang tautan.
- WhatsApp admin page untuk status perangkat GOWA, tautan dashboard GOWA, dan riwayat antrean pesan.
- Worker WhatsApp dengan retry/backoff, rekonsiliasi hasil kirim yang tidak pasti, dan ringkasan agenda besok mulai pukul 15:00 WITA.
- Docker Compose untuk PHP-FPM, Nginx reverse proxy, worker, dan scheduler; GOWA dikelola terpisah.

## Stack

- PHP `^8.2`, CodeIgniter 4, Composer.
- PHPUnit 10 untuk test.
- MySQL/MariaDB lokal; PostgreSQL didukung untuk deploy container via env.
- UI server-rendered PHP dengan Bootstrap dan Font Awesome. FullCalendar, Moment, dan utility Tailwind hanya dimuat di kalender.
- GOWA eksternal di `https://gowa.kaltarastats.id`, device ID `Meetingku`, untuk pengiriman WhatsApp.

## Struktur Penting

- `app/Controllers`, `app/Models`, `app/Views` — kode aplikasi utama.
- `app/Commands` — worker dan scheduler WhatsApp.
- `app/Database/Migrations` — migrasi skema dan data aplikasi.
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

4. Jalankan migrasi (backup database terlebih dahulu untuk upgrade instalasi lama; lihat catatan breaking change di bawah):

   ```bash
   php spark migrate
   ```

5. Jalankan server lokal:

   ```bash
   php spark serve
   ```

   Buka `http://localhost:8080`.

### Agenda Ruangan

Dari Kalender, pilih ruangan, klik **Terapkan**, lalu buka **Agenda Ruangan**. Tautannya bisa dibagikan dan dibuka tanpa login. Tanggal bawaan adalah hari ini jika tautan tidak menyertakan `?date=YYYY-MM-DD`.

Pengunjung hanya melihat rapat yang disetujui, tanpa nama pemohon. Pegawai yang sudah login melihat semua status. Tambah, edit, dan hapus ruangan tetap khusus admin.

## WhatsApp Gateway

MeetingKu memakai GOWA eksternal di `https://gowa.kaltarastats.id` dengan device ID **`Meetingku`** (peka huruf besar/kecil). Pengelolaan perangkat dan pairing dilakukan melalui [dashboard GOWA](https://gowa.kaltarastats.id/).

Env backend (lokal di `.env`, deployment di secret/env platform):

```ini
GOWA_BASE_URL=https://gowa.kaltarastats.id
GOWA_USERNAME=replace-with-gowa-basic-auth-username
GOWA_PASSWORD=replace-with-gowa-basic-auth-password
GOWA_DEVICE_ID=Meetingku
WA_DAILY_SUMMARY_GROUP_ID=120363425375670792@g.us
```

`GOWA_USERNAME` dan `GOWA_PASSWORD` wajib diisi dengan kredensial **HTTP Basic Auth GOWA**. Keduanya dikirim backend melalui autentikasi HTTP, bukan token WAHA atau API key MeetingKu. Jangan menaruh kredensial di UI, URL, log, atau request publik MeetingKu. `GOWA_BASE_URL` dan `GOWA_DEVICE_ID` memiliki default seperti contoh; konfigurasi `WAHA_*` tidak lagi digunakan. URL harus HTTPS tanpa username/password, query, atau fragment; jika server memakai base path, sertakan pada `GOWA_BASE_URL`.

Sebelum menjalankan worker, pastikan perangkat `Meetingku` sudah login dan terhubung di dashboard GOWA. MeetingKu membaca `GET /app/status` dan mengirim teks melalui `POST /send/message` dengan payload `phone` dan `message`; kedua request selalu memakai header **`X-Device-Id: Meetingku`** (atau nilai `GOWA_DEVICE_ID`). Pemilihan perangkat di browser GOWA tidak mengatur perangkat untuk worker. Status siap kirim memerlukan `is_connected=true` dan `is_logged_in=true`. ID dari `results.message_id` disimpan sebagai ID provider dalam antrean. Nomor personal menggunakan `@s.whatsapp.net` (tujuan lama `@c.us` dikonversi), sedangkan ID grup `@g.us` dipertahankan. Lihat [kontrak API resmi GOWA](https://github.com/aldinokemal/go-whatsapp-web-multidevice/blob/main/docs/openapi.yaml).

MeetingKu hanya membaca status dan mengirim teks; pengelolaan perangkat tetap di dashboard eksternal. Tautan dashboard menggunakan `<GOWA_BASE_URL>/`. Pastikan app dan worker dapat menjangkau GOWA melalui HTTPS dengan sertifikat TLS valid, dan batasi akses API/dashboard pada jaringan tepercaya/VPN atau allowlist sumber deployment.

Halaman WhatsApp menampilkan **Jadwal kirim** (`scheduled_at`, termasuk jadwal ulang setelah gagal) dan **Waktu terkirim** (`sent_at`, diisi setelah GOWA mengonfirmasi pengiriman). Tanda `–` berarti waktu belum tercatat.

Command aplikasi:

```bash
php spark wa:worker --sleep 3 --limit 20
php spark wa:daily-summary
php spark wa:daily-summary-scheduler --time 15:00 --sleep 60
```

Scheduler mengejar jadwal jika hidup kembali setelah jam mulai pada hari yang sama. Enqueue otomatis hanya sekali per tanggal kirim. `wa:daily-summary --force` menambahkan satu pesan ekstra; setelah itu run tanpa `--force` tetap tidak menggandakan ringkasan.

Pesan `processing` yang klaimnya kedaluwarsa (lima menit), atau yang hasil kirimnya tidak pasti, masuk `needs_review`. Worker tidak mengirimnya ulang sendiri. Dari host yang tepercaya:

```bash
php spark wa:queue-reconcile list
php spark wa:queue-reconcile show ID
php spark wa:queue-reconcile confirm-sent ID --provider-id PROVIDER_ID
php spark wa:queue-reconcile confirm-unsent-and-requeue ID
```

Gunakan `list --after ID` untuk halaman berikutnya. Cocokkan pesan dengan riwayat WhatsApp/GOWA sebelum konfirmasi. `--provider-id` opsional; `confirm-sent` mencatat waktu rekonsiliasi, bukan waktu pengiriman yang tidak diketahui. Hentikan semua worker sebelum `confirm-unsent-and-requeue` dan jalankan hanya setelah dipastikan pesan memang belum terkirim. Biarkan item dalam `needs_review` jika hasilnya masih tidak jelas.

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
GOWA_BASE_URL=https://gowa.kaltarastats.id
GOWA_USERNAME=replace-with-gowa-basic-auth-username
GOWA_PASSWORD=replace-with-gowa-basic-auth-password
GOWA_DEVICE_ID=Meetingku
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

Keempat service tersebut masih diperlukan dan tetap aktif setelah penghapusan API inbound. Tidak ada container tambahan yang perlu dihapus untuk perubahan ini; pertahankan volume `writable_data` dan volume/direktori auth lama.

### Upgrade perbaikan audit

1. Pastikan semua runtime memakai PHP 8.2 atau lebih baru. Siapkan backup database yang sudah diuji pulih. Batasi akses aplikasi dan hentikan worker serta scheduler lama sebelum migrasi. Jika deployment lama masih memuat `public.zip`, blokir `/public.zip` dan `/writable/` di proxy sekarang, bukan setelah build selesai. Tinjau log akses, image lama, dan riwayat Git untuk paparan; cabut sesi atau rotasi kredensial yang diduga terdampak.
2. Build image baru, jalankan migrasi dari service app, lalu recreate keempat service. Jangan jalankan penulis lama bersamaan, dan jangan menghapus volume:

   ```bash
   docker compose build
   docker compose run --rm --no-deps meetingku-app php spark migrate
   docker compose up -d --force-recreate
   ```

3. Migrasi merotasi token host untuk meeting yang belum berakhir. Pesan yang belum tuntas di antrean memakai link baru; tautan lama yang sudah dikirim tidak berlaku. Pemilik/admin dapat menyalin link baru dari **Meeting saya** dan membagikannya kembali hanya ke host yang dituju.
4. Migrasi antrean mempertahankan riwayat dan penanda ringkasan lama. Pada MySQL, konversi tabel antrean ke InnoDB bila perlu dapat membangun ulang tabel. Periksa `/healthz`, login HTTPS, akses kalender, dan `wa:queue-reconcile list` sebelum membuka kembali akses.

**Rollback:** hentikan penulisan, worker, dan scheduler. Periksa `php spark migrate:status`, lalu kembalikan skema antrean dan indeks ke batch tepat sebelum upgrade audit. Rekonsiliasi `needs_review` sebelum berpindah karena worker lama tidak memproses status itu. Tanggal ringkasan terakhir dikembalikan ke penanda lama. Rotasi token sengaja tidak dibalik. Jangan membuka kembali versi yang menampilkan token di kalender publik atau image yang menyajikan arsip sesi. Rekonsiliasi pesan yang terlanjur terkirim sebelum menyalakan worker jika database dipulihkan dari backup.

Kalender mengambil rentang hari, minggu, atau bulan yang dibuka. Daftar meeting menampilkan 20 baris per halaman; laporan CSV dan cetak mengambil seluruh hasil filter, dengan periode maksimal 366 hari. Impor pegawai menerima XLSX maksimal 2 MiB, lembar pertama, kolom A–F, dan 250 baris data. Isi NIP dan nomor HP sebagai teks.

### Penghapusan API inbound dan API key MeetingKu

**Breaking change:** seluruh API inbound meeting, ruangan, dan pesan/antrean WhatsApp beserta pengelolaan key MeetingKu telah dihapus. Endpoint lama tidak dijadikan publik tanpa autentikasi dan tidak memiliki pengganti publik. Hentikan integrasi pemanggil API lama sebelum upgrade. UI CRUD meeting/ruangan, Zoom, notifikasi internal, status/dashboard GOWA, antrean/riwayat, worker, scheduler, dan `/healthz` tetap tersedia.

1. Jadwalkan maintenance dan hentikan penulisan melalui UI serta proses worker/scheduler sementara. Backup database lengkap (schema dan data) sebelum migrasi; simpan backup secara aman di luar repository. Pastikan backup dapat dipulihkan, termasuk tabel `wa_api_keys` dan relasi antrean lama. Jangan commit dump atau kredensial.
2. Deploy kode/image baru tanpa menjalankan kode lama bersamaan saat schema berubah. Jalankan migrasi menggunakan konfigurasi database target:

   ```bash
   php spark migrate
   ```

   Untuk Compose, setelah image baru dibangun, jalankan migrasi dari service app dengan environment deployment:

   ```bash
   docker compose build
   docker compose run --rm --no-deps meetingku-app php spark migrate
   docker compose up -d --force-recreate
   ```

3. Migrasi baru `2026-10-23-000001_RemoveWhatsappApiKeys.php` menghapus foreign key `wa_message_queue.api_key_id`, kolom tersebut, lalu tabel `wa_api_keys`. Baris antrean/riwayat, status retry, dan `wa_settings` dipertahankan. Kredensial key dan asosiasi key ke antrean yang dihapus **tidak dapat dipulihkan tanpa backup pra-migrasi**. Simpan `GOWA_USERNAME` dan `GOWA_PASSWORD` di secret/env platform untuk pengiriman outbound dan pembacaan status GOWA.
4. Aktifkan kembali akses UI dan keempat service. Periksa login/CRUD, halaman WhatsApp, pemrosesan antrean, scheduler, dan `/healthz` setelah deployment. Penghapusan API tidak memerlukan penghapusan container atau volume.

**Rollback:** `down()` migrasi hanya membuat ulang schema key kosong dan kolom/FK antrean yang nullable; tidak mengembalikan key maupun asosiasi sebelumnya. Rollback schema saja juga tidak mengembalikan route API yang dihapus. Untuk pemulihan penuh, hentikan penulisan/worker/scheduler, pulihkan backup pra-upgrade yang sesuai, lalu deploy kode lama yang cocok dengan schema tersebut. Restore backup mengembalikan antrean ke keadaan saat backup; rekonsiliasi pesan yang sudah terkirim sejak backup sebelum menyalakan worker agar tidak terjadi pengiriman ulang. Simpan volume auth/session; jangan gunakan `down -v` atau volume pruning.

### Migrasi dari WAHA ke GOWA

1. Pastikan device ID `Meetingku` sudah login dan terhubung di dashboard GOWA. Sesi/auth WAHA tidak dipindahkan oleh aplikasi.
2. Hentikan worker sementara dan tunggu pengiriman aktif selesai. Ganti `WAHA_*` dengan `GOWA_BASE_URL`, `GOWA_USERNAME`, `GOWA_PASSWORD`, dan `GOWA_DEVICE_ID` di secret/env platform. Terapkan nilai yang sama pada app dan worker. Pergantian provider sendiri tidak memerlukan migrasi database; jika penghapusan API/key belum diterapkan, migrasi terpisah di atas **tetap wajib**. Antrean, riwayat, dan retry/backoff tetap dipakai. Jangan menjalankan worker WAHA dan GOWA bersamaan pada antrean yang sama.
3. Rebuild/recreate app dan worker agar kode dan environment baru dimuat. Worker merupakan proses panjang; restart/recreate wajib setelah perubahan konfigurasi (restart saja tidak menerapkan environment Compose yang berubah):

   ```bash
   docker compose up -d --build --force-recreate meetingku-app meetingku-worker
   ```

   Tidak perlu menghapus container gateway eksternal atau volume. Pertahankan keempat service Compose. Jika orphan `wa-sender` dari cutover Baileys terdahulu masih ada, tinjau daftar orphan sebelum menghentikan/menghapus hanya orphan tersebut dan simpan volume/direktori auth lama. Jangan gunakan `down -v` atau volume pruning.
4. Pastikan halaman WhatsApp menampilkan `CONNECTED` untuk device ID `Meetingku`, kemudian periksa pemrosesan antrean dan scheduler. Pengiriman nyata harus diverifikasi pada lingkungan deployment dengan kredensial GOWA. Untuk rollback provider, hentikan worker GOWA, kembalikan kode/konfigurasi WAHA yang sesuai, dan rekonsiliasi pesan yang sudah terkirim sebelum memulai antrean kembali.


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

Untuk dump yang dibuat sebelum upgrade audit, impor dengan skema/kode sumber yang sesuai terlebih dahulu, lalu jalankan migrasi audit pada data hasil impor sebelum membuka akses atau memulai worker/scheduler. Ini memastikan token lama dirotasi dan penanda ringkasan lama terbawa.

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

- Command hanya mengimpor `INSERT` untuk tabel aktif MeetingKu: `pegawai`, `ruangan`, `meeting`, `wa_settings`, `wa_message_queue`. Tabel lama `wa_api_keys` tidak diimpor dan kolom lama `wa_message_queue.api_key_id` diabaikan; backup lama tetap sensitif karena dapat berisi data kredensial key.
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

## API Inbound Dihapus

API meeting, ruangan, dan pesan WhatsApp serta penerbitan/pengelolaan API key MeetingKu telah dihapus sepenuhnya, bukan dibuka tanpa autentikasi. Gunakan UI yang memerlukan login untuk pengelolaan aplikasi. [API.md](API.md) kini hanya berisi pemberitahuan penghentian; lihat [prosedur upgrade dan rollback](#penghapusan-api-inbound-dan-api-key-meetingku) sebelum deployment. Integrasi outbound GOWA memakai HTTP Basic Auth backend.

## Catatan Keamanan

- Jangan commit `.env`, session, log, cache, zip build, atau kredensial deploy.
- Public webroot harus `public/`, bukan root project.
- `GOWA_USERNAME` dan `GOWA_PASSWORD` adalah kredensial outbound backend; rotasi jika terekspos dan jangan tampilkan di browser, URL, atau log.
- Pastikan `writable/` bisa ditulis runtime tetapi tidak dipublikasikan langsung.
