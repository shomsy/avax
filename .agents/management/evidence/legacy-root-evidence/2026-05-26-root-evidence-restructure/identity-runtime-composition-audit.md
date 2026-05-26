# Identity Runtime Composition / Service Locator / Static Facade Audit

## Execution Metadata

| Field | Value |
|---|---|
| **Date** | 2026-05-24 |
| **Branch** | architecture/identity-runtime-convergence |
| **Worktree** | /home/shomsy/projects/avax-auth-rewrite-v2 |
| **Mode** | Harness-Full (11++ Enterprise Governance) |
| **Phase** | Phase 1 — Architecture Drift Discovery |

## Skill/Applicability Matrix

| Document/Skill | Loaded | Relevant | Used | Reason |
|---|---|---|---|---|
| AGENTS.md | YES | YES | YES | Root governance contract |
| .agents/how-to/architecture/how-to-runtime-composition.md | YES | YES | YES | Runtime composition leak laws |
| .agents/how-to/implementation/how-to-dependency-injection.md | YES | YES | YES | DI, ServiceProvider, container rules |
| avax-enterprise-codecraft | YES | YES | YES | Production code quality, SOLID, ownership |
| avax-security-threat-model | YES | YES | YES | Auth/session/token security boundaries |
| avax-api-compatibility-contract | YES | YES | YES | PublicSurface stability, backward compat |
| avax-runtime-performance-cache | YES | YES | YES | Long-lived worker safety, static state |
| avax-component-dogfooding | YES | YES | YES | Components must reuse AvaX capabilities |
| avax-source-of-truth-resolver | YES | YES | YES | Source-of-truth for Identity architecture |
| avax-test-evidence-quality | YES | YES | YES | Tests must prove behavior |
| avax-observability-failure-semantics | YES | YES | YES | Failure behavior, worker reset |

## Governance Loading

### How-To Files Applied

| File | Applied | Impact |
|---|---|---|
| how-to-runtime-composition.md | YES | Identified all runtime composition leaks |
| how-to-dependency-injection.md | YES | Identified all DI violations |

### Architecture Docs Read

| File | Purpose |
|---|---|
| components/Identity/**/*.php (620 files scanned) | Full component inventory |
| All ServiceProviders (7 found) | Composition root analysis |
| All PublicSurface classes (8 found) | API boundary analysis |
| All static capability classes | Static facade analysis |
| AuthBuilder (560 lines) | Assembly complexity analysis |

## Source-of-Truth Decision

**Current git state** is the source of truth. No stale evidence or TODO conflicts found for Identity component.

---

## Phase 1 Findings: Architecture Drift & Governance Violations

### BLOCKER Findings (Must Fix Before GREEN)

#### B1: Service Locator in PublicSurface — shortcuts.php
- **File:** `components/Identity/Auth/System/PublicSurface/shortcuts.php:17`
- **Pattern:** `return app(Auth::class)` — direct service locator in global helper
- **Severity:** BLOCKER
- **Rule Violated:** AGENTS.md §17, how-to-runtime-composition.md §11, how-to-dependency-injection.md §10.1
- **Impact:** Any code calling `auth()` performs runtime service location
- **Fix:** Replace with instance-based resolution at composition root, or document as deprecated userland convenience

#### B2: JwtAuth — Full Static Facade, No Lifecycle Management
- **File:** `components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php`
- **Pattern:** All-static class with mutable static state (`$jwtSigner`, `$tokenVerifier`, `$tokenBlacklist`)
- **Severity:** BLOCKER
- **Rule Violated:** how-to-runtime-composition.md §7.2 (static mutable state must have reset/setInstance), AGENTS.md §21 (long-lived worker state leaks)
- **Missing:** `reset()`, `setInstance()`, test injection, worker safety
- **Fix:** Convert to instance-based DI service OR add proper lifecycle management

#### B3: TokenBlacklist — Static Mutable Array, No Reset
- **Files:** `components/Identity/Tokens/System/Capabilities/JwtAuth/Tokens/TokenBlacklist.php`, `components/Identity/Tokens/System/Capabilities/JwtAuth/TokenBlacklist.php`
- **Pattern:** `private static array $revoked = []` — unbounded growth in long-lived workers
- **Severity:** BLOCKER
- **Rule Violated:** AGENTS.md §21 (unbounded array growth, mutable static without reset)
- **Fix:** Convert to instance-based DI service with reset capability

