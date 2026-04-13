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
- workload `client_credentials` runtime, inventory, and audience/scope ceilings
- OIDC provider metadata, JWKS, RS256 ID-token issuance, nonce enforcement, and userinfo
- explicit actor assurance policy catalog and composed access-policy tiers
- phishing-resistant-required OAuth client posture and sender-constrained token binding metadata
- DPoP proof verification, mTLS binding verification, and transport-level sender-constraint enforcement adapters
- admin elevation and privileged-user lifecycle flows
- passkey registration, authentication, rename, and revoke behind a runtime seam
- tenant-aware federation connections, verified-domain discovery, metadata sync, health checks, break-glass policy
  evaluation, start/complete login, and JIT linking
- SCIM directory registration, token rotation, user provisioning/delete, group sync, idempotency, drift detection, and
  provisioning audit
- tenant security configuration workflow with request, approval, apply, rollback, audit diff, and rollout versioning
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
- add optional OIDC logout/session-management packaging without breaking kernel-owned flows
- extend the SCIM adapter toward bulk and fuller RFC 7644 product behavior over the delivered SCIM kernel runtime
- add tenant-admin UI adapters over `Flow/TenantSecurity/`
- evaluate optional `league/oauth2-server` interop adapter without breaking package-owned OAuth kernel contracts
- evaluate optional WebAuthn interop adapter packaging over the runtime seam

### Future

- SAML brokering/runtime package, deeper tenant membership model, residency/compliance overlays, and external
  control-plane integration

## Non-Goals For This Package Today

- custom SAML implementation from scratch
- full standards-certified OIDC provider product surface
- full SCIM HTTP product surface
- tenant-admin product UI
- package-owned database migrations
- framework-specific session UIs
- fake placeholder modules without owned behavior
