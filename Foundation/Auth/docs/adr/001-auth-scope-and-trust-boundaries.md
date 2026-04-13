# ADR-001: Auth Scope And Trust Boundaries

## Status

Accepted

## Date

2026-04-12

## Context

`REFAKTOR.md` asks for a path from a strong auth core to an enterprise-grade identity system. This repository is a pure
PHP framework package with `System/` as the canonical system root, optional integrations in `integrations/`, and no
package-owned database or HTTP runtime.

The package already owns:

- password authentication
- server-side session identity
- token issuance and refresh rotation
- OAuth client registration, authorization-code issuance, PKCE verification, token revocation, and introspection
- MFA enrollment, challenge, recovery, and fresh-MFA checks
- adapter-first passkey registration and authentication
- tenant-aware federation connection and login orchestration
- deterministic risk rules and refresh-reuse review signals
- admin elevation and user lifecycle provisioning
- audit events and auth ingress ownership
- explicit actor assurance policy tiers
- sender-constrained OAuth token posture metadata
- key-version-aware HMAC token codec support
- HTTP DPoP proof verification and mTLS binding verification adapters
- federation domain verification, metadata sync, health checks, and break-glass policy evaluation

The package does not yet own:

- OIDC provider behavior
- SCIM runtime integration
- full tenant membership control plane
- KMS/HSM, mail, SIEM, and queue infrastructure
- approval-driven privileged workflows

## Decision

Choose the **Balanced** path.

The package remains an **auth kernel**, not a full custom identity platform.

### V1 Frozen Scope

The package owns and ships:

- password hashing and login
- server-side session hardening
- MFA with TOTP and backup codes
- password reset and MFA recovery
- refresh rotation and reuse detection
- OAuth authorization-code and refresh grants for package-owned clients
- audit events
- active session management
- admin elevation and lifecycle provisioning
- adapter-first passkeys and tenant-aware federation flows
- deterministic risk decisions and maintenance cleanup/export flows

### V2 Planned Scope

The package may add:

- optional OAuth interoperability adapters
- external SCIM and SIEM adapters
- deeper tenant membership ownership

### Future Dependency Strategy

Do not hand-roll standards-heavy subsystems when mature PHP libraries exist.

- OAuth: `league/oauth2-server`
- WebAuthn / passkeys: `web-auth/webauthn-framework` or `web-auth/webauthn-lib`
- Federation and SCIM: adapter-first, standards-based contracts over package-owned glue

## Trust Boundaries

### Kernel Boundary

`System/` owns:

- auth flows
- shared auth capabilities
- public auth facade and immutable boundary types
- session/token/MFA contracts
- audit event contracts

### Integration Boundary

`integrations/` owns:

- HTTP mapping
- container adapters
- future framework-specific transport or persistence glue

### Application Boundary

Applications own:

- real persistence implementations
- session registry persistence beyond in-memory use
- queue/job infrastructure
- HTTP routing and CSRF middleware
- secret storage and key management
- SIEM/export pipelines

## Consequences

- `System/` stays the canonical system root. `src/` is not introduced because it would add a hallway, not clarity.
- Session-backed web auth and token-backed API auth remain separate runtime lanes behind one public facade.
- Enterprise features enter only after the session/recovery core is defensible.
- Any future OAuth/WebAuthn/federation work must preserve screaming ownership and stay out of generic helper buckets.
