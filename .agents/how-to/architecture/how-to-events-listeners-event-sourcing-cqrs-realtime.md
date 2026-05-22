# How To Use Events, Listeners, Event Sourcing, CQRS & Realtime Architecture In AvaX

## Status

Normative / AvaX Governance

## Scope

AvaX framework, components, examples, tests, docs, evidence, and future event-driven features.

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

This document defines how AvaX uses events, listeners, event-driven reactions, event sourcing, CQRS, projections,
outbox/inbox, sagas, realtime delivery, and event governance.

It complements `how-to-use-advanced-architecture-patterns.md`, which covers the generic pattern mechanics. This document
covers the AvaX-specific model: how events work in AvaX, what the fluent DSL looks like, how listeners are compiled, how
realtime delivery fits, how event observability works, and how AvaX governance itself uses event-sourced thinking.

Core philosophy:

```text
Events are facts.
Listeners are reactions.
Commands request change.
Queries ask for state.
Projections create read models.
Event sourcing makes event history the source of truth only where justified.
Realtime delivers selected facts to live clients.
Evidence is the governance event log.
CURRENT_TRUTH is a projection.
```

This document does not give permission to create event sourcing everywhere.

It defines when events help and when they hurt.

---

## 2. Core Vocabulary

Each definition is short and practical.

| Term                           | Definition                                                                                       |
|--------------------------------|--------------------------------------------------------------------------------------------------|
| **Command**                    | A request to change state. Imperative action. `RegisterUser`, `ProcessPayment`.                  |
| **Event**                      | A fact that already happened. Past-tense. `UserRegistered`, `PaymentProcessed`.                  |
| **Listener**                   | A concrete reaction to an event. Invokable class. `SendWelcomeEmail`, `CreateUserProjection`.    |
| **Dispatcher**                 | The mechanism that delivers an event to its listeners.                                           |
| **Emitter**                    | The public surface that accepts an event and hands it to the dispatcher. `emit()`.               |
| **Listener Provider**          | The capability that knows which listeners belong to which event.                                 |
| **Listener Registry**          | The compiled registry of all event-to-listener mappings.                                         |
| **Compiled Listener Registry** | A frozen, hot-path-safe listener map built at boot, not at dispatch.                             |
| **Domain Event**               | A fact that happened inside a bounded context. Internal. `OrderPaid`.                            |
| **System Event**               | A framework-level fact. `ContainerCompiled`, `ListenerRegistryBuilt`.                            |
| **Integration Event**          | A fact published across a system boundary. Versioned, stable schema. `UserRegisteredForBilling`. |
| **Telemetry Event**            | A fact recorded for observability. `QueryExecuted`, `SessionStarted`.                            |
| **Lifecycle Event**            | A fact about object or transaction lifecycle. `EntityCreated`, `TransactionCommitted`.           |
| **Database Lifecycle Event**   | A lifecycle event tied to database operations. `BeforeSave`, `AfterCommit`.                      |
| **Event Store**                | An append-only store of domain events. Used in event sourcing.                                   |
| **Event Stream**               | A sequence of events for one aggregate or entity identity.                                       |
| **Stored Event**               | A persisted event with stream identity, version, and serialized payload.                         |
| **Projection**                 | A capability that turns events into read-optimized state.                                        |
| **Read Model**                 | A data shape optimized for reading. Not a domain model. `UserProfileView`.                       |
| **Outbox**                     | A durable queue of events stored in the same transaction, published after commit.                |
| **Inbox**                      | A deduplication and processing mechanism for incoming external messages.                         |
| **Saga**                       | A long-running workflow coordinated by events and commands across multiple steps.                |
| **Process Manager**            | Same as saga: coordinates multi-step workflows with explicit state.                              |
| **Pub/Sub**                    | Publish/subscribe messaging. Publishers send messages, subscribers receive them.                 |
| **Queue**                      | An asynchronous message buffer. Jobs are consumed by workers.                                    |
| **Realtime Event**             | A fact delivered to a live client via WebSocket, SSE, or stream.                                 |
| **WebSocket Message**          | A bidirectional realtime message over a persistent connection.                                   |
| **SSE Message**                | A server-to-client event over a unidirectional HTTP stream.                                      |
| **Event Replay**               | Re-processing stored events to rebuild state or projections.                                     |
| **Event Upcasting**            | Transforming old event payloads to match new schemas during replay.                              |
| **Snapshot**                   | A captured state at a point in time, used to speed up replay.                                    |
| **Current Truth Projection**   | CURRENT_TRUTH.md as a projection of governance evidence events.                                  |

