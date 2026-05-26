# Identity Component Architecture Triage Report

**Date:** 2026-05-25
**Component:** `components/Identity/`
**Scope:** Read-only triage of Identity component architecture, governance compliance, security invariants, and test quality
**Status:** RED

---

## Executive Summary

The Identity component has strong architectural foundations: canonical folder shape (PublicSurface/Flows/Capabilities/Configuration/Foundation), fluent builder pattern with sub-graph delegation, comprehensive sub-domain boundaries (Auth, Access, Tokens, Tenancy, Credentials, ExternalIdentity), and deprecated static facades with migration paths. However, critical security gaps, naming violations, and InMemory store leakage into production paths prevent any GREEN classification.

**Score: 4 / 11**

| Area | Score | Notes |
|------|-------|-------|
| Canonical Shape | 9/11 | Folder structure follows AvaX law well |
| Security Invariants | 2/11 | Stub Authenticate flow, exposed secrets, no fail-closed tests |
| DI / Assembly | 5/11 | Builder delegates well but 560 LOC, `withContainer()` uses container as service locator |
| Naming | 6/11 | JwtSigner duplicate, Credentials collision |
| Runtime Safety | 4/11 | Static facades with mutable state, reset() exists but not enforced |
| Test Quality | 4/11 | 36 unit + 5 arch tests exist for 648 PHP files; negative coverage unclear |
| InMemory Leakage | 3/11 | 15+ InMemory stores wired through builder defaults |
| PublicSurface | 7/11 | Auth facade is thin, but Credentials/ExternalIdentity/TenantContext are deprecated static |
| Worker Safety | 5/11 | reset() methods exist on static facades, but no lifecycle enforcement |
| Governance Compliance | 3/11 | Multiple AGENTS.md violations (BLOCKER stub, suppression-pattern, service locator) |
| Overall | **4/11** | Strong bones, critical flesh gaps |

---

## Classification Summary

| Severity | Count | Blocks GREEN |
|----------|-------|:---:|
| BLOCKER | 3 | YES |
| HIGH | 6 | YES |
| MEDIUM | 4 | NO* |
| LOW | 2 | NO |
| ACCEPTED_EXCEPTION | 1 | NO |

\* MEDIUM blocks GREEN only when untracked. These are tracked here.

---

## BLOCKER Findings

### BLOCKER-01: Security-Critical Flow Is Stub

**severity:** BLOCKER
**finding:** `Authenticate::authenticate()` returns `true` unconditionally
**governance_source:** AGENTS.md §1 Law 11 (security-sensitive behavior must fail closed), §22 Security Rule
**where:** `components/Identity/System/Flows/Authenticate/Authenticate.php:9-11`
**why_it_matters:** Any code path that calls this flow authenticates any credentials without verification. This is an authentication bypass. In a real deployment, any user with any password would be authenticated.
**required_action:** Replace stub with real credential verification flow that: (1) reads user from UserSourceInterface, (2) compares hashed password via PasswordHasher, (3) fails closed on missing user, invalid hash, or hash mismatch. Add negative tests proving rejection of invalid credentials.
**suggested_slice_boundary:** `security/todo-001-replace-authenticate-stub` — implement real authentication, add positive/negative tests, wire into Login flow
**validation_command:** `vendor/bin/phpunit --filter=Authenticate && vendor/bin/phpstan analyse components/Identity/System/Flows/Authenticate`
**blocks_GREEN:** YES — authentication bypass is exploitable

### BLOCKER-02: JwtSigner Duplicate Class Name

**severity:** BLOCKER
**finding:** Two classes named `JwtSigner` in adjacent namespaces with completely different implementations
**governance_source:** AGENTS.md §12 Fundamental Architecture Law (folder says flow or capability, unit says responsibility), §15 Strict Naming
**where:**
- `components/Identity/Tokens/System/Capabilities/JwtAuth/Signing/JwtSigner.php` — real signer using Firebase JWT
- `components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/JwtSigner.php` — empty stub `class JwtSigner {}`
**why_it_matters:** Class name collision in the same capability domain causes autoloading ambiguity, makes code review impossible, and the Verification variant is an empty class that will silently fail at runtime. `new JwtSigner()` behavior depends on which namespace is imported — this is a correctness hazard.
**required_action:** Rename `Verification/JwtSigner.php` to `JwtVerifier.php` implementing a proper verification interface. The empty stub must either be implemented or removed. Signing and verification are distinct responsibilities and must have distinct class names.
**suggested_slice_boundary:** `architecture/todo-002-rename-jwt-verifier` — rename, implement interface, fix all references, add verification tests
**validation_command:** `vendor/bin/phpstan analyse components/Identity/Tokens/System/Capabilities/JwtAuth`
**blocks_GREEN:** YES — naming collision + empty class in security-critical path

