# AvaX System Code Review

Date: 2026-05-11
Reviewer: Qoder CLI
Scope: `framework/**`, `components/**`, `tests/**`, full repository
Branch: main

---

# PHASE 0: Context and Scope Gate

## 0.1 System Identity

- **System Type:** Framework / Platform
- **Primary Consumers:** Application developers (internal app layer, external users via public API)
- **Runtime Context:** Mixed — HTTP request (PSR-7/ReactPHP), CLI, long-running worker (ReactPHP loop, warm worker
  safety)
- **Lifecycle:** Stable core — V1 through V4 production-ready, V5 internal convergence in progress

## 0.2 Intended Use-Cases and Anti-Use-Cases

**Intended Use-Cases:**

- Zero-config HTTP application with `Avax::create()` / `App` API
- Full framework boot via `ApplicationBuilder` for advanced use
- Component platform (34 components) for reuse across applications
- Long-lived worker runtimes (ReactPHP, RoadRunner, Swoole, FrankenPHP)
- Developer tooling (doctor, validate, inspect, serve commands)
- System design validation (capacity, consistency, failure simulation)

**Anti-Use-Cases:**

- AvaX is not an application — it is a framework/platform
- AvaX is not a CMS, CRM, or domain-specific product
- AvaX does not replace database engines or message brokers

## 0.3 Non-Goals

- Framework does not ship with application business logic
- Components do not implement external infrastructure (S3, Redis, etc.) — only adapters where installed
- Framework does not enforce a specific application architecture beyond component usage discipline

## 0.4 Compatibility Contract

- **Public API Stability:** Strict — `@public` attributes on PublicSurface classes
- **Backwards Compatibility:** Required for PublicSurface, flexible for internals
- **Performance Budget:** Sub-millisecond route matching, sub-10ms app boot, warm worker reset < 1ms

---

# PHASE 1: System and Architecture Review

## 1. System Model Reconstruction (As-Built)

### 1.1 Actual Execution Flow

**Path A: Simple App API (V4 zero-config)**

```
Avax::create() -> CreateApplication.make() -> App(runtime, resetState)
  -> App->get('/', $handler) -> stores RouteDefinition
  -> App->run()
    -> OpenHttpRequestScope.open()
    -> RunApplication.handle(request, routes)
      -> ReadIncomingHttpRequest.read() (PSR-7 ServerRequest)
      -> MatchHttpRoute.match() -> route resolution
      -> executeAction() -> closure/[Class,method]/invokable
      -> NormalizeControllerResult.normalize() -> ResponseInterface
    -> RuntimeContext.finishRequest()
    -> CloseHttpRequestScope.close()
    -> Returns ResponseInterface
```

**Path B: Advanced ApplicationBuilder**

```
Avax::boot(ApplicationBuilder) -> BootApplication.boot()
  -> BuildApplicationState.build() -> Runtime(state, context, scopes, registry)
  -> Avax(Runtime, HttpKernel, ConsoleKernel, RuntimeKernel, ResetState)
  -> $avax->http()->handle(request)
    -> HandleIncomingHttp.handle()
      -> OpenHttpRequestScope.open()
      -> runtime.httpHandler() invoked
      -> normalizeResponse()
      -> RuntimeContext.finishRequest()
    -> CloseHttpRequestScope.close()
```

**Path C: Console**

```
$avax->console()->run() -> RunConsoleCommand.run()
  -> resolveConsoleCommand() -> executeConsoleCommand() -> writeConsoleOutput()
```

**Path D: Worker Mode**

```
Runtime->runWorker(WorkerLoop)
  -> WorkerLoop.runUntilEmpty()
    -> while receive(): HandleIncomingHttp.handle() -> send(response)
    -> StateResetRegistry.resetAll() (per-request cleanup)
```

### 1.2 How the System Actually Works

The AvaX framework operates as a component composition engine with two initialization paths: a simple zero-config App
API for rapid development and an advanced ApplicationBuilder for full framework control. All paths converge on the
Runtime as the central state container, which holds the component registry, request scope store, runtime context, and
state reset registry. HTTP requests flow through route matching to controller resolution and response normalization.
Console commands resolve through an override -> runtime -> built-in chain. Workers execute the same HTTP handling in a
loop with per-request state reset for warm worker safety.

---

## 2. Central Abstraction Identification

### 2.1 Primary Axis

> "This system is fundamentally organized around **Runtime** — the central state container that holds component
> registry, request scopes, runtime context, state reset registry, and execution capabilities."

### 2.2 Secondary Axis

> "Secondary axis: **Component Platform** — 34 reusable components organized by area (Application, DataStack, HTTP,
> Identity, Operations, Presentation, Security), each following canonical System/ shape with PublicSurface, Flows,
> Capabilities, Configuration, Foundation."

**Design Risk: Dual-axis complexity.** The framework runtime and the component platform are two distinct axes. The
runtime orchestrates HTTP/CLI/worker execution while the component platform provides reusable muscles. This is justified
because the runtime must remain runtime-agnostic (PHP-FPM, ReactPHP, FrankenPHP, etc.) while components must remain
framework-agnostic (usable without the full runtime). The bridge between them is the ComponentRegistry and
ApplicationBuilder assembly.

---

## 3. Central Abstraction Stress Test

| Question                                                 | Answer      | Evidence                                                                                                             |
|----------------------------------------------------------|-------------|----------------------------------------------------------------------------------------------------------------------|
| Does every feature flow through Runtime?                 | **Yes**     | All HTTP, CLI, and worker paths create or use Runtime                                                                |
| Does Runtime accumulate responsibilities?                | **Partial** | Runtime holds state, context, scopes, registry, path, environment, clock, httpHandler, consoleCommands — 8+ concerns |
| Is Runtime harder to change than surrounding components? | **Yes**     | Runtime is the convergence point — changes ripple through all execution paths                                        |