---

## 3. Command vs Event vs Listener

### Rules

- A **command** asks the system to do something.
- An **event** states that something already happened.
- A **listener** reacts to an event.
- Event names **SHOULD** be past-tense facts.
- Command names **SHOULD** be imperative actions.
- Listener names **SHOULD** be exact reactions.

### Good Events

```text
UserRegistered
OrderPaid
PaymentFailed
ContainerCompiled
ListenerRegistryBuilt
TransactionCommitted
QueryExecuted
SessionStarted
```

### Bad Events

```text
RegisterUser        // command, not event
PayOrder            // command, not event
SendEmail           // command, not event
BuildContainer      // command, not event
UserEvent           // too vague
PaymentMessage      // not a fact
```

### Good Listeners

```text
SendWelcomeEmail
RecordRegistrationAudit
CreateUserProjection
PublishOutboxMessages
NotifyAdminOfFailure
```

### Bad Listeners

```text
UserRegisteredHandler      // Handler is jargon, not exact reaction
EventProcessor              // too broad
UserEventManager            // Manager is forbidden
EventHelper                 // Helper is forbidden
```

### Example

```php
// Command: asks for change
RegisterUser

// Event: fact that happened
final readonly class UserRegistered
{
    public function __construct(
        public UserId $userId,
        public EmailAddress $email,
        public DateTimeImmutable $registeredAt,
    ) {
    }
}

// Listener: exact reaction
final readonly class SendWelcomeEmail
{
    public function __invoke(UserRegistered $event): void
    {
        // send welcome email to $event->email
    }
}
```

---

## 4. AvaX Event / Listener Public Model

### Rules

- Userland events **SHOULD** be plain readonly classes.
- Userland listeners **SHOULD** be concrete invokable classes.
- AvaX **MUST NOT** require every event to implement `EventInterface`.
- AvaX **MUST NOT** require every listener to implement `ListenerInterface`.
- Stoppable behavior **MAY** use `PSR\StoppableEventInterface` when PSR-14 is available.
- Infrastructure contracts belong around emitter, dispatcher, provider, registry, compiler, and adapters — not userland
  events.

### Event Example

```php
final readonly class UserRegistered
{
    public function __construct(
        public UserId $userId,
        public EmailAddress $email,
        public DateTimeImmutable $registeredAt,
    ) {
    }
}
```

### Listener Example

```php
final readonly class SendWelcomeEmail
{
    public function __invoke(UserRegistered $event): void
    {
        // send welcome email
    }
}
```

### Why No EventInterface

Forcing `EventInterface` on userland events adds ceremony without value.

A readonly object with typed constructor parameters is already a well-shaped event.

Interfaces belong to infrastructure: dispatcher, provider, registry, compiler.

The dispatcher receives any object. It does not require a marker interface.

If a userland event needs stoppable semantics, it **MAY** implement `PSR\StoppableEventInterface`.

That is a capability choice, not a requirement.

---

## 5. AvaX Fluent Events DSL

### Target API

```php
onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class)
    ->do(CreateUserProjection::class);

emit(new UserRegistered(
    userId: $userId,
    email: $email,
    registeredAt: $clock->now(),
));
```

### Rules

- `onEvent()` registers listeners for an event type.
- `emit()` dispatches an event object.
- `onEvent()` **MUST NOT** dispatch.
- `emit()` **MUST NOT** register.
- `emit(object $event)` is the primary API.
- `class-string` or array/DTO emission **MAY** exist later only for boundary/replay cases.
- Priority **MAY** be supported.
- Higher priority **SHOULD** run first.
- Same priority **SHOULD** preserve registration order.
- Listener return values **SHOULD** be ignored by default.
- Listener failure **SHOULD** bubble by default unless an explicit failure policy exists.

### Primary API Is Object Emission

```php
// Correct: emit event object
emit(new UserRegistered($userId, $email, $clock->now()));

// NOT the primary API:
emit(event: UserRegistered::class, with: ['userId' => $id, 'email' => $email]);
```

Class-string and array emission **MAY** exist later for replay or boundary cases, but it is not the primary API.

The primary API passes a real event object.

---

## 6. Attributes and Compiled Metadata

### Declaration

```php
#[ListensTo(UserRegistered::class)]
final readonly class SendWelcomeEmail
{
    public function __invoke(UserRegistered $event): void
    {
    }
}
```