### BLOCKER-03: Static Facade Runtime State (TenantContext)

**severity:** BLOCKER
**finding:** `TenantContext` stores mutable tenant state in `private static ?TenantContextInterface $context = null`
**governance_source:** AGENTS.md §1 Law 10 (no hidden mutable state), §17 PublicSurface Rule (forbidden: hidden mutable state), canonical severity system (runtime state stored in singleton = BLOCKER)
**where:** `components/Identity/Tenancy/System/Capabilities/Context/TenantContext.php:18`
**why_it_matters:** In long-lived workers (RoadRunner, FrankenPHP, Swoole), static state persists across requests. If request A sets tenant "acme" and request B does not clear/reset it, request B operates on tenant "acme" — a cross-tenant data leak. The `reset()` method exists but is not enforced by any lifecycle hook.
**required_action:** Deprecate static facade fully. All code paths must use injected `TenantContextInterface`. Add request-lifecycle middleware or runtime hook that calls `TenantContext::reset()` between requests. Add negative test proving cross-request tenant leakage is impossible.
**suggested_slice_boundary:** `security/todo-003-tenant-context-di` — migrate consumers to DI injection, add lifecycle reset enforcement
**validation_command:** `vendor/bin/phpunit --filter=TenantContext && php tooling/refactor/check-runtime-composition-leaks.php`
**blocks_GREEN:** YES — cross-tenant data leak is exploitable in long-lived workers

---

## HIGH Findings

### HIGH-01: AuthBuilder at 560 LOC with 160+ Imports

**severity:** HIGH
**finding:** `AuthBuilder` is 560 lines with 159 use statements, 40+ configuration methods, and directly instantiates 5 sub-graphs in its constructor
**governance_source:** AGENTS.md §20 Enterprise Codecraft Rule (constructor with 8+ dependencies without design review = HIGH), §18 DI and Assembly Rule
**where:** `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
**why_it_matters:** Builder violates SRP — it owns Auth defaults, credential defaults, OAuth defaults, federation defaults, SCIM defaults, tenancy defaults, throttle bindings, container integration, bootstrap validation, external identity assembly, and capability readiness. The `withContainer()` method (lines 382-431) resolves 25+ container bindings, effectively acting as a service locator.
**required_action:** Split into focused builders or reduce surface: (1) AuthCoreBuilder for identity/auth flows, (2) CredentialBuilder for MFA/passkey, (3) ExternalIdentityBuilder for OAuth/OIDC/federation, (4) TenancyBuilder for tenant/admin/risk. Or use a single CompositionRoot class that receives pre-built sub-graphs rather than owning all defaults.
**suggested_slice_boundary:** `architecture/todo-004-split-auth-builder` — extract sub-builders, reduce AuthBuilder to coordination only
**validation_command:** `php tooling/refactor/check-constructor-bloat.php`
**blocks_GREEN:** YES* — HIGH severity, may be phase-allowed with owner/target/risk

### HIGH-02: withContainer() Acts as Service Locator

**severity:** HIGH
**finding:** `AuthBuilder::withContainer()` resolves 25+ dependencies from container including named string bindings
**governance_source:** AGENTS.md §1 Law 4 (PublicSurface receives and delegates, must not own runtime machinery), §18 DI Rule (forbidden: runtime class discovery for dependency resolution)
**where:** `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:382-431`
**why_it_matters:** The builder pulls from container using string class names and named bindings (`'auth.throttle.password_reset'`). This couples the builder to container registration order and makes dependency direction opaque. If a binding is missing, failure occurs at build time (good) but the error message is a generic container exception (bad — should be a clear ConfigurationException).
**required_action:** Replace `withContainer()` with explicit dependency registration or use a ServiceProvider pattern where each sub-graph registers its own bindings. Container lookups should be wrapped in try/catch with clear ConfigurationException messages.
**suggested_slice_boundary:** Same as HIGH-01 (builder refactor)
**validation_command:** `vendor/bin/phpstan analyse components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
**blocks_GREEN:** YES* — same slice as HIGH-01