#### B4: Policy — Static Facade with Lazy Construction
- **File:** `components/Identity/Access/System/Capabilities/Policy/Policy.php`
- **Pattern:** Static facade with `private static PolicyEvaluator` and lazy `new PolicyEvaluator()`
- **Severity:** BLOCKER
- **Rule Violated:** how-to-runtime-composition.md §2.2 (new in runtime), §7.2 (static mutable state)
- **Missing:** `reset()`, `setInstance()`, DI registration
- **Fix:** Convert to DI-based service or add proper lifecycle management

#### B5: TenantContext — Static Facade with Lazy Construction
- **File:** `components/Identity/Tenancy/System/Capabilities/Context/TenantContext.php`
- **Pattern:** Static facade with lazy `new DefaultTenantContext()`
- **Severity:** BLOCKER
- **Rule Violated:** how-to-runtime-composition.md §2.2
- **Missing:** `reset()` (setContext exists but no reset)
- **Fix:** Add reset(), or convert to DI

#### B6: Credentials — Static Facade with Lazy Construction
- **File:** `components/Identity/Credentials/System/PublicSurface/Credentials.php`
- **Pattern:** Static facade with lazy `new InMemoryCredentialStore()`
- **Severity:** BLOCKER
- **Rule Violated:** how-to-runtime-composition.md §2.2, §7.2
- **Missing:** `reset()` (setStore exists but no reset)
- **Fix:** Add reset(), or convert to DI

#### B7: ExternalIdentity — Static Facade with Lazy Construction
- **File:** `components/Identity/ExternalIdentity/System/PublicSurface/ExternalIdentity.php`
- **Pattern:** Static facade with lazy `new InMemoryExternalIdentityLinkStore()`
- **Severity:** BLOCKER
- **Rule Violated:** how-to-runtime-composition.md §2.2, §7.2
- **Missing:** `reset()` (setLinkStore exists but no reset)
- **Fix:** Add reset(), or convert to DI

#### B8: Identity (top-level) — Static Facade Creates Sub-Surfaces via `new`
- **File:** `components/Identity/System/PublicSurface/Identity.php`
- **Pattern:** `return new Tenancy()`, `return new Credentials()`, `return new ExternalIdentity()`
- **Severity:** BLOCKER
- **Rule Violated:** AGENTS.md §17 (PublicSurface must not own runtime machinery)
- **Fix:** PublicSurface should delegate to DI-resolved instances or be deprecated

### HIGH Findings (Must Fix Before Production-Complete)

#### H1: TenantResolver — All Static, Not DI-Managed
- **File:** `components/Identity/Tenancy/System/Capabilities/Resolution/TenantResolver.php`
- **Pattern:** `final readonly class TenantResolver` with all-static methods + nested static resolver classes
- **Severity:** HIGH
- **Rule Violated:** how-to-dependency-injection.md §7.2 (resolver is a "machine" that should come from DI)
- **Fix:** Convert to instance-based DI service

#### H2: Tenancy PublicSurface — Static Delegation to Static Capabilities
- **File:** `components/Identity/Tenancy/System/PublicSurface/Tenancy.php`
- **Pattern:** All-static methods delegating to `TenantResolver::resolve()` and `TenantContext::current()`
- **Severity:** HIGH
- **Rule Violated:** AGENTS.md §17 (PublicSurface must delegate to injected instances)
- **Fix:** Convert to instance-based facade

#### H3: ServiceProviders Don't Register All Public Surfaces
- **Files:** TenancyServiceProvider, CredentialsServiceProvider, ExternalIdentityServiceProvider, IdentityServiceProvider
- **Pattern:** ServiceProviders register configuration/interfaces but not the PublicSurface classes
- **Severity:** HIGH
- **Rule Violated:** how-to-dependency-injection.md §4.0 (ServiceProvider must register component public API entrypoints)
- **Fix:** Register PublicSurface classes in respective ServiceProviders

#### H4: TokenCodec — Constructor Creates Dependency
- **File:** `components/Identity/Auth/System/Capabilities/Tokens/TokenCodec.php:26`
- **Pattern:** `$this->tokenCodec = new HmacTokenCodec(secret: $secret, algorithm: $algorithm)` in constructor
- **Severity:** HIGH
- **Rule Violated:** how-to-dependency-injection.md §3.4 (new Class() for services)
- **Fix:** Inject TokenCodecInterface

#### H5: InMemoryCredentialStore — No Reset for Worker Safety
- **File:** `components/Identity/Credentials/System/Capabilities/CredentialStore/InMemoryCredentialStore.php`
- **Pattern:** Mutable array state without reset method
- **Severity:** HIGH
- **Rule Violated:** AGENTS.md §21 (long-lived worker state leaks)
- **Fix:** Add reset() method or mark as request-scoped only