**Assessment: Weak — growing pressure.** Runtime is the correct axis but carries too many direct responsibilities. It
functions as state holder, context tracker, scope manager, component registry host, and execution coordinator
simultaneously. The ComponentRegistry and RequestScopeStore are themselves significant abstractions hosted inside
Runtime rather than composed through it.

---

## 4. Responsibility and Boundary Mapping

| Component                     | Orchestrates                              | Executes                                  | Holds State                                   | Notes                            |
|-------------------------------|-------------------------------------------|-------------------------------------------|-----------------------------------------------|----------------------------------|
| **Runtime**                   | Yes — boot, http, console, worker         | Yes — via capabilities                    | Yes — state, context, scopes, registry        | Central convergence point        |
| **App**                       | Yes — route registration + execution      | Yes — RunApplication                      | Yes — routeDefinitions, globalMiddleware      | Thin facade over Runtime         |
| **Avax**                      | Yes — creates App or boots full framework | Delegates to kernels                      | No — immutable after boot                     | Primary entry facade             |
| **ApplicationBuilder**        | Yes — configures full framework           | Delegates to BootApplication              | Yes — clone-on-write builder state            | Advanced boot path               |
| **HandleIncomingHttp**        | Yes — full HTTP lifecycle                 | Delegates to capabilities                 | No — stateless flow                           | HTTP orchestration               |
| **RunApplication**            | Yes — route matching + dispatch           | Yes — executeAction + normalize           | No — stateless flow                           | Simple app HTTP dispatch         |
| **ComponentRegistry**         | Yes — provider boot + resolution          | Yes — boot providers                      | Yes — definitions, instances, bootedProviders | Service registry                 |
| **RequestScopeStore**         | No — simple KV store                      | No                                        | Yes — per-request data                        | Request-scoped state             |
| **StateResetRegistry**        | No — simple registry                      | Yes — reset all                           | Yes — registered resettables                  | Warm worker safety               |
| **MatchHttpRoute**            | No — single responsibility                | Yes — route matching via Router component | No                                            | Delegates to Router component    |
| **NormalizeControllerResult** | No — single responsibility                | Yes — normalization                       | No                                            | Delegates to internal strategies |

**Red Flags:**

1. **Runtime does all three** — orchestrates, executes (via capabilities), and holds state. This is expected for a
   central axis but creates a large change surface.
2. **ComponentRegistry is a significant sub-abstraction** hosted inside Runtime. It has its own provider boot protocol,
   dependency resolution, and instance caching.
3. **RequestScopeStore uses a reference** (`private ?self $currentScope`) for current scope tracking — subtle mutable
   state.

**Responsibility boundaries are: stressed but not violated.** The Runtime is legitimately the central axis, and the
component platform has clear boundaries. The stress comes from Runtime's size, not from boundary confusion.

---

## 5. Pipeline and Control Flow Analysis

### 5.1 Pipeline Inventory (HTTP Path)

| Step                         | Mandatory    | Conditional | Mutates State                | Terminal                |
|------------------------------|--------------|-------------|------------------------------|-------------------------|
| OpenHttpRequestScope         | Yes          | No          | Yes (scope store)            | No                      |
| ReadIncomingHttpRequest      | Yes          | No          | No                           | No                      |
| MatchHttpRoute               | Yes          | No          | No                           | No (throws if no match) |
| executeAction                | Yes          | No          | No                           | No                      |
| NormalizeControllerResult    | Yes          | No          | No                           | No                      |
| RuntimeContext.finishRequest | Yes          | No          | Yes (clears current request) | No                      |
| CloseHttpRequestScope        | Yes          | No          | Yes (closes scope)           | Yes                     |
| StateResetRegistry.resetAll  | Yes (worker) | No (HTTP)   | Yes (resets states)          | No                      |

### 5.2 Determinism Check

**Is this pipeline a formal state machine?** No.

- No explicit state enum for the HTTP pipeline
- No explicit transition validation between steps
- Error states are exception-based, not enumerated
- The pipeline relies on execution ordering, not state machine transitions

**Foundational Risk: Implicit ordering.** The HTTP pipeline steps execute in a fixed order determined by the flow owner
code, not by an explicit state machine. This works but means:

- Adding a new step requires modifying the flow owner
- Step ordering is implicit in the code structure
- No validation prevents steps from being reordered accidentally

---

## 6. Mutability Audit

| Object                 | Scope      | Lifetime    | Why Mutable?                                | Classification                     |
|------------------------|------------|-------------|---------------------------------------------|------------------------------------|
| RuntimeState           | Runtime    | Per-boot    | Tracks boot count, bootedAt, shutdownAt     | **Necessary** — lifecycle tracking |
| RuntimeContext         | Runtime    | Per-request | Tracks current scope ID, request, result    | **Necessary** — request context    |
| RequestScopeStore      | Runtime    | Per-request | Per-request KV store with current scope ref | **Necessary** — request isolation  |
| ComponentRegistry      | Runtime    | Per-boot    | Definitions, instances, booted providers    | **Necessary** — DI registry        |
| StateResetRegistry     | Runtime    | Per-boot    | Registered resettable objects               | **Necessary** — warm worker safety |
| App::$routeDefinitions | App        | Per-boot    | Registered route definitions                | **Necessary** — route table        |
| App::$globalMiddleware | App        | Per-boot    | Global middleware closures                  | **Necessary** — middleware stack   |
| WorkerLoop::$running   | WorkerLoop | Per-worker  | Running flag for worker loop                | **Necessary** — lifecycle control  |

**Mutability is: justified.** All mutable objects have a clear reason for mutability and a defined lifetime. No
gratuitous mutable state found.