### HIGH-03: Static Facade Pattern on Credentials and ExternalIdentity

**severity:** HIGH
**finding:** `Credentials` and `ExternalIdentity` use deprecated static facade pattern with `private static ?Interface $store = null`
**governance_source:** AGENTS.md §1 canonical severity (runtime state stored in singleton = BLOCKER, but these are marked @deprecated with reset() = HIGH)
**where:**
- `components/Identity/Credentials/System/PublicSurface/Credentials.php:19`
- `components/Identity/ExternalIdentity/System/PublicSurface/ExternalIdentity.php:19`
**why_it_matters:** Same cross-request leakage risk as TenantContext (BLOCKER-03). Marked @deprecated which shows awareness, but deprecated code in the active codebase is still executable. `reset()` exists but is not enforced.
**required_action:** Complete migration to DI injection. Remove static facade from public API. Add deprecation warnings at runtime when static methods are called. Ensure all consumers use injected stores.
**suggested_slice_boundary:** `architecture/todo-005-remove-static-facades` — migrate all consumers, remove static state, enforce lifecycle
**validation_command:** `vendor/bin/phpunit --filter=Credentials && vendor/bin/phpunit --filter=ExternalIdentity`
**blocks_GREEN:** YES* — HIGH, may be phase-allowed if migration is in progress

### HIGH-04: InMemory Stores Wired as Production Defaults

**severity:** HIGH
**finding:** 15+ InMemory stores are wired as defaults through `withContainer()` and sub-graph constructors
**governance_source:** AGENTS.md §1 Law 10 (runtime hot paths), §18 DI Rule (required dependencies must fail during configuration)
**where:** `AuthBuilder.php:389-428` (12 InMemory defaults), plus sub-graph constructors
**why_it_matters:** InMemory stores lose all state on process restart. In production, this means: lost MFA challenges, lost password reset tokens, lost email verifications, lost OAuth authorization codes, lost tenant configurations, lost admin elevations, lost passkey credentials. A worker restart invalidates all in-flight authentication flows.
**required_action:** Make InMemory stores opt-in for testing only. Production builds should require durable store implementations (database, Redis, etc.). Builder should fail-fast in enterprise mode if only InMemory stores are configured for security-sensitive data.
**suggested_slice_boundary:** `security/todo-006-durable-store-enforcement` — add enterprise mode validation for required durable stores
**validation_command:** `vendor/bin/phpunit --filter=AuthBuilder`
**blocks_GREEN:** YES* — HIGH, production-readiness gap

### HIGH-05: Auth Static Singleton Pattern

**severity:** HIGH
**finding:** `Auth` class maintains `private static ?Auth $instance = null` with `setInstance()` / `instance()` methods
**governance_source:** AGENTS.md §17 PublicSurface Rule (forbidden: hidden mutable state, service locator logic)
**where:** `components/Identity/Auth/System/PublicSurface/Auth.php:20-46`
**why_it_matters:** Global mutable singleton for the main auth surface. `instance()` throws if not set (good fail-fast), but `setInstance()` is called during boot with no lifecycle reset guarantee. Cross-request leakage if `resetInstance()` is not called.
**required_action:** Remove static singleton. All access should go through DI-injected `AuthInterface`. If `auth()` helper is needed, it should resolve from a request-scoped container, not a static property.
**suggested_slice_boundary:** Same as HIGH-03 (static facade removal)
**validation_command:** `vendor/bin/phpstan analyse components/Identity/Auth/System/PublicSurface/Auth.php`
**blocks_GREEN:** YES* — HIGH, same slice

### HIGH-06: Missing Negative Tests for Security Boundaries

