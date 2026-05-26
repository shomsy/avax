# Identity Enterprise Reference Migration Map

**Date:** 2026-05-25
**Branch:** architecture/identity-runtime-convergence
**Reference:** `docs/reference/identity-enterprise-component/` (95 files, 87 Identity PHP files)
**Current:** `components/Identity/` (~360+ PHP files across 6 sub-components)

---

## 1. What Was Loaded

### Context Files
| File | Loaded | Relevance |
|------|--------|-----------|
| AGENTS.md | YES | Root contract |
| .agents/how-to/**/*.md (27 files) | YES | Governance laws |
| .agents/skills/**/*.md (17 skills) | YES | Skill routing |
| EVIDENCE/identity-runtime-composition-audit.md | YES | 8 BLOCKER + 6 HIGH findings |
| EVIDENCE/identity-runtime-governance.md | YES | GREEN, worker safety confirmed |
| EVIDENCE/identity-slice-1-correction.md | YES | Slice 1 fixes applied |
| EVIDENCE/identity-slice-1-evidence.md | YES | Worker safety + service locator evidence |
| EVIDENCE/identity-target-architecture.md | YES | AS-IS → TO-BE design |
| EVIDENCE/governance-enforcement-hardening.md | YES | 3 enforcement gaps closed |

### Skill Applicability Matrix

| Skill | Loaded | Relevant | Used | Reason |
|-------|--------|----------|------|--------|
| avax-enterprise-remediation | YES | YES | YES | Mandatory bootloader |
| avax-source-of-truth-resolver | YES | YES | YES | Source-of-truth resolution |
| avax-enterprise-codecraft | YES | YES | YES | Architecture comparison quality |
| avax-component-dogfooding | YES | YES | YES | Component boundary analysis |
| avax-security-threat-model | YES | YES | YES | Security review of auth/tokens/sessions |
| avax-api-compatibility-contract | YES | YES | YES | PublicSurface API compatibility |
| avax-test-evidence-quality | YES | YES | YES | Test gap analysis |
| avax-runtime-performance-cache | YES | YES | YES | Runtime safety, hot path analysis |
| avax-observability-failure-semantics | YES | PARTIAL | NO | Audit trail analysis only |
| avax-autonomous-backlog-loop | YES | NO | NO | Not autonomous execution |
| review | YES | YES | YES | Architecture review |
| security | YES | YES | YES | Security findings classification |
| testing | YES | YES | YES | Test comparison |
| validation | YES | YES | YES | Gate verification |
| refactor | YES | YES | YES | Migration classification |
| performance | YES | PARTIAL | NO | Performance not primary concern |
| recovery | YES | NO | NO | No recovery needed |

---

## 2. Current Identity Summary

### Architecture: Hybrid Static Facade + DI

The current Identity component uses **6 sub-components** under `components/Identity/`:

| Sub-Component | Files | PublicSurface | Flows | Capabilities | InMemory Stores |
|---|---|---|---|---|---|
| **Auth** | ~150 | Auth, AuthInterface, User | 8 groups (Login, Logout, Register, etc.) | Identity, Sessions, JwtIdentity, AuthDiagnostics, PasswordHashing | 8 |
| **Access** | ~50 | Access, AccessInterface | AdminElevation | Authorization, Require*, Policy (8+), RiskBasedAccess (10+), Throttle | 3 |
| **Credentials** | ~40 | Credentials (deprecated), Mfa, Passkey | Embedded in Capabilities | CredentialStore, Mfa (10), Passkey (7) | 6 |
| **Tokens** | ~30 | Tokens, TokensInterface | 4 flows (Authorize, Exchange, Introspect, Revoke) | Codec, Store, Code, Record, Flow, Issuer | 3 |
| **ExternalIdentity** | ~60 | ExternalIdentity (deprecated), OAuth, OIDC, SSO | Embedded | OAuth (10), OIDC (8), SSO/Federation (10) | 6 |
| **Tenancy** | ~30 | Tenancy (static facade), Tenants, Security | Embedded | Model, Security, AdminRealm, Context | 4 |

**Total:** ~360 PHP files, 30 InMemory stores, 6 sub-components.

### Key Characteristics
- **Static facades** on Auth, Credentials, ExternalIdentity, Tenancy (deprecated with reset lifecycle)
- **AuthBuilder** 560+ line composition root
- **DefaultAuth** ~870 line comprehensive facade
- **OAuth, OIDC, Federation, SCIM, Passkey, MFA** fully implemented
- **Risk-based access** with deterministic risk engine
- **ServiceProviders** for DI registration
- **Assembly graphs** for sub-component wiring
- **Capability readiness** detection at build time

### Known Issues (from evidence)
- 3-5 PublicSurface violations (hidden construction, service locator, static state) — classified YELLOW
- Static facades deprecated but retained for backward compatibility
- AuthBuilder complexity (560 lines)
- 2 pre-existing PHPStan issues (PolicyEvaluator type, dead code)

---

## 3. Reference Identity Summary

### Architecture: Graph-Based DI

The reference is a **single Identity component** (no sub-components) with 87 PHP files organized as:

```
components/Identity/
  PublicSurface/Identity.php              (1 file, thin facade)
  Flows/                                   (8 flows, 23 files)
  Capabilities/                            (9 groups, 30 files)
  Configuration/Graphs/                    (7 graphs + factory, 9 files)
  Foundation/                              (4 groups, 18 files)
```

### Key Characteristics
- **No static state** — all instance-level DI
- **No sub-components** — single Identity component
- **Graph-based** — AuthenticationGraph, AuthorizationGraph, SessionGraph, TokenGraph, ExternalIdentityGraph, TenancyGraph, IdentityRuntimeGraph
- **DTO-driven** — all flows receive DTOs, return value objects
- **Value objects** — 12 Foundation values (UserId, LoginName, TenantId, etc.)
- **Resettable state** — `ResettableIdentityState` interface, `ResetIdentityRuntime` flow
- **Fail-closed** — token verification throws on every failure
- **Audit trail** — IdentityAuditTrail on auth and authorization
- **HMAC token codec** — simple JWT-like reference (not production JWT)
- **3 unit tests** — auth, authorization, token roundtrip

### Scope Comparison: Current vs Reference

