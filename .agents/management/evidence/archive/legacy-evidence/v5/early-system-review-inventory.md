# Early V5.6-Style System Review Inventory

## Status

**Stage:** V5 Readiness — Early System Review Inventory
**Date:** 2026-05-10
**Scope:** Framework runtime, component platform, public surface, execution flow
**Mode:** Standard

---

## 1. System Identity

| Field             | Value                                          |
|-------------------|------------------------------------------------|
| System Type       | PHP Framework / Platform                       |
| Primary Consumers | Application layer, CLI, HTTP workers           |
| Runtime Context   | HTTP request, CLI, long-running worker (V4-17) |
| Lifecycle         | V4 GREEN baseline, V5 readiness pass           |

### 1.1 Intended Use-Cases

- Rapid application development through `Avax::create()` simple API
- Full application boot through `ApplicationBuilder` advanced API
- HTTP request dispatch through router -> controller -> response pipeline
- CLI command execution
- Long-lived worker runtime (RoadRunner/Swoole/FrankenPHP adapters)
- Component platform with 74 reusable components across 15 areas

### 1.2 Anti-Use-Cases

- Micro-framework replacement for edge computing
- Direct superglobal access outside framework Request
- Reflection-based hot-path execution per request

### 1.3 Non-Goals

- V5 implementation (this is readiness only)
- V5.5 benchmarking (separate phase)
- V5.6 final review (separate phase)

### 1.4 Compatibility Contract

- Public API Stability: **strict** — `Avax::create()`, `App->get/post/put/patch/delete/run()`
- Backwards Compatibility: **required** within V4 baseline
- Performance Budget: Sub-ms framework overhead per request (to be proven in V5.5)

---

## 2. As-Built Execution Flow

### 2.1 Primary Execution Lifecycle

```
Avax::create($env)
  -> CreateApplication->make()
    -> Build Runtime state (ProjectPath, EnvironmentName, SystemClock)
    -> Build ComponentRegistry, RequestScopeStore, RuntimeContext, StateResetRegistry
    -> Build Runtime object
    -> Build App(Runtime, ResetApplicationState)
  -> App->get/post/put/patch/delete(path, action)
    -> Register RouteDefinition into internal route list
  -> App->run()
    -> handleRequest() [BYPASS: reads $_SERVER, $_GET, php://input directly]
      -> Build RuntimeRequest from superglobals
      -> handle(RuntimeRequest)
        -> OpenHttpRequestScope (request-scoped state)
        -> RunApplication->handle(runtimeRequest, routes)
          -> ReadIncomingHttpRequest->read() -> ServerRequest
          -> MatchHttpRoute->match() -> MatchedRoute
          -> dispatchRoute()
            -> executeAction()
              -> Closure -> direct invoke
              -> [Class, method] -> ControllerResolver + ArgumentResolver
              -> Class string -> ControllerResolver (invokable)
            -> NormalizeControllerResult->normalize()
          -> CloseHttpRequestScope
        -> Return ResponseInterface
    -> Return ResponseInterface
```

### 2.2 This Is How The System Actually Works

AvaX provides two boot paths: a simple `Avax::create()` zero-config path that builds an `App` with direct route
registration, and an advanced `Avax::boot()` path that uses `ApplicationBuilder` for full component assembly. At request
time, `App->run()` reads PHP superglobals directly (`$_SERVER`, `$_GET`, `php://input`) to build a `RuntimeRequest`,
then dispatches through a route match -> controller resolve -> argument resolve -> result normalize -> response
pipeline. Request-scoped state is opened before dispatch and closed after, with state reset registry available for
long-lived runtimes.

### 2.3 Mermaid Flow Diagram

```mermaid
flowchart LR
  A[Avax::create] --> B[CreateApplication]
  B --> C[Build Runtime]
  C --> D[Build App]
  D --> E[Register Routes]
  E --> F[App::run]
  F --> G[Read Superglobals]
  G --> H[Build RuntimeRequest]
  H --> I[Open Request Scope]
  I --> J[Match Route]
  J --> K[Resolve Controller]
  K --> L[Resolve Arguments]
  L --> M[Invoke Action]
  M --> N[Normalize Result]
  N --> O[Close Request Scope]
  O --> P[Return Response]
```

### 2.4 State Creation, Mutation, Decision, and Purity