#### H6: InMemoryExternalIdentityLinkStore — No Reset for Worker Safety
- **File:** `components/Identity/ExternalIdentity/System/Capabilities/ExternalIdentityLink/InMemoryExternalIdentityLinkStore.php`
- **Pattern:** Mutable array state without reset method
- **Severity:** HIGH
- **Fix:** Add reset() method

### MEDIUM Findings

#### M1: AuthBuilder — 560 Lines, Excessive Complexity
- **File:** `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
- **Pattern:** Single builder with 190+ lines of constructor, 70+ delegate methods, 100+ lines of ready()
- **Severity:** MEDIUM
- **Rule Violated:** how-to-dependency-injection.md §4.8 (builder should stay small, delegate to builders)
- **Note:** Delegation exists to sub-graphs, but builder itself is too large

#### M2: AuthBuilder withContainer() Uses Container Pulls
- **File:** `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:384-428`
- **Pattern:** `$this->clock ??= $container->get(Clock::class)` — lazy container pulls within builder
- **Severity:** MEDIUM (builder is allowed container resolution, but ??= pattern suggests missing defaults)
- **Note:** Acceptable in builder context but could be more explicit

#### M3: TokensServiceProvider Reads $_ENV Directly
- **File:** `components/Identity/Tokens/System/Configuration/TokensServiceProvider.php:31`
- **Pattern:** `$secret = $_ENV['TOKEN_SECRET'] ?? throw ...`
- **Severity:** MEDIUM
- **Rule Violated:** how-to-dependency-injection.md §3.7 (env reads should be at configuration boundary)
- **Note:** Acceptable for deployment-time configuration but should be documented

### LOW Findings

#### L1: Multiple InMemory Stores Missing Reset for Worker Safety
- **Files:** InMemoryAuthorizationCodeStore, InMemoryRefreshTokenStore, InMemoryMfaStore, InMemoryMfaChallengeStore, InMemoryPasskeyChallengeStore, InMemoryPasskeyCredentialStore, InMemoryPasswordResetStore, InMemoryEmailVerificationStore, InMemoryEmailChangeStore, InMemoryEmailVerificationStateStore, InMemoryLifecycleStore, InMemoryScimDirectoryStore, InMemoryScimProvisionedIdentityStore, InMemoryTenantStore, InMemoryTenantSecurityConfigurationStore, InMemoryTenantSecurityChangeRequestStore, InMemoryAdminElevationStore, InMemoryKnownAuthenticationEnvironmentStore, InMemoryRiskSignalStore, InMemoryAttemptLimitStorage, InMemoryAttemptThrottleStore, InMemoryFederationConnectionStore, InMemoryFederatedIdentityLinkStore, InMemoryOidcRequestObjectStore
- **Pattern:** Mutable state without explicit reset lifecycle
- **Severity:** LOW (acceptable for dev/test but needs documentation)
- **Note:** These are dev defaults — production should use persistent stores

---

## Component Dogfooding Assessment

| Component | Uses AvaX Capabilities | Status |
|---|---|---|
| Identity/Auth | Uses Security/Hashing for PasswordHasher | GOOD |
| Identity/Auth | Uses framework Container | GOOD |
| Identity/Tokens | No external component reuse | NEEDS_REVIEW |
| Identity/Access | No external component reuse | NEEDS_REVIEW |
| Identity/Credentials | No external component reuse | NEEDS_REVIEW |

---

## Runtime Compilation Readiness Assessment

| Aspect | Current Status | Target | Gap |
|---|---|---|---|
| Explicit DI graph | Partial (ServiceProviders exist) | Full | Missing bindings for many public surfaces |
| Compiled metadata | None | Compiled route/DI graph | Future work |
| No hot-path reflection | GREEN | GREEN | No class_exists found in Identity |
| No service locator | RED | GREEN | shortcuts.php, static facades |
| Worker safety | RED | GREEN | Multiple static states without reset |
| Compile-time validation | Partial (AuthBootstrapValidator) | Full | More compile-time checks needed |

---

## Risk Assessment

| Risk | Impact | Mitigation |
|---|---|---|
| Breaking backward compatibility with static facades | HIGH | Keep deprecated facades working, add @deprecated |
| Large refactoring scope (620 files) | HIGH | Slice into bounded increments |
| Worker state leak in production | BLOCKER | Add reset() immediately, convert to DI incrementally |
| Auth shortcut helper removal breaks userland | MEDIUM | Deprecate, don't remove; document DI alternative |
