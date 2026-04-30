# BUGS - Avax Enterprise Grade Gaps

Canonical active defect and regression queue for 10/10 enterprise grade target.

## Rules

- keep newest items first
- describe user-visible failure first
- include expected fixed behavior
- capture severity and risk
- use timestamp and estimate fields from `TIMELINE.md`
- keep `ACTIVE.md` in sync for non-closed items

## Entry Format

- `id`: BUG-E###
- `detected_at`: 2026-04-30
- `updated_at`: 2026-04-30
- `status`: open | in_progress | blocked | fixed | closed
- `severity`: low | medium | high | critical

---

## 🚨 ENTERPRISE GAPS (For 10/10)

### BUG-E001: No HTTP Built-in Server

- **severity**: critical
- **detected_at**: 2026-04-30
- **status**: fixed
- **fix**: TASK-E001 - HTTP Built-in Server ✅

---

### BUG-E002: Session Storage - No Redis Driver

- **severity**: critical
- **detected_at**: 2026-04-30
- **status**: fixed
- **fix**: TASK-E002 - Session Storage (RedisSessionStore, FileSessionStore) ✅

---

### BUG-E003: Cache Stores - No Redis Driver

- **severity**: critical
- **detected_at**: 2026-04-30
- **status**: fixed
- **fix**: TASK-E003 - Cache Stores (RedisCacheStore, MemcachedCacheStore) ✅

---

### BUG-E004: No Database Migrations

- **severity**: critical
- **detected_at**: 2026-04-30
- **status**: fixed
- **fix**: TASK-E004 - Database Migrations (MigrateCommand, SchemaCommand, SeederCommand) ✅

---

### BUG-E005: No Queue/Background Jobs

- **severity**: critical
- **detected_at**: 2026-04-30
- **status**: fixed
- **fix**: TASK-E005 - Queue Workers (Queue class, RedisQueue) ✅

---

### BUG-E006: Rate Limiting - Stub Only

- **severity**: high
- **detected_at**: 2026-04-30
- **status**: open
- **description**: Rate limiter is stub, no Redis-backed implementation
- **expected**: RedisRateLimiter with sliding window algorithm
- **fix**: TASK-E006 - API Rate Limiting (16h)

---

### BUG-E007: No WebSocket Support

- **severity**: high
- **detected_at**: 2026-04-30
- **status**: open
- **description**: No real-time bi-directional communication
- **expected**: WebSocket with channel broadcasting
- **fix**: TASK-E007 - WebSocket Support (24h)

---

### BUG-E008: File Storage - No Cloud Adapters

- **severity**: high
- **detected_at**: 2026-04-30
- **status**: open
- **description**: No S3, Azure, GCS cloud storage adapters
- **expected**: Storage abstraction with S3Adapter, signed URLs
- **fix**: TASK-E008 - Cloud Storage (16h)

---

### BUG-E009: No Email Queue

- **severity**: high
- **detected_at**: 2026-04-30
- **status**: open
- **description**: Email sent synchronously, slows down responses
- **expected**: Async email queue with SMTP driver
- **fix**: TASK-E009 - Email Queue (16h)

---

### BUG-E010: Test Coverage - Insufficient

- **severity**: high
- **detected_at**: 2026-04-30
- **status**: open
- **description**: Only 7 tests, need 70%+ coverage
- **expected**: 200+ tests covering all components
- **fix**: TASK-E010 - Test Coverage (40h)

---

### BUG-E011: No API Documentation Auto-gen

- **severity**: medium
- **detected_at**: 2026-04-30
- **status**: open
- **description**: No OpenAPI/Swagger auto-generation
- **expected**: /api/docs endpoint with Swagger UI
- **fix**: TASK-E011 - API Docs (12h)

---

### BUG-E012: Monitoring - Basic Only

- **severity**: medium
- **detected_at**: 2026-04-30
- **status**: open
- **description**: No APM, metrics, Sentry integration
- **expected**: Metrics component, custom metrics, Sentry
- **fix**: TASK-E012 - Monitoring (16h)

---

### BUG-E013: Blade Views - Incomplete

- **severity**: medium
- **detected_at**: 2026-04-30
- **status**: open
- **description**: Blade template engine incomplete
- **expected**: Full Blade with components, directives
- **fix**: TASK-E013 - Blade (16h)

---

### BUG-E014: CLI Commands - Limited

- **severity**: medium
- **detected_at**: 2026-04-30
- **status**: open
- **description**: No make:*, migrate:* commands
- **expected**: Full Artisan-like CLI
- **fix**: TASK-E014 - CLI Expansion (20h)

---

### BUG-E015: Security - Needs Hardening

- **severity**: medium
- **detected_at**: 2026-04-30
- **status**: open
- **description**: Need more security features
- **expected**: CSRF+, XSS+, audit logging
- **fix**: TASK-E015 - Security (12h)

---

### BUG-E016: Performance - No Optimizations

- **severity**: medium
- **detected_at**: 2026-04-30
- **status**: open
- **description**: No route/cache/config caching
- **expected**: Query caching, lazy loading, OPcache
- **fix**: TASK-E016 - Performance (12h)

---

## Summary

| Priority      | Count  | Est. Hours |
|---------------|--------|------------|
| Critical (P0) | 5      | 108h       |
| High (P1)     | 5      | 132h       |
| Medium (P2)   | 6      | 88h        |
| **Total**     | **16** | **328h**   |

---

## Status: OPEN - Ready for Enterprise Graduation
- `estimate`:
- `actual`:
- `symptom`:
- `expected_behavior`:
- `risk`:
- `links`:

## Current Items

No active items.
