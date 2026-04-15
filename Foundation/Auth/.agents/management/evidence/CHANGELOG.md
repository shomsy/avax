# Evidence Log

This is the single running list for feature work, bugs, TODOs, plans, decisions,
and follow-up items.

Update rules:

- keep newest items at the top
- use one line per item
- keep each item short and concrete
- link to the owning doc or code when useful
- if a status changes, update this file in the same work item
- add `actual` when the effort is known and worth recording
- add `owner` only when the closure needs accountability context
- use `TIMELINE.md` format for all new entries so duration and aging can be
  estimated

## Current Ledger

- `2026-04-12 23:10 CEST` | `AUTH-022` | shipped explicit assurance policy tiers, phishing-resistant-required OAuth
  client posture, sender-constrained token binding metadata for DPoP and mTLS, JWT `kid` support, new regression
  tests, and first-class docs for assurance, privacy retention, authorization hardening, and crypto lifecycle
- `2026-04-12 22:39 CEST` | `AUTH-021` | normalized the phishing-resistant access naming, moved the guard into an
  explicit capability namespace, made
  `ChangeEmail` a first-class flow keyed by `UserId`, and added regression tests for email-change and admin-elevation
  policy paths
- `2026-04-12 21:32 CEST` | `AUTH-020` | shipped package-owned authorization-policy boundaries, passkey rename,
  slice-local maintenance jobs for session/reset/MFA/passkey/OAuth cleanup, audit export seams, and repo-wide docs for
  the expanded admin realm, federation, provisioning, passkey, and risk scope
- `2026-04-12 15:36 CEST` | `AUTH-019` | shipped package-owned OAuth/API auth subsystem v1 with `Capability/OAuth`
  contracts, client registration, authorization-code + PKCE flow, OAuth refresh exchange, introspection, revoke, JWT
  client/scope claims, new regression tests, and boundary/doc updates
- `2026-04-12 15:04 CEST` | `AUTH-018` | converted `REFAKTOR.md` into a repo-owned ADR, threat model, and
  implementation roadmap; added `Capability/Session` with tracked session registry contracts; shipped active-session
  listing, targeted revoke, logout-all, and reset-driven session/challenge revocation across the auth kernel
- `2026-04-12 14:25 CEST` | `AUTH-017` | hardened the auth kernel with Argon2id-first hashing, login-time password
  rehash, shared recovery throttling, session idle/absolute expiry enforcement, stricter session cookie policy, and
  new regression tests for session and recovery security edges
- `2026-04-09 21:44 CEST` | `AUTH-016` | installed repo-local `pcov` fallback, added coverage-driver wrapper,
  removed Composer mutation timeout, added local artifact ignores, and converted the mutation blocker from environment
  failure into real mutation-quality signal
- `2026-04-09 21:12 CEST` | `AUTH-015` | restored local Composer verification, fixed autoload and PHPUnit config
  issues, replaced fragile final-class mocks with real seams, hardened header/TOTP/session edges, and executed strict
  review with updated release evidence
- `2026-04-09 19:21 CEST` | `AUTH-014` | separated `System/` auth kernel from `integrations/` adapters, moved Avax
  container wiring out of the kernel, added HTTP ingress/failure mappers, hardened access-denial messages, and
  documented the frozen boundary
- `2026-04-09 18:10 CEST` | `AUTH-013` | delivered first-class MFA slice with TOTP enrollment, replay-safe challenge
  verification, backup codes, recovery, fresh-MFA guards, new tests, phpunit source fix, and mutation config
- `2026-04-09 15:31 CEST` | `AUTH-012` | delivered auth-kernel refactor with immutable auth context, unified login
  result, request ingress, refresh rotation, password reset, email verification, MFA, audit log, and updated
  docs/evidence
