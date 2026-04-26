# Product Boundary — Clear Scope Separation

Version: 1.0.0
Status: Normative / Local
Scope: `Avax\Auth\` product positioning

This document defines the clear boundaries between auth kernel, identity kernel,
and full identity platform.

---

## The Three Layers

```
┌─────────────────────────────────────────────────────────────┐
│                  Full Identity Platform                      │
│  (NOT shipped — external integrations, full UI, etc.)      │
├─────────────────────────────────────────────────────────────┤
│                    Identity Kernel                         │
│     (tenant, federation, SCIM, admin realm, lifecycle)       │
├─────────────────────────────────────────────────────────────┤
│                     Auth Kernel                          │
│       (authentication, authorization, OAuth, MFA)          │
└─────────────────────────────────────────────────────────────┘
```

---

## Auth Kernel

**What it is:**

- Pure authentication and authorization primitives
- No tenant awareness
- No user provisioning
- No federation

**Shipped capabilities:**

- Password-based authentication (bcrypt/argon2)
- MFA (TOTP, backup codes, recovery)
- Session management and hardening
- OAuth 2.0 / OIDC client (auth code, refresh, client credentials)
- Sender-constrained tokens (DPoP, mTLS)
- JWT issuance and validation
- RBAC and permission policies
- Audit logging

**NOT part of auth kernel:**

- Tenant management
- User provisioning/scim
- Federation connections
- Client registration UI
- Admin elevation workflows

---

## Identity Kernel

**What it is:**

- Auth kernel + tenant/customer isolation
- User lifecycle management
- Federation
- Administrative controls

**Additional capabilities over auth kernel:**

- Tenant creation, membership, roles
- Tenant-scoped user management
- Federation (SAML/OIDC)
- SCIM /Users and /Groups
- Tenant security policies
- Admin realm (elevation)
- Lifecycle orchestration

**NOT part of identity kernel:**

- Full tenant-admin UI
- Cross-org approval
- SAML brokering
- External certification

---

## Full Identity Platform

**What it is NOT:**

- This package is NOT a full identity platform
- It does NOT ship complete UI
- It does NOT include SIEM/KMS/HSM integrations
- It does NOT have certified OIDC provider
- It does NOT have certified SCIM product

**What would be needed for full platform:**

- Complete admin UI
- SIEM integrations
- KMS/HSM support
- Email infrastructure
- External certification (e.g., SOC2, ISO27001)
- SAML brokering
- Cross-org approval workflows

---

## Quick Reference

| Capability         | Auth Kernel | Identity Kernel | Full Platform |
|--------------------|-------------|-----------------|---------------|
| Password auth      | ✅           | ✅               | ✅             |
| MFA                | ✅           | ✅               | ✅             |
| OAuth 2.0          | ✅           | ✅               | ✅             |
| JWT                | ✅           | ✅               | ✅             |
| Sessions           | ✅           | ✅               | ✅             |
| Sender constraints | ✅           | ✅               | ✅             |
| Tenants            | ❌           | ✅               | ✅             |
| Membership         | ❌           | ✅               | ✅             |
| SCIM               | ❌           | ✅               | ✅             |
| Federation         | ❌           | ✅               | ✅             |
| Admin elevation    | ❌           | ✅               | ✅             |
| Lifecycle          | ❌           | ✅               | ✅             |
| Admin UI           | ❌           | ❌               | ✅             |
| SIEM               | ❌           | ❌               | ✅             |
| Email              | ❌           | ❌               | ✅             |
| SAML brokering     | ❌           | ❌               | ✅             |
| Certification      | ❌           | ❌               | ✅             |

---

## Claiming the Right Scope

**Correct:**

- "Avax Auth is an authentication and authorization kernel"
- "This package provides identity primitives, not a full platform"
- "Use it to build your own identity solution"

**Incorrect:**

- "Avax Auth is an identity platform like Auth0"
- "It includes everything you need for user management"
- "Just install and you have full SSO"

## Canonical References

- `docs/STATUS.md` for shipped and non-goal truth
- `docs/capability-matrix.md` for executable evidence
- `docs/upgrade-migration-guide.md` for boundary and namespace migration
- `docs/supported-deployment-profiles.md` for deployment packaging
- `docs/choose-vs-external-idp.md` for product-positioning guidance

---

## Version

- Auth: 1.0.0+
- PHP: 8.5+
- Boundary: 1.0.0

---

*This document is authoritative for scope positioning.*
