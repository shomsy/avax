# Current State

This document is the canonical current-state summary for the Auth package.
It complements `REFAKTOR.md` and `TODO.md` by separating shipped scope from
open work and explicit non-goals.

## Shipped

- auth kernel hardening: password hashing, MFA, recovery, session hardening, audit, admin hardening, authorization separation, key lifecycle, and release hardening
- tenant and control-plane core: tenant lifecycle, membership, invites, ownership transfer, tenant-owned OAuth client management, federation, SCIM, and security-policy surfaces
- SCIM runtime core: `/Users`, `/Groups`, schema discovery, idempotency, drift remediation, bulk lane, health, throttling, and outage recovery
- OAuth and sender-constrained runtime core: PKCE, refresh rotation, reuse detection, DPoP/mTLS-aware binding, client registry, tenant ownership, and workload inventory
- lifecycle orchestration and HTTP hardening: lifecycle capability, CSRF/session utilities, and framework-neutral adapter surfaces

## Partial

- source-of-truth cleanup: historical review snapshots still need to remain outside current-state truth
- OIDC provider completeness: discovery, JWKS, ID tokens, userinfo, logout, PAR, JARM, and request-object claim validation exist, but full client-signed JAR validation and certification remain outside the kernel
- session revocation durability: SQL-backed durable registry exists, but the full backend matrix is still incomplete
- deployment trust boundary: sender-constraint adapters exist, but deployment profiles and unsafe-mode detection still need tighter operator packaging
- SCIM productization: kernel runtime exists, but the full RFC 7644 product envelope remains outside the package

## Explicit Non-Goals

- full standards-certified OIDC provider product surface
- OIDC-standard dynamic client registration endpoint
- client-signed JAR request-object validation as a full product promise
- SAML brokering runtime
- full SCIM HTTP product surface
- tenant-admin product UI
- KMS/HSM, mail, SIEM, and queue infrastructure
- cross-organization approval tooling and enterprise IAM governance systems

## Current Boundary Rule

- `System/` is the kernel
- `integrations/` is the optional integration surface
- kernel code does not import integration namespaces
- adapters stay thin and do not own business policy