**severity:** HIGH
**finding:** 36 unit tests for 648 PHP files in Identity component; negative test coverage for authentication, authorization, tenant isolation, and token verification is unproven
**governance_source:** AGENTS.md §24A Risk-Based Behavioral Testing Rule (security boundary without negative test is NOT proven), §24 Test Evidence Rule (forbidden: no negative tests for security)
**where:** `tests/Unit/Components/Identity/` (36 files), `tests/Architecture/Components/Identity/` (5 files)
**why_it_matters:** Identity, Auth, Tokens, Sessions, Authorization, and Tenant boundaries MUST have positive tests, negative tests, denial-path tests, invalidity-path tests, and fail-closed tests per AGENTS.md §24A. Without negative tests, security boundaries are unproven.
**required_action:** Add negative tests for: invalid credentials authentication, expired token rejection, cross-tenant access denial, unauthorized role escalation, invalid MFA challenge, revoked token usage, missing tenant context. Each security flow needs at least one negative test.
**suggested_slice_boundary:** `verify/todo-007-security-negative-tests` — add negative tests for all security boundaries
**validation_command:** `vendor/bin/phpunit --filter=Identity --testdox`
**blocks_GREEN:** YES* — HIGH, test quality gap

---

## MEDIUM Findings

### MEDIUM-01: Auth PublicSurface Delegates to Internal Identity Capability

**severity:** MEDIUM
**finding:** `Auth` PublicSurface delegates all behavior to `Identity` capability, which is correct, but the thin delegation layer adds no value beyond indirection
**governance_source:** AGENTS.md §17 PublicSurface Rule (delegation is allowed, but must not be machinery)
**where:** `components/Identity/Auth/System/PublicSurface/Auth.php:61-101`
**why_it_matters:** Every method on `Auth` is a one-line delegation to `$this->identity->...`. This is acceptable per governance but creates an unnecessary indirection layer. If Identity changes, Auth must change too.
**required_action:** Accept as-is for V1. Consider direct Identity injection in future refactor if indirection proves costly.
**suggested_slice_boundary:** Future cleanup
**blocks_GREEN:** NO — acceptable delegation pattern

### MEDIUM-02: `Credentials` Name Collision

**severity:** MEDIUM
**finding:** `Credentials` class exists in two namespaces: facade and flow value object
**governance_source:** AGENTS.md §15 Strict Naming (concept words should not automatically become folders)
**where:**
- `components/Identity/Credentials/System/PublicSurface/Credentials.php` (deprecated facade)
- `components/Identity/Auth/System/Flows/Login/Credentials.php` (flow value object)
**why_it_matters:** Same short name in different namespaces is technically fine but creates import confusion. The flow value object `Credentials` (login credentials DTO) shares a name with the deprecated `Credentials` facade (credential store).
**required_action:** Rename flow value object to `LoginCredentials` for clarity. Document the distinction.
**suggested_slice_boundary:** Future cleanup with facade removal
**blocks_GREEN:** NO — naming confusion, not correctness issue

### MEDIUM-03: No PHPStan Baseline for Identity Component

**severity:** MEDIUM
**finding:** No component-level PHPStan baseline or strictness configuration for Identity
**governance_source:** AGENTS.md §27 Required Validation (phpstan analyse must be clean)
**where:** Root `phpstan.neon` (if exists) or absence thereof
**why_it_matters:** Without component-level strictness, type safety gaps in Identity code may go undetected. Security-sensitive code (token signing, password hashing, authorization decisions) needs strict type checking.
**required_action:** Add PHPStan configuration targeting Identity component with strict rules. Run clean.
**suggested_slice_boundary:** Governance tooling pass
**blocks_GREEN:** NO — governance quality improvement

### MEDIUM-04: AuthBuilder `ready()` Returns Only Auth, Loses Sub-Graph Access

**severity:** MEDIUM
**finding:** `AuthBuilder::ready()` returns `new Auth(identity: $identity)` but all sub-graph assemblies (credential, oauth, federation, scim, tenancy, access) are discarded
**governance_source:** AGENTS.md §14 Framework Shape (configuration assembles, flows execute)
**where:** `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:535`
**why_it_matters:** The builder assembles extensive sub-graphs but only returns the Auth facade. Consumers who need access (e.g., `Access`, `Tenancy`, `Tokens`, `Mfa`, `Passkey` surfaces) must obtain them through other means. The `Access` capability shown in the restored files has 11 constructor dependencies that are not wired through this builder.
**required_action:** Either: (1) return a composed IdentitySystem object with all surfaces, or (2) document that Access/Tenancy/Tokens are assembled separately and require their own builders. Ensure no dependency is orphaned.
**suggested_slice_boundary:** `architecture/todo-008-compose-identity-system` — unify surface assembly
**blocks_GREEN:** NO — design observation, not broken behavior

