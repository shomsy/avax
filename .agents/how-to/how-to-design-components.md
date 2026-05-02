# How To Design Components

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
What public contract does it expose?
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

Owns public API contracts, endpoint contracts, DTO contracts, event contracts, command contracts, schema generation,
versioning, deprecation, and breaking-change detection.

Examples:

```text
DescribeHttpContract
DescribeCommandContract
DescribeEventContract
BuildOpenApiSchema
DetectBreakingContractChange
ValidateResponseContract
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
1. public contract
2. internal runtime behavior
3. fake/local adapter
4. production adapter boundary
5. configuration schema
6. health/doctor check
7. failure model
8. retry/timeout/circuit/backoff policy when external I/O exists
9. observability events
10. contract tests
11. failure tests
12. runtime-safety rules
13. example usage
14. documentation
15. operator diagnostics
```

This is the difference between an interface and a platform muscle.

A component that exposes only a contract is not complete.

A component that has folders but no behavior is not complete.

A component that has behavior but no failure model is not production-grade.

A component that has behavior but no contract tests is not reusable.

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

## 6. Component Boundary Law

Every AvaX component has three boundary zones:

```text
PublicSurface
ExportedCapabilities
InternalSystem
```

### 6.1 PublicSurface

PublicSurface is what external users may call.

It receives public calls, normalizes public input, and delegates.

It must not own real behavior.

It must not contain runtime machinery.

It must not contain adapter-specific implementation.

It must not contain request-scoped mutable state.

It must not become a dumping ground for convenience classes.

### 6.2 ExportedCapabilities

ExportedCapabilities are stable contracts, value objects, events, fakes, test contracts, or explicit reusable
capabilities that other components may depend on.

An exported capability must be intentional.

The default state of every internal unit is private.

A unit becomes reusable only when it is explicitly exported, documented, tested, and accepted as
compatibility-sensitive.

### 6.3 InternalSystem

InternalSystem contains the component's real engine:

```text
Flows
Capabilities
Configuration
Foundation
Adapters
Diagnostics
Tests
Docs
```

Other components must not import another component's internal system directly.

---

## 7. Component Collaboration Rule

Components may collaborate only through approved lanes:

```text
public contracts
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

A component may export a contract, event, value object, fake, test contract, adapter boundary, or reusable capability
only when all of the following are true:

```text
[ ] it has a stable reason to exist
[ ] it is useful outside the component
[ ] the name is obvious outside the component
[ ] it does not leak internal structure
[ ] it does not expose adapter-specific details unless that is its explicit job
[ ] dependency direction remains acyclic
[ ] it can be documented simply
[ ] it has contract tests or behavior tests
[ ] it has a fake, local adapter, or test helper when useful
[ ] changing it would be treated as a compatibility decision
```

If these conditions are not true, the unit must remain internal.

---

## 9. Import Rule

A component may import another component's exported capability only when:

```text
[ ] the dependency is declared explicitly
[ ] the dependency points to a public contract or exported capability
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
adapter-specific code leaks into another component's public API
runtime-specific APIs leak into generic component contracts
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

### 11.6 Adapter Boundary

Use when AvaX integrates with external infrastructure.

Example:

```text
ObjectStorage exposes ObjectStorageContract.
LocalObjectStorage is a local adapter.
S3ObjectStorage is a production adapter.
ObjectStorageHealthCheck verifies reachability.
ObjectStorageFailure maps external failures into AvaX failures.
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
contract tests
failure tests
provided fakes
local adapters
production adapter boundaries
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

### 13.3 Adapters

```text
[ ] Is there a fake adapter?
[ ] Is there a local adapter where useful?
[ ] Is there a production adapter boundary?
[ ] Are production adapters isolated from public API?
[ ] Are adapter failures mapped into AvaX failures?
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
[ ] adapter contract tests
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
local/fake adapter
contract tests
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
contract test
fake implementation
test builder
test assertion helper
example usage
```

A reusable component without test support is not truly reusable.

Production adapters must pass the same contract tests as fake/local adapters where possible.

If an adapter cannot pass the canonical contract tests, the deviation must be documented.

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
[ ] contract is small
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
Component public contracts -> used by consumers
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
public contract
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
adapter-specific code
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
[ ] contract tests exist
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

A component is complete only when it has:

```text
contract
behavior
adapter strategy
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
