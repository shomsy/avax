# AvaX Technical Dictionary

## Status

**MANDATORY** - This document defines canonical technical terms used across AvaX.

When a technical term appears in a class name, PHPDoc, or governance document, the meaning must align with this dictionary.

---

## Purpose

AvaX uses specific technical terms to describe architectural concepts. When these terms appear in code, every reader must understand the same meaning.

If a class uses one of these terms, its PHPDoc should point here:

```php
/**
 * Note: "Runtime" is defined in .agents/dictionary/framework-terms.md.
 */
```

This prevents naming drift, parallel concepts, and ambiguity in code review.

---

## Terms

### Runtime

**Simple explanation:** The execution environment that hosts and processes code during operation.

**AvaX meaning:** The live system state that handles requests, jobs, events, or worker loops. Includes request scope, worker lifecycle, state reset, and shutdown behavior. AvaX must be runtime-agnostic, supporting PHP-FPM, FrankenPHP, RoadRunner, Swoole, Workerman, ReactPHP, Amp, Fibers, CLI, and tests.

**Allowed usage:** `AccessRuntime`, `RuntimeSafety`, `RuntimeKernel`, `runtime()` method returning runtime state, classes that manage or interact with the live execution environment.

**Forbidden misuse:** Using "Runtime" as a synonym for "application" or "framework" when no execution lifecycle is involved. Naming static utility classes "Runtime".

**Example names:** `AccessRuntime`, `RuntimeSafety`, `BuildRuntimeKernel`, `RuntimeComposition`, `VerifyRuntimeSafety`

**Learning link:** `AGENTS.md` — Section 11 (AvaX Vision), `how-to-modern-php-attributes-di.md`

---

### PublicSurface

**Simple explanation:** The stable, external-facing API boundary of a component or system.

**AvaX meaning:** A thin delegation layer that receives input and delegates inward to Flows, Capabilities, or Configuration. Must not own runtime machinery, object graph assembly, business logic, security decisions, or hidden state. Tested through public contract tests.

**Allowed usage:** `PublicSurface/` folder inside `System/`, classes that act as DSL entrypoints, facades that delegate, input normalization boundaries.

**Forbidden misuse:** Placing business logic, runtime machinery, object graph construction, service locator patterns, or mutable state in PublicSurface classes. Letting PublicSurface grow large.

**Example names:** `Cache.php` (in PublicSurface/), `Database.php`, `Identity.php`, `Auth.php`

**Learning link:** `AGENTS.md` — Section 17 (PublicSurface Rule), `how-to-design-components.md` — Section 6.2

---

### Capability

**Simple explanation:** A reusable ability or mechanism that multiple flows depend on.

**AvaX meaning:** Shared behavior, boundary, or mechanism that supports multiple flows. Lives in `Capabilities/` folder. Answers "What ability does this component provide?" Must have honest cross-flow ownership, not be a junk drawer.

**Allowed usage:** `Capabilities/` folder, classes that provide reusable behavior like `ReadRedisCache`, `StoreObjectInS3`, `CheckCacheHealth`, `PublicApiCompatibility`.

**Forbidden misuse:** Using Capability as a technical category bucket. Creating `Capabilities/Adapters/` or `Capabilities/Services/`. Naming a capability after a pattern rather than ability.

**Example names:** `S3ObjectStorage`, `RedisCacheStore`, `PublicApiCompatibility`, `CheckCacheHealth`, `RegisterUser`

**Learning link:** `AGENTS.md` — Section 16 (Flow and Capability Rule), `how-to-architecture.md` — Section 12

---

### Flow

**Simple explanation:** One complete end-to-end action from start to finish.

**AvaX meaning:** A unit that owns one complete user, system, runtime, or platform action. Lives in `Flows/` folder. Answers "What happens from start to finish?" Default design choice — extract to Capability only after reuse is honest.

**Allowed usage:** `Flows/` folder, classes like `RegisterUser`, `HandleIncomingHttp`, `RunMigration`, `ChangePassword` that own a complete sequence.

**Forbidden misuse:** Using Flow as a vague container. Creating `Flows/Handlers/` or `Flows/Services/`. Naming a flow after a noun instead of an action. Splitting one flow into micro-units prematurely.