### Rules

- Attributes are declarations, not runtime behavior.
- Runtime **MUST NOT** scan attributes through reflection in the hot path.
- Attribute scanning belongs to compile/build phase.
- DSL declarations and attributes **MUST** compile into one listener registry.
- Decorative attributes are forbidden.
- If an attribute exists but runtime ignores it, that is **RED**.
- If an attribute is compiled but not tested, that is **YELLOW**.

### Compilation Discipline

Attributes are scanned once during boot.

The result is a compiled listener registry.

Dispatch uses the compiled registry, not reflection.

This is a hot-path rule.

See `how-to-modern-php-attributes-di.md` for compiled metadata discipline.

---

## 7. Event-Driven Architecture Rules

### When to Use Events

Use events for:

```text
- secondary reactions
- audit trails
- projections
- notification
- cache invalidation
- metrics and tracing
- cross-context notification
- outbox publication
- lifecycle observation
```

### When NOT to Use Events

Do not use events for:

```text
- hidden mandatory use-case logic
- replacing clear application flow
- core validation
- business invariants that must happen before success
- code paths where order must be obvious in the use case
```

### The Listener Removal Rule

If removing a listener breaks the main use case, that logic probably does not belong in a listener.

A listener is a secondary reaction.

If the use case cannot complete without it, the logic belongs in the flow, not in a listener.

### Example: Good vs Bad

Good: user registration completes, then listeners send email and record audit.

Bad: user registration emits `UserValidated` event, and the flow waits for a listener to return validation result.

The second case hides mandatory logic behind an event.

That is wrong.

---

## 8. Dogfooding Rules

### Rules

- AvaX event system **MUST** be dogfooded before full GREEN.
- At least one real/reference flow **SHOULD** emit a real event.
- At least one real listener **SHOULD** react.
- Tests **MUST** prove listener execution.
- Tests **SHOULD** prove behavior changes when listener is not registered.
- Dogfooding should start in reference app before core runtime.
- Core runtime dogfooding must avoid dependency cycles.

### Recommended Dogfooding Candidates

```text
SecureRegistrationApi emits UserRegistered → listener records audit/projection
ContainerCompiled
ServiceResolutionFailed
SessionStarted
QueryExecuted
FailureReported
```

### Container Warning

Do not emit events inside critical container hot path by default.

Diagnostic-only events are allowed.

Avoid `EventDispatcher → Container → EventDispatcher` cycles.

---

## 9. Event Sourcing

### What It Is

Event sourcing means event history is the source of truth for aggregate state.

It is not the same as event/listener.

Event/listener is a dispatch pattern.

Event sourcing is a persistence model.

### Rules

- Event sourcing **MUST NOT** be the default persistence model.
- Event sourcing **MAY** be used when history, replay, audit, projections, or causality are core value.
- Event sourcing **MUST NOT** be used for simple CRUD by default.
- Event sourcing requires: event store, stream identity, versioning, serialization, upcasting, snapshot/replay strategy,
  and concurrency control.

### Use Event Sourcing For

```text
- payments
- orders
- workflows
- sagas
- compliance/audit-critical state
- state transitions where history matters
- systems requiring replayable projections
```

### Avoid Event Sourcing For

```text
- simple profile CRUD
- settings
- lookup tables
- admin metadata
- trivial records
```

### Required Event Sourcing Concepts

```text
EventStore
StoredEvent
EventStream
StreamName
StreamVersion
ExpectedVersion
OptimisticConcurrency
EventSerializer
EventUpcaster
Snapshot
ProjectionRunner
```

### Conceptual Example (Future Capability)

```php
// Event sourcing is a future capability in AvaX.
// This is conceptual, not implemented.

final readonly class OrderPlaced
{
    public function __construct(
        public OrderId $orderId,
        public CustomerId $customerId,
        public Money $total,
        public DateTimeImmutable $placedAt,
    ) {
    }
}

$store->append(new EventStream(
    streamName: StreamName::fromString("order-123"),
    expectedVersion: ExpectedVersion::fromInt(5),
    events: [new OrderPlaced($orderId, $customerId, $total, $clock->now())],
));
```

Event sourcing requires significant infrastructure.

It is not available in V5.7.

It is planned for V6.x.

When implemented, it must be documented honestly.

Do not claim event sourcing capability without a real event store, versioning, and replay.

---

## 10. CQRS

### Definition