---

## LOW Findings

### LOW-01: Inline Delegate Methods on AuthBuilder

**severity:** LOW
**finding:** Lines 341-377 contain 37 single-line delegate methods like `{ $this->credentialGraph->withMfaStore($mfaStore); return $this; }`
**governance_source:** AGENTS.md §20 Enterprise Codecraft Rule (code style)
**where:** `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:341-377`
**why_it_matters:** Readability concern. Single-line methods are harder to debug and set breakpoints on. Not a correctness issue.
**required_action:** Format consistently (one statement per line) or leave as-is if team prefers compact style.
**blocks_GREEN:** NO

### LOW-02: `getSecret()` Exposes Signing Secret

**severity:** LOW
**finding:** `JwtSigner::getSecret()` returns the raw signing secret
**governance_source:** AGENTS.md §22 Security Rule (no secret may be logged, dumped, returned raw)
**where:** `components/Identity/Tokens/System/Capabilities/JwtAuth/Signing/JwtSigner.php:18-21`
**why_it_matters:** Getter exposes secret to any code with access to JwtSigner. If used only for testing/debugging, acceptable. If used in production code paths, the secret could leak into logs, responses, or error messages.
**required_action:** Add `#[SensitiveParameter]` attribute. Document intended use. Remove if not needed for production behavior.
**blocks_GREEN:** NO — LOW, but review before production use

---

## ACCEPTED_EXCEPTION

### EXCEPTION-01: Deprecated Static Facades with Migration Path

**severity:** ACCEPTED_EXCEPTION (HIGH deferred)
**finding:** `TenantContext`, `Credentials`, `ExternalIdentity` are marked @deprecated with `set*()` injection methods and `reset()` lifecycle methods
**governance_source:** AGENTS.md §1C Allowed Suppression (suppression recorded, has migration plan)
**where:** Three facade files
**why_it_matters:** These are intentionally kept for backward compatibility during migration. The @deprecated annotation, `set*()` injection support, and `reset()` methods show a deliberate migration strategy.
**required_action:** Track in exception register at `EVIDENCE/accepted-exceptions-ledger.md`. Set target version for removal. Require `reset()` calls in request lifecycle middleware.
**owner:** Identity component team
**target_version:** V2 or next major release
**expiry:** Before production deployment
**mitigation:** `reset()` must be called in request lifecycle; DI injection is available and recommended
**blocks_GREEN:** NO — tracked exception with migration path, but must be resolved before production

---

## Remediation Plan

### Recommended Slice Order

| Order | Slice | Files | Severity | Est. Impact |
|-------|-------|-------|----------|-------------|
| 1 | Replace Authenticate stub | 1-3 files | BLOCKER | Eliminates auth bypass |
| 2 | Rename JwtSigner → JwtVerifier | 2-5 files | BLOCKER | Eliminates naming collision |
| 3 | Add security negative tests | 15-20 files | HIGH | Proves security boundaries |
| 4 | TenantContext DI migration | 5-10 files | BLOCKER | Eliminates cross-tenant leak |
| 5 | Remove static facades (Credentials, ExternalIdentity, Auth) | 10-15 files | HIGH | Eliminates worker state leaks |
| 6 | Durable store enforcement | 3-5 files | HIGH | Production readiness |
| 7 | Split AuthBuilder | 5-8 files | HIGH | SRP, readability, testability |
| 8 | Compose IdentitySystem | 3-5 files | MEDIUM | Surface completeness |
| 9 | Governance cleanup (PHPStan, naming) | 5-10 files | MEDIUM/LOW | Quality improvements |

### Slice 1: Replace Authenticate Stub (Priority 1)

**Branch:** `security/todo-001-replace-authenticate-stub`

**Implementation:**
1. Read `UserSourceInterface` to find user by identifier
2. Read credential store for password hash
3. Use `PasswordHasher::verify()` to compare
4. Return false on missing user, missing hash, or verification failure
5. Add positive test: valid credentials authenticate
6. Add negative tests: wrong password, missing user, empty credentials
7. Run `vendor/bin/phpunit` and `vendor/bin/phpstan`