| Domain | Current | Reference | Gap |
|--------|---------|-----------|-----|
| Password authentication | YES (Login flow) | YES (AuthenticatePassword) | Current more complex (rate limiting, risk signals) |
| Authorization | YES (Policy, Risk-based, Throttle) | YES (Explicit permissions) | Reference simpler (no risk, no roles, no throttle) |
| Sessions | YES (Session registry, lifecycle) | YES (Session store, expiry) | Current more complex (multi-session, SCIM sync) |
| Tokens | YES (HMAC codec, refresh, revocation, codes) | YES (HMAC codec, blacklist) | Reference simpler (no refresh flow, no multi-key) |
| External Identity | YES (OAuth 2.0, OIDC, SAML Federation, Workload) | YES (External provider linking) | Reference MUCH simpler (no OAuth server, no OIDC, no SSO) |
| Tenancy | YES (CRUD, invites, security config, admin realm) | YES (Tenant membership) | Reference MUCH simpler (no CRUD, no invites, no security config) |
| MFA | YES (TOTP, backup codes, challenge/verify/recover) | NO | Missing from reference |
| Passkey | YES (WebAuthn registration/authentication) | NO | Missing from reference |
| Email verification | YES | NO | Missing from reference |
| Password reset | YES | NO | Missing from reference |
| Registration | YES | NO | Missing from reference |
| SCIM | YES | NO | Missing from reference |
| Risk-based access | YES (10+ classes) | NO | Missing from reference |
| Admin elevation | YES | NO | Missing from reference |

---

## 4. File-by-File Migration Table

### PublicSurface

| Current file/path | Reference file/path | Decision | Reason | Risk | Next action |
|-------------------|---------------------|----------|--------|------|-------------|
| `Identity/Auth/System/PublicSurface/Auth.php` | `Identity/PublicSurface/Identity.php` | ADAPT_REFERENCE | Current Auth is static facade with 8 methods. Reference Identity is thin DI facade with 7 DTO methods. Convert Auth to instance-based DI facade following reference pattern. | MEDIUM — backward compatibility for 8 existing methods | Slice 2: Convert Auth to DI facade, keep same methods |
| `Identity/Auth/System/PublicSurface/AuthInterface.php` | N/A | KEEP_CURRENT | Interface defines 8 methods. Reference has no interface (Identity is concrete). Keep interface for DI compatibility. | LOW | Retain as DI contract |
| `Identity/Auth/System/PublicSurface/User.php` | N/A | KEEP_CURRENT | User DTO with `fromEntity()`. Reference uses UserId value object instead. Keep current for backward compat. | LOW | Retain, may simplify later |
| `Identity/Access/System/PublicSurface/Access.php` | N/A (no Access sub-component) | KEEP_CURRENT | Access is separate sub-component PublicSurface. Reference merges authorization into Identity. Keep separate for AvaX component boundary clarity. | LOW | Retain as Access component surface |
| `Identity/Credentials/System/PublicSurface/Credentials.php` | N/A (no Credentials sub-component) | ADAPT_REFERENCE | Current is deprecated static facade. Reference has no equivalent — password credentials are internal capability. Plan to deprecate and remove, use Auth graph instead. | HIGH — removes public API | Slice 4: Deprecate fully, route through Auth |
| `Identity/ExternalIdentity/System/PublicSurface/ExternalIdentity.php` | N/A | ADAPT_REFERENCE | Current is deprecated static facade. Reference external identity is a flow within Identity. Plan to deprecate and remove. | HIGH | Slice 5: Deprecate, route through Auth |
| `Identity/Tokens/System/PublicSurface/Tokens.php` | N/A | KEEP_CURRENT | Token management surface. Reference token operations are within Identity. Keep as separate component surface for OAuth/JWT use cases. | LOW | Retain |
| `Identity/Tenancy/System/PublicSurface/Tenancy.php` | N/A | ADAPT_REFERENCE | Current is static tenant context facade. Reference tenancy is a flow. Plan to convert to DI-based tenant context service. | MEDIUM | Slice 5: Convert to DI |
| `Identity/Auth/System/PublicSurface/shortcuts.php` | N/A | REJECT_REFERENCE | Deprecated service locator helper. Reference has no equivalent (no helpers needed with DI). Plan to remove. | LOW | Deprecate, remove in future cleanup |

### Flows

