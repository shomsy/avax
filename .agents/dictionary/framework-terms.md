# AvaX Framework Technical Dictionary

**Status:** MANDATORY
**Purpose:** Define canonical meanings for technical terms used in AvaX class names, documentation, and governance.

When a technical term appears in a class name, PHPDoc, or governance document, its meaning is defined here.

Example PHPDoc note:

```text
Note: "Runtime" is defined in .agents/dictionary/framework-terms.md.
```

---

## Runtime

**Simple explanation:** The executing application lifecycle environment.

**AvaX meaning:** The set of lifecycle phases, request scope, worker state, and execution context that an application moves through (boot, request, worker, reset, shutdown).

**Allowed usage:** `AccessRuntime`, `BuildAuthRuntime`, `$access` (semantic name for an AccessRuntime dependency), `runtime()` method returning runtime state.

**Forbidden misuse:** Using "Runtime" as a dumping ground for unrelated behavior. A Runtime class should own lifecycle state, not domain logic.

**Example names:** `AccessRuntime`, `CacheRuntime`, `VerifyRuntimeSafety`, `$runtime`.

**Learning:** `how-to-runtime-composition.md`

---

## PublicSurface

**Simple explanation:** The stable public API entrypoints that receive and delegate.

**AvaX meaning:** The thin boundary layer of a component or framework that accepts natural inputs, normalizes them, and delegates to internal Flows or Capabilities. Must not own machinery.

**Allowed usage:** `PublicSurface/` folder, `Cache` class in PublicSurface, facade classes.

**Forbidden misuse:** Putting business logic, object graph assembly, or runtime machinery in PublicSurface.

**Example names:** `Cache`, `Auth`, `Router`, `Database`.

**Learning:** `how-to-architecture.md — Public Surface Units`, `how-to-design-components.md — PublicSurface Rule`

---

## Capability

**Simple explanation:** A reusable ability or mechanism that supports multiple flows.

**AvaX meaning:** A shared system boundary or reusable behavior that multiple flows depend on. Examples: authentication, caching, logging.

**Allowed usage:** `Capabilities/` folder, `PolicyEvaluation` capability, `CacheReading` capability.

**Forbidden misuse:** Using "Capability" as a folder name for technical categories. Each capability must be a named product responsibility.

**Example names:** `PolicyEvaluation`, `CacheReading`, `QueryCompilation`, `RuntimeSafety`.

**Learning:** `how-to-architecture.md — Capability Slices`

---

## Flow

**Simple explanation:** A complete end-to-end action or use case.

**AvaX meaning:** A single narrative of behavior from start to finish. One flow = one complete user, system, or platform action. Replaces "UseCase" terminology.

**Allowed usage:** `Flows/` folder, `RegisterUser` flow, `HandleIncomingHttp` flow.

**Forbidden misuse:** Using "Flow" as a suffix (`UserFlow`, `RequestFlow`). Flow names should be actions, not state containers.

**Example names:** `RegisterUser`, `HandleIncomingHttp`, `RunMigration`, `ChangePassword`.

**Learning:** `how-to-architecture.md — Flow Slices`

---

## Provider

**Simple explanation:** A registration and assembly unit for dependencies.

**AvaX meaning:** A ServiceProvider or similar class that registers bindings, configures defaults, and declares component dependencies in the container.

**Allowed usage:** `AuthServiceProvider`, `CacheServiceProvider`, `register()` method.

**Forbidden misuse:** Provider classes that execute runtime behavior. Providers register; they do not execute.

**Example names:** `AuthServiceProvider`, `DatabaseServiceProvider`, `ObservabilityServiceProvider`.

**Learning:** `how-to-dependency-injection.md`

---

## Builder

**Simple explanation:** A class that assembles an object graph or configures a component at configuration time.

**AvaX meaning:** A configuration-time assembly class that constructs dependency graphs, sets defaults, and converts user options into component runtime configuration. Must live in `Configuration/` or `Configuration/Builders/`.

**Allowed usage:** `BuildAuthRuntime`, `RegisterAuthDefaults`, `ConfigureAuth` (user-facing DSL).

**Forbidden misuse:** Builder classes in runtime folders, builders that execute behavior, builders that act as service locators, god builders over 300 lines.

**Example names:** `BuildAuthRuntime`, `AssembleHttpKernel`, `ConfigureCache`.

**Learning:** `how-to-design-components.md — Configuration/Builders Rule`, `how-to-dependency-injection.md — Builder Placement Rule`

---

## Factory

