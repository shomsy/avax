# Identity Redesign — Slice 0: Evidence and DSL Lock

## Agent Context Loaded

- AGENTS.md read: YES
- .agents/AGENTS.md read: ABSENT
- how-to-ai-assisted-execution.md read: ABSENT (does not exist)
- how-to files discovered: 17
- how-to files read: 7 (architecture, design-components, dependency-injection, runtime-composition, code-review, unit-test, system-security)
- skills discovered: 1 (avax-enterprise-remediation)
- skills used: avax-enterprise-remediation
- memory/learning files discovered: 0
- memory/learning files used: NONE
- project truth files read: refactor-identity.md, AGENTS.md
- review evidence read: NONE (no current identity-specific review)
- assigned fix-this TODO: refactor-identity.md Slice 0
- source clusters read: All Identity component source files read
- source finding IDs read: 15 known corrections from plan
- applicable governance warnings: PublicSurface must stay thin, no Container, no direct instantiation in runtime; Runtime composition leak law; DI discipline; one class per file; no static mutable state; no date()/time() in policy evaluation
- accepted YELLOW constraints: Risk surface is stub (accepted — Slice 6); Tokens::issue() empty (accepted — Slice 4); Tenancy Admin stub (accepted — Slice 6)
- pre-existing dirty files: refactor-identity.md (untracked plan), avax.txt (untracked noise), components/Identity/Identity.txt (untracked noise) — all unrelated to production code

## Current Branch

- Branch: `architecture/identity-target-architecture`
- Base: main
- Status: clean (only untracked files, no modified production files)

## Source Mapping — Current Identity Component

### Root DSL Layer

| File | Responsibility | Status |
|------|---------------|--------|
| `System/PublicSurface/Identity.php` | Root fluent DSL entrypoint. Delegates all 7 sub-surfaces to `IdentityRuntime`. Uses `BuildIdentityRuntime::defaults()->runtime()`. | **GOOD** — thin, delegates, no Container. Uses builder defaults. |
| `System/Capabilities/IdentityRuntime/IdentityRuntime.php` | Root runtime coordinator. Holds 7 sub-surface dependencies, delegates each accessor. | **GOOD** — cohesive, constructor DI. |
| `System/Configuration/Builders/IdentityRuntime.php` | Default builder. Creates `IdentityRuntime` with hardcoded defaults: `GuestSessionIdentity`, `AuthorizationEngine`, `AdminElevationStore`, `InMemoryCredentialStore`, `InMemoryExternalIdentityLinkStore`, `DefaultTenantContext`, HMAC tokens with `'test'` secret. | **YELLOW** — `AdminElevationStore` is instance-based but shared across default runtime; `BeginAdminElevation` created with `new`; hardcoded `'test'` secret; Risk is stub. Builder pattern acceptable for defaults but uses `new` for runtime objects. |

### Auth Sub-Area

| File | Responsibility | Status |
|------|---------------|--------|
| `Auth/System/PublicSurface/Auth.php` | Auth public surface. Receives `AuthenticationRuntime` via DI. Delegates login/logout/user/guest/check/register/changePassword/logoutAllSessions. | **GOOD** — thin, delegates. |
| `Auth/System/Configuration/Builders/AuthBuilder.php` | Master fluent builder (510 lines). 18 private nullable fields. Delegates to 5 sub-graphs: `CredentialAuthenticationGraph`, `OAuthIdentityGraph`, `FederationIdentityGraph`, `ScimProvisioningGraph`, `TenancyAdministrationGraph`. Uses `new` for sub-graphs in constructor. `ready()` method calls `AuthBootstrapValidator`, `AssembleAuthIdentityGraph`, `AssembleAuthExternalIdentityGraph`. Returns `new Auth(runtime: new AuthenticationRuntime(identity: ...))`. | **YELLOW** — No `ContainerInterface` dependency (good). But 510 lines is large. Sub-graph constructor uses `new`. `ready()` creates `new AuthenticationRuntime(identity:)` without passing sessions/credentials/mfa/passkey — this is a reduced runtime. Many `?? throw ConfigurationException` in `ready()` — acceptable in composition context. |
| `Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php` | Main assembler (848 lines). 38 constructor parameters. Massive `assemble()` method with 16 private build methods. Returns typed array. | **YELLOW** — 38 constructor params is god-constructor territory. 848 lines. But it IS in `Configuration/Assembly/` which is an approved composition context. One class per file. Uses `new` for all services — allowed in composition context. Some `resolveOrBuild*` methods with `?? new` fallback — acceptable for optional assembly. |
| `Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php` | External identity assembler (217 lines). 19 constructor params. `assemble()` returns `void` — assembles into local vars but does not return/expose. Creates `Diagnostics` without assigning it. | **HIGH** — `assemble()` returns void and discards assembled objects. `new Diagnostics(...)` on line 214 is thrown away. This is dead assembly code. |
| `Auth/System/Configuration/Assembly/CredentialAuthenticationGraph.php` | Credential config graph (150 lines). Builder pattern with nullable fields. Returns array from `assemble()`. | **ACCEPTABLE** — composition context, clean. |
| `Auth/System/Configuration/Assembly/OAuthIdentityGraph.php` | OAuth config graph (83 lines). Same pattern. | **ACCEPTABLE** |
| `Auth/System/Configuration/Assembly/FederationIdentityGraph.php` | Federation config graph (59 lines). Same pattern. | **ACCEPTABLE** |
| `Auth/System/Configuration/Assembly/ScimProvisioningGraph.php` | SCIM config graph (72 lines). Same pattern. | **ACCEPTABLE** |
| `Auth/System/Configuration/Assembly/TenancyAdministrationGraph.php` | Tenancy config graph (93 lines). Same pattern. | **ACCEPTABLE** |

