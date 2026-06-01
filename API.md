# MeetingKu API Documentation

Public API untuk integrasi eksternal dengan sistem MeetingKu.

## Autentikasi

Semua endpoint API **wajib** menggunakan header `X-API-KEY`:

```http
X-API-KEY: your_api_key_here
```

API key hanya diterima via header, **tidak bisa** via query parameter.

### Mendapatkan API Key

API key dibuat oleh admin melalui halaman WhatsApp di dashboard MeetingKu:
1. Login ke dashboard
2. Menu WhatsApp → API Keys
3. Buat key baru dengan nama deskriptif
4. Simpan key yang muncul (hanya ditampilkan sekali)

**Prefix key:**
- `mku_` — untuk meeting API
- `mkwa_` — untuk WhatsApp API

## Rate Limiting

Semua endpoint API dibatasi **60 request per menit** per IP.

Jika melebihi limit, response:
```json
{
  "code": 429,
  "message": "Too Many Requests"
}
```

## Meeting Endpoints

### List All Meetings

```http
GET /api/meetings
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "Rapat Koordinasi",
      "description": "Koordinasi bulanan",
      "date": "2026-06-15",
      "time_start": "09:00:00",
      "time_end": "11:00:00",
      "room_id": 1,
      "room_name": "Ruang Rapat Utama",
      "pegawai_id": 5,
      "pegawai_name": "Budi Santoso",
      "status": "scheduled",
      "zoom_link": "https://zoom.us/j/123456789",
      "created_at": "2026-06-01 10:00:00",
      "updated_at": "2026-06-01 10:00:00"
    }
  ]
}
```

### Get Single Meeting

```http
GET /api/meetings/{id}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Rapat Koordinasi",
    "description": "Koordinasi bulanan",
    "date": "2026-06-15",
    "time_start": "09:00:00",
    "time_end": "11:00:00",
    "room_id": 1,
    "room_name": "Ruang Rapat Utama",
    "pegawai_id": 5,
    "pegawai_name": "Budi Santoso",
    "status": "scheduled",
    "zoom_link": "https://zoom.us/j/123456789",
    "zoom_meeting_id": "123456789",
    "manual_zoom_link": null,
    "created_at": "2026-06-01 10:00:00",
    "updated_at": "2026-06-01 10:00:00"
  }
}
```

### Check Conflict

Cek apakah ada rapat bentrok di waktu tertentu.

```http
GET /api/meetings/conflict?date=2026-06-15&time_start=09:00:00&time_end=11:00:00&room_id=1
```

**Query Parameters:**
- `date` (required) — tanggal rapat (YYYY-MM-DD)
- `time_start` (required) — waktu mulai (HH:MM:SS)
- `time_end` (required) — waktu selesai (HH:MM:SS)
- `room_id` (required) — ID ruangan
- `exclude_id` (optional) — ID meeting yang dikecualikan (untuk update)

**Response:**
```json
{
  "status": "success",
  "conflict": true,
  "conflicts": [
    {
      "id": 2,
      "title": "Rapat Lain",
      "time_start": "10:00:00",
      "time_end": "12:00:00"
    }
  ]
}
```

### Create Meeting

```http
POST /api/meetings
Content-Type: application/json

{
  "title": "Rapat Koordinasi",
  "description": "Koordinasi bulanan",
  "date": "2026-06-15",
  "time_start": "09:00:00",
  "time_end": "11:00:00",
  "room_id": 1,
  "pegawai_id": 5
}
```

**Response (201 Created):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Rapat Koordinasi",
    ...
  }
}
```

### Update Meeting

```http
PUT /api/meetings/{id}
Content-Type: application/json

{
  "title": "Rapat Koordinasi (Revisi)",
  "date": "2026-06-16",
  "time_start": "10:00:00",
  "time_end": "12:00:00",
  "room_id": 1,
  "pegawai_id": 5
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "title": "Rapat Koordinasi (Revisi)",
    ...
  }
}
```

### Approve Meeting

Approve meeting yang membutuhkan persetujuan.

```http
PATCH /api/meetings/{id}/approve
Content-Type: application/json

