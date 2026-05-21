# how-to-dogfooding.md

# AvaX Internal Dogfooding Governance

## Status

**MANDATORY** - This document defines non-negotiable dogfooding rules for AvaX.

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

---

## 1. Status

This document is mandatory governance for AvaX framework development.

It defines how AvaX components must be reused inside AvaX itself.

This is not an optional architecture preference.

This is a correctness, maintainability, performance, and system-integrity rule.

AvaX must not become a collection of strong components that are bypassed by its own runtime.

AvaX must use AvaX.

---

## 2. Purpose

The purpose of this document is to prevent duplicate internal capability ownership.

If AvaX already has a component that owns a capability, other components must use that owner through the correct
boundary.

The goal is:

```text
one capability = one owner
all consumers = use that owner
no local mini-version
```

AvaX must behave like one composed framework engine, not like unrelated packages placed next to each other.

---

## 3. Core Law

A component is not enterprise-grade only because it exists.

A component becomes enterprise-grade when:

1. it has a clear ownership boundary
2. it provides real behavior
3. other parts of AvaX use it where appropriate
4. duplicate local behavior is removed
5. public APIs stay small
6. internal composition stays clean
7. runtime safety is preserved
8. validation proves the relationship

The core law:

```text
If AvaX owns a capability in one component, no other component may reimplement that capability locally.
```

---

## 4. What Dogfooding Means In AvaX

Dogfooding means AvaX uses its own components internally.

Examples:

```text
Storage uses Filesystem.
Queue uses Reliability.
Queue uses CallableSerialization.
Messaging uses Queue, Database, Reliability, and Observability.
SchemaGeneration uses DataTransfer, SecureRequest, and Router metadata.
Runtime uses Router, Container, ErrorHandling, WarmSafety, MemoryGuard, and Observability.
File writers use Filesystem or a dedicated file-writing boundary backed by Filesystem.
```

Dogfooding does not mean everything depends on everything.

Dogfooding means each component consumes another component through the correct public, configured, or flow boundary.

---

## 5. What Dogfooding Is Not

Dogfooding is not:

```text
- direct access to another component's internals
- circular dependencies
- importing random classes from deep folders
- making every component know every other component
- using static global state as a shortcut
- bypassing proper configuration
- creating hidden dependency spaghetti
- forcing reuse where a small local primitive is more honest
```

Good dogfooding increases clarity.

Bad dogfooding creates coupling.

The rule is:

```text
reuse through owned boundaries, not through internals
```

---

## 6. Ownership Rule

Every reusable capability must have exactly one active owner.

Examples:

| Capability                                    | Owner                 |
|-----------------------------------------------|-----------------------|
| Local filesystem operations                   | Filesystem            |
| Disk/object abstraction                       | Storage               |
| Hydration and validation                      | DataTransfer          |
| Request DTO lifecycle                         | SecureRequest         |
| Route registration and matching               | Router                |
| Controller creation and dependency resolution | Container             |
| Callable payload safety                       | CallableSerialization |
| Retry, timeout, circuit, fallback, bulkhead   | Reliability           |
| Process-based execution                       | Parallelism           |
| Cooperative task scheduling                   | Concurrency           |
| Correlation, traces, metrics, logs, audit     | Observability         |
| Query execution and transactions              | Database              |
| Background job execution                      | Queue                 |
| Outbox, inbox, envelopes, consumers           | Messaging             |
| Error classification and rendering            | ErrorHandling         |

If two components own the same capability, architecture is wrong.

One must be removed, merged, downgraded to Labs, or explicitly documented as a different capability.

---

## 7. Dependency Direction Rule

Components may only depend in the direction of abstraction and ownership.

Good:

```text
Storage -> Filesystem
Queue -> Reliability
Queue -> CallableSerialization
Messaging -> Queue
Messaging -> Database
Messaging -> Reliability
Messaging -> Observability
Runtime -> Router
Runtime -> Container
Runtime -> ErrorHandling
Runtime -> WarmSafety
SchemaGeneration -> DataTransfer
SchemaGeneration -> SecureRequest
SchemaGeneration -> Router
```

