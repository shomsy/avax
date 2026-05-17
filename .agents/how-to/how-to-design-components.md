# How To Design Components

## Status

**MANDATORY** - This document defines non-negotiable component design rules for AvaX.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, *
*BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

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

This document defines how AvaX components must be designed, completed, composed, exported, tested, and promoted into
platform-level capabilities.

AvaX must not become a pile of components.

AvaX must become a coherent platform made of clear planes:

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

This document is a hard governance rule for AvaX component design.

---

## 2. Core Philosophy

AvaX components are not decorative modules.

AvaX components are reusable platform muscles.

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

AvaX is not only a component collection.

AvaX must be organized around platform planes.

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

Every production AvaX component must follow one canonical filesystem shape.

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
.agents/how-to/how-to-dependency-injection.md — Section 6: Container Ownership Rule
.agents/how-to/how-to-runtime-composition.md — Full runtime composition law
```

Key principle for component design:

```text
Configuration/Builders/ assembles the system.
Capabilities/ creates runtime results.
Runtime execution code must not assemble dependencies.
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

AvaX does not use `UseCases/` as a default folder.

In AvaX vocabulary, a use case is represented as a `Flow`.

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

A `Docs/` folder inside `System/` is not required by default.

Prefer project-level documentation and reports unless the component is a package that intentionally ships its own docs.

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

unless the component is explicitly package-shaped and the docs are meant to ship with it.

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
| `Docs/`          |                      no | Prefer component README, docs, or reports.                          |
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

Use when AvaX integrates with external infrastructure.

Example:

```text
ObjectStorage exposes a stable public storage promise.
LocalObjectStorage provides local storage behavior.
S3ObjectStorage provides S3-backed storage behavior.
CheckObjectStorageHealth verifies reachability.
MapObjectStorageFailure maps external failures into AvaX failures.
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
[ ] Are production failures mapped into AvaX failures?
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

V1 proves that AvaX is real.

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

AvaX components must not become a pile of folders.

AvaX components must become platform muscles.

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