**Simple explanation:** A class that creates domain objects or runtime results.

**AvaX meaning:** A result-creation boundary. Distinct from Builder: Factory creates runtime results (responses, queries, messages); Builder assembles dependency graphs.

**Allowed usage:** `BuildCacheKey` (result factory), `TokenFactory` (creates token value objects).

**Forbidden misuse:** Factory classes that assemble dependency graphs. DDD Factories create domain objects, not runtime graphs.

**Example names:** `TokenFactory`, `ResponseFactory`, `BuildCacheKey`.

**Learning:** `how-to-dependency-injection.md — Factory Class Precision`

---

## Assembly

**Simple explanation:** The act of composing and wiring dependencies together.

**AvaX meaning:** Configuration-time dependency graph construction. Belongs in `Configuration/`, not runtime.

**Allowed usage:** `Assembly/` subfolder within `Configuration/`, "assembly" as a concept describing DI graph construction.

**Forbidden misuse:** `*Assembly` class names in runtime folders. Assembly is a configuration responsibility.

**Example names:** `TokensGraph` (in Configuration/Assembly/), `RegisterDependencies`.

**Learning:** `how-to-dependency-injection.md`, `how-to-runtime-composition.md`

---

## Graph

**Simple explanation:** A dependency graph or object graph assembled at configuration time.

**AvaX meaning:** The complete set of interconnected dependencies that form a component's runtime. Graph classes assemble and return the assembled object graph.

**Allowed usage:** `TokensGraph` (in Configuration/Assembly/), "graph" as a concept describing the dependency tree.

**Forbidden misuse:** `*Graph` class names outside Configuration/Assembly/. Large Graph classes that should be decomposed by subsystem boundaries.

**Example names:** `TokensGraph`, `AuthGraph` (in Configuration/Assembly/ only).

**Learning:** `how-to-dependency-injection.md`, `how-to-design-components.md — Section 30`

---

## DSL

**Simple explanation:** A domain-specific language — fluent API surface for human readability.

**AvaX meaning:** User-facing fluent configuration or query APIs. DSL readability comes from method chaining and fluent method names, not from `*Dsl` class name suffixes.

**Allowed usage:** DSL as a concept, fluent method chains, `ConfigureAuth::auth()->permissions()...`.

**Forbidden misuse:** `*Dsl` class name suffixes (`AuthDsl`, `IdentityDsl`). Class names must say what they own, not that they are a DSL.

**Example names:** `ConfigureAuth`, `ConfigureCache` (user-facing DSL entrypoints).

**Learning:** `how-to-architecture.md — Section 54.1`, `how-to-dependency-injection.md — Fluent DSL Design Principles`

---

## Facade

**Simple explanation:** A thin public entrypoint that delegates to internal owners.

**AvaX meaning:** A stable public API class that receives natural inputs and delegates to internal Flows or Capabilities. Must stay thin and stable.

**Allowed usage:** `Cache` facade, `Auth` facade, facade pattern for public API stability.

**Forbidden misuse:** Facades that accumulate ad-hoc behavior, facades that own runtime machinery.

**Example names:** `Cache`, `Auth`, `Router`, `DB`.

**Learning:** `how-to-design-components.md — PublicSurface Rule`

---

## Policy

**Simple explanation:** A rule or decision about what is allowed.

**AvaX meaning:** An authorization, access-control, or behavioral rule unit. Policies answer "is this allowed?" or "what applies here?".

**Allowed usage:** `RequirePermission`, `AccessPolicy`, `PolicyEvaluator`, `RateLimitPolicy`.

**Forbidden misuse:** Policy classes that own orchestration or IO. A policy decides; it does not execute flows.

**Example names:** `RequirePermission`, `RateLimitPolicy`, `TenantIsolationPolicy`.

**Learning:** `how-to-architecture.md`, Identity subsystem governance

---

## Token

**Simple explanation:** A cryptographic or session-based credential used for authentication/authorization.

**AvaX meaning:** JWT, access tokens, refresh tokens, CSRF tokens, or similar authentication artifacts owned by the Identity/Tokens subsystem.

**Allowed usage:** `TokenSigner`, `TokenValidator`, `TokenAuthority`, `TokenExpiry`.

**Forbidden misuse:** Token classes that own authentication flows. Tokens are values or operations on values, not flows.

**Example names:** `TokenSigner`, `TokenValidator`, `RefreshToken`, `CsrfToken`.

**Learning:** Identity subsystem governance

---

## Session