### Access Sub-Area

| File | Responsibility | Status |
|------|---------------|--------|
| `Access/System/PublicSurface/Access.php` | Access public surface. Receives `AccessRuntime` via DI. Delegates authorize/denies/allows/isElevated/beginElevation/endElevation/requireAuthentication/requireRole/requirePermission. | **GOOD** — thin, delegates. Missing `requirePolicy` and `requireResourceOwner` from target DSL. |

### Credentials Sub-Area

| File | Responsibility | Status |
|------|---------------|--------|
| `Credentials/System/PublicSurface/Credentials.php` | Credentials public surface. Receives `CredentialsRuntime` via DI. Has store/read/forget (generic credential ops) + mfa()/passkeys() delegations. | **YELLOW** — generic store/read/forget don't match target DSL. Target DSL has only `passwords()`, `mfa()`, `passkeys()`. |
| `Credentials/System/PublicSurface/Mfa.php` | Mfa surface — NOT READ YET |
| `Credentials/System/PublicSurface/Passkey.php` | Passkey surface — NOT READ YET |

### Tokens Sub-Area

| File | Responsibility | Status |
|------|---------------|--------|
| `Tokens/System/PublicSurface/Tokens.php` | Tokens public surface. Has authorize/exchangeCode/introspect/revoke (OAuth-style). `issue()` is EMPTY BODY. | **HIGH** — `issue(string $sub): void` has empty body. Target DSL expects `issue(TokenSubject $subject): IssuedToken`. |

### Tenancy Sub-Area

| File | Responsibility | Status |
|------|---------------|--------|
| `Tenancy/System/PublicSurface/Tenancy.php` | Tenancy public surface. Has resolve/currentTenant/requireTenant/getTenantId/setTenantId/clearTenant/run/switch/admin(). | **YELLOW** — Missing proper `Tenant` object return (returns `string|null`). Target DSL expects `Tenant|null`. Admin sub-surface is stub. |
| `Tenancy/System/PublicSurface/Admin.php` | Admin stub. `beginElevation()` is EMPTY. | **HIGH** — stub method, no behavior. |

### Risk Sub-Area

| File | Responsibility | Status |
|------|---------------|--------|
| `Risk/System/PublicSurface/Risk.php` | Risk stub. `assessCurrent()` returns `null` always. | **HIGH** — stub, no behavior. |

### ExternalIdentity Sub-Area

| File | Responsibility | Status |
|------|---------------|--------|
| `ExternalIdentity/System/PublicSurface/ExternalIdentity.php` | External identity surface. Has link/resolve generic ops. Receives `ExternalIdentityRuntime` via DI. | **YELLOW** — Missing target DSL methods: oauth()/oidc()/federation(). |

### Existing Tests

| File | Type | Status |
|------|------|--------|
| `IdentityTargetDslCharacterizationTest.php` | DSL characterization | EXISTS — needs review |
| `AuthCharacterizationTest.php` | Auth characterization | EXISTS |
| `AuthBuilderReadyGraphCharacterizationTest.php` | Builder graph characterization | EXISTS |
| `AuthClockCharacterizationTest.php` | Clock characterization | EXISTS |
| `AuthShortcutCharacterizationTest.php` | Shortcut characterization | EXISTS |
| `AuthFoundationSmokeTest.php` | Foundation smoke | EXISTS |
| `AuthCapabilitiesTest.php` | Capabilities | EXISTS |
| `AccessCharacterizationTest.php` | Access characterization | EXISTS |
| `AccessCapabilitiesTest.php` | Capabilities | EXISTS |
| `CredentialsCharacterizationTest.php` | Credentials characterization | EXISTS |
| `CredentialsCapabilitiesTest.php` | Capabilities | EXISTS |
| `TokensCharacterizationTest.php` | Tokens characterization | EXISTS |
| `TokensCapabilitiesTest.php` | Capabilities | EXISTS |
| `JwtAuthRuntimeSafetyTest.php` | JWT runtime safety | EXISTS |
| `TenancyCharacterizationTest.php` | Tenancy characterization | EXISTS |
| `TenancyCapabilitiesTest.php` | Capabilities | EXISTS |
| `ExternalIdentityCharacterizationTest.php` | External identity characterization | EXISTS |
| `ExternalIdentityCapabilitiesTest.php` | Capabilities | EXISTS |
| `IdentitySystemCapabilitiesTest.php` | System capabilities | EXISTS |