| Phase                 | State Created            | State Mutated             | Decisions Made            | Pure/Mechanical             |
|-----------------------|--------------------------|---------------------------|---------------------------|-----------------------------|
| `Avax::create()`      | Runtime, App, registries | N/A (boot-time one-shot)  | Environment, paths, clock | Mechanical assembly         |
| Route registration    | RouteDefinition array    | App route list            | None (declarative)        | Pure registration           |
| `App->run()`          | RuntimeRequest           | Running flag              | Superglobal read decision | Mechanical (not pure — I/O) |
| Route matching        | MatchedRoute             | None                      | Method + URI match        | Pure algorithm              |
| Controller resolution | Controller instance      | Container (if DI)         | Class existence check     | Mechanical                  |
| Argument resolution   | Argument array           | Container state           | Type/attribute resolution | Mechanical with reflection  |
| Action invocation     | Result (mixed)           | User code side effects    | User logic                | User-defined                |
| Result normalization  | ResponseInterface        | None                      | Result type dispatch      | Mechanical                  |
| Scope close           | None                     | RequestScopeStore cleared | None                      | Mechanical                  |

---

## 3. Primary Axis Statement

> "This system is fundamentally organized around the **runtime application lifecycle**."

The central abstraction is the `Runtime` object that owns application state, request scopes, component registry,
environment context, and lifecycle management. Everything flows through: boot -> register -> run -> match -> dispatch ->
respond -> reset.

### 3.1 Secondary Axis

> "Secondary axis: **component platform** (74 reusable components across 15 areas) — adds complexity and must be
> justified."

The component platform provides reusable capabilities (Cache, Database, Router, Session, Security, etc.) that the
framework assembles during boot and components may use at runtime. This is a legitimate secondary axis because the
framework is designed as a platform, not a monolith. Each component follows the canonical `System/` shape with
`PublicSurface/`, `Flows/`, `Capabilities/`, `Configuration/`, `Foundation/`.

### 3.2 Primary Axis Stress Test

| Question                                       | Answer  | Evidence                                                                                         |
|------------------------------------------------|---------|--------------------------------------------------------------------------------------------------|
| Does every feature flow through Runtime?       | Yes     | `Runtime` owns state, scopes, components, environment, lifecycle. All flows pass through it.     |
| Does Runtime accumulate responsibilities?      | Partial | Currently 8 constructor parameters. Borderline on constructor bloat (5-7 = check, 8+ = warning). |
| Is Runtime harder to change than surroundings? | No      | Runtime is a stable state holder. Changes are additive (new registries, new adapters).           |

**Classification:** PASS — stable axis, but constructor parameters (8) approach the bloat threshold.

---

## 4. Responsibility and Boundary Map

### 4.1 Framework-Level

| Component            | Orchestrates          | Executes               | Holds State               | Notes                                                     |
|----------------------|-----------------------|------------------------|---------------------------|-----------------------------------------------------------|
| `Avax`               | Boot paths            | Delegates to flows     | Kernel references         | Public entry point, thin facade                           |
| `App`                | Route registration    | Request dispatch       | Routes, middleware, flags | V4 zero-config API, reads superglobals in `handleRequest` |
| `CreateApplication`  | App assembly          | State building         | None (flow)               | Boot flow, wires Runtime + App                            |
| `RunApplication`     | Full request pipeline | Route match + dispatch | Resolver instances        | Core dispatch flow, uses RouteFacadeContainer             |
| `Runtime`            | Lifecycle management  | Scope management       | State, scopes, components | Central abstraction, 8 constructor params                 |
| `ComponentRegistry`  | Component boot        | Component resolution   | Component instances       | Owns all 74 component registrations                       |
| `RequestScopeStore`  | Scope lifecycle       | Scope open/close       | Per-request state         | Critical for long-lived runtimes                          |
| `StateResetRegistry` | Reset orchestration   | Per-object reset calls | Registered state objects  | Critical for worker safety                                |

**Red Flags:**

- `App->handleRequest()` reads superglobals directly (Section 5.1)
- `Runtime` has 8 constructor parameters — at constructor bloat warning threshold
- `RunApplication` creates `RouteFacadeContainer` internally — hidden static dependency pattern

### 4.2 Component Platform (15 Areas, 74 Components)

| Area           | Component Count | Examples                                                     |
|----------------|----------------:|--------------------------------------------------------------|
| API            |               5 | ApiBlueprint, Contracts, GraphQL, OpenAPI, SchemaGeneration  |
| Application    |              11 | Cache, Config, Container, Filesystem, Storage, Validation    |
| CLI            |               2 | Console, System                                              |
| DataStack      |               4 | Data, DataTransfer, Database, Persistence                    |
| DeveloperTools |               6 | CodeGeneration, Diagnostics, Documentation, DumpDebugger, Dx |
| Foundation     |               3 | CallableSerialization                                        |
| HTTP           |              13 | Router, Request, Response, Dispatcher, Session, Security     |
| Identity       |               7 | Access, Auth, Credentials, Security, Tenancy, Tokens         |
| Integration    |               1 | ObjectStorage                                                |
| Operations     |              17 | Queue, Events, Logging, Observability, Resilience, Scheduler |
| Presentation   |               2 | View, System                                                 |
| Security       |               3 | Cryptography, DataProtection, Hashing                        |
| SystemDesign   |               1 | System                                                       |