**Simple explanation:** A stateful interaction context between a user/system and the application.

**AvaX meaning:** HTTP session state, worker session context, or authentication session tracking.

**Allowed usage:** `SessionRegistry`, `SessionManager` (only if justified — prefer `SessionRegistry`), `SessionExpiry`.

**Forbidden misuse:** Session classes that own authentication decisions. Sessions track state; they do not authorize.

**Example names:** `SessionRegistry`, `SessionStore`, `SessionExpiry`.

**Learning:** Identity subsystem governance

---

## Credential

**Simple explanation:** Proof of identity — passwords, keys, certificates, passkeys.

**AvaX meaning:** Authentication proof material owned by the Identity/Credentials subsystem.

**Allowed usage:** `CredentialAuthority`, `CredentialValidator`, `CredentialStore`, `PasskeyCredential`.

**Forbidden misuse:** Credential classes that own session management or authorization decisions. Credentials verify identity; they do not manage sessions.

**Example names:** `CredentialAuthority`, `PasswordCredential`, `PasskeyCredential`.

**Learning:** Identity subsystem governance

---

## Tenant

**Simple explanation:** A multi-tenant isolation boundary — an organization, account, or workspace.

**AvaX meaning:** The unit of data and access isolation in multi-tenant systems.

**Allowed usage:** `TenantAccess`, `TenantIsolation`, `TenantContext`, `RequireTenant`.

**Forbidden misuse:** Tenant classes that own user authentication. Tenants isolate scope; they do not authenticate users.

**Example names:** `TenantAccess`, `TenantIsolationPolicy`, `TenantContext`.

**Learning:** Identity subsystem governance

---

## Elevation

**Simple explanation:** Temporary privilege increase — admin elevation, role escalation, time-limited access.

**AvaX meaning:** A controlled, auditable, time-limited increase in user permissions for administrative or emergency access.

**Allowed usage:** `ElevationRequest`, `RequireElevation`, `ElevationExpiry`, `AdminElevation`.

**Forbidden misuse:** Elevation classes that own base authentication. Elevation augments existing auth; it does not replace it.

**Example names:** `ElevationRequest`, `AdminElevation`, `ElevationAudit`.

**Learning:** Identity subsystem governance

---

## Risk

**Simple explanation:** A measurable threat or uncertainty factor in security decisions.

**AvaX meaning:** A quantified security risk score, threat level, or confidence factor used in adaptive authentication and authorization.

**Allowed usage:** `RiskAssessment`, `RiskScore`, `RiskPolicy`, `EvaluateRisk`.

**Forbidden misuse:** Risk classes that own authentication. Risk informs decisions; it does not authenticate.

**Example names:** `RiskAssessment`, `RiskScore`, `AdaptiveRiskPolicy`.

**Learning:** Identity subsystem governance

---

## Technical Theater

**Simple explanation:** Introducing patterns, abstractions, or ceremony that look sophisticated but solve no real problem.

**AvaX meaning:** Code that uses builders, factories, graphs, managers, coordinators, orchestrators, or wiring layers without a proven structural need. It moves complexity behind prettier names rather than reducing it.

**Allowed usage:** Patterns that demonstrably reduce cognitive load or solve a specific coupling problem.

**Forbidden misuse:** Creating a Builder to hide a long constructor without explaining why. Creating a Coordinator to wrap three method calls. Creating a Manager because "that's what enterprise code does."

**Example:** `AuthCoordinator` that only calls `$auth->login()` then `$session->start()` — this is technical theater. Call both directly in the Flow.

**Learning:** `how-to-design-components.md — Section 30.3`, `how-to-clean-code.md — Section 25.3`

---

## Cognitive Load

**Simple explanation:** The mental effort required to understand, modify, or navigate code.

**AvaX meaning:** A measurable quality attribute of code. Low cognitive load means a developer can understand a unit by reading it, not by opening five other files. High cognitive load means the design is suspicious.

**Allowed usage:** Designing for low surprise, fast navigation, boring cohesion. Using cognitive load as a review criterion.

**Forbidden misuse:** Accepting high cognitive load as "just how enterprise code is." Blaming the reader instead of the design.

**Example:** A `RegisterUser` Flow that requires reading `UserFactory`, `UserBuilder`, `UserGraph`, `UserAssembly`, and `UserServiceProvider` to understand what it does — cognitive load is too high.

**Learning:** `how-to-design-components.md — Section 30.5`, `how-to-clean-code.md — Section 25.5`

---

## Structural Honesty