CQRS separates write intent from read models when they have different reasons to change.

See `how-to-use-advanced-architecture-patterns.md` section 13 for detailed CQRS rules.

### AvaX-Specific Rules

- CQRS **MUST NOT** become a default folder style.
- CQRS **SHOULD** be used when write side and read side have different models, performance needs, or complexity.
- Commands mutate state.
- Queries read state.
- Events can feed projections.
- CQRS pairs naturally with event sourcing but does not require event sourcing.
- Event sourcing often benefits from CQRS but CQRS can exist without event sourcing.

### Good Use Cases

```text
- registration writes user state and projects RegisteredUserView
- order lifecycle writes aggregate and projects order dashboard
- audit-heavy workflows
```

### Bad Use Cases

```text
- simple CRUD module with no read/write difference
- fake Commands/Queries folders created for style
```

### AvaX Structure Rule

Do not create generic top-level `CQRS/` folder by default.

Place command/query/read-model logic inside the owning flow/capability.

The AvaX architecture law still wins:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

---

## 11. Projections and Read Models

### Definition

A projection turns events into read-optimized state.

### Rules

- Projection listeners **MUST** be idempotent where replay is possible.
- Projection state **MUST** be rebuildable if event source supports replay.
- Projection failures **MUST** be observable.
- Projection versions **SHOULD** be tracked.
- Read models **SHOULD** be named for how they are read, not how they are stored.

### Examples

```text
UserRegistered → RegisteredUserView
OrderPaid → RevenueProjection
PaymentFailed → PaymentFailureReport
```

### Placement

Projections belong inside the capability that owns the read use case:

```text
Capabilities/
  UserProfiles/
    ReadUserProfile.php
    UserProfileView.php
    ProjectUserProfile.php
```

Not:

```text
Projections/
  UserProjection.php
```

The folder says the capability, not the pattern.

---

## 12. Outbox / Inbox

### Outbox

Store integration events inside the same transaction, publish after commit.

### Rules

- External side effects **SHOULD** happen after commit.
- Do not send email, webhook, or queue message before DB commit if rollback is possible.
- Outbox publisher **MUST** be retryable.
- Outbox messages **MUST** be idempotent.
- Published events **MUST** have stable identifiers.
- Inbox **MUST** deduplicate incoming integration messages.

### Outbox Lifecycle Events

```text
OutboxStored
OutboxPublished
OutboxFailed
```

### Inbox Lifecycle Events

```text
InboxMessageReceived
InboxMessageDeduplicated
```

### Example

```php
// Inside the same DB transaction as the main state change:
$outbox->store(new IntegrationEvent(
    id: Uuid::v7(),
    type: 'user.registered',
    payload: $userData,
    occurredAt: $clock->now(),
));

// After commit (via transaction lifecycle event or explicit call):
$outboxRelay->publishPending();
```

The outbox is more important than event sourcing for most applications.

It guarantees that durable state changes and external publication happen together.

Without outbox, this failure is common:

```text
database saved → event publish failed → external systems never learn
```

---

## 13. Sagas and Process Managers

### Definition

A saga/process manager coordinates long-running workflows across multiple events and commands.

### Rules

- Use saga/process manager when workflow spans multiple steps, services, retries, or compensation.
- Do not hide simple sequential use cases inside saga.
- Saga state must be explicit.
- Compensation must be defined when side effects can fail.
- Saga events must be observable.
- Timeouts in saga must be explicit and scoped.

### Example

```text
OrderPlaced → ReserveStock → ChargePayment → ShipOrder
PaymentFailed → ReleaseStock → NotifyUser
```

### Saga State

A saga owns explicit state:

```text
Saga: OrderFulfillment
State: WaitingForPayment
Events: [OrderPlaced, StockReserved]
Next: ChargePayment
Compensation: ReleaseStock, NotifyUser
```

### Placement

Sagas belong inside the flow or capability that owns the workflow:

```text
Flows/
  FulfillOrder/
    FulfillOrder.php
    OrderFulfillmentSaga.php
    CompensateFailedFulfillment.php
```

Not:

```text
Sagas/
  OrderSaga.php
```

The folder says the workflow, not the pattern.

---

## 14. Realtime and Live Delivery

### Definition

Realtime delivery sends selected system facts to live clients or subscribers.

Covers: WebSockets, Server-Sent Events, streams, pub/sub, live projections, broadcast channels.

### Rules

