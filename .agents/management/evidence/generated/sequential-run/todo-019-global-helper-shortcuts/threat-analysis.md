# Threat Analysis — TODO-019 Global Helper Shortcuts

## Scope
- Duplicate CSRF function ownership resolution
- Security header modernization (CSP + HSTS)
- HTTP/Security shortcuts.php batch only

## Threats Mitigated

| Threat | Severity | Before | After |
|---|---|---|---|
| CSRF token from wrong owner | HIGH | `csrf_token()` defined in both HTTP/System and HTTP/Security; load-order determines which wins | Removed from HTTP/System; canonical owner is HTTP/Security |
| Missing CSP header | MEDIUM | No Content-Security-Policy in default security headers | CSP with `default-src 'self'` and restrictive policy |
| Missing HSTS header | MEDIUM | No Strict-Transport-Security in default headers | HSTS with `max-age=31536000; includeSubDomains` |
| Deprecated XSS header | LOW | `X-XSS-Protection: 1; mode=block` (deprecated by modern browsers) | Removed |

## Residual Risks

| Risk | Severity | Notes |
|---|---|---|
| Global `app()` service locator | MEDIUM | 15 other shortcuts.php files still use `app()` — only HTTP/Security batch in scope |
| CSRF behavior untested | LOW | `csrf_token()`, `csrf_field()`, `csrf_method()` require container bootstrap for behavior tests |
| CSP policy not exhaustive | LOW | Default CSP covers common cases; app-specific CSP tuning may be needed |
