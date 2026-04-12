# Implementation Roadmap

## Current Decision

This package follows the balanced path from `docs/adr/001-auth-scope-and-trust-boundaries.md`.

## Delivered In Kernel

- password hashing with Argon2id-first policy and login-time rehash
- login, logout, and refresh rotation
- password reset and MFA recovery
- MFA enrollment, challenge, backup codes, and fresh-MFA checks
- tracked web sessions with idle and absolute expiry
- active session listing, single-session revoke, and logout-all
- OAuth client registry, authorization-code issuance, PKCE enforcement, refresh exchange, revoke, and introspection
- audit events across security-sensitive flows

## Next Delivery Order

### Sprint 1

- complete session-backed integration examples
- document CSRF expectations for cookie-auth deployments
- add persistent session registry contract examples

### Sprint 2

- complete OAuth-backed adapter examples and persistence contracts
- add reset-confirm throttling and narrower refresh revocation controls

### Sprint 3

- package-owned RBAC base and policy conditions
- admin realm policy split

### Sprint 4

- evaluate optional `league/oauth2-server` interop adapter without breaking package-owned OAuth kernel contracts

### Sprint 5

- introduce passkey contracts over a WebAuthn library

### Sprint 6

- federation, provisioning, and deterministic risk contracts

## Non-Goals For This Package Today

- custom SAML implementation from scratch
- full identity control plane
- package-owned database migrations
- framework-specific session UIs
- fake placeholder modules without owned behavior