- Realtime message is not automatically a domain event.
- Domain event **MAY** be translated into realtime message.
- Realtime delivery **MUST NOT** expose sensitive internal event payloads.
- Realtime messages **SHOULD** use DTOs/view models.
- Realtime channels **MUST** be authorized.
- Backpressure **MUST** be considered.
- Disconnect/reconnect behavior **MUST** be defined.
- Ordering guarantees **MUST** be explicit.
- Delivery semantics **MUST** be documented:
    - at-most-once
    - at-least-once
    - exactly-once claim is forbidden unless proven

### Examples

```text
UserRegistered domain event → AdminDashboardUserRegistered realtime message
OrderPaid → MerchantDashboardOrderPaid
JobProgressUpdated → SSE progress update
```

### Realtime Message Translation

```php
// Domain event: rich, internal
final readonly class UserRegistered
{
    public function __construct(
        public UserId $userId,
        public EmailAddress $email,
        public string $hash,        // sensitive
        public array $permissions,  // internal
    ) {
    }
}

// Realtime message: filtered, client-safe
final readonly class AdminDashboardUserRegistered
{
    public function __construct(
        public string $userId,
        public string $emailPrefix,  // "user***@example.com"
        public DateTimeImmutable $registeredAt,
    ) {
    }
}
```

Never broadcast raw domain events to clients.

---

## 15. Pub/Sub, Queues, and Async Event Handling

### Rules

- In-process event dispatch is not the same as queue delivery.
- Queue delivery is async and failure-prone.
- Queued listeners **MUST** define retry, timeout, dead-letter, idempotency, and serialization.
- Async listener support **MUST NOT** be claimed until runtime/queue support is real.
- Listener execution mode **MUST** be explicit.
- Sync is default until async/queue is implemented.

### Allowed Execution Modes

```text
Sync now          — listener runs in the same process, same call stack
Queued later      — listener is pushed to a queue for async processing
Async later       — listener runs via async runtime (Fiber/ReactPHP/Amp)
AfterCommit later — listener runs after the current transaction commits
```

### Rule: Do Not Expose Inactive Modes

If queued listener support exists but no real queue driver is implemented, do not document it as production-ready.

Mark it honestly:

```text
queued listener support: ROADMAP — no driver implemented
```

---

## 16. Event Versioning and Upcasting

### Rules

- Stored/integration events **MUST** have schema version.
- Event payload changes **MUST** be versioned.
- Breaking payload changes require upcaster or new event type.
- Event names should be stable.
- Do not remove fields from stored events without migration/upcast strategy.
- Event replay must know how to read old events.

### Concepts

```text
EventVersion
EventSchema
EventUpcaster
EventName
EventTypeMap
```

### Versioning Example

```php
// v1
final readonly class UserRegisteredV1
{
    public function __construct(
        public string $name,
        public string $email,
    ) {
    }
}

// v2 — added userId type
final readonly class UserRegisteredV2
{
    public function __construct(
        public UserId $userId,
        public string $name,
        public string $email,
    ) {
    }
}

// Upcaster transforms v1 → v2 during replay
final readonly class UserRegisteredV1ToV2Upcaster
{
    public function upcast(UserRegisteredV1 $event): UserRegisteredV2
    {
        return new UserRegisteredV2(
            userId: UserId::fromHash(hash('xxh128', $event->email)),
            name: $event->name,
            email: $event->email,
        );
    }
}
```

---

## 17. Event Observability

### Rules

- Event dispatch **SHOULD** be observable.
- At minimum, event type, listener count, listener failure, and duration **SHOULD** be measurable.
- Correlation ID / trace ID **SHOULD** propagate where request context exists.
- Events must not log sensitive payloads.
- FailureBoundary integration **MAY** report failed listeners.
- Observability must not create circular dependencies.

### Recommended Observability Metrics

```text
event.dispatch.count
event.listener.count
event.listener.error
event.dispatch.duration
event.listener.duration
```

### Payload Redaction

Events that are logged or traced **MUST** redact sensitive fields:

```text
passwords
tokens
secrets
PII where applicable
financial data
```

Observability records what happened, not the full payload.

---

## 18. Failure and Retry Rules

### Rules

- Listener failure bubbles by default in sync dispatch.
- Continue-on-failure must be explicit.
- Retry must use canonical Resilience capability.
- DeadLetter must use canonical Queue/DeadLetter capability.
- FailureBoundary may wrap event dispatch at ingress or policy boundary.
- Never silently swallow listener failures.

