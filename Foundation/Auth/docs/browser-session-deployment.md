# Browser Session Deployment Guide

This package keeps browser-session auth and API-token auth as separate lanes.
For browser-attached credentials, deployments must keep the following posture.

## Cookie Policy

- set session cookies with `Secure`
- set session cookies with `HttpOnly`
- prefer `SameSite=Lax`
- use `SameSite=Strict` for highly contained backoffice surfaces when the
  product flow allows it
- never use `SameSite=None` without `Secure`

## CSRF Model

Use cookie-bound session auth only with explicit CSRF posture.

- state-changing browser routes must require a CSRF token
- routes that accept cross-site redirects must also validate `Origin` or
  `Referer`
- logout, password change, factor reset, email change, and admin actions are
  always CSRF-protected routes
- bearer-token API surfaces do not reuse the cookie CSRF model

See [examples/http/RequireCsrfProtection.php](../examples/http/RequireCsrfProtection.php)
for a framework-neutral reference middleware.

## Origin And Referer Rules

- allow exact same-origin requests
- for browsers that omit `Origin`, fall back to `Referer`
- reject cross-origin unsafe methods unless the route is intentionally public
- do not trust `X-Forwarded-*` headers without proxy hardening outside the
  package

## Browser Storage Policy

- session-based browser surfaces do not store long-lived access or refresh
  tokens in `localStorage`, `sessionStorage`, or IndexedDB
- cookie-based auth stores the session identifier only in the session cookie
- refresh tokens are for token clients, not for session-cookie browser flows
- if an SPA needs OAuth tokens, it uses a public-client flow and not the
  package session-cookie lane

## Session Fixation Procedure

1. create an anonymous session
2. capture the pre-login session id
3. perform a successful login
4. assert the session id changed
5. assert the old session id no longer resolves a valid user
6. repeat after re-auth or factor reset

## Compromise Response

- revoke one session when a single browser session is suspected
- revoke all tracked sessions after password reset or factor reset
- rotate refresh token families if token compromise scope is unclear
- review correlated audit events before restoring privileged access