**Note:** Component count is 74. Full component inventory available in capability ownership map (Part 4 deliverable).

---

## 5. System Invariants

### Invariant 1: Superglobal Isolation

**Rule:** All PHP superglobals must be isolated behind AvaX Request object. No other code may access superglobals
directly.

**Status:** VIOLATED — `App->handleRequest()` reads `$_SERVER['REQUEST_METHOD']`, `$_SERVER['REQUEST_URI']`, `$_SERVER`
headers, and `file_get_contents('php://input')` directly.

**Impact:** Breaks superglobal isolation rule. The `App` class is the sole owner of superglobal access per design, but
this should be explicit through a `Request` component, not inline in `handleRequest`.

### Invariant 2: Attribute Compilation, Not Reflection Per Request

**Rule:** Attributes must be compiled at boot time. No reflection per request, no attribute scanning per call.

**Status:** RISK — `RunApplication` uses `ReflectionMethod` in `invokeControllerAndMethod()`. This is per-request
reflection. The `RouteFacadeContainer` resolves controllers at runtime.

**Impact:** Hot-path reflection violates compiled metadata rule. V5 must introduce attribute compilation so controller
metadata is resolved from compiled cache, not reflection.

### Invariant 3: One Capability, One Owner

**Rule:** If AvaX owns a capability in one component, no other component may reimplement that capability locally.

**Status:** UNPROVEN — Requires full capability ownership scan (Part 4). Preliminary: `Cache` exists in
`components/Application/Cache`, `Filesystem` in `components/Application/Filesystem` and
`components/Operations/Filesystem`, `Storage` in `components/Application/Storage` and
`components/Integration/ObjectStorage`. These may be legitimate different capabilities but need dogfooding verification.

### Invariant 4: PublicSurface Delegation

**Rule:** PublicSurface receives. It does not execute real behavior. It must delegate to Flows, Capabilities, or
Configuration.

**Status:** PASS — `Avax` delegates to `CreateApplication`, `BootApplication`, `HttpKernel`, `ConsoleKernel`,
`ResetApplicationState`. `App` delegates to `RunApplication`, `OpenHttpRequestScope`, `CloseHttpRequestScope`,
`RenderApplicationError`. Thin public surface maintained.

### Invariant 5: Stage Lock Enforcement

**Rule:** Only one stage may be active. No V2 before V1 Green. No V3 before V1+V2 proven. No V4 before V1+V2+V3 proven.
V4-01 cannot start until V4-00 GREEN.

**Status:** PASS — All V4 stages GREEN per CURRENT_TRUTH.md evidence. V5 readiness pass is allowed. No implementation of
locked stages detected in this review.

### Invariant 6: Canonical Component Shape

**Rule:** Every production component follows `System/PublicSurface, Flows, Capabilities, Configuration, Foundation`. No
other top-level System/ folders allowed.

**Status:** PARTIAL — Tooling exists (`check-component-suite-structure.php`, `check-component-canonical-shape.php`) but
requires validation run. Preliminary scan shows many components follow the shape but full verification needed.

### Invariant 7: No Forbidden Folders

**Rule:** No `Services/`, `Helpers/`, `Utils/`, `Common/`, `Shared/`, `Managers/`, `Core/`, `Support/`, etc. as default
directories.

**Status:** PARTIAL — Tooling exists (`check-forbidden-folders.php`) but requires validation run. Preliminary:
`Contracts` folder exists in `components/API/Contracts` — this may be justified as a component but needs governance
review.

### Invariant 8: Constructor Bloat Discipline

**Rule:** 0-4 dependencies normal, 5-7 check, 8+ warning. If 12+ dependencies, probably does too much.

**Status:** WARNING — `Runtime` has 8 constructor parameters (at warning threshold). `Avax` has 5 constructor
parameters (in check range). Most components and flows are clean (0-4 params).

---

## 6. First Governance Gaps

### 6.1 Tooling Gap

**Observation:** The AGENTS.md §19 lists 14+ planned tooling gates. Existing tooling directory has many check scripts
but they are scattered across `tooling/Architecture/`, `tooling/Refactor/`, `tooling/architecture/` (duplicate), and
other subdirectories.

