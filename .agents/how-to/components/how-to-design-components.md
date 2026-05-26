# How To Design Components

## Status

**MANDATORY** - This document defines non-negotiable component design rules for the project.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, **BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

---

## 1. Purpose

This document defines how components must be designed, completed, composed, exported, tested, and promoted into
platform-level capabilities.

The framework must not become a pile of components.

The framework must become a coherent platform made of clear planes:

```text
runtime
control plane
contract plane
integration plane
reliability plane
observability plane
delivery plane
system-design validation plane
```

A component is not finished because it has folders.

A component is finished only when it solves a real platform problem through a stable public boundary, strong internal
behavior, testable adapters, failure handling, diagnostics, documentation, and operational proof.

This document is a hard governance rule for component design.

---

## 2. Core Philosophy

Components are not decorative modules.

Components are reusable platform muscles.

A component must have a clear reason to exist:

```text
What problem does this component solve?
Who owns this behavior?
What public API does it expose?
What internal runtime behavior does it protect?
How is it configured?
How does it fail?
How is it observed?
How is it tested?
How is it diagnosed in production?
How can another component safely compose with it?
```

A component must be boring from the outside and strong from the inside.

Public API should be small, stable, predictable, and easy to document.

Internal machinery may be powerful, but it must stay behind the component boundary.

---

## 3. Platform Plane Model

The framework is not only a component collection.

The framework must be organized around platform planes.

### 3.1 Runtime Plane

Owns application boot, runtime lifecycle, request scope, worker lifecycle, runtime adapters, state reset, shutdown, and
runtime safety.

Examples:

```text
BootApplication
HandleIncomingHttp
RunConsoleCommand
RunWorkerJob
OpenRequestScope
CloseRequestScope
ResetApplicationState
VerifyRuntimeSafety
```

### 3.2 Control Plane

Owns runtime visibility, health, readiness, liveness, status, diagnostics, and operator-facing introspection.

Examples:

```text
ReadApplicationStatus
ReadRuntimeStatus
ReadHealth
ReadReadiness
ReadLiveness
ReadRouteStatus
ReadContainerStatus
ReadQueueStatus
```

### 3.3 Contract Plane

Owns public API compatibility, endpoint compatibility, DTO compatibility, event compatibility, command compatibility,
schema generation,
versioning, deprecation, and breaking-change detection.

Examples:

```text
DescribeHttpApi
DescribeCommandSchema
DescribeEventSchema
BuildOpenApiSchema
DetectBreakingPublicApiChange
ValidatePublicApiCompatibility
```

### 3.4 Integration Plane

Owns controlled communication with external infrastructure and external services.

Examples:

```text
ObjectStorage
SearchIndex
MessageBroker
StreamProcessor
WebhookGateway
PaymentGateway
NotificationGateway
TranscodingGateway
RecommendationGateway
CdnInvalidator
```

Integration components must never be only interfaces. They must include failure behavior, local/fake adapters,
configuration schema, health checks, diagnostics, tests, and observability.

### 3.5 Reliability Plane

Owns resilience primitives and safety patterns used across runtime, integration, queue, workflow, and distributed-system
behavior.

Examples:

```text
Retry
Backoff
Timeout
CircuitBreaker
Bulkhead
Fallback
DeadLetter
Idempotency
Lock
Lease
Outbox
Inbox
Saga
Backpressure
LoadShedding
```

### 3.6 Observability Plane

Owns logs, metrics, traces, audit events, runtime timelines, correlation IDs, trace IDs, spans, telemetry exporters, and
sensitive-data redaction.

Examples:

```text
RecordMetric
RecordLog
StartTrace
FinishTrace
RecordAuditEvent
ExportTelemetry
ReadRuntimeTimeline
```

### 3.7 Delivery Plane

Owns build, compile, warmup, release verification, smoke checks, rollback posture, benchmark evidence, and
production-readiness proof.

Examples:

```text
CompileApplication
CompileRoutes
CompileContainer
BuildManifest
VerifyRelease
RunSmokeChecks
WriteEvidenceReport
ReadRollbackPlan
```

### 3.8 System-Design Validation Plane

Owns capacity models, load models, consistency models, sharding models, failure models, architecture tests, simulations,
and reference architectures.

Examples:

```text
ValidateCapacityModel
SimulateCacheOutage
SimulateDuplicateMessage
ValidateConsistencyBoundary
RunArchitectureTest
WriteTradeoffReport
```

This plane must validate behavior, not merely store examples.

---

## 4. Component Completion Standard

A platform engine is finished only when it has all of the following:

```text
1. public API
2. internal runtime behavior
3. fake/local implementation
4. production implementation boundary
5. configuration schema
6. health/doctor check
7. failure model
8. retry/timeout/circuit/backoff policy when external I/O exists
9. observability events
10. compatibility tests (contract tests)
11. failure tests
12. runtime-safety rules
13. example usage
14. documentation
15. operator diagnostics
```

This is the difference between an interface and a platform muscle.

A component that exposes only an interface is not complete.

A component that has folders but no behavior is not complete.

A component that has behavior but no failure model is not production-grade.

A component that has behavior but no compatibility tests is not reusable.

A component that cannot be diagnosed by an operator is not platform-ready.

---

## 5. Hard Execution Law

V1 must be green before V2 or V3 implementation.

Do not implement GraphQL, integration ports, SystemDesign suite, background supervisor, transcoding gateway,
recommendation gateway, or system-design simulations while any of these are RED:

```text
taxonomy
autoload
namespace integrity
tests
PHPStan/Psalm
runtime safety
component completion
public surface integrity
```

Planning may continue.

Architecture notes may continue.

Design documents may continue.

Implementation waits for Kernel Green.

Kernel Green means:

```text
component taxonomy is canonical
autoload is clean
namespaces match ownership
tests load and target canonical classes
static analysis is green or honestly baselined
runtime safety is proven
public surface does not leak internals
component completion rules are enforceable
```

No agent may bypass this law by calling new feature work "preparation", "scaffolding", "harmless foundation", or "
future-proofing".

If it creates production code for V2/V3 behavior, it waits for Kernel Green.

---

## 6. Canonical Component Filesystem Law

Every production A component must follow one canonical filesystem shape.

The component root must make ownership obvious.

The `System/` folder is the component system root.

Inside `System/`, only the following top-level folders are allowed by default:

```text
System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

This is the canonical component shape.

No other top-level `System/` folders are allowed unless a component-specific governance document explicitly justifies
them.

The default shape is:

```text
components/
  <Area>/
    <Component>/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

Example:

```text
components/
  Application/
    Cache/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

Example:

```text
components/
  DataStack/
    Database/
      System/
        PublicSurface/
        Flows/
        Capabilities/
        Configuration/
        Foundation/
```

The canonical reading order is:

```text
area -> component -> system -> public surface / flow / capability -> unit -> function
```

The structure must let a reader answer:

```text
What area is this?
What component owns this?
What public API exists?
What flows happen?
What capabilities power those flows?
How is this assembled?
Which small primitives support it?
```

---

## 6.1 Required and Conditional Folders

Not every canonical folder is mandatory in every component.

A folder exists only when it has real responsibility.

| Folder           |                       Status | Use When                                                                       |
|------------------|-----------------------------:|--------------------------------------------------------------------------------|
| `System/`        |                     Required | Every production component must have one system root.                          |
| `Capabilities/`  | Required for real components | The component owns reusable behavior, mechanisms, or platform muscle.          |
| `PublicSurface/` |                  Conditional | The component exposes stable user-facing or cross-component API.               |
| `Flows/`         |                  Conditional | The component owns end-to-end behavior.                                        |
| `Configuration/` |                  Conditional | The component can be assembled, registered, configured, booted, or integrated. |
| `Foundation/`    |                     Optional | The component needs tiny neutral local primitives.                             |

A component with no `Capabilities/` is usually not a real component.

A component with only folders and no behavior is not a component.

A component with only interfaces is not a platform muscle.

A component with only `PublicSurface/` and no internal behavior is a façade without an engine.

---

## 6.2 PublicSurface Rule

`PublicSurface/` contains stable public API entrypoints.

It is required only when the component exposes something external users or other components are allowed to call
directly.

Allowed:

```text
PublicSurface/
  Cache.php
  CacheKey.php
  CacheTtl.php
```

Allowed:

```text
PublicSurface/
  Database.php
  Query.php
  Schema.php
  Migrations.php
```

Forbidden:

```text
PublicSurface/
  BuildContainer.php
  ResolveDependencies.php
  SwooleRequestAdapter.php
  InternalRegistry.php
  RuntimeStateStore.php
  WorkerLoop.php