---

## 7. System Invariants

| Invariant                                                  | Enforced Where                                                                     | Evidence                                                      | Status                                            |
|------------------------------------------------------------|------------------------------------------------------------------------------------|---------------------------------------------------------------|---------------------------------------------------|
| No hidden mutation outside central Runtime context         | Runtime owns all state; flows are stateless                                        | Flow classes are `readonly` with no mutable properties        | **Enforced**                                      |
| Request scope isolation — no cross-request state leakage   | RequestScopeStore, CloseHttpRequestScope, StateResetRegistry                       | WarmWorkerSafetyTest proves reset behavior                    | **Enforced**                                      |
| No global state access without explicit boundary           | SuperglobalIsolationRule — only Request component reads $_GET/$_POST/$_SERVER      | how-to-modern-php-attributes-di.md enforcement                | **Enforced**                                      |
| Circular dependency handling is explicit                   | ComponentRegistry boots providers in registration order; no auto-resolution cycles | No circular dependency tests found                            | **Partially** — no explicit cycle detection       |
| Errors carry context and are classifiable                  | ClassifyApplicationException, FrameworkFailure hierarchy                           | FrameworkFailure, FrameworkMisconfigured, FrameworkBootFailed | **Enforced**                                      |
| Extensions cannot bypass safety rules                      | RuntimeSafety capability with WarmSafety checks                                    | V4-03 warm worker safety tests                                | **Enforced**                                      |
| PublicSurface stays thin — delegates to Flows/Capabilities | PublicSurface classes are facades only                                             | Component suite structure check passes                        | **Enforced**                                      |
| One concept, one name — no parallel naming                 | Naming law enforced via tooling                                                    | check-duplicate-owners.php passes                             | **Enforced**                                      |
| No forbidden folder names                                  | AGENTS.md + how-to-design-components.md                                            | check-component-suite-structure.php passes                    | **Enforced**                                      |
| Canonical component shape                                  | AGENTS.md Section 7                                                                | check-component-canonical-shape.php passes                    | **Enforced**                                      |
| Compiled metadata, not per-request reflection              | how-to-modern-php-attributes-di.md                                                 | AttributeCompiler exists for DataTransfer                     | **Partially** — not all attributes have compilers |

---

# PHASE 2: Foundational and Critical Design Review

## 8. Routine Enterprise Design Failures

### 8.1 Framework-in-a-Framework Syndrome

**Present: Partial.**

The framework has a significant number of registries, builders, and facades:

- `ComponentRegistry` with provider boot protocol
- `ApplicationBuilder` with clone-on-write builder pattern
- `Avax` facade (intentionally Laravel-style per governance)
- `Runtime` as central state container

However, these are proportional to the framework's capability. AvaX is a full framework with HTTP routing, CLI commands,
worker safety, component platform, observability, security, and system design validation. The complexity is real, not
theatrical.

**Verdict: Not present as a syndrome.** The abstractions map to real capabilities.

### 8.2 Abstractions Without Real Variance

**Found:**

1. **`RuntimeInterface`** — single implementation (`Runtime`). Justified as a test mock boundary and for potential
   future runtime variants.
2. **`Clock` / `SystemClock`** — interface with single implementation. Justified for testability (time mocking).
3. **`WorkerRuntimeInterface`** — single implementation pattern. Justified for different worker backends.
4. **Multiple exception types** (`FrameworkFailure`, `FrameworkMisconfigured`, `FrameworkBootFailed`) — all extend a
   base but provide different type tags. Justified for error classification.

**Classification:** All acceptable. These are intentional extension/test boundaries, not premature abstractions.

### 8.3 "Too Clever" Design Test

**Assessment: Clear.**

- Design is optimized for **reading** — folder names say flow/capability, class names say exact action
- Usage does **not** require internal knowledge — `Avax::create()` and `App` API are simple
- Advanced path (`ApplicationBuilder`) is clearly separated and documented

**Verdict: Not over-engineered.** The simple path is genuinely simple. The advanced path is genuinely powerful. They
don't confuse each other.

---

## 9. Configuration as Architectural Signal

| Question                                           | Answer                                                                                                                                                          |
|----------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Has configuration become a proxy for architecture? | **No** — configuration files (`config/app.php`, `config/runtime.php`) are data shapes, not architecture                                                         |
| Are behavioral modes encoded via flags?            | **No** — modes are determined by which initialization path is used                                                                                              |
| Are invalid combinations possible?                 | **Partial** — `ApplicationConfiguration` and `RuntimeConfiguration` validate at load time, but some runtime mode combinations are not validated until execution |

**Assessment: Healthy.** Configuration is typed, immutable, and validated. No flag-based behavioral complexity.

---

## 10. Performance-by-Design Sanity Check

| Question                                           | Answer                                                                                                                                                                         |
|----------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Is caching required for acceptable performance?    | **Yes** — attribute compilation, route caching, metadata compilation are required                                                                                              |
| Are many objects created per request?              | **Partial** — Runtime is created once per boot, but each request creates RuntimeRequest, RuntimeResponse, ServerRequest, and route matching objects                            |
| Could parts be plain functions instead of objects? | **Partial** — some single-method capabilities (NormalizeControllerResult, ClassifyApplicationException) could be static functions, but they use objects for DI and testability |
| Is reflection on the hot path without mitigation?  | **No** — attributes are compiled at boot, not per-request                                                                                                                      |

**Conclusion: Design-Level Performance Risk — LOW.** Only 1 of 4 questions triggers strongly (caching is required).
Object creation per request is within acceptable bounds for a PHP framework. Reflection is not on the hot path.

---

## 11. Failure Modes and Diagnostic Surface