**Example names:** `RegisterUser`, `HandleIncomingHttp`, `RunMigration`, `ChangePassword`, `PublishPendingEvent`

**Learning link:** `AGENTS.md` — Section 16 (Flow and Capability Rule), `how-to-architecture.md` — Section 11

---

### Provider

**Simple explanation:** A class that registers, configures, or wires dependencies into the container.

**AvaX meaning:** A ServiceProvider or configuration entry that owns assembly, registration, and dependency wiring for a component or subsystem. Lives in `Configuration/` or is the provider entry point. Every active production component with runtime behavior must have exactly one real ServiceProvider.

**Allowed usage:** `CacheServiceProvider`, `AuthServiceProvider`, `DatabaseServiceProvider`, classes that register bindings, configure defaults, delegate to builders.

**Forbidden misuse:** Putting business logic in providers. Using providers as service locators. Creating providers for tiny utilities. Letting providers grow beyond 250 lines without split review.

**Example names:** `CacheServiceProvider`, `AuthServiceProvider`, `BuildApplication`, `RegisterAuthDefaults`

**Learning link:** `how-to-dependency-injection.md`, `AGENTS.md` — Section 18 (DI and Assembly Rule)

---

### Builder

**Simple explanation:** A class that assembles a cohesive object graph or configuration at assembly time.

**AvaX meaning:** Assembly machinery that constructs dependency graphs, runtime packages, or configuration objects. Lives in `Configuration/Builders/` for internal assembly, or is a user-facing configuration DSL. Must not hide long constructors, receive Container as service locator, or assemble unrelated capabilities.

**Allowed usage:** `BuildAuthRuntime`, `AssembleHttpKernel`, `BuildDatabaseRuntime`, `TokenAuthenticationGraph`, classes that assemble one cohesive graph.

**Forbidden misuse:** Using Builder to hide constructor bloat without improving ownership. Creating `Builders/` as a generic folder. Builder receiving Container as service locator. Builder assembling unrelated capabilities into a god object.

**Example names:** `BuildAuthRuntime`, `AssembleHttpKernel`, `TokenAuthenticationGraph`, `RouteTableGraph`

**Learning link:** `how-to-architecture.md` — Section 13.3 (Builders Rule), `how-to-dependency-injection.md` — Section 8

---

### Assembly

**Simple explanation:** The act of constructing and wiring an object graph before runtime execution.

**AvaX meaning:** Configuration-time activity that creates the dependency graph, registers bindings, and prepares the system for runtime. Distinct from runtime behavior. Owned by `Configuration/`, Providers, and Builders. Runtime code should execute, not assemble.

**Allowed usage:** `Configuration/Assembly/`, methods like `authentication()`, `runtimeKernel()`, `routeTable()` that return assembled graphs, classes that convert user options into component configuration.

**Forbidden misuse:** Using assembly at runtime. Mixing assembly with business logic. Runtime classes that call `new` for dependencies instead of receiving them through DI.

**Example names:** `AssembleAuthDependencies`, `AssembleResponseComponent`, `BuildApplication`

**Learning link:** `how-to-dependency-injection.md`, `AGENTS.md` — Section 18

---

### Graph

**Simple explanation:** A cohesive set of related dependencies assembled as one unit.

**AvaX meaning:** A named assembly of related dependencies that form a complete sub-system. Used in builder naming when the assembled product is a set of interconnected objects. Prefer `Graph` suffix in `Configuration/Builders/` when naming the assembled product rather than repeating `Build*` in the class name.

**Allowed usage:** `TokenAuthenticationGraph`, `PasswordAuthenticationGraph`, `AuthorizationPolicyGraph`, `RuntimeKernelGraph`, classes representing a cohesive assembled dependency set.

**Forbidden misuse:** Using Graph for unrelated collections. Naming every builder class `*Graph`. Using Graph when a simpler name like `TokenAuthentication` is clearer.

**Example names:** `TokenAuthenticationGraph`, `AuthorizationPolicyGraph`, `RouteTableGraph`

**Learning link:** `how-to-architecture.md` — Section 13.3.3 (Builder Naming Rule)

---

### DSL