```

`PublicSurface/` receives and delegates.

It MUST NOT execute real behavior.

It MUST NOT instantiate services or objects other than simple value objects/DTOs/result wrappers.

It MUST delegate all real logic to injectable `Flows/` or `Capabilities/`.

**Status:** MANDATORY  
**Severity:** BLOCKER

A public surface class must not contain:

```text
business logic
runtime machinery
adapter-specific implementation
request-scoped mutable state
internal registries
service registration internals
private runtime state
```

If `PublicSurface/` grows large, the component is leaking internals.

---

## 6.3 Flows Rule

`Flows/` contains end-to-end behavior owned by the component.

A flow answers:

```text
What happens from start to finish?
```

Use `Flows/` when the component owns a complete behavior sequence.

Good:

```text
Flows/
  HandleIncomingHttp/
    HandleIncomingHttp.php

  RunMigration/
    RunMigration.php

  VerifyReleaseReadiness/
    VerifyReleaseReadiness.php
```

Bad:

```text
Flows/
  Handlers/
  Processors/
  Services/
  Commands/
```

A flow folder must be named as an action.

Good flow names:

```text
RegisterUser
HandleIncomingHttp
RunMigration
PublishPendingEvent
VerifyRuntimeSafety
DetectBreakingPublicApiChange
```

Weak flow names:

```text
UserFlow
RequestFlow
MigrationHandler
EventProcessor
CommandHandler
```

A flow may contain local value objects, events, decisions, and small helpers only if they belong exclusively to that
flow.

Do not extract flow-local concepts into shared capabilities too early.

Shared last.

---

## 6.4 Capabilities Rule

`Capabilities/` contains reusable behavior owned by the component.

A capability answers:

```text
What ability does this component provide?
```

Capabilities are the muscles of a component.

Good:

```text
Capabilities/
  QueryCompilation/
  SchemaDesign/
  ConnectionOpening/
  CacheReading/
  CacheWriting/
  RuntimeSafety/
  PublicApiCompatibility/
  ReliableEventPublishing/
```

Bad:

```text
Capabilities/
  Services/
  Managers/
  Helpers/
  Utils/
  Adapters/
  Contracts/
  Handlers/
  Processors/
```

A capability folder must say the real ability or boundary.

Good:

```text
Capabilities/
  S3ObjectStorage/
    StoreObjectInS3.php
    ReadObjectFromS3.php

  RedisCacheStore/
    ReadRedisCache.php
    WriteRedisCache.php

  StripePaymentGateway/
    ChargeCardThroughStripe.php
    RefundStripePayment.php
```

Bad:

```text
Capabilities/
  Adapters/
    S3Adapter.php
    RedisAdapter.php
    StripeAdapter.php
```

The folder must explain what the system does, not which pattern is being used.

---

## 6.5 Configuration Rule

`Configuration/` contains assembly, registration, bootstrapping, configuration schema, and dependency wiring.

It answers:

```text
How is this component built and connected?
```

Allowed:

```text
Configuration/
  BuildCache.php
  RegisterCacheDependencies.php
  BuildDatabase.php
  RegisterDatabaseDependencies.php
```

Forbidden:

```text
Configuration/
  ReadFromCache.php
  RunMigration.php
  ProcessPayment.php
  DetectBreakingPublicApiChange.php
```

Configuration assembles.

It does not own domain or runtime behavior.

If a file performs actual behavior, it belongs in `Flows/` or `Capabilities/`.

---

## 6.5.1 Configuration/Builders Rule

A component MAY have a `System/Configuration/Builders/` folder.

This folder is allowed only for classes that assemble configuration-time object graphs, runtime defaults, dependency
graphs, or component runtime packages.

`Builders/` is not a generic folder for any class that "builds something".

### Purpose

`System/Configuration/Builders/` exists to keep dependency assembly out of runtime execution code.

Builder classes in this folder answer questions like:

- How is this component assembled?
- Which default dependencies are registered?
- Which runtime object graph is created?
- How are user options converted into component runtime configuration?
- How are ServiceProvider bindings grouped without bloating the ServiceProvider?

### Allowed in Configuration/Builders

Allowed examples:

```text
BuildApplicationRuntime
BuildAuthRuntime
BuildHttpKernel
BuildCacheRuntime
BuildDatabaseRuntime
BuildRouterRuntime
AssembleAuthDependencies
AssembleResponseComponent
RegisterAuthDefaults
RegisterCacheDefaults
CreateComponentConfiguration
```

These classes may instantiate infrastructure dependencies because they are part of the composition layer.

They must remain deterministic, explicit, and testable.

### Forbidden in Configuration/Builders

Do NOT place runtime behavior here.

Forbidden examples:

```text
BuildSqlQuery
BuildHttpResponse
BuildGraphQLSchema
BuildOpenApiDocument
BuildUrl
BuildMiddlewarePipeline
BuildCacheKey
BuildEmailMessage
```

These belong in `System/Capabilities/`, because they create runtime results or execute component behavior.

### Boundary Rule

Use this distinction:

```text
Configuration/Builders/
= builds the component/runtime/dependency graph

Capabilities/
= performs runtime behavior or creates runtime results
```

### Naming Rule

Builder class names must say exactly what they assemble.

Avoid vague names:

```text
Builder
AuthBuilder
ComponentBuilder
RuntimeBuilder
ServiceBuilder
```

Prefer exact names:

```text
BuildAuthRuntime
RegisterAuthDefaults
AssembleHttpKernel
BuildDatabaseRuntime
ConfigureCacheStores
```

### Public API Rule

`Configuration/Builders/` is internal assembly machinery.

It must not be used as the public developer API unless explicitly designed as a configuration DSL.

If a class is a user-facing fluent configuration DSL, name it clearly:

```text
ConfigureAuth
ConfigureCache
ConfigureRouter
```

If a class is internal assembly, name it as an action:

```text
BuildAuthRuntime
RegisterAuthDefaults
AssembleRouterRuntime
```

### Large Builder Warning

Any builder over 300 lines must be reviewed for responsibility split.

If a builder does all of these:

- collects user options
- creates default dependencies
- wires runtime services
- creates runtime objects
- owns fallback behavior
- contains many `?? new` fallbacks

then it must be split.

Recommended split:

```text
Configuration/
  ConfigureAuth.php              // user-facing fluent DSL
  AuthServiceProvider.php        // provider entrypoint
  Builders/
    RegisterAuthDefaults.php     // default bindings
    BuildAuthRuntime.php         // runtime graph
    AssembleAuthCapabilities.php // capability graph
```

A large builder must not become a hidden container.

### 6.5.2 Container Ownership Rule — Cross-Reference

For the complete Container Ownership Rule, including:

- When DI/container is mandatory vs when direct `new` is allowed
- Builder placement rule (Configuration/Builders vs Capabilities)
- Runtime composition leak rule
- Factory class precision (result vs graph assembly)
- Path/context-based enforcement
- Clock default binding
- Examples canonical style

See:

```text
.agents/how-to/implementation/how-to-dependency-injection.md — Section 6: Container Ownership Rule
.agents/how-to/architecture/how-to-runtime-composition.md — Full runtime composition law
```

Key principle for component design:

```text
Configuration/Builders/ assembles the system.
Capabilities/ creates runtime results.
Runtime execution code must not assemble dependencies.
```

**Builder Governance Cross-Reference:**

For complete builder governance including:

- Builder validity rule (allowed/forbidden patterns)
- Builder decision questions
- Builder naming rule (class names and method names)
- Fluent DSL principles
- Assembly graph vs runtime DSL distinctions

See:

```text
.agents/how-to/architecture/how-to-architecture.md — Section 13.3 Builders Rule (canonical builder validity)
.agents/how-to/implementation/how-to-dependency-injection.md — Section 8 Fluent DSL Design Principles
.agents/how-to/implementation/how-to-dependency-injection.md — Section 6.8 Builder Placement Rule
```

---

## 6.6 Foundation Rule

`Foundation/` contains tiny neutral primitives used locally by the component.

It answers:

```text
Which small primitives support this component without owning behavior?
```

Allowed:

```text
Foundation/
  CacheKey.php
  CacheTtl.php
  DatabaseException.php
  QueryBinding.php
  FailureReason.php
```

Forbidden:

```text
Foundation/
  User.php
  Order.php
  ReleaseCandidate.php
  CompatibilityRules.php
  RuntimeWorker.php
```

If a concept has domain meaning, lifecycle, invariants, or behavior, it does not belong in `Foundation/`.

It belongs in the flow or capability that owns it.

Foundation must not become a hidden `Domain/` folder.

Foundation must not become `Common/`.

---

## 6.7 Forbidden Top-Level System Folders

The following folders are forbidden as default top-level folders inside `System/`:

```text
InternalSystem/
ExportedCapabilities/
Adapters/
Contracts/
Services/
Managers/
Helpers/
Utils/
Support/
Common/
Shared/
Domain/
Entities/
ValueObjects/
Aggregates/
Repositories/
Events/
Handlers/
Processors/
Commands/
Queries/
CQRS/
EventSourcing/
Sagas/
Policies/
Specifications/
Diagnostics/
Tests/
Docs/
```

Reason:

```text
They group code by technical category instead of ownership.
They hide flow.
They hide capability.
They encourage dumping grounds.
They weaken screaming architecture.
```

If one of these words is truly the domain language of a specific component, it must be justified in that component's
governance document.

No default use.

No automatic scaffolding.

No "just in case" folders.

---

## 6.7.1 Concept Words Are Not Folder Names

Some words in this document describe architecture responsibilities, not filesystem names.

When this document says contract or adapter, it means a design responsibility, not a default folder name.

Do not create Contracts/ or Adapters/ folders unless a component-specific governance document explicitly allows it.

Examples:

```text
contract
adapter
diagnostic
test
documentation
manifest
```

These words may describe what a component must provide.

They must not automatically become folders.

Correct:

```text
Capabilities/
  PublicApiCompatibility/
  S3ObjectStorage/
  CheckCacheHealth/
  DiagnoseCacheConfiguration/