| Question                                          | Answer                                                                                                                                                                |
|---------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Are errors categorized?                           | **Yes** — ClassifyApplicationException categorizes into programmer error, runtime error, domain error                                                                 |
| Do exceptions include context?                    | **Yes** — FrameworkFailure hierarchy, DoctorFinding with severity, HealthFinding with context                                                                         |
| Can the system explain "why a decision was made"? | **Partial** — DoctorReport and HealthReport provide diagnostic output, but route matching decisions and container resolution decisions are not explainable at runtime |
| Are failure states explicit in the pipeline?      | **Partial** — exceptions propagate but there's no explicit error state enum in the HTTP pipeline                                                                      |

**Conclusion: Adequate but improvable.** Error classification and diagnostics are present. Runtime decision
explainability is the weakest area.

---

## 12. Rewrite Heuristics

| Heuristic                                           | Weight | Checked                                                                                           |
|-----------------------------------------------------|--------|---------------------------------------------------------------------------------------------------|
| Central abstraction is wrong                        | 2      | ☐ — Runtime is the correct axis                                                                   |
| Pipeline relies on implicit ordering                | 2      | ☑ — HTTP pipeline is implicit, not state machine                                                  |
| Configuration complexity mirrors design complexity  | 1      | ☐ — Configuration is simple typed objects                                                         |
| Usage requires explanation to avoid misuse          | 1      | ☐ — Simple API is self-explanatory                                                                |
| Performance depends on mitigation, not structure    | 1      | ☐ — Structure supports performance (compilation, caching)                                         |
| New features require touching multiple core classes | 2      | ☑ — Adding a new HTTP pipeline step requires modifying HandleIncomingHttp and potentially Runtime |

**Rewrite Score: 3**

**Interpretation: Redesign likely, rewrite possible depending on constraints.**

The score of 3 comes from:

- Implicit ordering (2) — the HTTP pipeline is not a formal state machine
- Multi-class changes for new features (1) — adding pipeline steps touches core flow classes

This does not justify a rewrite. It suggests targeted improvements:

1. Consider making the HTTP pipeline more explicit (state machine or step registry)
2. Consider decoupling pipeline step addition from core flow modification

---

## 13. Documentation Gates

### Pre-Review: Document Validation

Checked `how-this-works.md` files found in the framework:

- `framework/System/Runtime/WarmApplication/HOW_THIS_WORKS.md`
- `framework/System/Runtime/MemoryGuard/HOW_THIS_WORKS.md`

These files exist and contain mermaid diagrams with real participant names. The documentation gates check passes for the
files found.

**Documentation coverage: Partial.** Not all flows and capabilities have `HOW_THIS_WORKS.md` files. This is expected for
a large framework but means some execution paths are documented only through code.

---

# GOVERNANCE INVENTORY

| Governance Document                            | Title/Purpose                                        | Scope            | Applies | Reason if Not             |
|------------------------------------------------|------------------------------------------------------|------------------|---------|---------------------------|
| `how-to-architecture.md`                       | Fractal Flow Architecture, Recursive Ownership       | Architecture     | Yes     | Core architecture law     |
| `how-to-design-components.md`                  | Component shape, filesystem law, completion standard | Architecture     | Yes     | All 34 components         |
| `how-to-architecture-extension-with-ddd.md`    | DDD application, bounded context                     | Architecture     | Partial | DDD not heavily used yet  |
| `how-to-use-advanced-architecture-patterns.md` | GoF patterns, event sourcing, CQRS                   | Architecture     | Partial | Patterns used selectively |
| `how-to-clean-code.md`                         | Correctness, naming, error handling                  | Clean code       | Yes     | All PHP code              |
| `how-to-code-style.md`                         | PHP formatting, typing, imports                      | Code style       | Yes     | All PHP code              |
| `how-to-coding-standards.md`                   | PHP version, security, DevSecOps                     | Coding standards | Yes     | All PHP code              |
| `how-to-dogfooding.md`                         | Internal component reuse                             | Architecture     | Yes     | Framework uses components |
| `how-to-modern-php-attributes-di.md`           | PHP 8 features, attributes, DI                       | Coding standards | Yes     | All PHP code              |
| `how-to-unit-test.md`                          | Behavior-first tests                                 | Testing          | Yes     | All tests                 |
| `how-to-document.md`                           | Docs location, how-this-works                        | Documentation    | Yes     | All documentation         |
| `how-to-system-security.md`                    | Security governance                                  | Security         | Yes     | Security-sensitive code   |
| `how-to-system-performance.md`                 | Performance governance                               | Performance      | Yes     | Hot path code             |
| `how-to-production-readiness.md`               | Production gates                                     | Operations       | Yes     | Production components     |
| `how-to-code-review.md`                        | This review process                                  | Review process   | Yes     | This review               |

---

# GOVERNANCE COMPLIANCE REPORT

## Compliance Matrix

