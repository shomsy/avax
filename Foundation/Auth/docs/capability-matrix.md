# Capability Matrix

Version: 2.0.0

This matrix is the canonical truth table for all capabilities. Use together with `docs/STATUS.md`.

| Capability | Status | Ownership | Evidence |
|---|---|---|---|
| **Auth Kernel** | | | |
| password hashing (bcrypt/argon2) | ✅ supported | kernel | `Capability/PasswordHashing/` |
| MFA (TOTP, backup codes) | ✅ supported | kernel | `Flow/Mfa/*` |
| password recovery | ✅ supported | kernel | `Flow/Recover/*` |
| session management | ✅ supported | kernel | `Capability/Session/`, `Flow/Session/*` |
| session revocation (SQL) | ✅ supported | kernel | `Capability/Session/PdoSessionRegistry.php` |
| session revocation (Redis) | ✅ supported | kernel | `Capability/Session/RedisSessionRegistry.php` |
| authorization (RBAC/ABAC) | ✅ supported | kernel | `Capability/Access/*` |
| admin realm / high-assurance | ✅ supported | kernel | `Flow/AdminRealm/*` |
| audit / logging | ✅ supported | kernel | `Capability/Audit/*` |
| key lifecycle | ✅ supported | kernel | `docs/crypto-key-lifecycle.md` |
| **OAuth / OIDC** | | | |
| OAuth 2.0 authorization code | ✅ supported | kernel | `Flow/OAuth/*` |
| PKCE | ✅ supported | kernel | `Capability/OAuth/Pkce.php` |
| refresh token rotation | ✅ supported | kernel | `Capability/OAuth/RefreshToken.php` |
| DPoP (sender constraint) | ✅ supported | kernel | `Capability/OAuth/SenderConstraint/*` |
| mTLS binding | ✅ supported | kernel | `Capability/OAuth/SenderConstraint/*` |
| OIDC discovery / JWKS | ✅ supported | kernel | `Capability/Oidc/*` |
| ID token issuance | ✅ supported | kernel | `Capability/Oidc/OidcIdToken.php` |
| userinfo endpoint | ✅ supported | kernel | `Flow/Oidc/UserInfo/*` |
| OIDC logout (front/back channel) | ✅ supported | kernel | `Flow/Oidc/FrontChannelLogout/*`, `Flow/Oidc/BackChannelLogout/*` |
| PAR (Push Authorization Request) | ✅ supported | kernel | `Flow/Oidc/PushAuthorizationRequest/*` |
| JARM (JWT Auth Response Mode) | ✅ supported | kernel | `Flow/Oidc/ReturnJwtAuthorizationResponse/*` |
| pairwise subject identifiers | ✅ supported | kernel | `Capability/Oidc/SubjectIdentifier.php` |
| client_credentials (workload) | ✅ supported | kernel | `Flow/OAuth/Grant/ClientCredentials/*` |
| dynamic client registration | ⚠️ partial | OAuth+tenant | Integrated via `Flow/OAuth/RegisterClient/*` |
| JAR client-signed request | ⚠️ partial | boundary | Signing not in kernel scope |
| **Tenant / Control-Plane** | | | |
| tenant lifecycle | ✅ supported | kernel | `Capability/Tenant/`, `Flow/Tenant/*` |
| membership management | ✅ supported | kernel | `Capability/Tenant/TenantMember.php` |
| invites | ✅ supported | kernel | `Flow/Tenant/InviteMember/*` |
| ownership transfer | ✅ supported | kernel | `Flow/Tenant/TransferOwnership/*` |
| tenant-owned OAuth clients | ✅ supported | kernel | `Capability/OAuth/OAuthClient.php` |
| tenant security policies | ✅ supported | kernel | `Capability/TenantSecurity/*` |
| **SCIM** | | | |
| SCIM /Users | ✅ supported | kernel | `Flow/Scim/Users/*` |
| SCIM /Groups | ✅ supported | kernel | `Flow/Scim/Groups/*` |
| schema discovery | ✅ supported | kernel | `Capability/Scim/SchemaDiscovery.php` |
| idempotency | ✅ supported | kernel | `Capability/Scim/IdempotencyKey.php` |
| drift remediation | ✅ supported | kernel | `Capability/Scim/DriftDetection.php` |
| sync health / throttling | ✅ supported | kernel | `Capability/Scim/ScimDirectoryHealth.php` |
| bulk operations | ✅ supported | kernel | `Flow/Scim/Bulk/*` |
| **Risk / Fraud** | | | |
| risk signals | ✅ supported | kernel | `Capability/Risk/*` |
| risk engine | ✅ supported | kernel | `Capability/Risk/DeterministicRiskEngine.php` |
| device trust assessment | ⚠️ partial | boundary | `Capability/DeviceTrust/*` (policy only) |
| **Quality & Governance** | | | |
| quality gates automation | ✅ supported | kernel | `tooling/quality-gates.php` |
| terminology glossary | ✅ supported | governance | `.rules/glossary/AUTH.md`, `SECURITY.md` |
| **Explicit Non-Goals** | | | |
| trusted device / remembered device | ❌ non-goal | external | `docs/trusted-device-policy.md` |
| external certification | ❌ non-goal | external | N/A |
| SAML brokering | ❌ non-goal | external | N/A |
| full tenant-admin UI | ❌ non-goal | external | N/A |

---

## Status Legend

| Symbol | Meaning |
|--------|---------|
| ✅ supported | Kernel owns, tests exist |
| ⚠️ partial | Kernel owns part, product envelope outside |
| ❌ non-goal | Explicitly not claimed |

---

## Evidence Links

Each capability has executable evidence. Run quality gates:

```bash
composer quality-gates
```

---

## Version

- Matrix: 2.0.0
- Auth: 1.0.0+
- Updated: 2026-04-14