```

Wrong:

```text
Contracts/
Adapters/
Diagnostics/
Tests/
Docs/
Manifests/
```

The filesystem must still say flow or capability.

---

## 6.8 Use Case Translation Rule

The framework does not use `UseCases/` as a default folder.

In the framework's vocabulary, a use case is represented as a `Flow`.

A flow owns one complete user, system, runtime, or platform action.

Correct:

```text
System/
  Flows/
    RegisterUser/
      RegisterUser.php

    ChangePassword/
      ChangePassword.php

    RunMigration/
      RunMigration.php
```

Incorrect:

```text
Application/
  UseCases/
    RegisterUserUseCase.php

System/
  UseCases/
    ChangePasswordUseCase.php
```

The folder must say what happens, not which architectural pattern is being used.

Use Flow when one action completes the story.

Use Capability when behavior is reusable across multiple flows.

### 6.8.1 Use Case Goal Level Mapping

We map Alistair Cockburn's use case levels (*Writing Effective Use Cases*) directly to The architecture layers:
1. **Summary Level (Cloud/Kite):** High-level business process (e.g. `ManageCustomerAccounts`). Mapped to framework subsystems or components (e.g. `components/AccountManagement/`), never to a single Flow.
2. **User-Goal Level (Sea Level):** A primary goal of a primary actor (e.g. `RegisterUser`, `CheckoutBasket`). Mapped directly to a **Flow Slice** (e.g. `Flows/RegisterUser/RegisterUser.php`).
3. **Subfunction Level (Fish/Underwater):** A low-level step or helper action (e.g. `VerifyEmailToken`, `HashPassword`). Mapped to a **Capability** (e.g. `Capabilities/VerifyEmailToken/`) or a private helper method inside the Flow, NEVER to its own Flow slice.

### 6.8.2 Constraints on Flow Size and Complexity

- **Size Limit:** A Flow orchestrator file **MUST NOT** exceed 150 lines of code. If it exceeds 150 lines, it must delegate subfunction steps to reusable Capabilities.
- **Nesting Limit:** The nested execution depth of steps inside a Flow **MUST NOT** exceed 3 levels.

### 6.8.3 Classifications

- **BLOCKER:** Subfunction-level goal mislabeled or implemented as a primary user-goal Flow slice (e.g., creating `Flows/VerifyEmailToken` instead of delegating to a Capability).
- **RED:** A Flow file exceeding 150 lines of procedural orchestration code or having nested step depth greater than 3.
- **YELLOW:** Flows containing inline business logic that should be refactored to a Capability but is temporarily documented.

---


## 6.9 Flow vs Capability Rule

Use a `Flow` when:

```text
one use case completes the story
the behavior has a beginning, middle, and end
the sequence matters
the logic is local to one action
extracting it would hide the domain story
```

Use a `Capability` when:

```text
multiple flows use it
the behavior is reusable
the rule is broader than one use case
extraction improves clarity more than locality
```

Default to `Flow` first.

Extract to `Capability` only after reuse is honest.

---

## 6.10 Exported Capability Rule

`ExportedCapabilities` is a concept, not a required folder.

Do not create an `ExportedCapabilities/` folder by default.

A capability is exported only when it is intentionally made stable for other components or users.

A unit may be considered exported through one of these lanes:

```text
PublicSurface/
documented public value object
documented public event
documented public API
documented fake or test kit
component manifest
container registration
configuration profile
```

An exported unit must be:

```text
documented
tested
stable
compatibility-sensitive
safe to depend on
```

Default state is internal.

Export is a decision.

---

## 6.11 Internal System Rule

`InternalSystem` is a concept, not a folder.

Do not create an `InternalSystem/` folder.

The internal system is represented by:

```text
Flows/
Capabilities/
Configuration/
Foundation/
```

Other components must not import internal files directly unless they are explicitly exported through an approved lane.

Correct:

```text
Component A depends on Component B PublicSurface.
Component A depends on Component B documented public API.
Component A receives Component B event.
Framework configuration composes both.
```

Wrong:

```text
Component A imports Component B System/Capabilities/InternalThing.php.
Component A imports Component B System/Flows/DoSomething.php.
Component A reads Component B private registry.
```

Internals stay private.

---

## 6.12 Diagnostics Rule

Diagnostics are capabilities, not a mandatory root folder.

Do not create `Diagnostics/` by default.

Good:

```text
Capabilities/
  CheckCacheHealth/
    CheckCacheHealth.php

  DiagnoseCacheConfiguration/
    DiagnoseCacheConfiguration.php

  ReadDatabaseStatus/
    ReadDatabaseStatus.php
```

Bad:

```text
Diagnostics/
  CacheDiagnostics.php
  DatabaseDiagnostics.php
```

Diagnostics must say what they check, diagnose, or report.

Operator diagnostics are required for platform-complete components, but the filesystem must still follow capability
naming.

---

## 6.13 Tests Rule

Tests normally live in the project test tree.

Default:

```text
tests/
  Unit/
  Integration/
  Architecture/
```

Do not create `System/Tests/` by default.

A component may contain reusable contract-test fixtures only when the component intentionally exports a test kit.

Allowed only with justification:

```text
System/
  PublicSurface/
  Capabilities/
  Foundation/
    ContractTesting/
```

Better in most cases:

```text
tests/
  Unit/
    Components/
      Application/
        Cache/
```

Production code and tests must not be mixed without a clear reason.

---

## 6.14 Docs Rule

Documentation is required.

A `Docs/` folder inside `System/` is forbidden as a generic technical bucket.

Use the documentation layer that matches the ownership boundary:

```text
docs/                                  global or cross-component documentation
components/<Area>/<Component>/docs/    component-local self-explaining architecture
components/<Area>/<Component>/README.md short component ownership summary
EVIDENCE/ or .agents/management/evidence/ proof and reports
```

Component-local `docs/` is allowed when it explains local ownership, ADRs, dictionaries, diagrams, mistakes, or flow behavior. It must not redefine global governance.

Allowed:

```text
components/
  Application/
    Cache/
      README.md
      System/
        PublicSurface/
        Capabilities/
```

Allowed:

```text
components/
  Application/
    Cache/
      docs/
        README.md
        adr/
        dictionary/
        diagrams/
        mistakes.md
```

Allowed:

```text
docs/
  components/
    cache.md
```

Allowed:

```text
EVIDENCE/
  recovery-reports/
```

Do not create:

```text
System/
  Docs/
```

Use component-local `docs/` outside `System/` instead.

---

## 6.15 Component README Rule

Every serious component should have a short component README.

Recommended:

```text
components/<Area>/<Component>/README.md
```

The README must answer:

```text
What problem does this component solve?
Which platform plane does it belong to?
What is the public surface?
What flows does it own?
What capabilities does it provide?
How is it configured?
How does it fail?
How is it observed?
How is it tested?
What is not owned here?
```

A component README explains ownership.

It must not duplicate every implementation detail.

---

## 6.16 Component Manifest Rule

Every reusable platform component should have a component manifest when it is mature enough to be composed by the
framework.

The manifest is not a random metadata file.

It makes platform composition visible.

It should declare:

```text
component name
component plane
public surface
exported units
required imports
optional imports
events emitted
events consumed
configuration keys
runtime-safety requirements
resettable state
health checks
doctor checks
operator diagnostics
provided fakes
implementation compatibility tests (contract tests)
failure tests
```

The manifest may be code or documentation.

Allowed:

```text
Configuration/
  CacheComponent.php