### Failure Policies

| Policy                  | Behavior                                                             |
|-------------------------|----------------------------------------------------------------------|
| **Bubble**              | Listener failure propagates. Default for sync dispatch.              |
| **ReportAndContinue**   | Failure is recorded, remaining listeners continue. Must be explicit. |
| **RetryThenFail**       | Listener is retried using canonical Resilience policy, then fails.   |
| **RetryThenDeadLetter** | After retries, failed event goes to dead-letter queue.               |
| **Ignore**              | Forbidden unless justified for diagnostic-only listener.             |

### Rule: No Silent Swallow

A listener that fails must leave evidence.

Swallowing failure is worse than bubbling failure.

Bubbling failure stops the world and demands attention.

Swallowed failure creates silent data loss.

---

## 19. Database Lifecycle Events

### Target Model (V5.8 ROADMAP)

```php
onEntity(User::class)
    ->beforeSave(ValidateUser::class)
    ->afterCreate(SendWelcomeEmail::class)
    ->afterUpdate(RecordUserAuditTrail::class);

onTransaction()
    ->afterCommit(PublishOutboxMessages::class)
    ->afterRollback(ClearPendingDomainEvents::class);
```

### Rules

- Entity lifecycle events are not generic domain events by default.
- Transaction lifecycle events are critical for safe side effects.
- Bulk operations must have separate lifecycle.
- Do not fire per-entity events for bulk updates unless entities are actually loaded and policy says so.
- afterCommit is preferred for external side effects.

### AfterCommit Discipline

External side effects (email, webhook, queue, outbox publish) **SHOULD** happen after transaction commit.

Not before.

Not during.

After.

If the transaction rolls back, the side effect must not have happened.

### V5.8 Status

Database lifecycle events are **PLANNED / LOCKED**.

Not implemented.

Not production-ready.

Examples in this section are design targets, not working APIs.

---

## 20. Governance Event Sourcing

### Philosophy

AvaX governance uses event-sourced thinking for its own evidence trail.

```text
Evidence files are the event log.
CURRENT_TRUTH.md is a projection.
TODO.md is a projection.
ACTIVE.md is a projection.
Stage ledgers are projections/snapshots.
Superseded reports are old events with later correction events.
GREEN status must be produced by evidence, not opinion.
```

### Rules

- A stage completion is a governance event.
- A gate failure is a governance event.
- A truth reconciliation is a governance event.
- A report superseded marker is a governance event.
- No GREEN may exist without evidence.
- Do not delete old truth to hide history. Supersede it.
- Current truth must be explainable from evidence history.

### Governance Event Types

```text
StageStarted
StageCompleted
ValidationPassed
ValidationFailed
GateFailed
GateFixed
TruthReconciled
EvidenceWritten
ReportSuperseded
FeatureMarkedGreen
FeatureDeferred
```

### Core Principle

```text
Current truth is not a feeling.
Current truth is a projection of evidence.
```

### How It Works

When a stage completes:

```text
1. Validation commands run (event: ValidationPassed or ValidationFailed)
2. Evidence is written (event: EvidenceWritten)
3. Truth files are updated (projection update)
4. If a previous report was wrong, it is superseded (event: ReportSuperseded)
5. CURRENT_TRUTH.md reflects the latest projection
```

Do not delete old evidence.

Supersede it.

The evidence history must always reconcile to current truth.

---

## 21. Architecture Placement Rules

### Rules

- Events capability should have one canonical owner: `components/Operations/Events/`.
- Domain-specific event sources may exist, but they must not claim generic ownership.
- MessageBus/EventBus may be adapter-specific if documented.
- Database telemetry events may be event sources.
- Session events may be session lifecycle sources.
- Do not create multiple canonical dispatchers.
- Do not create generic top-level `EventSourcing` or `CQRS` folders unless they are real package/component roots.
- Keep event sourcing kit optional.

### Current Owner Convergence

V5.7-01 identified four event systems:

| System                                | Scope               | Decision                                         |
|---------------------------------------|---------------------|--------------------------------------------------|
| `Operations/Events`                   | General-purpose     | **CANONICAL OWNER**                              |
| `Operations/MessageBus/EventBus`      | MessageBus handlers | Keep as internal mechanism                       |
| `DataStack/Database/Telemetry/Events` | Database lifecycle  | Keep as internal, future: emit through canonical |
| `HTTP/Session/SessionEventBus`        | Session lifecycle   | Keep as internal                                 |