**Simple explanation:** A Domain-Specific Language — a fluent, readable API that reads like intent, not implementation.

**AvaX meaning:** Call-site ergonomics where method chains and naming create human-readable intent. The receiving boundary owns normalization, wrapping, conversion, and defaults. Call sites answer "What is happening?" not "How many internal objects are required?" DSL stability requires extra scrutiny — syntax and semantics changes break user code.

**Allowed usage:** Fluent configuration APIs like `ConfigureAuth`, fluent builders, intent-first method chains like `$cache->remember('key', $ttl, $callback)`, public API entry points.

**Forbidden misuse:** Using DSL as a folder name. Creating DSL that hides security decisions. Exposing internal mechanics through DSL. Changing DSL syntax without deprecation cycle.

**Example names:** `ConfigureAuth`, `ConfigureCache`, `Route::get()`, `Cache::remember()`

**Learning link:** `AGENTS.md` — Section 17, `how-to-clean-code.md` — Section 5.4.1 (Intent-First Fluent API Rule)

---

### Facade

**Simple explanation:** A thin public entry point that delegates to internal owners.

**AvaX meaning:** A static or instance facade that provides convenient access to an underlying subsystem. Must delegate to the correct internal owner, not accumulate ad-hoc behavior, remain thin and stable, and log deprecation when forwarding to changed internals. The container facade is intentionally allowed at bootstrap level only.

**Allowed usage:** PublicSurface facades, `Cache::`, `Route::`, `Auth::` static entry points, thin delegation boundaries.

**Forbidden misuse:** Facades owning business logic. Facades accumulating unrelated methods. Facades hiding dependency chaos. Using facades inside component internals instead of DI.

**Example names:** `Cache`, `Route`, `Auth`, `Response` (as public entry points)

**Learning link:** `AGENTS.md` — Section 17, `how-to-dependency-injection.md` — Section 6.6

---

### Policy

**Simple explanation:** A rule or set of rules that govern access, behavior, or configuration decisions.

**AvaX meaning:** Authorization rules, security constraints, or behavioral constraints that the system enforces. Policies must protect the object, not only the route. Policy evaluation must be fast and deterministic. Policies are evaluated by `PolicyEvaluator` or similar capability.

**Allowed usage:** `AuthorizationPolicy`, `AccessPolicy`, `RateLimitPolicy`, `PasswordPolicy`, classes or rules that define what is allowed or denied.

**Forbidden misuse:** Policies that contain business logic. Policies that perform IO. Policies that are merely configuration flags without enforcement.

**Example names:** `RequirePermission`, `AccessPolicy`, `PasswordPolicy`, `RateLimitPolicy`

**Learning link:** `AGENTS.md` — Section 22 (Security Rule), `how-to-architecture.md` — Section 35

---

### Token

**Simple explanation:** A credential or opaque identifier used for authentication, authorization, or session management.

**AvaX meaning:** Security-sensitive string or object that represents authentication state, access rights, or session identity. Includes JWT, access tokens, refresh tokens, API tokens, CSRF tokens. Tokens must be signed, validated, and scoped. Must never be logged or exposed in evidence.

**Allowed usage:** `TokenSigner`, `TokenAuthenticationGraph`, `AccessToken`, `RefreshToken`, `TokenValidation`, classes that create, validate, or manage tokens.

**Forbidden misuse:** Logging token values. Storing tokens in mutable static state. Using tokens without expiry or scope. Treating all string identifiers as tokens.

**Example names:** `TokenSigner`, `AccessToken`, `TokenValidation`, `TokenAuthenticationGraph`

**Learning link:** `AGENTS.md` — Section 22, `avax-security-threat-model` skill

---

### Session

**Simple explanation:** A bounded period of authenticated user interaction with the system.

**AvaX meaning:** Request-scoped or longer-lived state that tracks an authenticated user's interaction lifecycle. Includes session creation, validation, expiration, and destruction. Session state must be scoped and must not leak between requests in long-lived workers.

**Allowed usage:** `SessionState`, `SessionManager` (if owned by a capability), `sessions()` method returning session-related behavior, session validation, session storage.

**Forbidden misuse:** Storing session data in mutable static. Using sessions for cross-user state. Session objects that persist beyond their lifecycle. Leaking session data between workers.