```

Allowed:

```text
component.md
```

Do not create `Manifests/` as a dumping ground.

---

## 6.17 Component Folder Decision Table

| Folder           |               Required? | Rule                                                                |
|------------------|------------------------:|---------------------------------------------------------------------|
| `System/`        |                     yes | Every production component has one system root.                     |
| `PublicSurface/` |             conditional | Use only for stable public API.                                     |
| `Flows/`         |             conditional | Use for end-to-end behavior owned by the component.                 |
| `Capabilities/`  | yes for real components | Use for reusable component muscle.                                  |
| `Configuration/` |             conditional | Use for assembly, registration, config schema, and integration.     |
| `Foundation/`    |                optional | Use only for tiny neutral local primitives.                         |
| `Diagnostics/`   |                      no | Prefer exact diagnostic capabilities.                               |
| `Adapters/`      |                      no | Use concrete boundary names.                                        |
| `Contracts/`     |                      no | Prefer the real promise, e.g. Compatibility, PublicApi, CacheStore. |
| `UseCases/`      |                      no | Use Flows instead.                                                  |
| `Tests/`         |                      no | Prefer central test tree unless exporting a test kit.               |
| `Docs/`          |                      no | Forbidden as a `System/` bucket. Use component-local `docs/`.        |
| `Domain/`        |                      no | DDD concepts live inside owning flows or capabilities.              |
| `Services/`      |                      no | Use exact action names.                                             |
| `Commands/`      |                      no | Use flow names.                                                     |
| `Queries/`       |                      no | Use read capability names.                                          |

---

## 7. Component Collaboration Rule

Components may collaborate only through approved lanes:

```text
public API contracts
exported capabilities
public surface entrypoints
event contracts
message contracts
framework/application configuration
neutral foundation primitives
contract tests
fakes
```

Components must not know each other's internals.

Correct mental model:

```text
Components export stable capabilities.
Framework assembles components.
Flows orchestrate components.
Events and messages decouple components.
Internals stay private.
```

Wrong mental model:

```text
Components call each other's internal folders.
Components bootstrap each other.
Components use static facades to bypass declared dependencies.
Components share mutable request state.
Components create circular dependencies.
```

Composition is allowed.

Entanglement is forbidden.

---

## 8. Export Rule

A component may export an event, value object, fake, implementation boundary, or reusable capability
only when all of the following are true:

```text
[ ] it has a stable reason to exist
[ ] it is useful outside the component
[ ] the name is obvious outside the component
[ ] it does not leak internal structure
[ ] it does not expose implementation-specific details unless that is its explicit job
[ ] dependency direction remains acyclic
[ ] it can be documented simply
[ ] it has compatibility tests or implementation compatibility tests
[ ] it has a fake, local implementation, or test helper when useful
[ ] changing it would be treated as a compatibility decision
```

If these conditions are not true, the unit must remain internal.

---

## 9. Import Rule

A component may import another component's exported capability only when:

```text
[ ] the dependency is declared explicitly
[ ] the dependency points to a public API or exported capability
[ ] the consumer does not know the provider's internal folder structure
[ ] the dependency can be replaced by a fake in tests
[ ] the dependency is assembled through configuration or container registration
[ ] the dependency does not create a circular dependency
[ ] runtime-safety rules are preserved
```

Imports must be visible in one of these places:

```text
component manifest
container registration
configuration owner
runtime assembly
application profile
```

Hidden imports are forbidden.

---

## 10. Forbidden Collaboration

The following is forbidden:

```text
component A imports component B internal Flow
component A imports component B internal Capability without export
component A and component B depend on each other directly
static facade calls inside internals bypass dependency declaration
one component stores request-scoped state inside another component
a component reads another component's private registry
a component uses another component to avoid owning its own behavior
generic reusable code is placed into Shared, Utils, Helpers, Common, or Misc
implementation-specific code leaks into another component's public API
runtime-specific APIs leak into generic component public API
```

If two components appear to need each other, extract one of these:

```text
neutral contract
event contract
message contract
higher-level orchestration flow
foundation primitive
platform capability owned above both components
```

---

## 11. Correct Collaboration Patterns

### 11.1 Contract Dependency

Use when one component needs stable behavior from another component.

Example:

```text
Queue depends on SerializerContract.
Queue does not depend on JsonSerializer internals.
```

### 11.2 Event Collaboration

Use when the producer should not know who reacts.

Example:

```text
Database emits QueryExecuted.
Observability records query timeline.
Performance detects slow query.
```

### 11.3 Message Collaboration

Use when work crosses async boundaries.

Example:

```text
Billing publishes InvoiceIssued.
Notifications consumes InvoiceIssued.
Analytics consumes InvoiceIssued.
```

### 11.4 Framework Assembly

Use when multiple components must be wired together.

Example:

```text
BuildApplication registers Router, Container, Cache, Events, Queue, Observability.
Components do not manually bootstrap each other.
```

### 11.5 Higher-Level Flow Orchestration

Use when several components are needed to complete one business or system flow.

Example:

```text
HandleIncomingHttp uses Router, Container, Middleware, Validation, Response, Observability.
Router does not own the full HTTP lifecycle.
```

### 11.6 External Boundary Implementation

Use when the framework integrates with external infrastructure.

Example:

```text
ObjectStorage exposes a stable public storage promise.
LocalObjectStorage provides local storage behavior.
S3ObjectStorage provides S3-backed storage behavior.
CheckObjectStorageHealth verifies reachability.
MapObjectStorageFailure maps external failures into the project failures.
```

### 11.7 Reliability Wrapper

Use when external I/O or unstable dependency exists.

Example:

```text
WebhookDelivery uses RetryPolicy, TimeoutPolicy, CircuitBreaker, BackoffPolicy, and DeadLetterStore.
```

External I/O without reliability policy is incomplete.

---

## 12. Component Manifest Rule

Every reusable component must declare a component manifest.

The manifest should describe:

```text
component name
component plane
public surface
exported contracts
exported capabilities
required imports
optional imports
events emitted
events consumed
messages published
messages consumed
configuration keys
runtime-safety requirements
resettable state
health checks
doctor checks
operator diagnostics
implementation compatibility tests (contract tests)
failure tests
provided fakes
local implementations
production implementation boundaries
```

Example:

```php
ComponentManifest::for('Cache')
    ->plane('reliability')
    ->exports(CacheContract::class)
    ->exports(CacheStoreContract::class)
    ->requires(Clock::class)
    ->optionallyUses(Metrics::class)
    ->emits(CacheHit::class)
    ->emits(CacheMiss::class)
    ->providesFake(FakeCache::class)
    ->providesHealthCheck(CacheHealthCheck::class)
    ->providesDoctorCheck(CacheDoctorCheck::class);
```

A component without a manifest is not fully platform-visible.

---

## 13. Platform Engine Checklist

Before a component is marked complete, it must answer every section below.

### 13.1 Public Contract

```text
[ ] What is the public API?
[ ] Who may call it?
[ ] What is stable?
[ ] What is internal?
[ ] What exceptions or failures may cross the boundary?
[ ] What is a breaking change?
```

### 13.2 Internal Runtime Behavior

```text
[ ] What flow owns the behavior?
[ ] What capabilities power the behavior?
[ ] What state exists?
[ ] What state must be reset?
[ ] What must remain deterministic for tests?
```

### 13.3 External Boundary Implementations

```text
[ ] Is there a fake implementation?
[ ] Is there a local implementation where useful?
[ ] Is there a production implementation boundary?
[ ] Are production implementations isolated from public API?
[ ] Are production failures mapped into the project failures?
```

### 13.4 Configuration

```text
[ ] Is there a configuration schema?
[ ] Are defaults explicit?
[ ] Are invalid configs rejected early?
[ ] Can config be explained?
[ ] Are secrets redacted?
```

### 13.5 Health and Doctor

```text
[ ] Is there a health check?
[ ] Is there a doctor check?
[ ] Does the check explain impact?
[ ] Does the check suggest a fix?
[ ] Can the check run in CI and local mode?
```

### 13.6 Failure Model

```text
[ ] What can fail?
[ ] How is each failure represented?
[ ] Which failures are retryable?
[ ] Which failures are terminal?
[ ] Which failures are operator-actionable?
[ ] Which failures must be audited?
```

### 13.7 Reliability Policy

Required when external I/O exists:

```text
[ ] retry policy
[ ] timeout policy
[ ] backoff policy
[ ] circuit breaker policy when useful
[ ] fallback policy when useful
[ ] dead-letter behavior when async
[ ] idempotency behavior when duplicate execution is possible
```

### 13.8 Observability

```text
[ ] What events are emitted?
[ ] What metrics are recorded?
[ ] What trace spans exist?
[ ] What log context is attached?
[ ] What audit event exists if security or money is involved?
[ ] Are sensitive values redacted?
```

### 13.9 Tests

```text
[ ] contract tests
[ ] happy-path tests
[ ] failure-path tests
[ ] implementation compatibility tests
[ ] runtime-safety tests when state exists
[ ] configuration validation tests
[ ] doctor/health tests
```

### 13.10 Documentation

```text
[ ] public usage documented
[ ] configuration documented
[ ] failure behavior documented
[ ] testing strategy documented
[ ] diagnostics documented
[ ] examples documented
```

---

## 14. Runtime-Safety Rule

Every component must declare whether it is safe for long-lived runtimes.

Long-lived runtimes include:

```text
FrankenPHP worker mode
RoadRunner
Swoole
Workerman
ReactPHP/Amp/Fiber-based runtimes
```

A component must document:

```text
[ ] request-scoped state
[ ] static mutable state
[ ] resettable state
[ ] singleton dependencies
[ ] scoped dependencies
[ ] external connection lifecycle
[ ] worker shutdown behavior
[ ] memory growth risk
```

If a component holds mutable state, it must either:

```text
implement reset behavior
declare itself request-scoped
prove immutability
or be forbidden in long-lived runtime mode
```

Runtime safety is not optional.

---

## 15. External I/O Rule

Any component that talks to external systems must be treated as failure-prone.

External systems include:

```text
database
cache server
message broker
object storage
search engine
payment provider
email provider
HTTP API
CDN
filesystem outside controlled local runtime
transcoding service
recommendation service
```

Such a component must include:

```text
failure types
timeout policy
retry/backoff policy
circuit breaker when useful
health check
doctor check
observability
local/fake implementation
compatibility tests (contract tests)
failure tests
operator diagnostics
```

An external I/O component without these is incomplete.

---

## 16. Operator Diagnostics Rule

Every platform engine must provide operator diagnostics.

Diagnostics must answer:

```text
Is it configured?
Is it reachable?
Is it healthy?
What failed?
What is the impact?
What should be done next?
Can the failure be retried?
Is this a code issue, configuration issue, infrastructure issue, or runtime issue?
```

Good diagnostic output:

```text
Problem:
  Redis cache store is configured, but Redis extension is missing.