Only `Operations/Events/` owns the general-purpose event system.

The others are domain-specific sources that may emit through canonical events in the future.

### AvaX Architecture Law

```text
folder says flow or capability
unit says responsibility
function says exact action
public surface receives
flows execute
capabilities power
configuration assembles
foundation supports
```

---

## 22. Testing Rules

### Required Tests for Events/Listeners

```text
- listener is called when event is emitted
- multiple listeners are called for same event
- priority ordering works
- no-listener case returns event without error
- listener failure bubbles by default
- stoppable propagation works if supported
- attributes compile into registry if implemented
- runtime dispatch has no hot-path reflection
- dogfooding proves real adoption
```

### Required Tests for Event Sourcing (When Implemented)

```text
- append event to stream
- optimistic concurrency conflict is detected
- replay aggregate from events
- upcast old event
- rebuild projection from events
- idempotent projection handles duplicate events
- snapshot restore works if implemented
```

### Required Tests for Realtime (When Implemented)

```text
- channel authorization works
- message shape matches DTO
- no sensitive payload leak
- disconnect behavior is correct if supported
- backpressure behavior is correct if supported
```

### Test Quality Rules

- No `assertTrue(true)`.
- No class-exists-only tests.
- No fake-green event tests.
- Tests must prove behavior, not class existence.

---

## 23. Tooling and Gates

### Future Governance Gates

```text
check-events-canonical-owner.php
check-events-no-hot-path-reflection.php
check-events-attributes-compiled.php
check-events-dsl-adoption.php
check-psr14-interop.php
check-no-decorative-events.php
check-event-sourcing-not-default.php
check-outbox-aftercommit-discipline.php
check-realtime-sensitive-payloads.php
```

### Gate Rules

- Gates must catch fake GREEN.
- Gates must fail if event sourcing is claimed without EventStore/projections/replay.
- Gates must fail if async/queued listeners are documented as production without real queue/runtime support.
- Gates must fail if multiple canonical event owners exist.

### Current Gate Status

All event gates are **PLANNED / NOT IMPLEMENTED**.

They will be implemented as part of V5.7-10 (Tooling Gates).

---

## 24. Production Readiness Checklist

### Events/Listeners Production-Ready Only If

```text
[ ] canonical owner exists
[ ] DSL or registration is tested
[ ] runtime dispatch is tested
[ ] no hot-path reflection
[ ] no duplicate owner
[ ] docs are honest
[ ] failure behavior is defined
[ ] observability extension exists
[ ] dogfooding exists
```

### Event Sourcing Production-Ready Only If

```text
[ ] EventStore exists
[ ] stream versioning exists
[ ] optimistic concurrency exists
[ ] serializer exists
[ ] versioning/upcasting exists
[ ] projection runner exists
[ ] replay is tested
[ ] idempotency is handled
[ ] docs warn against default use
```

### Realtime Production-Ready Only If

```text
[ ] transport exists
[ ] auth exists
[ ] backpressure exists
[ ] reconnect behavior exists
[ ] message DTOs exist
[ ] sensitive payload filtering exists
[ ] observability exists
```

---

## 25. Anti-Patterns

### Forbidden Patterns

| Anti-Pattern                                                         | Why It Is Wrong                                                               |
|----------------------------------------------------------------------|-------------------------------------------------------------------------------|
| `EventInterface` marker for everything                               | Adds ceremony without value. Events are plain facts.                          |
| `ListenerInterface` that weakens concrete type safety                | Concrete invokable listeners are safer and clearer.                           |
| Using events to hide mandatory business logic                        | If removing a listener breaks the use case, the logic belongs in the flow.    |
| Emitting events before transaction commits for external side effects | Rollback makes the side effect a lie. Use afterCommit.                        |
| Claiming event sourcing while only storing logs                      | Event sourcing requires event store, versioning, replay, concurrency control. |
| Using event sourcing for CRUD by default                             | Event sourcing is rarely the first answer. See pattern decision rules.        |
| Duplicate EventBus/EventDispatcher owners                            | One canonical owner. See dogfooding rules.                                    |
| Runtime reflection scanning attributes                               | Attributes are compiled at boot, not scanned at dispatch.                     |
| Async listener options that do nothing                               | Do not expose inactive modes as production-ready.                             |
| "Exactly once" delivery claim without proof                          | Forbidden unless mathematically proven and tested.                            |
| Realtime broadcasting raw domain events                              | Sensitive payloads leak. Translate to client-safe DTOs.                       |
| Projections that are not idempotent                                  | Replay breaks. Projections must be idempotent.                                |
| Deleting old evidence instead of superseding it                      | Governance history is lost. Supersede, do not delete.                         |
| CQRS folder theater                                                  | `Commands/`, `Queries/` as folders is style, not substance.                   |