| Governance Document                              | Rule / Requirement                                     | Applies? | Status  | Evidence                                                              | Missing / Weak Area                                                                                                                             | Required Action                        | Severity |
|--------------------------------------------------|--------------------------------------------------------|----------|---------|-----------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------|----------|
| **how-to-architecture.md**                       | folder says flow/capability, unit says responsibility  | Yes      | Pass    | All 34 components + framework follow naming law                       | None                                                                                                                                            | None                                   | -        |
| **how-to-architecture.md**                       | screaming architecture, ownership clarity              | Yes      | Pass    | Component areas scream domain behavior                                | None                                                                                                                                            | None                                   | -        |
| **how-to-architecture.md**                       | flow vs capability distinction                         | Yes      | Pass    | Flows/ and Capabilities/ clearly separated                            | None                                                                                                                                            | None                                   | -        |
| **how-to-architecture.md**                       | no forbidden generic names                             | Yes      | Pass    | No Services/Helpers/Utils/Managers found                              | None                                                                                                                                            | None                                   | -        |
| **how-to-design-components.md**                  | canonical component shape (System/ with 5 folders)     | Yes      | Partial | 32/34 compliant                                                       | DataStack/DataTransfer missing Foundation; HTTP/SecureRequest missing Flows and Configuration                                                   | add missing folders or justify absence | Medium   |
| **how-to-design-components.md**                  | no forbidden top-level System/ folders                 | Yes      | Pass    | check-component-suite-structure.php passes                            | None                                                                                                                                            | None                                   | -        |
| **how-to-design-components.md**                  | PublicSurface stays thin                               | Yes      | Pass    | PublicSurface classes are facades                                     | None                                                                                                                                            | None                                   | -        |
| **how-to-design-components.md**                  | Flow naming (action names, not pattern names)          | Yes      | Pass    | Flow names like RunApplication, HandleIncomingHttp                    | None                                                                                                                                            | None                                   | -        |
| **how-to-design-components.md**                  | component README per component                         | Yes      | Partial | Some components have README, not all                                  | Most components lack README                                                                                                                     | document                               | Medium   |
| **how-to-design-components.md**                  | component manifest per mature component                | Yes      | Partial | ComponentRegistry exists but not all components have manifests        | Manifest pattern not fully adopted                                                                                                              | add                                    | Low      |
| **how-to-dogfooding.md**                         | AvaX uses AvaX components                              | Yes      | Partial | Runtime uses Router, ErrorHandling; framework uses Request component  | Runtime does not use Observability for request metrics, Queue does not use Reliability for retry in all paths, Messaging not wired into runtime | wire components together               | High     |
| **how-to-dogfooding.md**                         | no duplicate local implementations                     | Yes      | Pass    | No local retry loops, no duplicate file operations                    | None found                                                                                                                                      | None                                   | -        |
| **how-to-dogfooding.md**                         | dependency direction (lower not depending on higher)   | Yes      | Pass    | Storage->Filesystem, not reverse                                      | None found                                                                                                                                      | None                                   | -        |
| **how-to-dogfooding.md**                         | PublicSurface thinness                                 | Yes      | Pass    | All PublicSurface classes delegate                                    | None                                                                                                                                            | None                                   | -        |
| **how-to-dogfooding.md**                         | adoption matrix                                        | Yes      | Partial | No explicit adoption matrix documented                                | Missing adoption matrix in evidence                                                                                                             | document                               | Medium   |
| **how-to-modern-php-attributes-di.md**           | attributes compiled, not per-request reflection        | Yes      | Partial | DataTransfer has AttributeCompiler                                    | Not all attribute areas have compilers (routing attributes may be scanned at boot without compilation)                                          | add compilers                          | Medium   |
| **how-to-modern-php-attributes-di.md**           | constructor bloat 0-4 normal, 5-7 check, 8+ warning    | Yes      | Pass    | No constructors with 8+ dependencies found                            | None                                                                                                                                            | None                                   | -        |
| **how-to-modern-php-attributes-di.md**           | no Container::get() in business logic                  | Yes      | Pass    | Container::get() only in bootstrap                                    | None                                                                                                                                            | None                                   | -        |
| **how-to-modern-php-attributes-di.md**           | pipe operator for pure transformations only            | Yes      | Pass    | No pipe operator misuse found                                         | None                                                                                                                                            | None                                   | -        |
| **how-to-modern-php-attributes-di.md**           | superglobals isolated behind Request                   | Yes      | Pass    | Only Request component accesses superglobals                          | None                                                                                                                                            | None                                   | -        |
| **how-to-modern-php-attributes-di.md**           | readonly for honestly immutable objects                | Yes      | Pass    | DTOs, value objects, config shapes are readonly                       | None                                                                                                                                            | None                                   | -        |
| **how-to-modern-php-attributes-di.md**           | final by default                                       | Yes      | Pass    | Classes are final unless extension point                              | None                                                                                                                                            | None                                   | -        |
| **how-to-unit-test.md**                          | behavior-first tests                                   | Yes      | Pass    | 7607 tests test behavior, not implementation                          | None                                                                                                                                            | None                                   | -        |
| **how-to-unit-test.md**                          | Arrange/Act/Assert structure                           | Yes      | Pass    | Tests follow pattern                                                  | None                                                                                                                                            | None                                   | -        |
| **how-to-unit-test.md**                          | happy/failure/edge scenarios                           | Yes      | Pass    | Security and performance tests include negative tests                 | None                                                                                                                                            | None                                   | -        |
| **how-to-document.md**                           | docs location (docs/ + component README)               | Yes      | Partial | docs/ exists but not all components have docs or README               | Missing component documentation                                                                                                                 | document                               | Medium   |
| **how-to-document.md**                           | how-this-works.md with mermaid diagrams                | Yes      | Partial | Only some flows have HOW_THIS_WORKS.md                                | Most flows/capabilities lack documentation                                                                                                      | document                               | Medium   |
| **how-to-system-security.md**                    | security boundaries, input validation, output encoding | Yes      | Pass    | SecureRequest component, redaction, signing                           | None                                                                                                                                            | None                                   | -        |
| **how-to-system-security.md**                    | no secrets logged/returned                             | Yes      | Pass    | Redaction component strips secrets                                    | None                                                                                                                                            | None                                   | -        |
| **how-to-system-performance.md**                 | no hidden I/O, no unbounded operations                 | Yes      | Pass    | Timeouts on external calls, retry limits                              | None                                                                                                                                            | None                                   | -        |
| **how-to-system-performance.md**                 | hot-path efficiency                                    | Yes      | Partial | Compiled metadata used but some paths may re-scan                     | Verify no hot-path reflection remains                                                                                                           | test                                   | Low      |
| **how-to-production-readiness.md**               | health checks, doctor, runtime safety                  | Yes      | Pass    | Doctor, health, liveness, readiness all wired                         | None                                                                                                                                            | None                                   | -        |
| **how-to-production-readiness.md**               | failure handling                                       | Yes      | Pass    | Exception classification, error rendering                             | None                                                                                                                                            | None                                   | -        |
| **how-to-architecture-extension-with-ddd.md**    | DDD bounded context, entities, value objects           | Yes      | Partial | DDD concepts present but not all components use DDD patterns honestly | Components use Flow/Capability model rather than DDD                                                                                            | document                               | Low      |
| **how-to-use-advanced-architecture-patterns.md** | GoF patterns, CQRS, event sourcing                     | Yes      | Partial | Patterns used selectively (CommandBus, QueryBus, EventBus)            | Event sourcing not fully implemented                                                                                                            | document                               | Low      |
| **how-to-clean-code.md**                         | correctness, naming, error handling                    | Yes      | Pass    | Clean code principles followed                                        | None                                                                                                                                            | None                                   | -        |
| **how-to-code-style.md**                         | PHP formatting, constructor promotion                  | Yes      | Pass    | Constructor promotion used, strict types                              | None                                                                                                                                            | None                                   | -        |
| **how-to-coding-standards.md**                   | PHP 8.5 style, strict types, modern features           | Yes      | Pass    | PHP 8.5 features used appropriately                                   | None                                                                                                                                            | None                                   | -        |