{
  "approved": true
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "status": "approved",
    ...
  }
}
```

### Delete Meeting

```http
DELETE /api/meetings/{id}
```

**Response:**
```json
{
  "status": "success",
  "message": "Meeting berhasil dihapus"
}
```

## Room Endpoints

### List All Rooms

```http
GET /api/rooms
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Ruang Rapat Utama",
      "capacity": 20,
      "location": "Lantai 2",
      "created_at": "2026-01-01 00:00:00",
      "updated_at": "2026-01-01 00:00:00"
    }
  ]
}
```

## WhatsApp Endpoints

### Send WhatsApp Message

```http
POST /api/whatsapp/messages
Content-Type: application/json

{
  "to": "6281234567890",
  "message": "Reminder: Rapat besok pukul 09:00"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "to": "6281234567890",
    "message": "Reminder: Rapat besok pukul 09:00",
    "status": "queued",
    "created_at": "2026-06-01 10:00:00"
  }
}
```

### Get Message Status

```http
GET /api/whatsapp/messages/{id}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "to": "6281234567890",
    "message": "Reminder: Rapat besok pukul 09:00",
    "status": "sent",
    "sent_at": "2026-06-01 10:00:05",
    "created_at": "2026-06-01 10:00:00"
  }
}
```

## Error Responses

### 401 Unauthorized

API key tidak valid atau tidak ada:
```json
{
  "status": "error",
  "message": "Unauthorized"
}
```

### 404 Not Found

Resource tidak ditemukan:
```json
{
  "status": "error",
  "message": "Meeting tidak ditemukan"
}
```

### 422 Validation Error

Input tidak valid:
```json
{
  "status": "error",
  "messages": {
    "title": ["Title harus diisi"],
    "date": ["Date tidak valid"]
  }
}
```

### 500 Server Error

Error internal server (pesan generic untuk keamanan):
```json
{
  "status": "error",
  "message": "Gagal menyimpan data"
}
```

## Contoh Penggunaan

### cURL

```bash
# List meetings
curl -H "X-API-KEY: mku_your_key_here" \
  https://meetingku.example.com/api/meetings

# Create meeting
curl -X POST \
  -H "X-API-KEY: mku_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Rapat Baru",
    "date": "2026-06-20",
    "time_start": "14:00:00",
    "time_end": "16:00:00",
    "room_id": 1,
    "pegawai_id": 5
  }' \
  https://meetingku.example.com/api/meetings
```

### JavaScript (Fetch)

```javascript
const API_KEY = 'mku_your_key_here';
const BASE_URL = 'https://meetingku.example.com/api';

// List meetings
const response = await fetch(`${BASE_URL}/meetings`, {
  headers: { 'X-API-KEY': API_KEY }
});
const data = await response.json();

// Create meeting
const createResponse = await fetch(`${BASE_URL}/meetings`, {
  method: 'POST',
  headers: {
    'X-API-KEY': API_KEY,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'Rapat Baru',
    date: '2026-06-20',
    time_start: '14:00:00',
    time_end: '16:00:00',
    room_id: 1,
    pegawai_id: 5
  })
});
const result = await createResponse.json();
```

### PHP

```php
$apiKey = 'mku_your_key_here';
$baseUrl = 'https://meetingku.example.com/api';

// List meetings
$ch = curl_init($baseUrl . '/meetings');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-KEY: ' . $apiKey]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);

// Create meeting
$ch = curl_init($baseUrl . '/meetings');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'title' => 'Rapat Baru',
    'date' => '2026-06-20',
    'time_start' => '14:00:00',
    'time_end' => '16:00:00',
    'room_id' => 1,
    'pegawai_id' => 5
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$result = json_decode($response, true);
```

## Catatan Keamanan

- **Jangan pernah** commit API key ke repository
- Gunakan environment variable untuk menyimpan API key
- Rotasi API key secara berkala
- Jika key terekspos, segera revoke dan buat yang baru
- API hanya bisa diakses dari IP/network yang tepercaya (jika ada whitelist)
- Semua request harus via HTTPS di production

## Support

Untuk pertanyaan atau issue terkait API, hubungi admin MeetingKu atau buat issue di repository.