**Gap:** No single validation entry point. The canonical validation commands in AGENTS.md reference
`php tooling/refactor/check-*.php` but some commands like `php avax runtime:doctor` and
`php tooling/security/check-security-governance.php` may not exist yet.

**Risk:** Fragmented tooling structure makes it hard to run full validation as a single step.

### 6.2 Superglobal Bypass

**Observation:** `App->handleRequest()` directly reads `$_SERVER`, calls `file_get_contents('php://input')`.

**Gap:** While `App` is the public entry point and this is the intended design boundary, it technically violates the
superglobal isolation rule which says "Request component is the sole owner of superglobal access."

**Risk:** Medium. The behavior is intentional but the implementation location is debatable.

### 6.3 Hot-Path Reflection

**Observation:** `RunApplication->invokeControllerAndMethod()` uses `ReflectionMethod` per request to invoke controller
methods.

**Gap:** V4 design uses reflection for controller invocation. V5 must introduce compiled metadata to eliminate
per-request reflection.

**Risk:** Performance. This is a V5 target, not a V4 blocker. Current V4 baseline is acceptable.

### 6.4 RouteFacadeContainer

**Observation:** `RunApplication` creates `new RouteFacadeContainer()` internally and clones it for resolver
construction.

**Gap:** Hidden static dependency pattern. The container is not injected, making it hard to test and violating explicit
DI discipline.

**Risk:** Medium. Affects testability and DI purity.

### 6.5 Documentation Completeness

**Observation:** Only 5 component READMEs exist across 74 components. The `docs/` directory has content but
component-level documentation is sparse.

**Gap:** Component completion standard requires documentation for every component. Most components lack local README and
docs/ documentation.

**Risk:** Medium. Affects developer experience and component discoverability.

### 6.6 Duplicate Tooling Directories

**Observation:** `tooling/Architecture/` and `tooling/architecture/` both exist with overlapping check scripts.

**Gap:** Duplicate tooling directory structure. `tooling/Refactor/` and `tooling/Architecture/` have duplicated check
scripts (check-docs-mirror.php, check-duplicate-owners.php, check-namespace-drift.php, check-public-surface.php,
check-runtime-leaks.php).

**Risk:** Low for correctness, medium for maintenance confusion.

---

## 7. Component Taxonomy Summary

| Metric                        | Value                    |
|-------------------------------|--------------------------|
| Total classes                 | 9102                     |
| Total tests                   | 7451                     |
| Test status                   | PASSING                  |
| PHPStan errors                | 0                        |
| Areas                         | 15                       |
| Components                    | 74                       |
| Component READMEs             | 5                        |
| Documentation directories     | docs/ (9 subdirectories) |
| Tooling scripts (check-*.php) | 26                       |
| Governance how-to documents   | 15                       |

---

## 8. Evidence Pointers

| Claim                           | Evidence Source                                                            |
|---------------------------------|----------------------------------------------------------------------------|
| V4 all GREEN                    | `CURRENT_TRUTH.md`, `EVIDENCE/v5/v4-final-truth-lock.md`                   |
| Execution flow                  | `framework/System/PublicSurface/Avax.php`, `App.php`, `RunApplication.php` |
| Superglobal bypass              | `App.php:311-324` (`handleRequest` method)                                 |
| Hot-path reflection             | `RunApplication.php:165` (`ReflectionMethod` usage)                        |
| Constructor bloat               | `Runtime` class (8 params), `Avax` class (5 params)                        |
| Component inventory             | `find components -mindepth 2 -maxdepth 2 -type d`                          |
| Tooling inventory               | `find tooling -name "check-*.php"`                                         |
| Governance inventory            | `.agents/how-to/how-to-*.md` (15 documents)                                |
| RouteFacadeContainer hidden dep | `RunApplication.php:62-64`                                                 |

---

## 9. What This Review Does NOT Cover

- V5 implementation (out of scope)
- V5.5 benchmarks (out of scope)
- V5.6 final review (out of scope)
- Full component capability ownership scan (Part 4)
- Dogfooding adoption matrix (Part 5)
- Security boundary penetration test (separate review)
- Performance benchmarking (V5.5)
- Full governance compliance matrix per component

---

## 10. Next Allowed Actions

1. Complete Part 4: Capability ownership map
2. Complete Part 5: Dogfooding adoption matrix
3. Complete Part 6: Initial tooling gates
4. Then proceed to Part 7: P0/P1 blocker fixes
5. Then Part 8: Validation and final report