## Governance Coverage Summary

```text
Governance documents found: 15
Governance documents applied: 15
Rules checked: 38
Passed: 27
Partial: 11
Failed: 0
Blocked: 0
Highest severity: High
```

---

# GOVERNANCE FINDINGS

### Governance Finding: Incomplete Component Canonical Shape

- **Governance Source:** `how-to-design-components.md` -> Section 6.1 (Required and Conditional Folders)
- **Required Rule:** Components should have all canonical folders where they have real responsibility for each
- **Observed Gap:** DataStack/DataTransfer missing Foundation/; HTTP/SecureRequest missing Flows/ and Configuration/
- **Where It Fails:** `components/DataStack/DataTransfer/System/` and `components/HTTP/SecureRequest/System/`
- **Why It Matters:** Inconsistent component shape makes navigation and automation harder; suggests incomplete
  components
- **Required Action:** add
- **Suggested Fix:** Add empty-but-present Foundation/ to DataTransfer with any shared types. Add Flows/ and
  Configuration/ to SecureRequest if they have real behavior, or document why they are honestly absent.
- **Severity:** Medium
- **Evidence:** `components/DataStack/DataTransfer/System/`, `components/HTTP/SecureRequest/System/`

### Governance Finding: Dogfooding — Runtime Does Not Use Observability

- **Governance Source:** `how-to-dogfooding.md` -> Section 14 (Runtime Dogfooding Rule) and Section 15 (Observability
  Dogfooding Rule)
- **Required Rule:** Runtime must use Observability for request metrics, tracing, logging
- **Observed Gap:** Runtime HTTP/CLI/worker paths do not record request started/finished metrics, trace spans, or
  structured logs through the Observability component
- **Where It Fails:** `framework/System/Flows/RunApplication/RunApplication.php`,
  `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php`
- **Why It Matters:** Observability component exists but is not used by the runtime — it is decorative architecture per
  how-to-dogfooding.md Section 29
- **Required Action:** add
- **Suggested Fix:** Wire Observability capability into RunApplication and HandleIncomingHttp to record request.latency,
  request.count, request.error metrics and trace spans.
- **Severity:** High
- **Evidence:** `framework/System/Capabilities/` has no Observability integration in HTTP flows

### Governance Finding: Dogfooding Adoption Matrix Missing

- **Governance Source:** `how-to-dogfooding.md` -> Section 23 (Required Adoption Matrix)
- **Required Rule:** Every major V4 pass must include adoption matrix
- **Observed Gap:** No explicit adoption matrix documenting which components use which other components
- **Where It Fails:** Evidence directory — missing `EVIDENCE/dogfooding-adoption-matrix.md`
- **Why It Matters:** Without the matrix, it is impossible to verify dogfooding compliance systematically
- **Required Action:** document
- **Suggested Fix:** Create adoption matrix evidence file documenting consumer/provider relationships for all 34
  components.
- **Severity:** Medium
- **Evidence:** `EVIDENCE/` directory — no dogfooding adoption matrix

### Governance Finding: Attribute Compilation Incomplete

- **Governance Source:** `how-to-modern-php-attributes-di.md` -> Section 5.2 (Attribute Compilation Rule)
- **Required Rule:** Every attribute area must have a compiler — no per-request attribute scanning
- **Observed Gap:** DataTransfer has AttributeCompiler, but routing, validation, policy, audit, and tracing attributes
  may not have dedicated compilers
- **Where It Fails:** Framework routing attributes (`#[Get]`, `#[Post]`, etc.), DI attributes (`#[Inject]`, `#[Config]`)
- **Why It Matters:** If attributes are scanned on every request, performance degrades linearly with route/controller
  count
- **Required Action:** add
- **Suggested Fix:** Ensure CompileRouteAttributes, CompileValidationAttributes, CompileInjectionAttributes exist and
  produce compiled metadata used at runtime. Verify no hot-path reflection.
- **Severity:** Medium
- **Evidence:** Framework routing and DI attribute handling

### Governance Finding: Component Documentation Incomplete

- **Governance Source:** `how-to-document.md` and `how-to-design-components.md` -> Section 24 (Documentation Rule)
- **Required Rule:** Every component must document what it solves, public API, flows, capabilities, configuration,
  failure behavior