Bad:

```text
Filesystem -> Storage
Reliability -> Queue
Database -> Messaging
DataTransfer -> SchemaGeneration
Container -> App-specific controller
PublicSurface -> deep runtime machinery
```

The lower-level owner must not depend on a higher-level consumer.

---

## 8. Public Boundary Rule

A component may use another component only through one of these boundaries:

```text
1. PublicSurface
2. configured runtime object
3. flow owner
4. explicit capability interface
5. documented internal extension point
```

Forbidden:

```text
- reaching into another component's private internals
- depending on test-only classes
- depending on concrete runtime-specific classes unless the current component is the runtime integration owner
- using file paths from another component as an API
- duplicating another component because the proper API feels inconvenient
```

If the public boundary is missing, add a proper boundary to the owning component. Do not bypass it.

---

## 9. PublicSurface Dogfooding Rule

PublicSurface classes must stay thin.

They may:

```text
- receive public calls
- normalize small public input
- delegate to flows, capabilities, or configuration
- expose stable user-facing methods
```

They must not:

```text
- own real business/runtime behavior
- own mutable runtime state
- own request-scoped state
- contain job loops
- contain DB driver logic
- contain retry/circuit internals
- serialize callable payloads directly
- scan attributes repeatedly
- perform raw file operations directly
```

Example bad design:

```php
final class Storage
{
    private static array $disks = [];

    public static function put(string $path, string $contents): void
    {
        file_put_contents($path, $contents);
    }
}
```

Why this is wrong:

```text
- PublicSurface owns mutable registry state
- Storage bypasses Filesystem
- raw file operation is duplicated
- warm runtime safety is unclear
```

Better direction:

```text
Storage PublicSurface -> WriteStoredObject flow -> LocalDisk -> Filesystem
```

---

## 10. Filesystem And Storage Rule

Filesystem and Storage must not overlap.

Filesystem owns:

```text
- local path operations
- local file reads
- local file writes
- local folder creation
- local deletion
- local permissions
- path traversal protection
- local filesystem errors
```

Storage owns:

```text
- named disks
- stored object paths
- object visibility
- disk selection
- temporary URL support or rejection
- object-level API
```

Correct dependency:

```text
Storage -> Filesystem
```

Forbidden dependency:

```text
Filesystem -> Storage
```

Forbidden duplication:

```text
Storage directly using file_get_contents()
Storage directly using file_put_contents()
Storage directly using unlink()
Storage directly using mkdir()
Storage directly using chmod()
Storage directly implementing path traversal protection when Filesystem owns it
```

Allowed exceptions:

```text
- Filesystem internals
- tests
- tooling scripts
- bootstrap code with documented reason
```

---

## 11. Queue Dogfooding Rule

Queue must use existing AvaX components.

Queue must use:

```text
Reliability -> retry, backoff, timeout, failure policy
CallableSerialization -> callable payload serialization
Observability -> job metrics, failure records, correlation context
Parallelism -> process execution when worker isolation is used
Database -> database-backed queue driver
```

Queue must not:

```text
- implement a local retry engine
- serialize closures directly
- spawn processes directly
- write observability logs manually
- duplicate dead-letter behavior in multiple locations
- use raw unmanaged sleep loops without a sleeper/backoff boundary
```

Correct direction:

```text
Queue Worker -> Reliability RetryPolicy
Queue Worker -> Observability failure metric
Queue Payload -> CallableSerialization
Queue Driver -> Database when database-backed
```

---

## 12. Messaging Dogfooding Rule

Messaging must compose existing runtime components.

Messaging should use:

```text
Database -> transactional outbox
Queue -> async relay and consumers
Reliability -> retry and failure behavior
Observability -> correlation, tracing, metrics, audit
Idempotency or Inbox -> duplicate prevention
DataStack Json -> payload representation if appropriate
```

Messaging must not:

```text
- create its own retry mechanism
- create its own queue mechanism
- create its own DB transaction abstraction
- create static mutable handler registries unless boot-only and reset-safe
- hide outbox/inbox behavior inside MessageBus PublicSurface
```

Correct direction:

```text
MessageBus PublicSurface -> PublishMessage flow
PublishMessage -> Outbox
OutboxRelay -> Queue
Consumer -> Inbox
Consumer -> Reliability
Consumer -> Observability
```

---

## 13. SchemaGeneration Dogfooding Rule

SchemaGeneration must not invent its own model of the application.

It must use:

```text
DataTransfer -> DataObject shape, attributes, validation metadata
SecureRequest -> request DTO contract
Router -> route metadata
DataStack Json -> schema document representation if useful
```

SchemaGeneration must not:

```text
- rescan attributes through a separate duplicate reflection engine if DataTransfer owns metadata
- create a second router metadata universe
- infer request schemas without SecureRequest/DataTransfer
- generate fake OpenAPI documents without real route/request/response data
```

Correct direction:

```text
Router route list
  -> SecureRequest input metadata
  -> DataTransfer shape metadata
  -> JsonSchema
  -> OpenAPI document
```

---

## 14. Runtime Dogfooding Rule

Runtime and App layers must use AvaX components.

Runtime must use:

```text
Router -> route resolution
Container -> controller/service resolution
ErrorHandling -> exception classification/rendering
WarmSafety -> request reset lifecycle
MemoryGuard -> worker memory monitoring
Observability -> request metrics/tracing/logging
ResponseNormalization -> controller return normalization
SecureRequest -> request DTO autowiring
DataTransfer -> hydration/validation
```

Runtime must not:

```text
- implement a second router
- implement a second container
- render exceptions outside ErrorHandling
- bypass WarmSafety in long-running runtimes
- create request-scoped static state
- leak ReactPHP/RoadRunner/Swoole types into core public API
```

---

## 15. Observability Dogfooding Rule

Observability must be used by runtime paths, not only exist as standalone objects.

Components should record:

```text
Runtime -> request started, request failed, latency
Queue -> job started, job failed, retry count, dead-letter
Messaging -> message published, consumed, failed, duplicated
Database -> query timing, transaction failure where appropriate
Storage -> object operation failure where appropriate
Reliability -> circuit open, timeout, fallback used
```

Observability writers must use redaction before writing.

Forbidden:

```text
- writing secrets to logs
- writing raw context arrays without redaction
- each component inventing its own logging format
- metrics/traces/audit existing but never used by runtime code
```

---

## 16. Reliability Dogfooding Rule

Reliability is the only owner of reliability policies.

Reliability owns:

```text
- retry
- timeout
- circuit breaker
- bulkhead
- fallback
- backpressure
- rate limiting
- idempotency
- lock if part of reliability surface
```

Other components must use Reliability.

Forbidden:

```text
Queue implementing its own retry loop
Messaging implementing its own relay retry logic
Database implementing its own deadlock retry separately
Runtime implementing its own timeout behavior
External HTTP client implementing its own circuit breaker
```

Allowed:

```text
small local loop only when it is not a reliability policy
test-only fake behavior
documented primitive used by Reliability itself
```

---

## 17. CallableSerialization Dogfooding Rule

CallableSerialization owns callable payload safety.

Any serialized callable, closure, or executable payload must go through CallableSerialization.

Forbidden:

```text
serialize($closure)
unserialize($payload)
opis/closure used directly outside CallableSerialization
laravel/serializable-closure used directly outside CallableSerialization
unsigned worker payloads
unverified unserialize before signature verification
```

Allowed:

```text
CallableSerialization internals
tests for CallableSerialization itself
non-callable value serialization through approved codec boundary
```

---

## 18. DataTransfer Dogfooding Rule

DataTransfer owns object hydration, casting, validation, and metadata extraction.

Forbidden outside DataTransfer/SecureRequest:

```text
manual request DTO hydration
manual validation attribute scanning
manual property assignment from input arrays
duplicate caster maps
duplicate validation engines
```

