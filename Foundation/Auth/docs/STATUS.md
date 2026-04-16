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

- [x] **Auth Kernel Hardening**: password hashing (bcrypt/argon2), MFA (TOTP, backup codes, recovery), session
  hardening, audit, admin hardening, authorization separation, key lifecycle, release hardening
- [x] **Tenant & Control-Plane Core**: tenant lifecycle, membership, invites, ownership transfer, tenant-owned OAuth
  client management, federation/SCIM/security-policy
- [x] **SCIM Runtime Core**: `/Users`, `/Groups`, schema discovery, idempotency, drift remediation, bulk lane, sync
  health, throttling, outage recovery
- [x] **OAuth / Sender-Constrained Runtime**: PKCE, refresh rotation, reuse detection, DPoP/mTLS-aware binding, client
  registry, tenant ownership, workload client_credentials
- [x] **Practical OIDC Provider Lane**: discovery, JWKS, ID tokens, userinfo, logout, PAR, JARM, dynamic client
  registration surface, and client-signed request-object validation for confidential and public clients with
  package-owned key material
- [x] **Lifecycle Orchestration**: lifecycle capability, CSRF/session utilities, adapter surfaces
- [x] **Quality Gates Automation**: 11/13 gates automated via `composer quality-gates`
- [x] **Glossary**: Auth, Security glossaries with 90+ terms
- [x] **Conformance Mapping**: Quality gates → CI commands mapping
- [x] **Enterprise Mode**: session registry as first-class runtime requirement with boot-time fail-fast
- [x] **Session Revocation**: SQL + Redis durable registries, enterprise-mode fail-fast, tracked-session revocation
  flows, and backend conformance tests
- [x] **Source Of Truth Cleanup**: `docs/STATUS.md` is canonical, `Auth.txt` is downgraded to a non-canonical merged
  artifact, and historical notes live in `docs/archive/`
- [x] **Compatibility Migration Path**: major-version migration is documented and checked via
  `tooling/check-migration-path.php`
- [x] **Support Explainability Runtime**: `AuthIssueExplainer` and the `Auth` facade publish operator-safe explanations
  for access denial, step-up, sender-constraint, session-revocation, and trusted-device posture
- [x] **Deployment Trust Boundary**: sender-constraint adapters, trusted-proxy guards, deployment profiles, executable
  smoke tests, and operator diagnostics are package-owned; edge certificate-chain execution remains a documented
  deployment boundary
- [x] **System Shape Enforcement**: `tooling/check-system-shape.php` and release/conformance tooling fail when `System/`
  drifts away from the canonical screaming shape or reintroduces junk-drawer folders
- [x] **Conformance Harness**: `composer conformance` and `composer evidence:bundle` generate machine-readable release
  evidence
- [x] **Local Certification Posture**: certification profile, evidence bundle, provenance, rollback evidence, SBOM, and
  conformance harness exist as package-owned release rigor
- [x] **Root Ownership Contract**: `System/Capability/Access`, `System/Capability/Identity`,
  `System/Capability/ExternalIdentity`, `System/Capability/IdentitySync`, `System/Capability/Tenant`, and
  `System/Capability/Diagnostics` now act as explicit owner facades above the local `Flow/` lanes, with canonical
  architecture docs, ADRs, migration map, and characterization tests

---

## Partial

- [~] **Mutation Quality Posture**: mutation tooling runs locally and is wired into package tooling, but release-quality
  MSI/timeout posture still remains an active risk until the current escaped/timeouts set is driven down

---

## Not Implemented

- [ ] Tenant-admin product UI
- [ ] SAML brokering runtime
- [ ] External certification program

---

## Explicit Non-Goals

These are explicitly NOT part of the shipped product:

- Standards-certified OIDC provider (practical lane, not certified product)
- External client-key hosting, remote `jwks_uri` fetch/rotation, and software-statement validation for OIDC registration
- Trusted device / remembered device support (permanently rejected - requires full device trust chain beyond auth kernel
  scope)
- Full tenant-admin product UI
- KMS/HSM, mail, SIEM infrastructure
- Cross-org approval tooling
- SAML brokering runtime
- External certification program

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

| Gate                  | Status    | Automation                           |
|-----------------------|-----------|--------------------------------------|
| 1. Trust              | ✅ PASSED  | phpstan + phpunit                    |
| 2. Operator Clarity   | ✅ PASSED  | phpstan table                        |
| 3. Rollback Posture   | ✅ PASSED  | rollback evidence                    |
| 4. Contract Stability | ✅ PASSED  | phpstan + rector                     |
| 5. State Ownership    | ✅ PASSED  | integration tests                    |
| 6. Async Containment  | ✅ PASSED  | integration tests                    |
| 7. Deterministic CI   | ✅ PASSED  | phpstan + phpunit                    |
| 8. Observability      | ⚠️ MANUAL | human review                         |
| 9. Runtime Hardening  | ✅ PASSED  | secret scan                          |
| 10. Performance       | ✅ PASSED  | optional mutation diagnostics        |
| 11. Source Truth      | ✅ PASSED  | source-truth + system-shape checkers |
| 12. Evidence          | ✅ PASSED  | SBOM                                 |
| 13. Self-Healing      | ⚠️ MANUAL | human review                         |

**Automation Rate: 84.6%** (11/13 automated)

---

## Version

- Auth: 1.0.0+
- PHP: 8.3+
- Status: 2.1.0

---

## Product Boundary Summary

| Layer           | Scope                                                                 | Status        |
|-----------------|-----------------------------------------------------------------------|---------------|
| Auth Kernel     | Authentication, authorization, OAuth, MFA, JWT, sessions              | ✅ Shipped     |
| Identity Kernel | Tenants, SCIM, federation, OIDC provider lane, admin realm, lifecycle | ✅ Shipped     |
| Full Platform   | UI, SIEM, email, certification, SAML brokering                        | ❌ Not shipped |

---

*This document is authoritative. See `REFAKTOR.md` for detailed backlog.*