Impact:
  Cache::store('redis') will fail during runtime.

Fix:
  Install ext-redis or change cache.default_store to file.

Severity:
  Blocker in production, warning in local development.
```

Bad diagnostic output:

```text
Redis error.
```

---

## 17. Contract Test Rule

Every exported capability must provide at least one of:

```text
implementation compatibility test (contract test)
fake implementation
test builder
test assertion helper
example usage
```

A reusable component without test support is not truly reusable.

Production implementations must pass the same compatibility tests as fake/local implementations where possible.

If an implementation cannot pass the canonical compatibility tests, the deviation must be documented.

---

## 18. Failure Test Rule

Every component must test failure behavior.

At minimum, failure tests must cover:

```text
invalid configuration
missing dependency
external dependency unavailable, if external I/O exists
timeout, if external I/O exists
retry exhaustion, if retry exists
circuit open, if circuit breaker exists
duplicate message/request, if idempotency matters
state leak, if runtime state exists
permission denial, if security-sensitive
```

Untested failure behavior is not production behavior.

It is wishful thinking.

---

## 19. Reuse Promotion Rule

A unit may be promoted from internal to reusable only after it proves reuse.

Promotion checklist:

```text
[ ] at least two real consumers need it
[ ] ownership is still clear
[ ] public name is stable
[ ] public API is small
[ ] no internal implementation leaks
[ ] tests exist
[ ] docs explain usage
[ ] fake or test helper exists
[ ] backward compatibility impact is understood
```

Do not promote speculative abstractions.

Prefer local duplication over false reuse.

Prefer honest internal ownership over vague global sharing.

---

## 20. Dependency Direction Rule

Allowed direction:

```text
Foundation primitives -> used by everyone
Component public API -> used by consumers
Component internals -> used only by owner
Framework/Application -> composes components
Operations flows -> orchestrate multiple components
```

Forbidden direction:

```text
Component internals -> another component internals
Low-level primitive -> application/runtime behavior
Reusable component -> framework-specific runtime details
Domain component -> infrastructure implementation detail
```

If direction is unclear, the design is not finished.

---

## 21. Circular Dependency Rule

Circular component dependency is forbidden.

Bad:

```text
Cache depends on Database.
Database depends on Cache.
```

Better:

```text
Database emits QueryExecuted.
Cache exposes CacheContract.
Application flow composes Database and Cache.
Observability listens to both.
```

If two components need each other, move coordination above them.

The higher-level owner may be:

```text
Framework flow
Application flow
Operations flow
Platform engine
Event/message contract
Neutral foundation contract
```

---

## 22. Naming Rule

Component names must be banal, intuitive, predictive, and descriptive.

A component name should answer what capability exists.

Good:

```text
Cache
Queue
Scheduler
ObjectStorage
SearchIndex
MessageBroker
RuntimeSafety
ConfigIntelligence
RouteIntelligence
ContainerIntelligence
Idempotency
Outbox
Workflow
Observability
```

Weak:

```text
Manager
Processor
Helper
Utils
Common
Support
Misc
CoreStuff
SystemTools
```

Folder says flow or capability.

Unit says responsibility.

Function says exact action.

---

## 23. Public Surface Rule

PublicSurface must stay small.

Allowed:

```text
facade
root public API class
public interface
public value object
public DTO
public factory
public kernel interface
stable alias
```

Forbidden:

```text
business logic
runtime machinery
flow implementation
implementation-specific code
internal registry
request-scoped mutable state
private configuration builder
helper class
utility class
```

If PublicSurface grows large, the component is probably leaking internals.

---

## 24. Documentation Rule

Every component must document:

```text
what problem it solves
which platform plane it belongs to
public API
exported capabilities
configuration
failure behavior
observability
runtime-safety rules
testing strategy
operator diagnostics
examples
```

Documentation must mirror the source.

If the source changes, documentation must change.

A component with stale documentation is not complete.

---

## 24A. Self-Explaining Documentation Gate

### Status
**MANDATORY**
**Severity:** BLOCKER

### Rule

NO component may be marked GREEN without self-explaining documentation at every important ownership boundary.

Self-explaining documentation is not optional. It is the difference between a component that explains itself and a component that requires archaeological excavation.

Every important boundary must be readable by a human or AI agent without opening the code.

### 24A.1 Minimum Required Documentation

Every important boundary MUST have:

| Document | Required When | Severity If Missing |
|----------|--------------|---------------------|
| `README.md` | Always for important boundaries | HIGH |
| `dictionary/` | When complexity justifies it (5+ domain-specific terms) | MEDIUM |
| `docs/adr/` | When architectural decisions are locked | MEDIUM |
| Mermaid diagrams | When flows are non-trivial (3+ handoffs or 5+ steps) | MEDIUM |
| `mistakes.md` | When the area is risky (security, persistence, external I/O, runtime state) | HIGH |

### 24A.2 Complexity Thresholds

A boundary is **important** when ANY of the following are true:

```text
- boundary has 10+ PHP files
- boundary has System/ folder (canonical component shape)
- boundary has PublicSurface/ folder
- boundary has Flows/ or Capabilities/ folder
- boundary exposes public API
- boundary owns security-sensitive behavior
- boundary owns persistence or external I/O
- boundary owns runtime state or worker lifecycle
```

A boundary is **complex** when ANY of the following are true:

```text
- boundary has 25+ PHP files
- boundary has 3+ sub-boundaries
- boundary has 5+ domain-specific terms
- boundary has 3+ external dependencies
- boundary has non-trivial flow (3+ handoffs or 5+ steps)
- boundary has locked architectural decisions
```

Complex boundaries require the full documentation suite. Important boundaries require at minimum README.md.

### 24A.3 Exemptions

The following boundaries are exempt from mandatory documentation:

```text
- small utilities under 3 PHP files with no subdirectories
- Foundation/ folders (tiny neutral primitives)
- InternalSystem/ concept folders
- ExportedCapabilities/ concept folders
- single-file adapters or shims
- test fixtures and test helpers
- generated or compiled artifacts
```

Exempt boundaries must still follow naming rules and responsibility clarity. Exemption does not mean chaos.

### 24A.4 README.md Requirements

Every important boundary README.md MUST explain:

```text
- what this boundary owns (positive space)
- what does NOT belong here (negative space — mandatory for AI grounding)
- which platform plane it belongs to
- what public API it exposes
- what flows it owns or participates in
- what capabilities it provides
- how it is configured
- how it fails
- how it is observed
- how it is tested
- what is not owned here
```

A README that only restates the folder name is insufficient. A README that does not explain negative space is incomplete.

### 24A.5 Dictionary Requirements

When a boundary is complex enough to warrant a dictionary:

```text
- every public term must have an entry
- every term must explain "What It Is"
- every term must explain "What It Is NOT" (mandatory for AI grounding)
- every term must explain "Common Confusion" (mandatory for AI grounding)
- terms must use canonical names from docs/governance/canonical-terms.md
- entries must be grounded in real code behavior, not abstract definitions
```

Dictionary entry format:

```markdown
<a id="term-slug"></a>

### `TermName`

**What It Is:**
Plain explanation of the concept.

**What It Is NOT:**
Clear boundaries on what this term does not mean.

**Common Confusion:**
What other terms this is easily confused with and why they are different.
```

### 24A.6 ADR Requirements

When architectural decisions are locked:

```text
- ADR MUST have Status (proposed | accepted | deprecated | superseded)
- ADR MUST have Context (why this decision needed to be made)
- ADR MUST have Decision (what was decided)
- ADR MUST have Consequences (what this means for the codebase)
- ADR MUST include trade-off analysis (why this option over alternatives)
- ADR MUST record assumptions and risks
```

ADR format:

```markdown
# ADR-NNN: Short Decision Title

**Status:** proposed | accepted | deprecated | superseded

## Context
Why this decision needed to be made.

## Decision
What was decided.

## Consequences
What this means for the codebase, team, and future work.

## Trade-off Analysis
| Option | Pros | Cons | Why Rejected |
|--------|------|------|--------------|
| Option A | ... | ... | ... |
| Option B (chosen) | ... | ... | — |

## Assumptions
- ...