---

## 26. Decision Matrix

| Need                                         | Use                                              |
|----------------------------------------------|--------------------------------------------------|
| Secondary reaction after something happened  | Event + Listener                                 |
| Cross-context notification                   | Domain/Integration Event                         |
| Durable external publication after DB commit | Outbox                                           |
| Different read model than write model        | CQRS + Projection                                |
| History is source of truth                   | Event Sourcing                                   |
| Long-running workflow                        | Saga / Process Manager                           |
| Live browser/client update                   | Realtime DTO via WebSocket/SSE                   |
| Audit of framework development truth         | Evidence as Event Log + CURRENT_TRUTH projection |
| Simple CRUD                                  | Do not use event sourcing by default             |

---

## 27. Roadmap Alignment

### V5.7: Events Fluent DSL

```text
- Events Fluent DSL
- emit(new Event)
- #[ListensTo]
- compiled registry
- PSR-14 adapter
- event dogfooding
```

Status: **COMPLETE / GREEN** (as of 2026-05-13). All 14 sub-stages (V5.7-00 through V5.7-13) are implemented and
validated. See `CURRENT_TRUTH.md` for evidence.

### V5.8: Database Lifecycle Events

```text
- Database lifecycle events
- transaction afterCommit
- outbox groundwork
```

Status: **COMPLETE / GREEN** (as of 2026-05-13). All 14 sub-stages (V5.8-01 through V5.8-14) are implemented and
validated. See `CURRENT_TRUTH.md` for evidence.

### V5.9: Boot DSL

```text
- Boot DSL loads events/events.php
- compile registries at boot
```

Status: PLANNED / UNBLOCKED (as of 2026-05-15). See `CURRENT_TRUTH.md` for current stage lock.

### V6.x: Future Capabilities

```text
- projections
- event store
- event sourcing kit
- CQRS read models
- async/queued listeners
- realtime
- typed scenario DSL event assertions
- parallel test runtime
```

Status: ROADMAP. Not active production claims.

### Rule: Roadmap Items Are Not Production Claims

A feature listed in roadmap is not an active production capability.

Do not document roadmap items as if they work.

Mark them honestly:

```text
event sourcing: ROADMAP — not implemented
realtime: ROADMAP — not implemented
async listeners: ROADMAP — no queue driver implemented
```

---

## 28. Relationship to Other Governance Documents

This document works with:

| Document                                       | Relationship                                                                                                |
|------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| `how-to-use-advanced-architecture-patterns.md` | That document covers generic pattern mechanics. This covers AvaX-specific event model, DSL, and governance. |
| `how-to-design-components.md`                  | Events capability follows canonical component shape.                                                        |
| `how-to-dogfooding.md`                         | Event system must be dogfooded before GREEN.                                                                |
| `how-to-system-security.md`                    | Event payloads must be redacted. Realtime channels must be authorized.                                      |
| `how-to-system-performance.md`                 | No hot-path reflection. Compiled registry. No hidden I/O in listeners.                                      |
| `how-to-unit-test.md`                          | Event behavior must be tested, not class existence.                                                         |
| `how-to-modern-php-attributes-di.md`           | `#[ListensTo]` is compiled metadata, not runtime reflection.                                                |
| `how-to-production-readiness.md`               | Event system must pass production readiness gates.                                                          |
| `how-to-architecture.md`                       | Event folders follow screaming architecture law.                                                            |
| `how-to-code-review.md`                        | Event code review must check all applicable how-to rules.                                                   |

---

## 29. Final Law

Events are facts.

Listeners are reactions.

The DSL is the declaration language.

The compiled registry is the hot-path discipline.

Event sourcing is rare, not default.

CQRS is pressure-driven, not style-driven.

Realtime is filtered, not raw.

Outbox is more important than event sourcing.

AfterCommit is the rule, not the exception.

Evidence is the governance event log.

Current truth is a projection.

```text
folder says flow or capability
unit says responsibility
function says exact action
```

No event sourcing without event store.

No realtime without auth.

No async without queue.

No GREEN without evidence.
