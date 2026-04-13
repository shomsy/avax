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
- DPoP proof verification, mTLS binding verification, and transport-level sender-constraint enforcement adapters
- admin elevation and privileged-user lifecycle flows
- passkey registration, authentication, rename, and revoke behind a runtime seam
- tenant-aware federation connections, verified-domain discovery, metadata sync, health checks, break-glass policy
  evaluation, start/complete login, and JIT linking
- deterministic risk signals and refresh-reuse review hooks
- composed access-policy enforcement for role, permission, resource-owner, fresh-MFA, and admin-elevation checks
- cleanup/export maintenance flows for sessions, reset tokens, MFA/passkey challenges, authorization codes, and audit
  events
- audit events across security-sensitive flows
- first-class privacy retention, authorization hardening, assurance matrix, crypto lifecycle, workload identity,
  release-hardening, verification-matrix, and runbook docs
- multi-key HMAC verification for key rollover windows

## Next Delivery Order

### Next

- complete executable tests for every remaining example and adapter seam
- add optional client-credentials workload runtime if the kernel grows a non-human principal boundary
- evaluate optional `league/oauth2-server` interop adapter without breaking package-owned OAuth kernel contracts
- evaluate optional WebAuthn interop adapter packaging over the runtime seam

### Future

- conditional SCIM/runtime adapters, deeper tenant membership model, and external control-plane integration

## Non-Goals For This Package Today

- custom SAML implementation from scratch
- OIDC provider surface
- full SCIM runtime
- full identity control plane
- package-owned database migrations
- framework-specific session UIs
- fake placeholder modules without owned behavior