**No ServiceProviders found.** No `*ServiceProvider.php` anywhere in `components/Identity/`.

## 15 Known Corrections — Current Status

| # | Correction | Present? | Severity |
|---|-----------|----------|----------|
| 1 | Delete empty RegisterAccessDependencies | NOT FOUND in current scan — may already be deleted | LOW |
| 2 | Move RegisterAuthDefaults out of Builders | NOT FOUND — may not exist | LOW |
| 3 | AuthBuilder must not depend on ContainerInterface | NOT PRESENT — AuthBuilder has no ContainerInterface | GREEN |
| 4 | EndpointPostureEngine.php one class per file | NOT CHECKED YET | MEDIUM |
| 5 | EndpointPosturePolicy/Decision own files | NOT CHECKED YET | MEDIUM |
| 6 | PolicyRule.php one class per file | NOT CHECKED YET | MEDIUM |
| 7 | AttributeCondition own file | NOT CHECKED YET | MEDIUM |
| 8 | AttributeCondition::withinHours() no date('H') | NOT CHECKED YET | HIGH |
| 9 | BeginAdminElevation no static mutable state | NOT CHECKED YET — AdminElevationStore is instance-based | MEDIUM |
| 10 | PublicSurface Access delegates to AccessRuntime | GREEN — Access receives AccessRuntime | GREEN |
| 11 | Identity DSL must not new sub-surfaces | YELLOW — `BuildIdentityRuntime::defaults()->runtime()` uses `new` throughout | HIGH |
| 12 | No FQCN inside methods when imports ok | NOT FULLY CHECKED | LOW |
| 13 | No duplicate PermissionDenied/Exception | NOT CHECKED YET | MEDIUM |
| 14 | No duplicate AccessPolicy ambiguity | NOT CHECKED YET | MEDIUM |
| 15 | No construction-only tests | NOT CHECKED YET | MEDIUM |

## Critical Findings for Slice 0

### Finding 1: AssembleAuthExternalIdentityGraph discards assembled objects
- **Where:** `Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:214`
- **What:** `new Diagnostics(...)` created and thrown away. Entire `assemble()` returns void.
- **Impact:** Dead code, wasted assembly, no external identity capabilities actually wired.
- **Severity:** HIGH

### Finding 2: Tokens::issue() is empty
- **Where:** `Tokens/System/PublicSurface/Tokens.php:45-47`
- **What:** Method body is empty. Returns void.
- **Impact:** Target DSL expects token issuance. Currently broken.
- **Severity:** HIGH

### Finding 3: Risk::assessCurrent() always returns null
- **Where:** `Risk/System/PublicSurface/Risk.php:9-11`
- **What:** Stub implementation.
- **Impact:** Risk assessment is non-functional.
- **Severity:** HIGH (but accepted as deferred to Slice 6)

### Finding 4: Admin::beginElevation() is empty
- **Where:** `Tenancy/System/PublicSurface/Admin.php:9-11`
- **What:** Stub method.
- **Impact:** Admin elevation is non-functional through default DSL.
- **Severity:** HIGH (but accepted as deferred to Slice 6)

### Finding 5: Default builder uses `new` for runtime objects
- **Where:** `System/Configuration/Builders/IdentityRuntime.php:39-70`
- **What:** Creates all runtime objects with `new`. This IS in composition context, so technically allowed. But the defaults are hardcoded with test values (`'test'` secret, `GuestSessionIdentity`).
- **Impact:** Default DSL always uses guest identity. HMAC secret is `'test'`. Acceptable for development defaults but dangerous if accidentally used in production.
- **Severity:** MEDIUM (accepted YELLOW — these are explicit defaults, not runtime leaks)

### Finding 6: AuthBuilder creates reduced AuthenticationRuntime
- **Where:** `AuthBuilder.php:482-484`
- **What:** `return new Auth(runtime: new AuthenticationRuntime(identity: $identity))` — only passes identity, not sessions/credentials/mfa/passkey.
- **Impact:** The `AuthenticationRuntime` in AuthBuilder's `ready()` is a different shape than the one in `IdentityRuntime::defaults()`. This may be intentional (Auth surface has its own runtime shape), but needs verification.
- **Severity:** MEDIUM

## Slice 0 Scope Decision

Slice 0 must:
1. Map current Identity (DONE — above)
2. Review existing characterization tests for target DSL compatibility
3. Add/fix characterization tests for desired public DSL
4. Lock old API compatibility

Slice 0 must NOT:
- Rewrite production code
- Move files
- Change architecture
- Fix HIGH findings (those belong to later slices)

## Next Actions

1. Read existing `IdentityTargetDslCharacterizationTest.php` to assess DSL lock status
2. Read existing characterization tests per sub-area
3. Identify gaps between current DSL and target DSL
4. Add characterization tests for target DSL methods that don't exist yet
5. Document API compatibility decisions