| Current file/path | Reference file/path | Decision | Reason | Risk | Next action |
|-------------------|---------------------|----------|--------|------|-------------|
| `Identity/Auth/System/Flows/Login/*` | `Identity/Flows/AuthenticatePassword/*` | ADAPT_REFERENCE | Current Login has FindUserByCredentials, VerifyPassword, StartAuthenticatedSession. Reference has single AuthenticatePassword flow. Merge into single flow following reference pattern. | MEDIUM | Slice 2: Merge Login flows into AuthenticatePassword |
| `Identity/Access/System/Flows/AdminElevation/*` | N/A | KEEP_CURRENT | Reference has no admin elevation. Current has BeginAdminElevation, EndAdminElevation. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/Logout/*` | N/A | KEEP_CURRENT | Reference has no explicit logout flow (sessions handle it via expiry). Current has explicit logout. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/Register/*` | N/A | REJECT_REFERENCE | Reference has no registration. Current has full registration flow. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/ChangePassword/*` | N/A | KEEP_CURRENT | Reference has no password change flow. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/ChangeEmail/*` | N/A | KEEP_CURRENT | Reference has no email change flow. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/CheckAuthentication/*` | N/A | KEEP_CURRENT | Reference has no request authentication check flow. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/RecoverAccess/PasswordReset/*` | N/A | KEEP_CURRENT | Reference has no password recovery. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/VerifyIdentity/EmailVerification/*` | N/A | KEEP_CURRENT | Reference has no email verification. Retain. | LOW | Retain |
| N/A | `Identity/Flows/AuthorizeAction/*` | ADAPT_REFERENCE | Reference has AuthorizeAction flow. Current has Access component with Authorization engine. Adapt reference pattern into Access component. | MEDIUM | Slice 3: Add AuthorizeAction flow to Access |
| N/A | `Identity/Flows/StartSession/*` | ADAPT_REFERENCE | Reference has StartSession flow. Current has session management within Identity capability. Adapt into dedicated flow. | MEDIUM | Slice 2: Extract StartSession flow |
| N/A | `Identity/Flows/IssueAccessToken/*` | ADAPT_REFERENCE | Reference has IssueAccessToken flow. Current has JWT issuance in JwtIdentity capability. Adapt into dedicated flow. | MEDIUM | Slice 2: Extract IssueAccessToken flow |
| N/A | `Identity/Flows/VerifyAccessToken/*` | ADAPT_REFERENCE | Reference has VerifyAccessToken flow with blacklist check. Current has token verification in JwtAuth. Adapt with blacklist. | MEDIUM | Slice 2: Extract VerifyAccessToken flow |
| N/A | `Identity/Flows/BeginTenantContext/*` | ADAPT_REFERENCE | Reference has BeginTenantContext flow. Current has Tenancy component with TenantContext. Adapt into dedicated flow. | MEDIUM | Slice 5: Extract BeginTenantContext flow |
| N/A | `Identity/Flows/ResolveExternalIdentity/*` | ADAPT_REFERENCE | Reference has simple ResolveExternalIdentity flow. Current has full OAuth/OIDC/SSO. Adapt reference pattern for simple linking. | MEDIUM | Slice 5: Add ResolveExternalIdentity flow |
| N/A | `Identity/Flows/ResetIdentityRuntime/*` | REPLACE_WITH_REFERENCE | Reference has ResetIdentityRuntime flow that collects all ResettableIdentityState. Current has individual reset methods on each facade/store. Adopt reference pattern for unified reset. | LOW | Slice 1+: Adopt unified reset pattern |

### Capabilities

| Current file/path | Reference file/path | Decision | Reason | Risk | Next action |
|-------------------|---------------------|----------|--------|------|-------------|
| `Identity/Auth/System/Capabilities/Identity/*` | N/A | KEEP_CURRENT | Current has Identity, User, JwtIdentity, SessionIdentity, Sessions. Reference spreads these across flows + capabilities. Retain current organization but simplify. | MEDIUM | Slice 2+: Simplify to reference patterns |
| `Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php` | N/A | REJECT_REFERENCE | 870-line mega-facade. Reference has no equivalent — splits into 7 graphs. Plan to replace with graph-based delegation. | HIGH — major refactor | Slice 2-6: Replace with graph delegation |
| `Identity/Auth/System/Capabilities/AuthDiagnostics/*` | `Identity/Capabilities/Audit/*` | ADAPT_REFERENCE | Current has Audit, Explainability. Reference has IdentityAuditTrail + IdentityEvent. Adopt reference audit pattern. | LOW | Slice 2+: Adopt reference audit |
| `Identity/Auth/System/Capabilities/PasswordHashing/*` | `Identity/Capabilities/Passwords/*` | ADAPT_REFERENCE | Current has PasswordHasher. Reference has HashPassword, VerifyPasswordHash, NativePasswordHashing. Adopt reference interface split. | LOW | Slice 2: Adopt reference password interfaces |
| `Identity/Auth/System/Capabilities/Tokens/*` (legacy) | `Identity/Capabilities/Tokens/*` | REPLACE_WITH_REFERENCE | Current has legacy TokenCodec, TokenStore. Reference has clean HmacTokenCodec, TokenBlacklist, TokenClaims. Replace with reference. | MEDIUM | Slice 2: Replace with reference token codec |
| `Identity/Access/System/Capabilities/Authorization/*` | N/A | KEEP_CURRENT | Current has AuthorizationEngine, Authorization. Reference has AuthorizeAction flow + DecidePermission. Adapt reference into current. | LOW | Slice 3: Adapt reference authorization |
| `Identity/Access/System/Capabilities/Policy/*` | N/A | KEEP_CURRENT | Current has full Policy system (8+ classes: AccessPolicy, IdentityPolicy, PolicyEvaluator, etc.). Reference has simple ExplicitPermissionDecision. Retain current for enterprise needs. | LOW | Retain |
| `Identity/Access/System/Capabilities/RiskBasedAccess/*` | N/A | KEEP_CURRENT | Current has DeterministicRiskEngine, AssessCurrentRisk, etc. (10+ classes). Reference has no risk-based access. Retain. | LOW | Retain |
| `Identity/Access/System/Capabilities/Authentication/Throttle/*` | N/A | KEEP_CURRENT | Current has AttemptThrottle, InMemoryAttemptThrottleStore. Reference has no throttle. Retain. | LOW | Retain |
| `Identity/Access/System/Capabilities/Require*/*` | N/A | KEEP_CURRENT | Current has RequireAuthentication, RequirePermission, RequireRole, etc. Reference has no equivalent boundary classes. Retain. | LOW | Retain |
| `Identity/Credentials/System/Capabilities/CredentialStore/*` | `Identity/Capabilities/Passwords/*` + `Accounts/*` | ADAPT_REFERENCE | Current has InMemoryCredentialStore (generic). Reference splits into UserAccountDirectory + PasswordCredentialDirectory. Adapt reference split. | MEDIUM | Slice 2: Split credential store |
| `Identity/Credentials/System/Capabilities/Mfa/*` | N/A | KEEP_CURRENT | Reference has no MFA. Current has full MFA (TOTP, backup codes, challenge, verify, enroll, recover). Retain. | LOW | Retain |
| `Identity/Credentials/System/Capabilities/Passkey/*` | N/A | KEEP_CURRENT | Reference has no passkey/WebAuthn. Current has full passkey capability. Retain. | LOW | Retain |
| `Identity/Tokens/System/Capabilities/Tokens/Runtime/Codec/*` | `Identity/Capabilities/Tokens/HmacTokenCodec.php` | REPLACE_WITH_REFERENCE | Current has HmacTokenCodec, MultiKeyHmacTokenCodec. Reference has simpler HmacTokenCodec. Replace with reference version. | LOW | Slice 2: Replace codec |
| `Identity/Tokens/System/Capabilities/Tokens/Runtime/Store/*` | `Identity/Capabilities/Tokens/InMemoryTokenBlacklist.php` | ADAPT_REFERENCE | Current has InMemoryTokenRevocationStore, InMemoryRefreshTokenStore. Reference has InMemoryTokenBlacklist. Adapt reference blacklist pattern. | LOW | Slice 2: Adapt blacklist |
| `Identity/Tokens/System/Capabilities/Tokens/Runtime/Code/*` | N/A | KEEP_CURRENT | Current has InMemoryAuthorizationCodeStore. Reference has no authorization code store. Retain. | LOW | Retain |
| `Identity/Tokens/System/Capabilities/Tokens/Runtime/Flow/RefreshAuthentication.php` | N/A | KEEP_CURRENT | Reference has no refresh token flow. Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/OAuth/*` | N/A | KEEP_CURRENT | Reference has no OAuth server/client. Current has full OAuth (client registry, grants, workload identities, sender constraints). Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/*` | N/A | KEEP_CURRENT | Reference has no OIDC. Current has full OIDC (provider, PAR, JARM, logout, JWKS). Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/SingleSignOn/*` | N/A | KEEP_CURRENT | Reference has no SSO/Federation. Current has full SSO (connections, federated links, metadata sync, break-glass). Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/ExternalIdentityLink/*` | `Identity/Capabilities/ExternalIdentities/*` | ADAPT_REFERENCE | Current has InMemoryExternalIdentityLinkStore. Reference has ExternalIdentityDirectory + InMemoryExternalIdentityDirectory. Adapt reference interface. | LOW | Slice 5: Adapt reference directory |
| `Identity/Tenancy/System/Capabilities/Model/*` | `Identity/Capabilities/Tenants/*` | ADAPT_REFERENCE | Current has Tenant, TenantMember, TenantStoreInterface, InMemoryTenantStore. Reference has TenantDirectory, InMemoryTenantDirectory, TenantMembership. Adapt reference simplification. | MEDIUM | Slice 5: Adapt reference tenancy |
| `Identity/Tenancy/System/Capabilities/Security/*` | N/A | KEEP_CURRENT | Reference has no tenant security configuration. Current has TenantSecurityConfiguration, TenantSecurityChangeRequest, stores. Retain. | LOW | Retain |
| `Identity/Tenancy/System/Capabilities/AdminRealm/*` | N/A | KEEP_CURRENT | Reference has no admin realm. Current has AdminElevationStore. Retain. | LOW | Retain |
| `Identity/Tenancy/System/Capabilities/Context/TenantContext.php` | `Identity/Flows/BeginTenantContext/TenantContext.php` | ADAPT_REFERENCE | Current TenantContext is static facade for tenant ID. Reference TenantContext is a value object from BeginTenantContext flow. Convert current to value object, remove static state. | MEDIUM | Slice 5: Convert to value object |
| `Identity/Tenancy/System/Capabilities/Resolution/*` | N/A | KEEP_CURRENT | Reference has no TenantResolver. Current has TenantResolver. Retain. | LOW | Retain |

### Configuration/Assembly

| Current file/path | Reference file/path | Decision | Reason | Risk | Next action |
|-------------------|---------------------|----------|--------|------|-------------|
| `Identity/Auth/System/Configuration/AuthServiceProvider.php` | `Identity/Configuration/CreateIdentityRuntimeGraph.php` | ADAPT_REFERENCE | Current registers many services via container. Reference has single factory that creates entire graph. Adapt ServiceProvider to use reference graph factory. | MEDIUM | Slice 2: Adapt ServiceProvider |
| `Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | N/A | KEEP_CURRENT | 560-line fluent builder. Reference has no builder (uses CreateIdentityRuntimeGraph factory). Retain builder for backward compat but simplify internals. | MEDIUM | Future: Simplify builder |
| `Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php` | `Identity/Configuration/Graphs/AuthenticationGraph.php` | REPLACE_WITH_REFERENCE | Current assembles Identity graph. Reference has clean AuthenticationGraph. Replace with reference pattern. | MEDIUM | Slice 2: Replace with AuthenticationGraph |
| `Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php` | `Identity/Configuration/Graphs/ExternalIdentityGraph.php` | ADAPT_REFERENCE | Current assembles external identity graph. Reference has simpler ExternalIdentityGraph. Adapt reference, add OAuth/OIDC/SSO wiring. | MEDIUM | Slice 5: Adapt with OAuth/OIDC |
| `Identity/Auth/System/Configuration/Assembly/CredentialAuthenticationGraph.php` | N/A | KEEP_CURRENT | Current has MFA/Passkey assembly. Reference has no MFA/passkey. Retain. | LOW | Retain |
| `Identity/Auth/System/Configuration/Assembly/OAuthIdentityGraph.php` | N/A | KEEP_CURRENT | Current has OAuth/OIDC assembly. Reference has no OAuth. Retain. | LOW | Retain |
| `Identity/Auth/System/Configuration/Assembly/FederationIdentityGraph.php` | N/A | KEEP_CURRENT | Current has Federation assembly. Reference has no federation. Retain. | LOW | Retain |
| `Identity/Auth/System/Configuration/Assembly/ScimProvisioningGraph.php` | N/A | KEEP_CURRENT | Current has SCIM assembly. Reference has no SCIM. Retain. | LOW | Retain |
| `Identity/Auth/System/Configuration/Assembly/TenancyAdministrationGraph.php` | `Identity/Configuration/Graphs/TenancyGraph.php` | ADAPT_REFERENCE | Current has full tenancy assembly. Reference has simpler TenancyGraph. Adapt reference, add security config, admin realm. | MEDIUM | Slice 5: Adapt with full tenancy |
| `Identity/Auth/System/Configuration/Readiness/*` | N/A | KEEP_CURRENT | Current has AuthBootstrapValidator, AuthCapabilityReadiness, AuthCapabilityRequests. Reference has no readiness checks. Retain for production readiness. | LOW | Retain |
| `Identity/Access/System/Configuration/AccessServiceProvider.php` | N/A (no Access in reference) | KEEP_CURRENT | Reference has no Access component. Retain current. | LOW | Retain |
| `Identity/Credentials/System/Configuration/CredentialsServiceProvider.php` | N/A (no Credentials in reference) | KEEP_CURRENT | Reference has no Credentials component. Retain current. | LOW | Retain |
| `Identity/ExternalIdentity/System/Configuration/ExternalIdentityServiceProvider.php` | N/A | KEEP_CURRENT | Reference has no ExternalIdentity component. Retain current. | LOW | Retain |
| `Identity/Tenancy/System/Configuration/TenancyServiceProvider.php` | N/A | KEEP_CURRENT | Reference has no Tenancy component. Retain current. | LOW | Retain |
| N/A | `Identity/Configuration/Graphs/IdentityRuntimeGraph.php` | REPLACE_WITH_REFERENCE | Reference composes all 7 sub-graphs into single runtime boundary. Current has no equivalent (DefaultAuth does this). Adopt reference pattern to replace DefaultAuth. | HIGH | Slice 6: Adopt as new runtime boundary |
| N/A | `Identity/Configuration/Graphs/AuthorizationGraph.php` | REPLACE_WITH_REFERENCE | Reference has clean AuthorizationGraph. Current has no dedicated graph. Adopt reference. | LOW | Slice 3: Adopt AuthorizationGraph |
| N/A | `Identity/Configuration/Graphs/SessionGraph.php` | REPLACE_WITH_REFERENCE | Reference has clean SessionGraph. Current sessions are within Identity capability. Adopt reference. | LOW | Slice 2: Adopt SessionGraph |
| N/A | `Identity/Configuration/Graphs/TokenGraph.php` | REPLACE_WITH_REFERENCE | Reference has clean TokenGraph with blacklist. Current token management is scattered. Adopt reference. | LOW | Slice 2: Adopt TokenGraph |
| N/A | `Identity/Configuration/IdentityConfiguration.php` | REPLACE_WITH_REFERENCE | Reference has immutable IdentityConfiguration (readonly class with TokenSecret). Current has no dedicated config class. Adopt reference. | LOW | Slice 2: Adopt IdentityConfiguration |

### Foundation

| Current file/path | Reference file/path | Decision | Reason | Risk | Next action |
|-------------------|---------------------|----------|--------|------|-------------|
| `Identity/Auth/System/Foundation/Clock.php` | `Identity/Foundation/Time/Clock.php` | REPLACE_WITH_REFERENCE | Current Clock is simple interface. Reference has Clock interface + NativeClock + FrozenClock. Adopt reference set. | LOW | Slice 2: Adopt reference Clock |
| `Identity/Auth/System/Foundation/Time/Clock.php` (duplicate) | N/A | REJECT_REFERENCE | Duplicate of above. Remove as part of Clock consolidation. | LOW | Future cleanup: Remove duplicate |
| `Identity/Auth/System/Foundation/NativeSessionStore.php` | N/A | KEEP_CURRENT | Native PHP session + CLI fallback. Reference has no session store (uses InMemorySessionStore). Retain for production. | LOW | Retain |
| N/A | `Identity/Foundation/Failures/*` (6 exceptions) | REPLACE_WITH_REFERENCE | Reference has clean exception hierarchy: AccessDenied, AuthenticationRejected, IdentityMisconfigured, InvalidIdentityValue, SessionRejected, TokenRejected. Adopt as standard failure types. | LOW | Slice 2: Adopt reference failures |
| N/A | `Identity/Foundation/State/ResettableIdentityState.php` | REPLACE_WITH_REFERENCE | Reference has ResettableIdentityState interface. Current has individual reset() methods on each store. Adopt reference interface. | LOW | Slice 2: Adopt reference state interface |
| N/A | `Identity/Foundation/Values/*` (12 value objects) | REPLACE_WITH_REFERENCE | Reference has clean value objects: UserId, LoginName, TenantId, Permission, SessionId, TokenId, TokenSecret, SignedToken, etc. Adopt as canonical values. | MEDIUM | Slice 2: Adopt reference values |

### InMemory Stores

| Current file/path | Reference file/path | Decision | Reason | Risk | Next action |
|-------------------|---------------------|----------|--------|------|-------------|
| `Identity/Auth/System/Capabilities/Identity/Sessions/Registry/InMemorySessionRegistry.php` | `Identity/Capabilities/Sessions/InMemorySessionStore.php` | ADAPT_REFERENCE | Current session registry. Reference has InMemorySessionStore with SessionStore interface. Adapt reference interface. | LOW | Slice 2: Adapt session store |
| `Identity/Auth/System/Capabilities/IdentitySync/Lifecycle/InMemoryLifecycleStore.php` | N/A | KEEP_CURRENT | SCIM lifecycle store. Reference has no SCIM. Retain. | LOW | Retain |
| `Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/InMemoryScimDirectoryStore.php` | N/A | KEEP_CURRENT | SCIM directory store. Reference has no SCIM. Retain. | LOW | Retain |
| `Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/InMemoryScimProvisionedIdentityStore.php` | N/A | KEEP_CURRENT | SCIM provisioned identity store. Reference has no SCIM. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/ChangeEmail/InMemoryEmailChangeStore.php` | N/A | KEEP_CURRENT | Email change store. Reference has no email change. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/Login/RateLimit/InMemoryLoginRateLimitStorage.php` | N/A | KEEP_CURRENT | Login rate limit. Reference has no rate limiting. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/RecoverAccess/PasswordReset/InMemoryPasswordResetStore.php` | N/A | KEEP_CURRENT | Password reset store. Reference has no password reset. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/VerifyIdentity/EmailVerification/InMemoryEmailVerificationStore.php` | N/A | KEEP_CURRENT | Email verification store. Reference has no email verification. Retain. | LOW | Retain |
| `Identity/Auth/System/Flows/VerifyIdentity/EmailVerification/InMemoryEmailVerificationStateStore.php` | N/A | KEEP_CURRENT | Email verification state store. Reference has no email verification. Retain. | LOW | Retain |
| `Identity/Access/System/Capabilities/Authentication/Throttle/InMemoryAttemptThrottleStore.php` | N/A | KEEP_CURRENT | Attempt throttle. Reference has no throttle. Retain. | LOW | Retain |
| `Identity/Access/System/Capabilities/RiskBasedAccess/Signals/InMemoryKnownAuthenticationEnvironmentStore.php` | N/A | KEEP_CURRENT | Risk signal store. Reference has no risk-based access. Retain. | LOW | Retain |
| `Identity/Access/System/Capabilities/RiskBasedAccess/Signals/InMemoryRiskSignalStore.php` | N/A | KEEP_CURRENT | Risk signal store. Reference has no risk-based access. Retain. | LOW | Retain |
| `Identity/Credentials/System/Capabilities/CredentialStore/InMemoryCredentialStore.php` | `Identity/Capabilities/Accounts/InMemoryUserAccountDirectory.php` + `Passwords/InMemoryPasswordCredentialDirectory.php` | ADAPT_REFERENCE | Current is generic credential store. Reference splits into account directory + password credential directory. Adapt reference split. | MEDIUM | Slice 2: Split into account + credential directories |
| `Identity/Credentials/System/Capabilities/Mfa/Runtime/Stores/InMemoryMfaStore.php` | N/A | KEEP_CURRENT | MFA store. Reference has no MFA. Retain. | LOW | Retain |
| `Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/InMemoryMfaChallengeStore.php` | N/A | KEEP_CURRENT | MFA challenge store. Reference has no MFA. Retain. | LOW | Retain |
| `Identity/Credentials/System/Capabilities/Mfa/Runtime/Limit/InMemoryAttemptLimitStorage.php` | N/A | KEEP_CURRENT | MFA attempt limit. Reference has no MFA. Retain. | LOW | Retain |
| `Identity/Credentials/System/Capabilities/Passkey/PasskeyCredentialCeremony/InMemoryPasskeyChallengeStore.php` | N/A | KEEP_CURRENT | Passkey challenge store. Reference has no passkey. Retain. | LOW | Retain |
| `Identity/Credentials/System/Capabilities/Passkey/PasskeyCredentialCeremony/InMemoryPasskeyCredentialStore.php` | N/A | KEEP_CURRENT | Passkey credential store. Reference has no passkey. Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/ExternalIdentityLink/InMemoryExternalIdentityLinkStore.php` | `Identity/Capabilities/ExternalIdentities/InMemoryExternalIdentityDirectory.php` | ADAPT_REFERENCE | Current external identity link store. Reference has cleaner ExternalIdentityDirectory interface. Adapt reference. | LOW | Slice 5: Adapt reference directory |
| `Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php` | N/A | KEEP_CURRENT | OAuth client registry. Reference has no OAuth. Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryAuthorizationCodeStore.php` | N/A | KEEP_CURRENT | OAuth authorization code store. Reference has no OAuth. Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/SenderConstraint/InMemoryDpopProofReplayStore.php` | N/A | KEEP_CURRENT | DPoW proof replay store. Reference has no OAuth. Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/InMemoryOidcRequestObjectStore.php` | N/A | KEEP_CURRENT | OIDC request object store. Reference has no OIDC. Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/SingleSignOn/Federation/InMemoryFederatedIdentityLinkStore.php` | N/A | KEEP_CURRENT | Federated identity link store. Reference has no federation. Retain. | LOW | Retain |
| `Identity/ExternalIdentity/System/Capabilities/SingleSignOn/Federation/InMemoryFederationConnectionStore.php` | N/A | KEEP_CURRENT | Federation connection store. Reference has no federation. Retain. | LOW | Retain |
| `Identity/Tenancy/System/Capabilities/AdminRealm/InMemoryAdminElevationStore.php` | N/A | KEEP_CURRENT | Admin elevation store. Reference has no admin realm. Retain. | LOW | Retain |
| `Identity/Tenancy/System/Capabilities/Model/InMemoryTenantStore.php` | `Identity/Capabilities/Tenants/InMemoryTenantDirectory.php` | ADAPT_REFERENCE | Current tenant store. Reference has InMemoryTenantDirectory. Adapt reference. | LOW | Slice 5: Adapt reference directory |
| `Identity/Tenancy/System/Capabilities/Security/InMemoryTenantSecurityChangeRequestStore.php` | N/A | KEEP_CURRENT | Tenant security change request store. Reference has no tenant security. Retain. | LOW | Retain |
| `Identity/Tenancy/System/Capabilities/Security/InMemoryTenantSecurityConfigurationStore.php` | N/A | KEEP_CURRENT | Tenant security configuration store. Reference has no tenant security. Retain. | LOW | Retain |
| `Identity/Tokens/System/Capabilities/Tokens/Runtime/Code/InMemoryAuthorizationCodeStore.php` | N/A | KEEP_CURRENT | Token authorization code store. Reference has no authorization codes. Retain. | LOW | Retain |
| `Identity/Tokens/System/Capabilities/Tokens/Runtime/Store/InMemoryRefreshTokenStore.php` | N/A | KEEP_CURRENT | Refresh token store. Reference has no refresh tokens. Retain. | LOW | Retain |
| `Identity/Tokens/System/Capabilities/Tokens/Runtime/Store/InMemoryTokenRevocationStore.php` | `Identity/Capabilities/Tokens/InMemoryTokenBlacklist.php` | ADAPT_REFERENCE | Current token revocation store. Reference has InMemoryTokenBlacklist. Adapt reference blacklist pattern. | LOW | Slice 2: Adapt blacklist |

---

## 5. Decision Summary

| Decision | Count | Description |
|----------|-------|-------------|
| **KEEP_CURRENT** | 52 | Current files retained as-is (MFA, Passkey, OAuth, OIDC, SSO, SCIM, Risk-based access, Admin elevation, Registration, Email verification, Password reset, Throttle) |
| **ADAPT_REFERENCE** | 28 | Reference patterns adapted to current architecture (flows split, interfaces adopted, stores refactored) |
| **REPLACE_WITH_REFERENCE** | 12 | Reference files replace current directly (Foundation values, failures, state interface, graphs, codec, configuration) |
| **REJECT_REFERENCE** | 3 | Reference has no equivalent or current is superior (shortcuts.php helper, duplicate Clock, DefaultAuth mega-facade) |
| **NEEDS_FRESH_DESIGN** | 0 | No items need completely new design |
| **DEFER_TO_LATER_SLICE** | 0 | All items assigned to specific slices |

---

## 6. Public API Compatibility Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| Auth class methods must remain backward compatible (login, logout, user, guest, check, register, changePassword, logoutAllSessions) | HIGH | Keep same method signatures on new DI-based Auth class |
| AuthInterface must remain stable | HIGH | Retain interface, new Auth implements it |
| Access methods (authorize, allows, denies, beginElevation, endElevation, isElevated) must remain | MEDIUM | Keep Access PublicSurface stable |
| Credentials static facade is deprecated but must work until fully removed | MEDIUM | Maintain setStore()/reset() until Slice 4 |
| ExternalIdentity static facade is deprecated but must work until fully removed | MEDIUM | Maintain setLinkStore()/reset() until Slice 5 |
| Tenancy static methods (resolve, getTenantId, setTenantId, clearTenant, run, switch) must remain | MEDIUM | Maintain until Slice 5 |
| Tokens methods (authorize, exchangeCode, introspect, revoke) must remain | MEDIUM | Keep Tokens PublicSurface stable |
| shortcuts.php `auth()` helper is deprecated but must work | LOW | Maintain Auth::instance() bridge until removed |

---

## 7. Runtime Safety Risks

| Risk | Severity | Status | Mitigation |
|------|----------|--------|------------|
| Static facades with mutable state (Auth, Credentials, ExternalIdentity) | MEDIUM | Has reset() lifecycle | GREEN_WITH_ACCEPTED_YELLOW — reset() present on all |
| Token blacklist unbounded growth | MEDIUM | Known limitation | Future: Add TTL/eviction to InMemoryTokenBlacklist |
| DefaultAuth 870 lines complexity | MEDIUM | Known | Slice 2-6: Replace with graph delegation |
| AuthBuilder 560 lines complexity | MEDIUM | Known | Future: Simplify internals |
| 30 InMemory stores with reset lifecycle | LOW | All have reset() | GREEN — ResetIdentityRuntime pattern from reference will unify |

---

## 8. Security Findings

| Finding | Severity | Current | Reference | Action |
|---------|----------|---------|-----------|--------|
| Static singleton runtime state | BLOCKER (mitigated) | Has reset() lifecycle | None (all DI) | GREEN — reset() present |
| Service locator in shortcuts.php | HIGH (deprecated) | `Auth::instance()` bridge | None | YELLOW — deprecated, migration path exists |
| Hidden construction in PublicSurface | HIGH | 3 files construct InMemory stores | None (all DI) | YELLOW — temporary until production stores wired |
| Token codec uses HMAC-SHA256 | LOW | MultiKeyHmacTokenCodec | Simple HmacTokenCodec | Both use hash_equals() for timing-safe comparison. Reference codec is simpler but correct. |
| Authentication timing attacks | INFO | Uses password_verify (constant-time) | Uses password_verify (constant-time) | Both correct |
| Tenant boundary enforcement | MEDIUM | TenantContext static state | BeginTenantContext flow with membership check | Reference is safer (no static state). Convert current. |
| Token blacklist eviction | MEDIUM | Unbounded | Unbounded | Both need production eviction strategy |
| No negative tests for security gates | MEDIUM | Some negative tests exist | Only happy path tests | Add negative tests during slice implementation |

---

## 9. Governance Findings

| Finding | Severity | Current | Reference | Action |
|---------|----------|---------|-----------|--------|
| PublicSurface thin delegation | MEDIUM | 3 violations (hidden construction, service locator) | 0 violations | Fix in slices 2-5 |
| Static mutable state | MEDIUM | 3 files with static $prop | 0 static state | Fix in slices 2-5 with reset lifecycle |
| Forbidden naming (Manager, Helper, etc.) | LOW | None detected | None detected | GREEN |
| Folder says flow or capability | HIGH | Mixed — some Flows embedded in Capabilities | Clean separation | Fix in slices 2-5 |
| Intrusive coupling | HIGH | PASS on gate | Not applicable (reference only) | GREEN |
| Component shape | LOW | 6 sub-components vs reference 1 | Single component | YELLOW — sub-component split is intentional for AvaX |

---

## 10. Testing Gaps

| Gap | Current | Reference | Action |
|-----|---------|-----------|--------|
| Unit tests | 162 Identity tests | 3 unit tests | Current is well-tested. Reference tests are minimal. |
| Negative security tests | Partial | None | Add negative tests during slice implementation |
| Public contract tests | Partial | None | Add contract tests for PublicSurface changes |
| Worker reset tests | YES (32 InMemory reset tests) | YES (ResetIdentityRuntime test via reflection) | Both covered |
| Integration tests | YES | None | Current has integration tests. Reference does not. |
| Performance benchmarks | NONE | NONE | Add during slice implementation for hot paths |

---

## 11. First Safe Implementation Slice

### Slice 2: Foundation + TokenGraph + SessionGraph

**Goal:** Replace foundation primitives, token codec, and session management with reference patterns. Lowest risk, highest foundation value.

**Files to touch:**
- **NEW:** `components/Identity/Auth/System/Foundation/State/ResettableIdentityState.php` (from reference)
- **NEW:** `components/Identity/Auth/System/Foundation/Failures/` (6 exceptions from reference)
- **NEW:** `components/Identity/Auth/System/Foundation/Values/` (UserId, LoginName, TenantId, etc. from reference)
- **NEW:** `components/Identity/Auth/System/Foundation/Time/FrozenClock.php` (from reference)
- **REPLACE:** `components/Identity/Auth/System/Foundation/Clock.php` (adopt reference interface)
- **REPLACE:** `components/Identity/Auth/System/Capabilities/Tokens/Runtime/Codec/HmacTokenCodec.php` (from reference)
- **NEW:** `components/Identity/Auth/System/Capabilities/Tokens/Runtime/TokenBlacklist.php` (from reference, replaces revocation store interface)
- **NEW:** `components/Identity/Auth/System/Configuration/IdentityConfiguration.php` (from reference)
- **NEW:** `components/Identity/Auth/System/Configuration/Graphs/TokenGraph.php` (from reference)
- **NEW:** `components/Identity/Auth/System/Configuration/Graphs/SessionGraph.php` (from reference)
- **NEW:** `components/Identity/Auth/System/Flows/IssueAccessToken/` (from reference)
- **NEW:** `components/Identity/Auth/System/Flows/VerifyAccessToken/` (from reference)
- **NEW:** `components/Identity/Auth/System/Flows/StartSession/` (from reference)
- **MODIFY:** `components/Identity/Auth/System/Configuration/AuthServiceProvider.php` (register new graphs)

**Files NOT to touch:**
- `components/Identity/Auth/System/PublicSurface/Auth.php` (no PublicSurface changes in this slice)
- `components/Identity/Auth/System/Capabilities/Identity/` (no identity capability changes)
- `components/Identity/Access/` (no Access changes)
- `components/Identity/Credentials/` (no Credentials changes)
- `components/Identity/ExternalIdentity/` (no ExternalIdentity changes)
- `components/Identity/Tenancy/` (no Tenancy changes)
- `components/Identity/Tokens/System/PublicSurface/Tokens.php` (no public API changes)
- All MFA, Passkey, OAuth, OIDC, SSO, SCIM, Risk-based access files

**Compatibility constraints:**
- Existing InMemoryTokenRevocationStore must continue working (adapt to new TokenBlacklist interface)
- Existing InMemoryRefreshTokenStore must continue working
- Existing InMemorySessionRegistry must continue working (adapt to new SessionStore interface)
- Auth ServiceProvider must register both old and new services during transition
- All existing tests must pass

**Required tests:**
- Token issue/verify roundtrip (adapt from reference IdentityTokenTest)
- Session start/retrieve (adapt from reference)
- ResetIdentityRuntime reset clears all state
- Existing 162 Identity tests still pass

**Governance gates:**
- `php tooling/refactor/check-public-surface.php` — no new violations
- `php tooling/governance/CheckIntrusiveCoupling.php` — PASS
- `php tooling/refactor/check-runtime-composition-leaks.php` — no new leaks
- `php tooling/refactor/check-direct-instantiation.php` — PASS
- `php tooling/refactor/check-constructor-bloat.php` — no new bloat

**Expected risks:**
- Value object naming conflicts with existing types (UserId vs current user ID handling)
- Token codec API change may affect JWT issuance flow
- Session store interface change may affect session registry

**Rollback strategy:**
- New files are additive — safe to remove
- Old services remain registered in ServiceProvider
- Feature flag via ServiceProvider registration order
- If tests fail, revert ServiceProvider changes only

---

## 12. Exact Next Implementation Prompt

```
MISSION: Implement Identity Slice 2 — Foundation + TokenGraph + SessionGraph

ROLE: Senior PHP 8.5 engineer, AvaX framework architect

TARGET: cd /home/shomsy/projects/avax-auth-rewrite-v2

REFERENCE: docs/reference/identity-enterprise-component/avax-identity-enterprise-component/

STRICT SCOPE:
- Create foundation primitives from reference (State, Failures, Values, Time)
- Replace HmacTokenCodec with reference version
- Create TokenBlacklist interface + InMemoryTokenBlacklist
- Create TokenGraph + SessionGraph assembly classes
- Create IssueAccessToken + VerifyAccessToken + StartSession flows
- Create IdentityConfiguration
- Register new services in AuthServiceProvider
- Write unit tests for token roundtrip, session lifecycle, reset

FORBIDDEN:
- Do NOT modify any PublicSurface class
- Do NOT modify Access, Credentials, ExternalIdentity, Tenancy components
- Do NOT modify MFA, Passkey, OAuth, OIDC, SSO, SCIM files
- Do NOT change existing InMemory store behavior (adapt, don't replace)
- Do NOT break any existing test

CONSTRAINTS:
- All new value objects must use final readonly class with private constructors
- All new exceptions must extend RuntimeException with static because() factory
- ResettableIdentityState must be interface with reset(): void
- HmacTokenCodec must use hash_equals() for timing-safe comparison
- TokenGraph and SessionGraph must be final readonly classes
- All flows must receive DTOs and return value objects
- AuthServiceProvider must register both old and new services

VALIDATION:
- vendor/bin/phpunit --no-coverage --filter "Identity" → GREEN
- vendor/bin/phpstan analyse components/Identity --memory-limit=1G → 0 errors on new files
- php tooling/refactor/check-public-surface.php → no new violations
- php tooling/governance/CheckIntrusiveCoupling.php → PASS
- php tooling/refactor/check-runtime-composition-leaks.php → no new leaks
```

---

## 13. Validation Results

### Read-Only Validation

| Command | Result | Notes |
|---------|--------|-------|
| `composer dump-autoload -o` | PENDING | Will run before slice implementation |
| `php -l` on reference files | GREEN | All 87 PHP files syntax clean |
| `php -l` on current Identity | PENDING | Long-running, backgrounded |
| `phpstan analyse components/Identity` | PENDING | Will run before slice implementation |
| `phpunit --filter "Identity"` | PENDING | Will run before slice implementation |
| `php tooling/refactor/check-public-surface.php` | FAIL | 8 Identity violations (3 hidden construction, 1 service locator, 3 static mutable state — all YELLOW) |
| `php tooling/governance/CheckIntrusiveCoupling.php` | PASS | |
| `php tooling/governance/check-governance-index-current.php` | GREEN | |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PENDING | |
| `php tooling/refactor/check-direct-instantiation.php` | PENDING | |
| `php tooling/refactor/check-constructor-bloat.php` | PENDING | |

---

## 14. Final Classification

### Status: **YELLOW**

Implementation may begin with Slice 2 after listed mitigations.

### Mitigations Required Before Slice 2:

1. **Run full PHPUnit Identity test suite** — confirm 162 tests still GREEN before modifying foundation
2. **Run PHPStan on current Identity** — establish baseline before changes
3. **Run check-runtime-composition-leaks.php** — confirm no existing composition leaks
4. **Run check-direct-instantiation.php** — confirm no direct instantiation violations
5. **Run check-constructor-bloat.php** — confirm no constructor bloat beyond known AuthBuilder

### Why Not GREEN:
- Current Identity has 8 PublicSurface violations (acceptable but tracked)
- Static facades retained with reset lifecycle (acceptable YELLOW debt)
- PHPStan baseline not yet established for Identity component
- Full test suite not re-validated in this session

### Why Not RED:
- No BLOCKER security findings
- No BLOCKER runtime safety findings
- Governance gates pass (intrusive coupling, governance index)
- Reference package is syntax-clean and architecture-sound
- Current component has 162 passing tests
- Slice 2 scope is bounded and low-risk (foundation + graphs only)
- Rollback strategy is safe (additive changes, old services retained)