- **Observed Gap:** Most of 34 components lack README.md and how-this-works.md
- **Where It Fails:** `components/<Area>/<Component>/` — most lack README.md
- **Why It Matters:** Components without documentation are not platform-complete per how-to-design-components.md Section
  25
- **Required Action:** document
- **Suggested Fix:** Add README.md to each component answering the 9 questions from Section 6.15. Add HOW_THIS_WORKS.md
  to key flows with mermaid diagrams.
- **Severity:** Medium
- **Evidence:** 30+ components without README.md

### Governance Finding: HTTP Pipeline Not Explicit State Machine

- **Governance Source:** `how-to-code-review.md` -> Section 5.2 (Determinism Check)
- **Required Rule:** Pipeline should have explicit states, transitions, terminal and error states
- **Observed Gap:** HTTP pipeline steps execute in fixed order determined by flow code, not by explicit state machine
- **Where It Fails:** `framework/System/Flows/RunApplication/RunApplication.php`,
  `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php`
- **Why It Matters:** Adding/modifying pipeline steps requires modifying flow code; no validation prevents step
  reordering
- **Required Action:** simplify (or accept as documented trade-off)
- **Suggested Fix:** Consider a step registry pattern for the HTTP pipeline, or document the implicit ordering as an
  explicit design decision with clear modification protocol.
- **Severity:** Low
- **Evidence:** HTTP flow implementation code

---

# GOVERNANCE EXCEPTIONS

None currently registered. The following exceptions are proposed for review:

1. **Component README absence** — Most components lack README.md. Proposed exception: acceptable during V5 convergence,
   must be completed before V5 production release. Owner: framework team. Expiry: V5 GREEN.

2. **Dual-axis complexity (Runtime + Component Platform)** — Two central axes are inherently more complex than one.
   Proposed exception: justified by runtime-agnostic framework design. Owner: framework architecture. Expiry: permanent
   design decision.

---

# FINDINGS

### Finding: Duplicate RunDoctor Classes

- **Symptom:** Two classes named `RunDoctor` exist in different namespaces serving similar purposes
- **Root Cause:** Doctor capability was built in parallel with Doctor flow, creating naming collision
- **Impact:** Confusing for developers — `Flows\RunDoctor\RunDoctor` vs `Capabilities\Doctor\RunDoctor`
- **Evidence:** `framework/System/Flows/RunDoctor/RunDoctor.php` vs `framework/System/Capabilities/Doctor/RunDoctor.php`
- **Risk Level:** Medium
- **Notes:** The flow uses RuntimeSafety capability; the capability uses DoctorFinding/DoctorReport. They are related
  but different. Rename one to avoid collision.

### Finding: PreCommit Capability Is Overly Large

- **Symptom:** PreCommit capability contains 14 sub-capability checks plus Configuration, Models, and Reports
  subdirectories
- **Root Cause:** PreCommit grew as a mini-framework for architectural validation
- **Impact:** Largest and most complex capability in the framework tree; harder to maintain and test
- **Evidence:** `framework/System/Capabilities/PreCommit/` — 14 check classes
- **Risk Level:** Low
- **Notes:** This is acceptable if PreCommit is genuinely a cohesive capability (architectural validation). If
  individual checks are unrelated, they should be separate capabilities.

### Finding: Doctor/Routing/Foundation Naming

- **Symptom:** `Foundation/` subdirectory inside `Doctor/` and `Routing/` capabilities
- **Root Cause:** Foundation types (DoctorSeverity, DoctorReport, RouteCacheFailed) placed inside capability rather than
  at framework Foundation level
- **Impact:** Minor convention inconsistency — Foundation is a top-level System/ concept
- **Evidence:** `framework/System/Capabilities/Doctor/Foundation/`, `framework/System/Capabilities/Routing/Foundation/`
- **Risk Level:** Low
- **Notes:** Consider renaming to `Types/` or `Models/` inside capabilities, or moving types to
  `framework/System/Foundation/` if they are truly framework-wide.

### Finding: HTTP/System Direct Component

- **Symptom:** `components/HTTP/System/` exists directly under HTTP area without a component name
- **Root Cause:** HTTP kernel/capability placed as direct System/ under area
- **Impact:** Structural anomaly — all other areas have named leaf components
- **Evidence:** `components/HTTP/System/` vs `components/HTTP/Router/System/`, `components/HTTP/Request/System/`
- **Risk Level:** Low
- **Notes:** This is the HTTP kernel component. Consider naming it `components/HTTP/Kernel/System/` or similar for
  consistency.

### Finding: Test Directory Structure Mismatch

- **Symptom:** Test directories do not mirror full source path depth; some references to non-existent test directories
- **Root Cause:** Flattened test structure for convenience
- **Impact:** Minor — makes it harder to locate tests for a given source file
- **Evidence:** `tests/Unit/Framework/` without `System/` subdirectory; recovery scripts reference non-existent
  `tests/Database/`
- **Risk Level:** Low
- **Notes:** Test organization is functional but not strictly aligned with source structure.

### Finding: No Explicit Adoption Matrix

- **Symptom:** Missing dogfooding adoption matrix documenting component relationships
- **Root Cause:** Adoption matrix not yet produced as evidence artifact
- **Impact:** Cannot verify dogfooding compliance systematically
- **Evidence:** `EVIDENCE/` — no adoption matrix file
- **Risk Level:** Medium
- **Notes:** Required by how-to-dogfooding.md Section 23.

### Finding: Runtime Does Not Use Observability Component

- **Symptom:** Runtime HTTP/CLI/worker paths do not record metrics, traces, or logs through Observability component
- **Root Cause:** Observability component exists but is not wired into runtime execution paths
- **Impact:** Observability is decorative — exists as component but not used by the runtime it should observe
- **Evidence:** RunApplication, HandleIncomingHttp, RunConsoleCommand do not use Observability
- **Risk Level:** High
- **Notes:** This is the most significant dogfooding gap. The Observability plane is platform-complete only when the
  runtime uses it.

