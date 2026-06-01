# Changelog

All notable changes to MeetingKu will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Security
- **API Hardening** (2026-06-01)
  - Removed `plain_key` from database storage (only hash stored now)
  - API key authentication now header-only (`X-API-KEY`), removed query parameter fallback
  - Added rate limiting (60 requests/minute) to all API endpoints via Throttler filter
  - Sanitized error messages to prevent information leakage
  - Added failed authentication logging with IP address
  - Added `WaApiKeyService::generateMeetingKey()` for `mku_` prefixed keys

### Documentation
- Added comprehensive API documentation (`API.md`)
- Updated README.md with Public API section and security notes
- Updated AGENTS.md with API security guidelines
- Added CHANGELOG.md for version tracking

### Added
- CSRF exclusion for API routes (proper for API key authentication)
- Route group with throttle filter for all API endpoints

## [1.0.0] - Initial Release

### Features
- Meeting management (CRUD, calendar, upcoming/all views)
- Employee (pegawai) management with spreadsheet import
- Room (ruangan) management
- Zoom integration (automatic & manual link management)
- WhatsApp gateway integration via Node.js service
- WhatsApp API key management
- Message queue with retry mechanism
- Daily agenda scheduler (15:00 WITA)
- Docker Compose deployment (PHP-FPM, Nginx, worker, scheduler, wa-sender)
- PostgreSQL support (with MySQL import tool for migration)

### Technical
- CodeIgniter 4 framework (PHP 8.1+)
- PHPUnit 10 for testing
- Tailwind CSS + Bootstrap for UI
- FullCalendar for calendar views
- Baileys library for WhatsApp connection