Allowed:

```text
SecureRequest delegating to DataTransfer
SchemaGeneration reading DataTransfer metadata
tests
small value object constructors
```

---

## 19. Hot Path Rule

Dogfooding is mandatory in hot paths, but it must be efficient.

Hot paths include:

```text
- route matching
- controller invocation
- container resolution
- SecureRequest hydration
- DataTransfer validation
- middleware execution
- queue job processing
- database query execution
- message consumption
- observability writing
```

Hot paths must avoid:

```text
- repeated reflection
- repeated config parsing
- repeated route compilation
- repeated container graph building
- repeated attribute scanning
- repeated filesystem scanning
- unnecessary serialization/deserialization
```

Use:

```text
- compiled metadata
- cached DataTransfer shapes
- compiled route table
- configured runtime objects
- immutable warm state
- connection pools
- prebuilt registries
```

Dogfooding must not become slow composition.

Correct composition should be both clearer and faster.

For compiled metadata, attribute compilation, and hot-path reflection discipline, see:

```text
how-to-modern-php-attributes-di.md
```

---

## 20. Legacy And Duplicate Ownership Rule

When a new canonical component exists, old overlapping code must be handled.

Allowed outcomes:

```text
1. migrate old code into new component
2. delete old code
3. quarantine old code as Legacy
4. move old code to Labs
5. document old code as Roadmap only
```

Forbidden outcomes:

```text
- leave both active
- let tests cover both as if both are canonical
- keep old static facades that overlap new components
- keep old Adapters/Drivers folders if governance forbids them
- count duplicate code as more coverage
```

Example:

```text
If Application/Storage is canonical, old Operations/Filesystem/Storage must not remain active as a second Storage owner.
```

---

## 21. External Integration Rule

External integrations are not required for core dogfooding unless explicitly in scope.

Examples:

```text
S3 storage -> ROADMAP unless real dependency/config exists
Redis queue -> ROADMAP unless real dependency/config exists
OpenTelemetry exporter -> ROADMAP unless real dependency/config exists
Prometheus exporter -> ROADMAP unless real dependency/config exists
PostgreSQL specialized pool -> ROADMAP unless real implementation exists
```

But local core behavior must be real:

```text
LocalDisk must be real.
MemoryQueue or DatabaseQueue must be real.
Pdo/SQLite pool must be real.
InMemory/File observability writers must be real.
Outbox/Inbox local or DB-backed behavior must be real.
```

Do not fake external integrations.

Mark them honestly.

---

## 22. Approved Exceptions

A component may avoid using an existing component only if one of these is true:

```text
1. the existing component is lower quality or not ready, and the exception is documented
2. using the component would create a dependency cycle
3. the code is test-only
4. the code is tooling-only
5. the code is bootstrap-only and runs before the component is available
6. the code is inside the owning component itself
7. performance requires a lower-level primitive and the reason is documented
```

Every exception must be recorded in the evidence report.

Undocumented exceptions are violations.

---

## 23. Required Adoption Matrix

Every major V4 pass must include this matrix:

```text
Consumer Component | Existing Component To Use | Current Usage | Duplicate Found | Action | Status
```

Example:

```text
Storage | Filesystem | partially used | raw file writes found | migrate to Filesystem | YELLOW
Queue | Reliability | local retry loop found | yes | replace with RetryPolicy | YELLOW
Messaging | Observability | no propagation | yes | add correlation metadata | YELLOW
SchemaGeneration | DataTransfer | used for DataObject only | partial | add SecureRequest/OpenAPI route usage | YELLOW
```

No component can be marked GREEN if the adoption matrix shows unresolved duplicate behavior.

---

## 24. Required Architecture Checks

Where practical, add automated checks.

Recommended script:

```text
php tooling/refactor/check-component-adoption.php
```

The check should detect:

```text
- raw file operations outside Filesystem/tooling/tests
- direct process spawning outside Parallelism/tooling/tests
- direct callable serialize/unserialize outside CallableSerialization
- local retry loops outside Reliability
- Storage dependency inside Filesystem
- duplicate Storage owners
- runtime-specific API leakage into public App API
- PublicSurface classes holding runtime mutable state
- generated cache artifacts committed by mistake
```

If full static detection is too large, start with focused checks:

```text
1. Filesystem/Storage adoption
2. raw file operation audit
3. callable serialization audit
4. PublicSurface mutable state audit
```

---

## 25. Required Tests

Dogfooding must be tested.

Minimum test categories:

```text
1. Composition tests
2. Architecture tests
3. Negative tests
4. Runtime safety tests
5. PublicSurface thinness tests
6. Hot path regression tests where practical
```

Examples:

```text
Storage uses Filesystem.
Filesystem does not depend on Storage.
Queue retry uses Reliability.
Messaging envelope carries Observability correlation ID.
SchemaGeneration reads DataTransfer metadata.
Runtime exception path uses ErrorHandling.
Callable payload goes through CallableSerialization.
PublicSurface delegates to flow.
```

---

## 26. Documentation Requirement

Each component documentation must state:

```text
- what component owns
- what component does not own
- which AvaX components it uses
- which AvaX components use it
- approved exceptions
- local runtime safety rules
- external integrations marked GREEN/YELLOW/LABS/ROADMAP
```

Add a section to every HOW_THIS_WORKS.md:

```md
## Internal AvaX Usage

This component is used by:

- ...

This component uses:

- ...

This component must not duplicate:

- ...
```

---

## 27. Review Checklist

A dogfooding review passes only if:

```text
- every reused capability has one owner
- every consumer uses the correct owner
- duplicate local implementations are removed or quarantined
- PublicSurface stays thin
- no runtime state leaks through facades
- no raw file/process/serialization/retry logic appears outside owners
- no circular dependencies are introduced
- tests prove composition
- docs explain component relationships
- validation passes
```

If any item fails, status is YELLOW or RED.

---

## 28. Final Status Rules

A component may be marked GREEN only if:

```text
- it owns exactly one clear capability
- it uses existing AvaX components where appropriate
- other components use it instead of duplicating it
- public API is stable and thin
- runtime state is safe
- negative behavior is tested
- composition is tested
- docs explain internal usage
- PHPStan is clean
- full test suite passes
- governance checks pass
```

A component must remain YELLOW if:

```text
- it works but bypasses an existing component
- it has duplicate local behavior
- docs do not explain internal usage
- runtime safety is unclear
- public API owns too much behavior
- external integration is missing but claimed
```

A component must be RED if:

```text
- it is broken
- it lies about behavior
- it has placeholder production code
- it bypasses critical security or serialization boundaries
- it causes runtime leaks
```

A component may be ROADMAP if:

```text
- it is intentionally not implemented
- it requires external infrastructure
- it is not needed for local V4 core
- it is documented honestly
```

A component may be LABS if:

```text
- it exists experimentally
- behavior is useful but not production-ready
- API may change
- tests are exploratory
```

---

## 28.2 Practical Test Pyramid Cross-Reference

**Status:** MANDATORY
**Severity:** BLOCKER

Component dogfooding requires contract tests at the pyramid boundary.

See:

- `how-to-unit-test.md` — Section 91: Practical Test Pyramid Rule

Dogfooding test requirements:

- **Contract Tests:** Every component boundary exposed to other components needs contract tests. Consumer expectations must be executable.
- **Integration Tests:** Component-to-component integration should be tested separately from unit behavior.
- **No Duplication:** Do not test the same component behavior at every pyramid layer. Test details in unit tests, contracts at boundaries, journeys in E2E.
- **Sociable Tests:** Use real AvaX components where they remain fast. Use fakes only for slow/external dependencies.

A component that is dogfooded without contract tests is a ticking integration bomb.

---

## 29. Final Law

AvaX must use AvaX.

If a component exists but the framework bypasses it, the component is decorative.

Decorative architecture is not enterprise-grade.

The framework becomes real when its components become the muscles of the runtime.
