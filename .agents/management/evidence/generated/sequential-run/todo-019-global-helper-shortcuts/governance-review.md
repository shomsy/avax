# Governance Review — TODO-019 Global Helper Shortcuts

## Applicable Governance Documents

| Document | Status |
|---|---|
| AGENTS.md | READ |
| .agents/how-to/how-to-system-security.md | READ — CSRF ownership, security headers |
| .agents/how-to/how-to-design-components.md | READ — canonical ownership |
| .agents/how-to/how-to-coding-standards.md | READ |
| .agents/how-to/how-to-unit-test.md | READ |
| .agents/how-to/how-to-use-ai-assisted-execution.md | READ |

## Findings Table

| Finding | Severity | File | Problem | Resolution |
|---|---|---|---|---|
| Duplicate csrf_token() | HIGH | HTTP/System/shortcuts.php | CSRF function defined in two places, load-order dependent | Removed from HTTP/System, canonical owner is HTTP/Security |
| Deprecated XSS header | MEDIUM | HTTP/Security/shortcuts.php | `X-XSS-Protection: 1; mode=block` is deprecated by modern browsers | Replaced with CSP + HSTS |
| Missing CSP header | MEDIUM | HTTP/Security/shortcuts.php | No Content-Security-Policy in default headers | Added restrictive CSP |
| Missing HSTS header | MEDIUM | HTTP/Security/shortcuts.php | No Strict-Transport-Security in default headers | Added HSTS with 1-year max-age |
| Global app() calls | ACCEPTED_YELLOW | shortcuts.php files | `app()` service locator calls hide dependencies | ACCEPTED — thin compatibility shims, documented as temporary |
| File length 201 lines | ACCEPTED_YELLOW | shortcuts.php | 17 functions in one file | ACCEPTED — each is a thin one-liner |

## Compliance Matrix

| Rule | Status |
|---|---|
| Canonical ownership | PASS — csrf_token owned by HTTP/Security only |
| No duplicate definitions | PASS — removed from HTTP/System |
| Security headers current | PASS — CSP + HSTS added, XSS removed |
| Tests prove behavior | PASS — 5 new tests |
| Advanced OOP: folder says flow/capability | PASS — PublicSurface/ |
| No forbidden folder names | PASS |

## Residual Risks

| Risk | Severity | Notes |
|---|---|---|
| Global `app()` service locator | ACCEPTED_YELLOW | 15 other shortcuts.php files still use `app()` — only HTTP/Security batch in scope |
| CSRF behavior tests need container | LOW | `csrf_token()`, `csrf_field()`, `csrf_method()` behavior tests require container bootstrap |
| File length 201 lines | ACCEPTED_YELLOW | 17 thin one-liner functions, not a dumping ground |

## Decision

Governance review complete. All HIGH and MEDIUM findings addressed. ACCEPTED_YELLOW items documented with justification.
