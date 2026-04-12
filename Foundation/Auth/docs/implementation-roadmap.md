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
- explicit actor assurance policy catalog and composed access-policy tiers
- phishing-resistant-required OAuth client posture and sender-constrained token binding metadata
- admin elevation and privileged-user lifecycle flows
- passkey registration, authentication, rename, and revoke behind a runtime seam
- tenant-aware federation connections, login discovery, start/complete login, and JIT linking
- deterministic risk signals and refresh-reuse review hooks
- composed access-policy enforcement for role, permission, resource-owner, fresh-MFA, and admin-elevation checks
- cleanup/export maintenance flows for sessions, reset tokens, MFA/passkey challenges, authorization codes, and audit
  events
- audit events across security-sensitive flows
- first-class privacy retention, authorization hardening, assurance matrix, and crypto lifecycle docs

## Next Delivery Order

### Sprint 1

- complete session-backed integration examples
- document CSRF expectations for cookie-auth deployments
- add persistent session registry contract examples

### Sprint 2

- complete OAuth-backed adapter examples and persistence contracts
- add richer audit exporters and security-notification adapter examples

### Sprint 3

- evaluate richer sender-constraint proof verification adapters for HTTP and mTLS environments
- add package examples for high-assurance admin passkey policy and OAuth client posture

### Sprint 4

- evaluate optional `league/oauth2-server` interop adapter without breaking package-owned OAuth kernel contracts
- evaluate optional WebAuthn interop adapter packaging over the runtime seam

### Sprint 5

- domain verification, federation metadata sync, and stronger tenant connection policy

### Sprint 6

- conditional SCIM/runtime adapters, deeper tenant membership model, and external control-plane integration

## Non-Goals For This Package Today

- custom SAML implementation from scratch
- OIDC provider surface
- full SCIM runtime
- full identity control plane
- package-owned database migrations
- framework-specific session UIs
- fake placeholder modules without owned behavior