## Risks
- ...
```

### 24A.7 Mermaid Diagram Requirements

Non-trivial flows MUST have Mermaid diagrams:

```text
- flows with 3+ handoffs require sequenceDiagram
- flows with 5+ steps require step-by-step annotation
- flows crossing component boundaries require explicit participant labels
- diagrams use real file/function names, not abstract participant names
- autonumber is used by default unless it makes the picture worse
- flowchart is used only when topology teaches better than call order
```

### 24A.8 Mistakes Documentation Requirements

Risky areas MUST document known mistakes:

```text
- security-sensitive areas: common attack vectors and how this boundary defends
- persistence areas: data corruption scenarios and prevention
- external I/O areas: failure modes and recovery
- runtime state areas: state leak scenarios and reset behavior
- configuration areas: misconfiguration scenarios and early detection
```

Mistakes file format:

```markdown
# Known Mistakes in <Boundary>

## Mistake: <What Goes Wrong>

**Symptom:** What you see when this happens.

**Root Cause:** Why it happens.

**Prevention:** How this boundary prevents it.

**Recovery:** What to do when it happens anyway.
```

### 24A.9 Ownership Rules

```text
Who writes docs: The agent or developer who creates or modifies the boundary writes the docs.
When docs are reviewed: Docs are reviewed as part of every code review touching the boundary.
When docs are updated: Docs MUST be updated in the same commit as the code change.
Who approves docs: The reviewer who approves the code change must also approve the docs.
Stale doc markers: If docs reference files/functions that no longer exist, docs are stale.
```

Code without docs is incomplete. Code with stale docs is misleading. Both block GREEN.

### 24A.10 GREEN Status Requirement

A component without self-explaining documentation at every important boundary MUST NOT be marked GREEN.

Severity:

```text
BLOCKER: missing README.md on important boundary
BLOCKER: missing mistakes.md on risky security/persistence boundary
HIGH: missing README.md negative space explanation
HIGH: missing dictionary on complex boundary
MEDIUM: missing dictionary entry "What It Is NOT" section
MEDIUM: missing dictionary entry "Common Confusion" section
MEDIUM: missing Mermaid diagram on non-trivial flow
MEDIUM: missing ADR on locked architectural decision
LOW: ADR missing trade-off analysis table
LOW: dictionary entry could be clearer
```

### 24A.11 Gate Integration

The configured self-explaining architecture gate validates:

```text
- important boundaries have README.md
- README.md explains ownership
- README.md explains negative space
- complex boundaries have dictionary/
- dictionary entries have required sections
- locked decisions have adr/
- ADRs have required sections
```

The gate is an automated check. Human review must still verify quality, not just presence.

---

## 25. Review Checklist

A component design passes review only if:

```text
[ ] it belongs to a clear platform plane
[ ] it solves a concrete platform problem
[ ] it has one obvious ownership boundary
[ ] public surface is small
[ ] internal behavior is not exposed
[ ] exported capabilities are explicit
[ ] imports are explicit
[ ] dependency direction is acyclic
[ ] external I/O has reliability policy
[ ] failure model exists
[ ] configuration schema exists
[ ] health/doctor checks exist
[ ] observability events exist
[ ] compatibility tests exist
[ ] failure tests exist
[ ] runtime-safety rules exist
[ ] example usage exists
[ ] documentation exists
[ ] operator diagnostics exist
```

If any required item is missing, the component is not platform-complete.

It may be marked:

```text
draft
experimental
internal
planned
partial
```

It must not be marked production-ready.

---

## 26. V1 / V2 / V3 Roadmap Discipline

### V1: Production Kernel

V1 proves that The framework is real.

V1 owns:

```text
taxonomy
autoload
namespace integrity
tests
static analysis
runtime safety
public surface integrity
kernel boot
HTTP kernel
console kernel
worker kernel minimum
request scope
state reset
control plane minimum
doctor minimum
observability minimum
golden path app
production-readiness evidence
```

### V2: Enterprise Platform Engine

V2 builds platform engines.

V2 owns:

```text
API contract engine
integration engine
reliability engine
observability engine
security/identity/tenancy engine
queue/worker engine
scheduler engine
workflow/saga engine
delivery engine
```

### V3: System-Design Framework

V3 validates large-system behavior.

V3 owns:

```text
capacity model
load model
latency budget
availability target
consistency model
partitioning model
sharding model
replication model
messaging model
projection model
failure model
simulation runner
architecture tests
reference architectures
tradeoff reports
```

V2 and V3 are not backlog dreams.

They are planned engine layers with explicit contracts, failure models, diagnostics, tests, examples, and executable
proof.

---

## 27. Final Law

Components must not become a pile of folders.

Components must become platform muscles.

Concepts are not folders.

Responsibilities must be translated into flow and capability names.

A component is complete only when it has:

```text
public API
behavior
external implementation boundary
configuration
failure model
reliability policy
observability
tests
runtime safety
diagnostics
examples
documentation
```

Interfaces are cheap.

Platform muscles are proven.

Build muscles.

---

## Completion Language

### Component Status Definitions

A component **MUST NOT** be marked **production-complete** unless:

- [ ] Public API is stable and documented
- [ ] Internal runtime behavior is implemented
- [ ] Fake/local implementation exists
- [ ] Production implementation boundary exists
- [ ] Configuration schema is defined
- [ ] Health check exists
- [ ] Doctor check exists
- [ ] Failure model is documented
- [ ] Reliability policy exists (for external I/O)
- [ ] Observability events are recorded
- [ ] Compatibility tests exist (contract tests)
- [ ] Failure tests exist
- [ ] Runtime-safety rules are documented
- [ ] Example usage exists
- [ ] Documentation exists
- [ ] Operator diagnostics exist

A component **MUST** be marked **draft** if any of the above are missing.

A component **MUST** be marked **experimental** if it's not proven in production.

A component **MUST** be marked **production-ready** only when all items above are satisfied.

## 28. Component Status Ownership Rule

### Status

**MANDATORY**
**Severity:** HIGH

### Rule

Every component must have an explicit status before production readiness claims.

### Allowed Statuses

```text
ACTIVE_GREEN
ACTIVE_YELLOW
ROADMAP
SCAFFOLD
LABS_ONLY
EVIDENCE_ONLY
PURE_FOUNDATION
TEST_ONLY
DEPRECATED
```

### Required Fields

Every status entry MUST include:

| Field                          | Required          |
|--------------------------------|-------------------|
| component path                 | yes               |
| status                         | yes               |
| owner                          | yes               |
| reason                         | yes               |
| production autoload decision   | yes               |
| ServiceProvider requirement    | yes when ACTIVE   |
| health/doctor requirement      | yes               |
| test requirement               | yes               |
| security review requirement    | yes when relevant |
| performance review requirement | yes when relevant |
| V5.9 blocking decision         | yes when relevant |

A component with no status MUST NOT be silently treated as ACTIVE_GREEN.

Gates must use component status ownership. A ROADMAP/SCAFFOLD/LABS_ONLY component MUST NOT leak into production runtime
autoload unless explicitly justified.

---

## 29. Large Unit Review Thresholds

Large code is not automatically wrong, but it is automatically suspicious.

Mandatory review triggers:

```text
Class over 300 lines:            mandatory responsibility review
Method over 50 lines:            mandatory extraction or explanation review
Constructor with 8+ dependencies: mandatory design review
PublicSurface over 150 lines:    mandatory behavior leak review
Builder over 300 lines:          BLOCKER until classified
ServiceProvider over 250 lines:  mandatory split review
Test class over 500 lines:       mandatory test organization review
```

Threshold trigger requires documented decision. No large unit may be called GREEN without review decision.

---

## 30. Universal Enterprise Codecraft Rule

**Status:** MANDATORY  
**Scope:** All production code, configuration, tests, and infrastructure.
**Severity:** BLOCKER

### 30.1 Enterprise-Grade and Human-Readable

Every unit must be:

- enterprise-grade
- readable
- intuitive
- structurally honest

This applies to:

- systems and subsystems
- capabilities and flows
- runtimes and public surfaces
- policies and rules
- value objects and configuration
- tests and infrastructure adapters

Enterprise-grade does NOT mean:

- complicated
- abstract
- ceremonial
- pattern-heavy
- over-engineered
- architecturally theatrical

Enterprise-grade means:

- explicit responsibility
- low cognitive load
- strong boundaries
- predictable behavior
- safe defaults
- testable behavior
- clear ownership
- readable call-sites
- discoverable structure
- fluent APIs
- maintainable evolution
- no hidden complexity

### 30.2 Human-Readable Enterprise Design

The framework's code must read naturally.

Folders tell the system story.
Class names explain intent.
Method names explain action.
Call-sites must feel fluent and predictable.

The codebase should explain itself without architectural archaeology.

### 30.3 No Technical Theater Rule

Do not introduce:

- Builders
- Factories
- Graphs
- Assemblies
- Managers
- Services
- Helpers
- Utils
- Coordinators
- Orchestrators
- Setup/Wiring layers
- pattern-heavy abstractions

unless they solve a proven structural problem.

Pattern usage must reduce cognitive load, not move complexity behind prettier names.

### 30.4 Fluent Class API Rule

Every class should feel like a small fluent API unit.

Prefer:

- readable property names
- readable method names
- intention-revealing calls
- cohesive APIs
- natural language call-sites

Avoid:

- `execute()` everywhere
- `build()` everywhere
- `create()` everywhere
- technical naming by default
- mechanical type mirroring

Public fluent direction:

```text
App::flow(...)
App::identity()->auth()
App::identity()->access()
```

### 30.5 Cognitive Load Minimization Rule

The system must optimize for:

- fast understanding
- safe modification
- low surprise
- easy navigation
- discoverability
- boring cohesion

If understanding a class requires opening five more classes immediately, the design is suspicious.

### 30.6 Structural Honesty Rule

The structure must reveal the real subsystem boundaries.

Never hide:

- god objects
- dependency chaos
- orchestration complexity
- circular coupling
- unrelated responsibilities

behind:

- builders
- graphs
- factories
- facades
- configuration wrappers

### 30.7 Recursive Decomposition Rule

When a unit becomes large:

- first identify hidden subsystems
- then capabilities
- then flows
- then policies/rules/value objects

Do not split mechanically.
Split by real ownership and behavior.

Stop decomposing when the unit becomes:

- boring
- cohesive
- readable
- predictable

### 30.8 Readability vs Safety Balance

A rule succeeds only if code becomes:

- safer
AND
- easier to understand.

If a rule improves safety but destroys readability:
redesign the structure.

If a shortcut improves readability but hides risk:
reject the shortcut.

### 30.9 Hard Boundary Rules

#### 30.9.1 Horizontal Blindness

Sibling subsystems do not directly depend on each other.
Parent gateway/orchestrator coordinates them.

Example: `AuthenticationGateway` coordinates `CredentialAuthority` and `SessionRegistry`.
Forbidden: `CredentialAuthority` directly depends on `SessionRegistry`.

#### 30.9.2 Boundary Value Objects

Raw primitives must not cross subsystem boundaries when carrying business or security meaning.

Examples: `EmailAddress`, `PlainPassword`, `TenantId`, `UserId`, `TokenId`, `PermissionName`, `RoleName`, `ClientId`, `SessionId`, `AuthContextId`.

#### 30.9.3 Command/Query Clarity

Commands mutate.
Queries read.
Mixed behavior requires explicit result object and evidence.

#### 30.9.4 HLD/LLD Mirror

Architecture language must match physical code structure.

If the architecture says A coordinates B and C, code must not wire B directly to C.

#### 30.9.5 Single Preferred Entry

Each subsystem exposes one obvious public surface/gateway.

Multiple entry points create unstable API and attack surface.

### 30.10 Naming Direction

Avoid default usage of:

- Builder
- Factory
- Graph
- Assembly
- DSL
- Wiring
- Setup
- Manager
- Service
- Helper
- Util
- Support

Avoid framework branding in class names by default.

Prefer:

- subsystem names
- capability names
- product names
- domain names
- fluent call-sites

Examples:

```text
AuthenticationGateway
CredentialAuthority
SessionRegistry
MfaProtection
PasskeyAccess
ExternalLogin
TenantAccess
PolicyEnforcement
RiskAssessment
TokenAuthority
```

### 30.11 GREEN / YELLOW / RED Criteria

GREEN:

- units read naturally without opening multiple other files
- call-sites are fluent and intention-revealing
- structure matches documented architecture
- no pattern-heavy abstractions without proven need
- boundaries use value objects, not raw primitives
- one entry point per subsystem

YELLOW:

- technical theater exists in legacy code with documented migration plan
- builder/factory used but justified with evidence
- mixed command/query in non-critical path with explicit result object

RED:

- god objects hidden behind facades or builders
- sibling components depend on each other directly
- raw primitives cross security boundaries
- architecture docs say one thing, code does another
- multiple uncontrolled public entry points per subsystem

---

### Stage Completion Language

A stage **MUST NOT** be marked GREEN unless:

- All MANDATORY rules pass
- All tests pass
- PHPStan passes (no errors)
- Component completion criteria are met
- Component status entries are current for all production components
- No BLOCKER/HIGH issue remains unresolved
- Every deferred MEDIUM issue has owner, reason, and next action

A stage **MUST** be YELLOW if:

- Validation passes but component completion incomplete
- MEDIUM/HIGH findings remain deferred
- Component status entries are incomplete

A stage **MUST** be RED if:

- Tests fail
- PHPStan fails with errors
- Component violates mandatory design rules
- Component status is missing for an ACTIVE production component

---

## 30. Object-Oriented Enterprise Architecting Rule

**Status:** MANDATORY
**Scope:** All systems, subsystems, components, flows, capabilities, configuration, tests.
**Severity:** BLOCKER

Source: Object-Oriented Enterprise Architecting principles, translated into The project's governance.

### 30.1 Object-Oriented Thinking Rule

Object-orientation is modeling real-world and system complexity as interacting objects with clear responsibilities.

Classes must represent meaningful concepts: roles, tasks, information, views, commands, events, aggregates, policies, capabilities, or transformations.

Classes must not exist only because a pattern name is available.

A class named `UserManagerHelper` is suspicious. A class named `CredentialAuthority` is not.

### 30.2 Poor OO Practice Warning

Object-orientation does not protect against poor design.

Poor OO creates:

- tight coupling between units that should be independent
- fragile handovers between subsystems that lose information
- expensive change because every change ripples through hidden dependencies
- systems that become impossible to adapt without rewriting

Framework review must treat fake OOP as an architectural risk, not a style nit.

Fake OOP indicators:

- god objects with `Manager`/`Service`/`Handler` naming
- classes that exist only to wrap one method call
- inheritance used for code reuse, not behavioral contracts
- objects that know too much about sibling internals
- anemic domain models with all logic in flows

### 30.3 Pattern as Thinking Tool Rule

Design patterns are tools for thinking and structure, not decorations.

GoF, DDD, CQRS, Event Sourcing, Data Mesh, EventStorming ideas may be used only when they:

- clarify ownership
- reduce coupling
- expose real system behavior
- make handovers explicit
- protect invariants

Pattern names must not become class-name theater.

`OrderCreatedEvent` is valuable when it represents a real domain event.
`EventFactoryBuilderProxy` is theater.

### 30.4 Ubiquitous Language Rule

Framework class, folder, and API names must make sense to both developers and system/domain stakeholders.

Names must support conversation, not just compilation.

If a name only makes sense as framework mechanics, it is suspicious.

Examples of ubiquitous language:

- `CredentialAuthority` — stakeholders understand "credentials" and "authority"
- `SessionRegistry` — stakeholders understand "sessions" and "registration"
- `RiskAssessment` — stakeholders understand "risk" and "assessment"

Examples of framework-mechanics-only names:

- `AbstractBaseComponentHandler`
- `GenericServiceProcessor`
- `DefaultImplementationManager`

### 30.5 EventStorming Discovery Rule

For complex flows, use EventStorming thinking before writing code:

1. Discover domain/system events first (what happened?)
2. Derive commands from events (what caused it?)
3. Identify aggregates/information owners (who owns the data?)
4. Map handovers (what crosses boundaries?)
5. Then write code

This is especially required for:

- Identity/Auth redesign
- Tokens/Sessions redesign
- Tenancy redesign
- Risk assessment redesign
- External Identity integration

EventStorming output must be captured in evidence:

- event list
- command list
- aggregate ownership map
- handover inventory

### 30.6 Slicing Is Architecture Rule

The most important architecture decision is slicing the problem space.

Slice by:

- cohesion (what belongs together?)
- information ownership (who owns this data?)
- task/role alignment (who does what?)
- handover boundaries (what crosses?)
- deployability potential (can this evolve independently?)
- runtime independence (can this run without that?)
- coupling pressure (how hard do these push on each other?)

Bad slicing creates:

- fragile handovers
- information loss
- rework
- accidental coupling

### 30.7 Bounded Context + Context Map Rule

Major subsystems must define:

- bounded context name
- owned concepts
- consumed concepts
- published APIs/events/views
- collaborators
- upstream/downstream relationships
- accepted coupling
- handovers
- ownership of information

Context maps are required evidence for major redesigns.

For DDD extension, see `how-to-architecture-extension-with-ddd.md`.

### 30.8 IRTV Modelling Rule

For complex subsystems, model:

- **Information:** what knowledge/data matters?
- **Roles:** who/what uses it?
- **Tasks:** what work is performed?
- **Views:** what interfaces/workspaces/APIs expose it?

IRTV should guide:

- subsystem naming
- PublicSurface design
- flow boundaries
- test boundaries
- capability extraction

### 30.9 Knowledge Backbone Rule

Important systems need an explicit knowledge backbone:

- key information objects
- ownership of each
- transformations applied
- update flows
- consumers
- views that expose it

Do not let core knowledge live accidentally inside services, builders, managers, or configuration wrappers.

Knowledge must be named, owned, and testable.

### 30.10 Handover Risk Rule

Every handover between subsystems or contexts is an architectural risk.

Handover evidence must identify:

- what information crosses the boundary
- who owns it on each side
- what can be lost in translation
- what must be transformed
- what contract protects it
- what tests prove it works

Hidden handovers are architecture bugs.

Handover bugs manifest as:

- lost data
- stale state
- inconsistent models
- silent failures
- integration rework

### 30.11 Views as Loose Coupling Rule

Views are first-class architecture artifacts.

A view may be:

- public API surface
- workspace for a role
- query surface
- command surface
- transformation boundary
- event processing boundary
- communication bridge between contexts

Views must expose consumer-specific knowledge without leaking internal models.

### 30.12 Command/Query Clarity (CQRS Thinking)

Views benefit from command/query separation.

Separate:

- commands that change state
- queries that read state
- transformations
- event processing
- communication

Do not apply CQRS as ceremony.
Use it to clarify state change versus knowledge access.

Commands mutate. Queries read.
Mixed behavior requires explicit result object and evidence.

### 30.13 Data Mesh / Data Product Thinking

When the framework exposes data across components or contexts, treat it as a product:

- owned by a clear producer
- documented for consumers
- stable API
- consumer-oriented (not producer-convenient)
- transformed for consumer needs
- versioned where breaking changes are possible

This is especially relevant for:

- domain events
- telemetry data
- identity risk signals
- policy decisions
- token/session state
- generated metadata

### 30.14 Event Sourcing as Option

Event Sourcing is an architectural option for storing changes as event history.

Use when:

- auditability matters
- state reconstruction is needed
- time-travel debugging is valuable
- change history is a business requirement

Do not conflate Event Sourcing with EventStorming.
EventStorming is a discovery technique.
Event Sourcing is a persistence strategy.

Do not use Event Sourcing as ceremony.

### 30.15 Enterprise Reality Rule

The architecture must assume real systems contain:

- multiple vendors
- multiple technical generations
- external systems outside our control
- conflicting organizational interests
- political and business constraints
- legacy integration requirements

Therefore:

- boundaries must be explicit
- APIs must be versioned or compatibility-managed
- events must be documented
- ports must be defined
- compatibility layers must exist
- handover contracts must be testable

Design as if the real world is not clean. Because it is not.

### 30.16 Distributed vs Centralized Tradeoff

Do not assume centralized or distributed architecture is best.

Both have strengths and weaknesses.

Major choices must record:

- distributed option and its tradeoffs
- centralized option and its tradeoffs
- hybrid option if relevant
- strengths, weaknesses, opportunities, threats
- assumptions
- evidence
- tactical feasibility

Use claims-based SWOT where appropriate.

### 30.17 Tactical Detail Before Strategic Bet

High-level diagrams are not enough.

Major architecture decisions require tactical LLD/code-level feasibility proof.

The devil is in the details.

No GREEN strategic decision without tactical evidence.

### 30.18 Transformation / Constructor Mental Model

Components can be understood as repeatable transformation machines:

- input received
- recipe/rules applied
- output produced
- knowledge preserved or updated
- repeatability guaranteed

This is a mental model only.
It is not a reason to add `Constructor`/`Factory` naming.

Use it to clarify:

- flows
- policies
- compilers
- metadata graphs
- runtime plans

### 30.19 Model-to-Code Conversation Rule

Models are not decoration.

If a diagram or model exists, code structure must reflect it.

If code diverges from the model:

- either update the model
- or fix the code

No stale architecture theater.

### 30.20 Claims-Based Architecture Evidence

For major architectural alternatives, record claims:

- expected benefit
- risk accepted
- weakness acknowledged
- opportunity identified
- threat monitored
- assumption documented
- evidence gathered
- validation path defined

Architecture decisions must not be opinion-only.

### 30.21 Architectural Quanta

Bounded contexts, views, and capabilities may become independently deployable or independently evolvable units.

Design boundaries should keep that option possible when relevant.

This affects:

- dependency direction
- deployment packaging
- API versioning
- event contract stability
- test independence

### 30.22 Project Translation Rule

All extracted ideas must be translated into the project terms:

- PublicSurface receives
- Flows execute
- Capabilities power
- Configuration assembles
- Foundation supports
- Context Maps document boundaries
- Evidence proves claims
- Governance Review validates
- Component Dogfooding ensures reuse

Do not import terminology blindly if it conflicts with project language.

### 30.23 GREEN / YELLOW / RED Criteria

GREEN:

- classes represent real concepts, not pattern names
- ubiquitous language understood by stakeholders
- handovers documented and tested
- context maps exist for major subsystems
- IRTV model applied to complex subsystems
- knowledge backbone is explicit, not accidental
- models match code structure
- tactical evidence supports strategic decisions

YELLOW:

- some framework-mechanics names exist with migration plan
- handover contracts partially documented
- context maps exist but are stale
- knowledge partially hidden in services

RED:

- fake OOP: god objects, wrapper classes, inheritance for reuse
- pattern theater: classes named after patterns, not concepts
- hidden handovers: information crosses boundaries without contract
- no context map for major subsystem
- models exist but code diverges without explanation
- strategic decisions without tactical evidence
- core knowledge accidentally trapped in builders/managers

---

## 32. Balanced Coupling Rule

**Status:** MANDATORY  
**Severity:** BLOCKER  

This section establishes rules for balancing coupling within software design, translating lessons from *Balancing Coupling in Software Design* (Khononov) to the Screaming Architecture.

### 32.1 Coupling Dimensions

Every unit and subsystem in the project **MUST** control its coupling across three primary dimensions:
1. **Afferent (Inward) & Efferent (Outward) Coupling:** High-level orchestrators (Flows) may depend on low-level capabilities, but low-level capabilities or foundation components **MUST NOT** depend on high-level orchestrators. Sibling components **MUST NOT** cross-couple.
2. **Temporal Coupling:** Actions that must occur in sequence or simultaneously **MUST** be coordinated explicitly by a Flow or event-handling structure. They **MUST NOT** be coupled implicitly via shared mutable state, filesystems, or global side-effects.
3. **Semantic Coupling:** Subsystems **MUST NOT** share internal schema details, database structures, or mutable states. Sibling communication **MUST** be mediated strictly via explicit, immutable PublicSurface contracts (boundary value objects).

### 32.2 Classifications

- **BLOCKER:** Circular dependencies, sibling components depending directly on each other's private internals, or out-of-order component collaboration.
- **RED:** Direct dependency on raw database tables of another component, bypassing its PublicSurface facade.
- **YELLOW:** Temporal coupling that is documented and managed via event handlers with clear retry and dead-letter policies.

### 32.3 Component Decomposition Principle Rule

**Status:** MANDATORY  
**Severity:** BLOCKER

Before creating a new component or splitting an existing component, the decomposition principle MUST be documented.

A component is not justified by folders, interfaces, or ServiceProvider shells.

A component is justified only when it owns a distinct change axis, knowledge domain, or platform capability.

**Good component decomposition reasons:**
```text
Distinct change axis — this component changes for different reasons than its neighbors.
Clear information ownership — this data or behavior has a single honest owner.
Cohesive platform capability — this is reusable platform muscle (cache, database, queue, events, auth).
Predictable impact boundary — changes inside this component do not surprise distant components.
Different runtime or lifecycle — this has different deployment, performance, or lifetime needs.
Replaceable implementation — this component's implementation should be swappable without touching consumers.
Security or trust boundary — this component owns a security-sensitive surface.
```

**Bad component decomposition reasons:**
```text
Split because the file is long — length is not a decomposition principle.
Split by technical category — "Services", "Repositories", "Handlers" are not component boundaries.
Split to have more components — component count is not a quality metric.
Split by framework layer — MVC layers are not component boundaries.
Split for team topology alone — org structure does not define component boundaries.
Split to reduce constructor size — too many dependencies signals responsibility problems, not component answers.
```

**Rule:**
```text
A component without a documented decomposition principle is an accidental boundary.
Accidental component boundaries become integration cost, not platform value.
```

**Component Creation Checklist:**
```text
1. What problem does this component solve that existing components cannot?
2. What is the decomposition principle? (one sentence)
3. What change axis does this component protect?
4. What knowledge does this component isolate?
5. What public API will this component expose?
6. Which existing components will this component depend on?
7. Which existing components will depend on this component?
8. What failure modes does this component own?
9. How is this component observed in production?
10. What does this component NOT own?
```

### 32.4 Component Coupling Assessment Rule

**Status:** MANDATORY  
**Severity:** HIGH

Every active component MUST be assessable for coupling health.

Component coupling is assessed through:

```text
1. Knowledge exchanged — what data, types, events, or contracts cross component boundaries?
2. Integration strength — direct call, interface, event, or published contract?
3. Physical/logical distance — same area, different area, different subsystem?
4. Change frequency — do components change together often, rarely, or never?
5. Co-change pressure — does a change in one component force changes in another?
```

**Component-level tight coupling is forbidden when:**
```text
Components change independently but are forced to change together.
Knowledge leaks across component boundaries without ownership.
One component depends on another component's internal implementation details (Intrusive Coupling Blocker).
Component boundaries are physical only — folders separate but knowledge flows freely.
```

**Component co-change smell:**
```text
If a single feature requires changes in 3+ components that are not in the same platform plane,
the component boundaries are suspect.
```

**Rule:**
```text
If moving two coupled components into the same component makes the coupling obviously acceptable,
the coupling was intrusive regardless of component distance.
```

