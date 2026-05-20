# Review — TODO-019 Global Helper Shortcuts

**Branch:** security/todo-019-global-helper-shortcuts
**HEAD:** f1af9908c
**Work commit:** 91c015afc
**Evidence repair commit:** f1af9908c

---

## Scope Verification

| Check | Result |
|---|---|
| Branch is not main | PASS |
| Only scoped files touched | PASS — 2 shortcuts.php + 1 test + evidence |
| No unrelated cleanup | PASS |
| No public API drift | PASS — duplicate removal + header update |
| No forbidden files | PASS |

---

## Code Review

### Duplicate csrf_token() Removal — PASS

- Removed from `components/HTTP/System/PublicSurface/shortcuts.php`
- Canonical owner: `components/HTTP/Security/System/PublicSurface/shortcuts.php`
- Replaced with comment redirecting to canonical owner
- No behavioral change — only removes duplicate definition

### Deprecated XSS → CSP + HSTS — PASS

- Removed `X-XSS-Protection: 1; mode=block` (deprecated by modern browsers)
- Added `Content-Security-Policy` with restrictive default (`default-src 'self'`)
- Added `Strict-Transport-Security` with `max-age=31536000; includeSubDomains`
- Correct security header modernization

### Security Shortcuts Tests — PASS

| Test | Purpose |
|---|---|
| test_load_order_all_functions_exist | Verifies all functions exist after file load |
| test_csrf_token_owned_by_security_not_http_system | Reflection-based canonical owner proof |
| test_secure_headers_no_deprecated_xss_protection | Verifies XSS header removed |
| test_secure_headers_includes_csp | Verifies CSP header present |
| test_secure_headers_includes_hsts | Verifies HSTS header present |

Tests are behavioral, not instantiation-only.

---

## Security Behavior Verification

| Behavior | Status |
|---|---|
| Canonical CSRF ownership | PASS — only defined in HTTP/Security |
| No deprecated headers | PASS — X-XSS-Protection removed |
| Modern security headers | PASS — CSP + HSTS added |
| No hidden service locator introduced | PASS — only removed app() call |
| No broad redesign | PASS — targeted changes only |

---

## Evidence Review

| File | Complete | Truthful |
|---|---|---|
| context-loaded.md | YES | YES |
| implementation-summary.md | YES | YES — matches diff |
| validation-report.md | YES | YES — verified by rerun |
| governance-review.md | YES | YES — accepted YELLOW documented |
| test-proof.md | YES | YES — test counts match |
| threat-analysis.md | YES | YES — threats are accurate |
| final-decision.md | YES | YES — TODO_CLOSED justified with YELLOW |

---

## Validation Rerun

`vendor/bin/phpunit --no-coverage tests/Unit/Components/HTTP/Security/` → **24 tests, 49 assertions, GREEN**

---

## Accepted YELLOW

| Item | Severity | Notes |
|---|---|---|
| Global app() service locator | MEDIUM | 15 other shortcuts.php files still use app() — only HTTP/Security batch in scope |
| CSRF behavior tests need container | LOW | csrf_token/csrf_field/csrf_method require container bootstrap for full behavior tests |
| PublicSurface file length (201 lines) | LOW | 17 thin one-liner functions, not a dumping ground |
| CSP policy not exhaustive | LOW | Default covers common cases; app-specific tuning may be needed |

---

## Decision

**MERGE_READY_WITH_YELLOW**

No blockers. Duplicate CSRF function removed correctly. Security headers modernized (CSP + HSTS). Canonical ownership proven via reflection test. Accepted YELLOW items are documented and justified.