**Example names:** `SessionState`, `SessionValidation`, `DestroySession`, `sessions()`

**Learning link:** `AGENTS.md` — Section 22, `avax-security-threat-model` skill

---

### Credential

**Simple explanation:** A secret or proof of identity used for authentication.

**AvaX meaning:** Password, API key, certificate, OAuth token, or other secret that proves identity. Credentials must be hashed, encrypted, or otherwise protected. Must never be logged, dumped, returned raw, or exposed in evidence. Credential handling is a security boundary.

**Allowed usage:** `CredentialStore`, `CredentialValidation`, `HashedCredential`, methods like `verifyCredentials()`, credential rotation logic.

**Forbidden misuse:** Logging credentials. Storing credentials in plaintext. Returning credentials in API responses. Using credentials as cache keys.

**Example names:** `CredentialStore`, `HashedCredential`, `VerifyCredentials`, `CredentialRotation`

**Learning link:** `AGENTS.md` — Section 22, `avax-security-threat-model` skill

---

### Tenant

**Simple explanation:** An isolated organizational boundary in a multi-tenant system.

**AvaX meaning:** A logical separation boundary that ensures data, configuration, and access control are scoped to a specific organization or customer. Tenant isolation must be enforced at every security boundary. Cached decisions must include tenant scope.

**Allowed usage:** `TenantScope`, `TenantIsolation`, `TenantResolver`, tenant-aware policies, tenant-scoped configuration.

**Forbidden misuse:** Using tenant as a synonym for user. Tenant state that leaks between requests. Tenant decisions cached without tenant scope.

**Example names:** `TenantScope`, `TenantResolver`, `TenantIsolation`, `ReadTenantConfig`

**Learning link:** `AGENTS.md` — Section 22, `avax-security-threat-model` skill — Long-Lived Worker Security

---

### Elevation

**Simple explanation:** The act of increasing privilege or access level beyond the current baseline.

**AvaX meaning:** Privilege escalation, admin elevation, role change, or temporary permission grant. Elevation is security-sensitive and must be explicit, auditable, time-bounded, and fail-closed. Admin elevation must be runtime-safe.

**Allowed usage:** `AdminElevation`, `ElevatePermission`, `RequireElevation`, elevation audit logs, time-bounded permission grants.

**Forbidden misuse:** Silent elevation. Elevation without audit trail. Permanent elevation without justification. Elevation that bypasses authorization checks.

**Example names:** `AdminElevation`, `ElevatePermission`, `RequireElevation`, `ElevationAudit`

**Learning link:** `AGENTS.md` — Section 22, Identity component governance

---

### Risk

**Simple explanation:** The potential for harm, loss, or security impact from a design decision or runtime behavior.

**AvaX meaning:** A classified threat or vulnerability that requires assessment, mitigation, or acceptance as a documented trade-off. Used in threat modeling, risk registers, and security reviews. Risk classification drives severity decisions (BLOCKER, HIGH, MEDIUM, LOW, ACCEPTED_YELLOW).

**Allowed usage:** `RiskAssessment`, `RiskRegister`, `AccessRisk`, `SecurityRisk`, risk classification in evidence, risk documentation in design documents.

**Forbidden misuse:** Using Risk as a vague label without classification. Ignoring documented risks. Downgrading risks without evidence.

**Example names:** `RiskAssessment`, `AccessRisk`, `RuntimeRisk`, `RiskRegister`

**Learning link:** `AGENTS.md` — Section 22, `avax-security-threat-model` skill, `.agents/management/evidence/RISK_REGISTER.md`

---

## Cross-References

- `AGENTS.md` — Root governance contract
- `how-to-architecture.md` — Architecture governance
- `how-to-clean-code.md` — Clean code standards
- `how-to-dependency-injection.md` — DI and fluent API governance
- `how-to-design-components.md` — Component design standards
- `how-to-code-style.md` — Code style governance

## Adding New Terms

When a new technical term becomes necessary in AvaX code:

1. Add it to this dictionary with all required fields.
2. Reference this dictionary in PHPDoc where the term appears.
3. Update cross-references if the term connects to existing governance.
4. Do not use the term in production code until it is defined here.
