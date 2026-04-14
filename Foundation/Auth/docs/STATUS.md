# Auth Status — Canonical State

Version: 2.0.0
Status: Normative / Local
Scope: `Avax\Auth\**`

This is the canonical state document. All other status documents should reference this file.

## Legend

- `[x]` done
- `[~]` partial / boundary-only
- `[ ]` not implemented

---

## Shipped Capabilities

### Done

- [x] **Auth Kernel Hardening**: password hashing (bcrypt/argon2), MFA (TOTP, backup codes, recovery), session hardening, audit, admin hardening, authorization separation, key lifecycle, release hardening
- [x] **Tenant & Control-Plane Core**: tenant lifecycle, membership, invites, ownership transfer, tenant-owned OAuth client management, federation/SCIM/security-policy
- [x] **SCIM Runtime Core**: `/Users`, `/Groups`, schema discovery, idempotency, drift remediation, bulk lane, sync health, throttling, outage recovery
- [x] **OAuth / Sender-Constrained Runtime**: PKCE, refresh rotation, reuse detection, DPoP/mTLS-aware binding, client registry, tenant ownership, workload client_credentials
- [x] **Lifecycle Orchestration**: lifecycle capability, CSRF/session utilities, adapter surfaces
- [x] **Quality Gates Automation**: 13 quality gates automated via `composer quality-gates`
- [x] **Glossary**: Auth, Security glossaries with 90+ terms
- [x] **Conformance Mapping**: Quality gates → CI commands mapping

---

## Partial

- [~] **OIDC Provider**: discovery, JWKS, ID tokens, userinfo, logout, PAR, JARM, request-object — full JAR client-signed validation remains boundary
- [~] **Session Revocation**: SQL backend done, Redis pending, full conformance matrix incomplete
- [~] **Deployment Trust Boundary**: sender-constraint adapters exist, deployment profiles and smoke tests pending
- [~] **Architecture Cleanup**: ControlPlane/UI folders outside taxonomy, TenantMembership duplicate naming

---

## Not Implemented

- [ ] External certification program
- [ ] Full RFC 7644 SCIM product surface (kernel exists, product envelope pending)
- [ ] Tenant-admin product UI
- [ ] SAML brokering runtime

---

## Explicit Non-Goals

These are explicitly NOT part of the shipped product:

- Standards-certified OIDC provider (practical lane, not certified product)
- OIDC-standard dynamic client registration endpoint (integrated via OAuth)
- Client-signed JAR request-object validation as product promise
- Trusted device / remembered device support
- Full tenant-admin product UI
- KMS/HSM, mail, SIEM infrastructure
- Cross-org approval tooling

---

## Boundary Rule

```
System/           → kernel (authentication/authorization)
integrations/     → optional integration surface
Capability/       → shared domain enablers
Flow/             → business use cases
Foundation/       → tiny primitives only
```

---

## Quality Gates Status

| Gate | Status | Automation |
|------|--------|------------|
| 1. Trust | ✅ PASSED | phpstan + phpunit |
| 2. Operator Clarity | ✅ PASSED | phpstan table |
| 3. Rollback Posture | ✅ PASSED | rollback evidence |
| 4. Contract Stability | ✅ PASSED | phpstan + rector |
| 5. State Ownership | ✅ PASSED | integration tests |
| 6. Async Containment | ✅ PASSED | integration tests |
| 7. Deterministic CI | ✅ PASSED | phpstan + phpunit |
| 8. Observability | ⚠️ MANUAL | human review |
| 9. Runtime Hardening | ✅ PASSED | secret scan |
| 10. Performance | ✅ PASSED | mutation testing |
| 11. Source Truth | ⚠️ MANUAL | human review |
| 12. Evidence | ✅ PASSED | SBOM |
| 13. Self-Healing | ⚠️ MANUAL | human review |

**Automation Rate: 77%** (10/13 automated)

---

## Version

- Auth: 1.0.0+
- PHP: 8.3+
- Status: 2.0.0

---

*This document is authoritative. See `REFAKTOR.md` for detailed backlog.*