# MeetingKu API — Dihapus

**Breaking change:** MeetingKu tidak lagi menyediakan API inbound untuk meeting, ruangan, atau antrean/pesan WhatsApp. Route API lama dan pengelolaan API key MeetingKu (`mku_`/`mkwa_`) telah dihapus, bukan dibuka tanpa autentikasi. Hentikan integrasi yang memanggil endpoint lama; tidak ada endpoint pengganti publik.

Pengelolaan meeting/ruangan tetap tersedia melalui UI yang memerlukan login. Notifikasi internal, antrean dan riwayat WhatsApp, worker, scheduler, status GOWA, serta tautan dashboard GOWA tetap tersedia.

`GOWA_USERNAME` dan `GOWA_PASSWORD` **diperlukan** untuk HTTP Basic Auth backend ke GOWA, bukan untuk mengakses MeetingKu. Device ID default adalah `Meetingku`. Konfigurasi `WAHA_*` sudah diganti; jangan mengekspos kredensial provider ke browser. Lihat konfigurasi gateway pada [README](README.md#whatsapp-gateway).

## Upgrade dan rollback

- Backup database lengkap sebelum upgrade, lalu jalankan `php spark migrate` dengan kode baru. Lihat [prosedur deployment di README](README.md#penghapusan-api-inbound-dan-api-key-meetingku).
- Migrasi menghapus foreign key dan kolom `wa_message_queue.api_key_id`, lalu tabel `wa_api_keys`. Baris antrean/riwayat dan `wa_settings` dipertahankan.
- Data kredensial key dan relasi key ke antrean yang dihapus **tidak dapat dipulihkan tanpa backup**. Rollback schema tidak mengembalikan key lama atau mengaktifkan integrasi lama dengan sendirinya.
- Jika perlu kembali ke versi lama, hentikan penulisan/proses antrean, pulihkan backup pra-upgrade yang cocok dengan kode lama, lalu deploy ulang versi tersebut. Pemulihan backup juga mengembalikan keadaan antrean saat backup; rekonsiliasi pesan yang sudah terkirim agar tidak terkirim ulang.

Dokumentasi endpoint, contoh request, dan petunjuk penerbitan key lama sengaja ditarik karena tidak berlaku lagi.