**Simple explanation:** The code structure reveals the real architecture, not a美化ed version of it.

**AvaX meaning:** Subsystem boundaries, dependency directions, and responsibility assignments are visible in the folder/class structure. God objects, dependency chaos, and circular coupling are not hidden behind facades, builders, or configuration wrappers.

**Allowed usage:** Structure that matches architecture docs. Folders that say flow or capability. Dependencies that flow through DI.

**Forbidden misuse:** A `SimpleAuth` facade hiding 12 interconnected classes with circular dependencies. A `Configuration` class that registers 40 unrelated services.

**Example:** `AuthenticationGateway` clearly coordinates `CredentialAuthority` and `SessionRegistry` — this is structurally honest. `AuthManager` does everything — this is structurally dishonest.

**Learning:** `how-to-design-components.md — Section 30.6`, `how-to-architecture.md — Section 55.6`

---

## Fluent API

**Simple explanation:** An API designed to read like natural language through method chaining and intention-revealing names.

**AvaX meaning:** Call-sites that express intent, not internal mechanics. The boundary method accepts natural inputs and normalizes internally. Public surfaces feel like small fluent API units.

**Allowed usage:** `App::identity()->auth()`, `$cache->remember('key', $ttl, $fn)`, `$router->get('/users', $handler)`.

**Forbidden misuse:** `Auth::from(Token::from(Session::from($request)))` — nested construction violates fluent API. Class names ending in `Dsl` — the class should say what it owns, not that it is a DSL.

**Example:** `$context->finishRequest($response)` — fluent. `$context->finishRequest(RuntimeResult::fromResponse(RuntimeResponse::fromPsrResponse($response)))` — not fluent.

**Learning:** `how-to-design-components.md — Section 30.4`, `how-to-clean-code.md — Section 5.4.1`

---

## Gateway

**Simple explanation:** The single preferred entry point to a subsystem.

**AvaX meaning:** A class or PublicSurface that coordinates internal capabilities and exposes one controlled API to consumers. Implements the Single Preferred Entry rule.

**Allowed usage:** `AuthenticationGateway`, `CacheGateway`, `DatabaseGateway`. A gateway coordinates siblings; it does not leak internal machinery.

**Forbidden misuse:** Multiple gateways for the same subsystem. A gateway that exposes every internal method. A gateway that is just a thin wrapper with no coordination value.

**Example:** `AuthenticationGateway` coordinates `CredentialAuthority`, `SessionRegistry`, and `MfaProtection` — one entry point for authentication consumers.

**Learning:** `how-to-design-components.md — Section 30.9.5`, `how-to-architecture.md — Section 55.9.5`

---

## Subsystem

**Simple explanation:** A coherent unit of the system larger than a capability, smaller than the full system.

**AvaX meaning:** A bounded area with clear ownership, internal capabilities, and one gateway. Examples: Identity, Cache, Database, HTTP. Subsystems decompose recursively into capabilities and flows.

**Allowed usage:** `Identity` subsystem, `Cache` subsystem, `Identity/System/PublicSurface/`, `Identity/System/Capabilities/`.

**Forbidden misuse:** Using "Subsystem" as a folder name. Subsystem is a concept, not a directory. Creating subsystems for every class.

**Example:** `components/Identity/System/` — the Identity subsystem with its PublicSurface, Flows, Capabilities, Configuration, and Foundation.

**Learning:** `how-to-design-components.md — Section 30.7`, `how-to-architecture.md — Section 55.7`

---

## Recursive Decomposition

**Simple explanation:** The process of breaking large units into smaller units by identifying real ownership boundaries.

**AvaX meaning:** When a unit becomes large, first identify hidden subsystems, then capabilities, then flows, then policies/rules/value objects. Do not split mechanically — split by behavior and ownership. Stop when units become boring, cohesive, readable, and predictable.

**Allowed usage:** Decomposing a 500-line class into `CredentialAuthority`, `SessionRegistry`, and `MfaProtection` because each owns distinct behavior.

**Forbidden misuse:** Splitting `UserService` into `UserServicePart1`, `UserServicePart2`. Extracting `UserManagerHelper` from a method. Mechanical extraction without ownership analysis.

**Example:** A large `AuthRuntime` decomposed into `AuthenticationGateway` (entry), `CredentialAuthority` (verification), `SessionRegistry` (state), `MfaProtection` (policy) — each owns clear behavior.

**Learning:** `how-to-design-components.md — Section 30.7`, `how-to-architecture.md — Section 55.7`