**Validation:**
```bash
vendor/bin/phpunit --filter=Authenticate
vendor/bin/phpstan analyse components/Identity/System/Flows/Authenticate
```

### Slice 2: Rename JwtSigner to JwtVerifier (Priority 2)

**Branch:** `architecture/todo-002-rename-jwt-verifier`

**Implementation:**
1. Create `JwtVerifierInterface` with `verify(string $token): array` method
2. Rename `Verification/JwtSigner.php` → `Verification/JwtVerifier.php`
3. Implement verification using Firebase JWT::decode
4. Update all references
5. Add tests: valid token verifies, expired token rejects, tampered token rejects
6. Run validation

**Validation:**
```bash
vendor/bin/phpstan analyse components/Identity/Tokens/System/Capabilities/JwtAuth
vendor/bin/phpunit --filter=Jwt
```

### Slice 3: Security Negative Tests (Priority 3)

**Branch:** `verify/todo-007-security-negative-tests`

**Required tests:**
- `AuthenticateTest::test_invalid_credentials_fail()`
- `AuthenticateTest::test_missing_user_fails()`
- `AuthorizationTest::test_unauthenticated_access_denied()`
- `AuthorizationTest::test_insufficient_role_denied()`
- `TenantContextTest::test_cross_tenant_access_denied()`
- `TokenVerificationTest::test_expired_token_rejected()`
- `TokenVerificationTest::test_tampered_token_rejected()`
- `MfaChallengeTest::test_invalid_challenge_fails()`
- `AdminElevationTest::test_non_admin_elevation_denied()`

**Validation:**
```bash
vendor/bin/phpunit --filter=Identity --testdox
```

---

## TODO FOR 11++

1. **BLOCKER** Replace `Authenticate::authenticate()` stub with real credential verification
2. **BLOCKER** Rename `Verification/JwtSigner` to `JwtVerifier`, implement verification
3. **BLOCKER** Migrate `TenantContext` from static facade to DI injection with lifecycle reset
4. **HIGH** Split `AuthBuilder` into focused sub-builders or reduce surface
5. **HIGH** Remove `withContainer()` service locator pattern
6. **HIGH** Remove static facades: `Credentials`, `ExternalIdentity`, `Auth` singleton
7. **HIGH** Enforce durable stores in production (fail-fast on InMemory in enterprise mode)
8. **HIGH** Add negative tests for all security boundaries (authentication, authorization, tokens, tenant isolation, MFA)
9. **MEDIUM** Compose all surfaces into unified IdentitySystem or document separate assembly
10. **MEDIUM** Add PHPStan strict mode for Identity component
11. **LOW** Format AuthBuilder delegate methods consistently

---

## Next Implementation Prompt

Begin with Slice 1: Replace the `Authenticate::authenticate()` stub. This is the highest-risk finding (authentication bypass) and has the smallest file scope (1-3 files). The implementation should:

1. Create a proper `Authenticate` flow that takes `UserSourceInterface`, `CredentialStoreInterface`, and `PasswordHasher` as constructor dependencies
2. Implement credential verification with fail-closed behavior
3. Add 5+ tests covering happy path and all negative paths
4. Run full validation before committing

Branch: `security/todo-001-replace-authenticate-stub`
Expected files changed: 3-8
Validation: `vendor/bin/phpunit --filter=Authenticate && vendor/bin/phpstan analyse components/Identity/System/Flows/Authenticate`

---

## Why This Is RED

```text
validation: No validation run (read-only triage)
gates: No gates run (read-only triage)
deviation_audit: 3 BLOCKER, 6 HIGH, 4 MEDIUM, 2 LOW, 1 ACCEPTED_EXCEPTION
corrections: None (read-only triage)
remaining_deviations: 16 total findings
suppression_check: No suppression detected in current work
exception_register: 1 entry (deprecated static facades with migration path)
risk_assessment: Authentication bypass (BLOCKER-01), naming collision in security code (BLOCKER-02), cross-tenant state leak (BLOCKER-03) are active security risks
severity_decision: RED because 3 BLOCKER findings remain uncorrected — authentication bypass, security naming collision, and cross-tenant state leak are all exploitable in production
evidence: This file at EVIDENCE/identity-triage-2026-05-25-21.md
```