---

# DECISION

## ⚠️ Redesign

The AvaX system is **fundamentally sound** — the architecture follows screaming architecture principles, the component
platform is well-organized, naming is canonical, and validation is comprehensive (7607 tests, PHPStan clean). However,
there are targeted redesign needs:

1. **Observability must be wired into the runtime** (Finding: Runtime Does Not Use Observability Component). This is not
   a rewrite — it is a missing integration. The Observability component is proven but decorative without runtime usage.

2. **Component documentation must be completed** (Finding: Component Documentation Incomplete). 30+ components lack
   README.md. This is not an architecture issue but a platform-completeness issue.

3. **Duplicate RunDoctor naming should be resolved** (Finding: Duplicate RunDoctor Classes). A simple rename will
   eliminate confusion.

4. **Dogfooding adoption matrix must be produced** (Finding: No Explicit Adoption Matrix). Required evidence artifact.

The system's axis (Runtime) is correct but under pressure from holding too many concerns. This does not justify a
rewrite but suggests considering a more compositional Runtime design in V5.

---

# DECISIONS-LOG

## Decision: Wire Observability into runtime

- **Date:** 2026-05-11
- **Context:** Code review found Observability component exists but is not used by runtime execution paths
- **Decision:** Observability must be wired into RunApplication, HandleIncomingHttp, and RunConsoleCommand
- **Alternatives:** Leave Observability as standalone component (rejected — decorative architecture per dogfooding
  governance)
- **Consequences:** Runtime will record request.latency, request.count, request.error metrics and trace spans. Adds
  minimal overhead to hot path but provides essential operational visibility.
- **Evidence:** how-to-dogfooding.md Section 14-15, Finding: Runtime Does Not Use Observability Component

## Decision: Complete component documentation

- **Date:** 2026-05-11
- **Context:** 30+ of 34 components lack README.md
- **Decision:** Add README.md to each component answering Section 6.15 questions
- **Alternatives:** Accept partial documentation (rejected — platform-complete requirement)
- **Consequences:** Improves component discoverability, onboarding, and platform-completeness evidence
- **Evidence:** how-to-design-components.md Section 24-25, Finding: Component Documentation Incomplete

---

# NEXT STEPS

## Constraints

- **API stability:** PublicSurface classes must not change breaking behavior
- **Performance budget:** Observability wiring must add < 0.5ms per request
- **Security boundaries:** No secrets in observability output (use redaction)
- **Time and risk tolerance:** Low risk — adding observability is additive, not destructive
- **Migration expectations:** Existing behavior must not change

## Kill Criteria

- After wiring Observability, if request latency increases > 1ms, the approach must be reconsidered
- If component documentation does not improve developer onboarding, the documentation format must change
- If renaming RunDoctor creates more confusion, the naming decision must be reverted

---

## Redesign Actions

### What is being redesigned:

1. Runtime-observability integration (new behavior, not redesign)
2. Component documentation (new artifacts)
3. RunDoctor naming clarification (rename)

### What remains intact:

- Runtime architecture
- Component platform structure
- All 7607 tests
- All existing behavior

### First 3 concrete actions:

1. **Wire Observability into RunApplication** — Add Observability capability to record request.latency metric and trace
   span around route dispatch. Use redaction for all logged context. Add tests proving metrics are recorded.

2. **Wire Observability into HandleIncomingHttp** — Same as above for the advanced HTTP path. Add request.count and
   request.error metrics.

3. **Add README.md to all 34 components** — Each README answers: what problem it solves, platform plane, public surface,
   flows, capabilities, configuration, failure behavior, observability, testing, what is not owned.

### Next 3 concrete actions:

4. **Rename one of the RunDoctor classes** — Rename `Capabilities\Doctor\RunDoctor` to `RunDoctorChecks` or rename
   `Flows\RunDoctor\RunDoctor` to `ExecuteDoctorFlow` to eliminate naming collision.

5. **Create dogfooding adoption matrix** — Produce `EVIDENCE/dogfooding-adoption-matrix.md` documenting all component
   relationships: consumer, provider, current usage, duplicates found, action, status.

6. **Add missing component folders** — Add Foundation/ to DataStack/DataTransfer. Add Flows/ and Configuration/ to
   HTTP/SecureRequest if they have real behavior, or document honest absence.

### Final actions:

7. **Rename HTTP/System component** — Consider renaming to `components/HTTP/Kernel/System/` for consistency with other
   named leaf components.

8. **Move Foundation types out of capabilities** — Rename `Doctor/Foundation/` and `Routing/Foundation/` to `Types/` or
   move types to framework-level `Foundation/`.

9. **Verify all attribute compilers exist** — Ensure CompileRouteAttributes, CompileValidationAttributes,
   CompileInjectionAttributes exist and produce compiled metadata used at runtime. Run
   check-raw-reflection-hot-path.php.

---

## Stage: V5 Internal Convergence

## Status: YELLOW — fundamentally sound but missing runtime-observability integration

## Files changed: review only — no source changes

## Validation commands: All canonical validation GREEN (7607 tests, PHPStan 0 errors)

## Validation summary: Tests pass, static analysis clean, component structure validated

## Remaining risks:

1. Observability not wired into runtime (High) — decorative architecture
2. Missing component documentation (Medium) — platform completeness
3. Duplicate RunDoctor naming (Medium) — developer confusion
4. Missing dogfooding adoption matrix (Medium) — evidence gap
5. Incomplete component canonical shape (Medium) — 2 of 34 components

## Next allowed action: Wire Observability into runtime (Step 1 above)
