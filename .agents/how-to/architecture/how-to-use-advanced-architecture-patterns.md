# How to Use Advanced Architecture Patterns

## 1. Status of This Document

This document is part of the The architecture governance.

It defines when and how advanced architecture patterns may be used.

This document covers:

- CQRS
- Event Sourcing
- Domain Events
- Integration Events
- Outbox
- Inbox
- Saga
- Idempotency
- State Machines
- Projections
- Read Models
- Policies
- Specifications
- Failure Models
- Architecture Decision Records
- Architecture Fitness Tests
- Compatibility Checks

This document is not a pattern catalog.

This document is not a permission slip to make the architecture more complex.

This document is not a justification for folders such as:

```text
Commands/
Queries/
Handlers/
Adapters/
Services/
Managers/
Processors/
CQRS/
EventSourcing/
Sagas/
Policies/
Specifications/
```

Advanced patterns are allowed only when they reduce real pressure.

They are forbidden when they create architecture theater.

The core The architecture law still wins:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

Advanced patterns are design responses.

They are not default structure.

---

## 2. Operational Summary

Advanced architectural patterns are not status symbols.

Use them only when they make the system safer, clearer, more recoverable, or easier to evolve.

Allowed reasons:

```text
protect invariants
make state transitions explicit
make retries safe
make integration reliable
make history truthful
separate complex reads from complex writes
support replay
support auditability
protect public API compatibility
make failure behavior explicit
make architecture rules testable
```

Forbidden reasons:

```text
because it looks enterprise
because DDD examples use it
because other frameworks use it
because the system might need it someday
because it sounds more professional
because a simple flow looks too boring
```

Pattern rule:

```text
Use the smallest pattern that solves the pressure.
```

Recommended order:

```text
1. Better naming
2. Explicit invariant
3. Explicit state transition
4. Idempotency
5. Domain event
6. Outbox / Inbox
7. Read model / projection
8. CQRS
9. Saga
10. Event Sourcing
```

Event Sourcing is rarely the first answer.

Outbox is often more useful than Event Sourcing.

State machines are often more useful than CQRS.

Idempotency is usually more important than architectural purity.

---

## 3. Main Rule

```text
Advanced patterns are allowed only when they make ownership clearer,
failure safer, history more truthful, state transitions more explicit,
or change easier.
```

They must obey the core architecture law:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

A pattern must never become the folder language unless the pattern itself is the real domain capability.

Bad:

```text
System/
  CQRS/
    Commands/
    Queries/

  EventSourcing/
    Events/
    Projectors/

  Sagas/
    PaymentSaga.php
```

Good:

```text
System/
  Flows/
    RegisterUser/
      RegisterUser.php
      UserRegistered.php

    ProcessPayment/
      ProcessPayment.php
      PaymentProcessed.php

  Capabilities/
    UserProfiles/
      ReadUserProfile.php
      UserProfileView.php

    ReliableEventPublishing/
      StorePendingEvent.php
      PublishPendingEvent.php
      MarkEventAsPublished.php

    PaymentRecovery/
      CompensateFailedPayment.php
      PaymentCompensationPlan.php
```

The good version tells the reader what the system does.

The bad version tells the reader which pattern the author wanted to use.

---

## 4. Naming Rules for Advanced Patterns

### 4.1 Do Not Use Pattern Names as Default Folders

Avoid these as broad folders:

```text
Commands/
Queries/
Handlers/
Adapters/
Sagas/
Policies/
Specifications/
Projectors/
Processors/
Services/
Events/
```

They sort code by technical shape.

They do not explain ownership.

Use flow and capability names instead.

Bad:

```text
Capabilities/
  Commands/
    RegisterUserCommand.php

  Queries/
    GetUserProfileQuery.php

  Handlers/
    RegisterUserHandler.php
```

Good:

```text
Flows/
  RegisterUser/
    RegisterUser.php
    RegistrationInput.php
    UserRegistered.php

Capabilities/
  UserProfiles/
    ReadUserProfile.php
    UserProfileView.php
```

### 4.2 Command Naming Rule

The word `Command` is often too technical.

Do not use `Commands/` as a folder.

Do not create generic `Command.php`.

Prefer exact action names.

Bad:

```text
Commands/
  RegisterUserCommand.php

Handlers/
  RegisterUserHandler.php
```

Good:

```text
Flows/
  RegisterUser/
    RegisterUser.php
    RegistrationInput.php
```

Allowed narrow use:

```text
Operations/
  MessageBus/
    System/
      PublicSurface/
        Command.php
```

Only if `Command` is the actual public language of a message bus.

Even then, concrete messages must use exact names:

```text
RegisterUser
ApproveRelease
PublishPendingEvent
```

Not:

```text
GenericCommand
BaseCommand
CommandData
```

### 4.3 Query Naming Rule

The word `Query` is allowed only when it is real user-facing or system-facing language.

Do not use `Queries/` as a generic folder.

Bad:

```text
Queries/
  GetUserQuery.php
```

Good:

```text
Capabilities/
  UserProfiles/
    ReadUserProfile.php
    ListVisibleUserProfiles.php
```

Allowed:

```text
components/
  DataStack/
    Database/
      System/
        PublicSurface/
          Query.php
```

Because database query is a real framework concept.

### 4.4 Adapter Naming Rule

Avoid generic `Adapters/`.

The word adapter is usually architecture jargon, not domain language.

Prefer boundary-specific names.

Bad:

```text
Capabilities/
  Adapters/
    S3Adapter.php
    RedisAdapter.php
    StripeAdapter.php
```

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

A folder should explain the boundary or capability.

If a generic adapter folder appears, it must be justified by an active governance exception.

### 4.5 Handler Naming Rule

Avoid `Handler` as default.

`Handler` often hides the real action.

Bad:

```text
RegisterUserHandler
PaymentHandler
EventHandler
```

Good:

```text
RegisterUser
ProcessPayment
PublishPendingEvent
NotifyBillingAfterPaymentFailed
```

`Handler` may be used only when the domain language is actually about handling a generic protocol message.

Even then, prefer more exact names when possible.

### 4.6 Service Naming Rule

Avoid `Service` unless the domain itself uses the word and the responsibility is exact.

Bad:

```text
PaymentService
UserService
CompatibilityService
```

Good:

```text
ProcessPayment
CreatePaymentIntent
DetectBreakingPublicApiChange
CalculateReleaseReadiness
```

### 4.7 Policy Naming Rule

Policy is allowed when it means a named decision rule.

Do not use `Policies/` as a dumping ground.

Bad:

```text
Policies/
  UserPolicy.php
  PaymentPolicy.php
```

Good:

```text
Capabilities/
  ReleaseReadiness/
    ReleaseApprovalPolicy.php
    VerifyReleaseApprovalPolicy.php

Capabilities/
  PublicApiCompatibility/
    DeprecationPolicy.php
    EvaluateDeprecationPolicy.php
```

### 4.8 Specification Naming Rule

Specification is allowed only when it represents a reusable business rule that can be evaluated.

Do not use it for every condition.

Bad:

```text
Specifications/
  UserSpecification.php
```

Good:

```text
Capabilities/
  ReleaseReadiness/
    ReleaseCanBeApproved.php

Capabilities/
  PublicApiCompatibility/
    PublicApiChangeIsBreaking.php
```

Use simple predicates when simple predicates are enough.

### 4.9 Event Naming Rule

Domain events must be past-tense facts.

Good:

```text
UserRegistered
PaymentProcessed
ReleaseApprovedForProduction
BreakingPublicApiChangeDetected
```

Bad:

```text
UserEvent
PaymentMessage
DoPayment
ProcessUser
UpdateEvent
```

An event records what happened.

It must not ask for work.

---

## 5. Pattern Decision Rule

Every advanced pattern requires a short decision record before it is considered accepted.

Use this template:

```md
# ADR-0000-use-<pattern>-for-<capability>

## Decision

<What pattern is being used and where.>

## Pressure

<What real pressure requires this pattern?>

## Simpler Option Rejected

<Why simple flow, direct persistence, domain event, audit log, or direct query is not enough.>

## Ownership

<Which flow or capability owns this?>

## Consistency

<What must remain consistent?>

## Failure

<What happens when it fails halfway?>

## Idempotency

<What happens if this runs twice?>

## Proof

<Which test proves this pattern is needed and works?>

## Revisit Condition

<When should we remove or simplify this?>
```

No ADR means the advanced pattern is not green.

It may be experimental.

It may be yellow.

It must not be treated as production-ready.

---

## 6. Invariant-First Design

Before using any advanced pattern, ask:

```text
What must always remain true?
```

Examples:

```text
A completed component cannot be marked production-ready without tests and validation evidence.
A deprecated public API cannot be removed without a migration path.
A paid invoice cannot become unpaid without an explicit reversal.
A migration must not run twice.
A webhook must not create the same payment twice.
A published integration event must not disappear after the database transaction commits.
```

Invariants are more important than pattern names.

If there is no invariant, lifecycle, failure risk, or consistency rule, an advanced pattern is probably unnecessary.

### 6.1 Invariant Placement

Put invariants near the flow or capability that owns them.

Good:

```text
Capabilities/
  ReleaseReadiness/
    ReleaseCandidate.php
    ApproveReleaseForProduction.php
    ReleaseCannotBeApproved.php
```

Bad:

```text
Domain/
  Rules/
    Rules.php
```

### 6.2 Invariant Proof

Every serious invariant needs a test.

Example:

```text
test_release_cannot_be_approved_when_static_analysis_is_red
test_migration_does_not_run_twice
test_payment_webhook_is_idempotent
```

No test means no green claim.

---

## 7. State Machine Rule

Use a state machine when a concept has a real lifecycle.

A lifecycle exists when the same thing moves through named states and not every transition is allowed.

Examples:

```text
Draft -> Reviewed -> Deprecated -> Removed
Pending -> Running -> Completed -> Failed
Unverified -> Verified -> ProductionReady
Open -> Paid -> Refunded
Queued -> Processing -> Published -> Failed -> DeadLettered
```

### 7.1 When to Use a State Machine

Use a state machine when:

```text
states are named in the domain
transitions have rules
invalid transitions are dangerous
events depend on transitions
users or systems care about lifecycle history
```

Do not use a state machine when:

```text
there are only two trivial states
there is no invalid transition
there is no lifecycle behavior
a boolean is honest and enough
```

### 7.2 State Machine Naming

Do not create a generic `StateMachine/` folder by default.

Bad:

```text
Capabilities/
  StateMachine/
    StateMachine.php
    Transition.php
```

Good:

```text
Capabilities/
  ReleaseReadiness/
    ReleaseState.php
    ApproveReleaseForProduction.php
    RejectReleaseForProduction.php

Capabilities/
  EventPublishing/
    EventPublicationState.php
    MarkEventAsPublished.php
    MarkEventAsFailed.php
```

The folder says the lifecycle owner.

The units say the domain state and transitions.

### 7.3 State Transition Table

Every non-trivial lifecycle must document allowed transitions.

Example:

```md
# Release Readiness State Transitions

| From | Action | To |
|---|---|---|
| Draft | verify | Verified |
| Verified | approveForProduction | Approved |
| Verified | rejectForProduction | Rejected |
| Approved | deprecate | Deprecated |
| Deprecated | remove | Removed |
```

### 7.4 State Machine Proof

Required tests:

```text
valid transition succeeds
invalid transition fails
transition emits expected domain event when needed
state cannot be mutated directly
```

---

## 8. Idempotency Rule

Idempotency answers:

```text
What happens if this action runs twice?
```

Use idempotency for flows that may be retried, duplicated, or triggered from outside.

Examples:

```text
HandleWebhook
ProcessPayment
PublishPendingEvent
RunMigration
VerifyReleaseReadiness
ProcessImport
SendNotification
ConsumeQueueMessage
```

### 8.1 When Idempotency Is Required

Idempotency is required when:

```text
the same external request may be delivered more than once
a job may retry after failure
a queue consumer may crash after partial work
a payment or external side effect is involved
a migration or import may be re-run
an operation publishes messages
```

### 8.2 Naming

Avoid generic `Idempotency/` as a dumping ground unless the component itself owns idempotency as a platform capability.

Bad:

```text
Capabilities/
  Idempotency/
    IdempotencyManager.php
```

Good:

```text
Capabilities/
  WebhookProcessing/
    WebhookIdempotencyKey.php
    RememberProcessedWebhook.php
    HasWebhookAlreadyBeenProcessed.php

Capabilities/
  MigrationExecution/
    HasMigrationAlreadyRun.php
    RecordExecutedMigration.php
```

### 8.3 Idempotency Proof

Required tests:

```text
first run performs work
second run does not duplicate side effects
same idempotency key returns same safe result
different idempotency key is treated independently
partial failure is handled explicitly
```

---

## 9. Domain Events

A Domain Event is a fact that happened inside the domain.

Use it when another part of the same bounded context or internal system needs to react to a meaningful fact.

Examples:

```text
UserRegistered
PaymentProcessed
ReleaseApprovedForProduction
BreakingPublicApiChangeDetected
MigrationExecuted
```

### 9.1 Domain Event Rule

A domain event is not a command.

Bad:

```text
SendEmail
CreateInvoice
ProcessPayment
```

Good:

```text
EmailRequested
InvoiceCreated
PaymentProcessed
```

### 9.2 Placement

Place the event near the flow or capability that emits it.

Good:

```text
Flows/
  ApproveReleaseForProduction/
    ApproveReleaseForProduction.php
    ReleaseApprovedForProduction.php
```

Bad:

```text
Domain/
  Events/
    ReleaseEvent.php
```

### 9.3 Domain Event Proof

Required tests:

```text
domain action records event
event is past-tense fact
event contains enough useful data
event does not expose infrastructure details
```

---

## 10. Integration Events

An Integration Event is a message published across a system boundary.

It is not automatically the same as a domain event.

A domain event may be translated into an integration event.

That translation must be explicit.

### 10.1 When Integration Events Are Needed

Use integration events when:

```text
another service or external system must be notified
the message crosses a bounded context or system boundary
compatibility and versioning matter
delivery must be observable
failure must be recoverable
```

### 10.2 Naming

Integration events must still be past-tense facts.

Good:

```text
PaymentProcessedForBilling
UserRegisteredForAnalytics
PublicApiDeprecatedForSubscribers
```

Avoid vague names:

```text
Message
EventData
IntegrationMessage
```

### 10.3 Compatibility

If an event crosses a boundary, it is public API.

It must have:

```text
stable schema
versioning rule
compatibility rule
deprecation rule
consumer expectation
```

---

## 11. Outbox Rule

Use Outbox when a state change and event publishing must be reliable together.

Without Outbox, this failure is common:

```text
database saved
event publish failed
external systems never learn about the change
```

Outbox makes the event part of the same durable change.

### 11.1 When Outbox Is Needed

Use Outbox when:

```text
domain state changes and integration event must both happen
publishing can fail independently
message delivery must be retried
events must not be lost
external consistency matters
```

Do not use Outbox when:

```text
the event is purely in-memory
no external delivery is needed
lost notification is acceptable
there is no durable state change
```

### 11.2 Naming

Do not create a generic `Outbox/` folder unless the component is explicitly a messaging platform.

Better:

```text
Capabilities/
  ReliableEventPublishing/
    StorePendingEvent.php
    ReadPendingEvents.php
    PublishPendingEvent.php
    MarkEventAsPublished.php
    MarkEventAsFailed.php
```

If `Outbox` is the known user-facing term, it may appear as a public or foundation concept:

```text
OutboxMessage
OutboxMessageId
OutboxPublicationState
```

But the folder should still say the capability.

### 11.3 Outbox Proof

Required tests:

```text
state change stores pending event
pending event is published later
published event is marked as published
failed publish is retried or marked failed
same event is not published twice
```

---

## 12. Inbox Rule

Use Inbox when consuming external messages must be safe against duplicates.

Inbox is the receiving side of integration reliability.

### 12.1 When Inbox Is Needed

Use Inbox when:

```text
external messages can be delivered more than once
consumer may crash halfway
message order or deduplication matters
side effects must not duplicate
```

### 12.2 Naming

Avoid generic `Inbox/` as a dumping ground.

Better:

```text
Capabilities/
  IncomingPaymentEvents/
    RememberReceivedPaymentEvent.php
    HasPaymentEventAlreadyBeenReceived.php
    MarkPaymentEventAsProcessed.php
```

### 12.3 Inbox Proof

Required tests:

```text
first message is processed
duplicate message is ignored or safely replayed
processing state is recorded
partial failure is recoverable
```

---

## 13. CQRS Rule

CQRS means command/write behavior and query/read behavior are separated because they have different reasons to change.

CQRS does not mean every application needs `Commands/` and `Queries/` folders.

CQRS is not a naming style.

CQRS is a response to pressure.

### 13.1 When CQRS Is Useful

Use CQRS when:

```text
write side has complex invariants
read side needs a different data shape
read performance needs denormalized views
same data is read far more than written
API response should not expose domain model
reporting/search/listing is heavy through the write model
read and write models change for different reasons
```

### 13.2 When CQRS Is Not Useful

Do not use CQRS when:

```text
CRUD is simple
same model serves read and write well
there are no complex invariants
there is no serious read pressure
you are splitting models only to look enterprise
```

### 13.3 Naming

Avoid `Commands/` and `Queries/`.

Bad:

```text
System/
  CQRS/
    Commands/
      RegisterUserCommand.php
    Queries/
      GetUserProfileQuery.php
```

Good:

```text
Flows/
  RegisterUser/
    RegisterUser.php
    RegistrationInput.php

Capabilities/
  UserProfiles/
    ReadUserProfile.php
    UserProfileView.php
```

If using a message bus where `Command` and `Query` are public concepts, keep them narrow and user-facing.

Do not let them become global architecture folders.

### 13.4 CQRS Proof

Required tests:

```text
write flow protects invariant
read model has shape needed by caller
read model can be rebuilt or updated
write model does not leak into public read API
query side does not mutate domain state
```

---

## 14. Read Model Rule

A Read Model is a data shape optimized for reading.

It is not automatically a domain model.

Use read models when callers need a shape different from the write model.

### 14.1 Naming

Good:

```text
UserProfileView
ReleaseReadinessSummary
PublicApiCompatibilityView
OrderHistoryView
```

Weak:

```text
UserQueryResult
DataView
ReadModel
Info
```

Name the read model by what the reader sees.

### 14.2 Placement

Place read models inside the capability that owns the read use case.

Good:

```text
Capabilities/
  UserProfiles/
    ReadUserProfile.php
    UserProfileView.php
```

Bad:

```text
ReadModels/
  UserReadModel.php
```

---

## 15. Projection Rule

A Projection builds or updates a read model from events or source data.

Use projections when a read model must be maintained separately from the write model.

### 15.1 Naming

Avoid generic `Projectors/`.

Good:

```text
Capabilities/
  ReleaseReadinessViews/
    UpdateReleaseReadinessView.php
    RebuildReleaseReadinessView.php
```

Weak:

```text
Projectors/
  ReleaseProjector.php
```

### 15.2 Projection Proof

Required tests:

```text
event updates view
duplicate event does not corrupt view
view can be rebuilt from source
projection lag can be observed when relevant
```

---

## 16. Saga Rule

A Saga coordinates a long-running workflow across multiple steps, systems, or transactions.

Use Saga only when a single transaction is not enough and compensating actions may be needed.

### 16.1 When Saga Is Needed

Use Saga when:

```text
workflow spans multiple systems
steps may fail independently
compensation is needed
there is no single transaction boundary
state must survive between steps
```

Do not use Saga when:

```text
a simple flow is enough
all work happens in one transaction
there is no compensation
there is no long-running state
```

### 16.2 Naming

Avoid generic `Sagas/`.

Bad:

```text
Sagas/
  PaymentSaga.php
```

Good:

```text
Flows/
  CompleteCheckout/
    CompleteCheckout.php
    CheckoutCompletionState.php
    CompensateReservedInventory.php
    CompensateAuthorizedPayment.php
```

If the word Saga is used, it must be a local implementation detail or documented public language.

### 16.3 Saga Proof

Required tests:

```text
happy path completes all steps
failure records state
compensation runs when needed
duplicate message does not duplicate compensation
workflow can resume after crash
```

---

## 17. Event Sourcing Rule

Event Sourcing means the event stream is the source of truth.

It does not mean merely using events.

It does not mean publishing events after changes.

It means current state is derived from stored events.

### 17.1 When Event Sourcing Is Useful

Use Event Sourcing when:

```text
history is more important than current state
auditability is mandatory
replay is required
read models must be rebuilt from domain history
debugging requires exact state evolution
business meaning lives in the sequence of changes
```

### 17.2 When Event Sourcing Is Not Useful

Do not use Event Sourcing when:

```text
you only need an audit log
you only need domain events
you only need integration events
you do not need replay
event schema versioning is not solved
the team cannot handle event evolution discipline
CRUD is enough
```

### 17.3 Naming

Avoid generic `EventSourcing/`.

Good:

```text
Capabilities/
  PaymentHistory/
    RecordPaymentEvent.php
    RebuildPaymentState.php
    PaymentEventStream.php
    PaymentRecorded.php
```

Bad:

```text
EventSourcing/
  Aggregate.php
  EventStore.php
  Projector.php
```

If `EventStore` is a real infrastructure concept, it may exist as a narrow unit.

The parent folder must still explain the capability.

### 17.4 Event Sourcing Required Rules

Event-sourced models must define:

```text
event stream identity
event ordering
event versioning
snapshot policy, if any
replay behavior
upcasting or migration strategy
read model rebuild strategy
idempotency behavior
event compatibility rule
```

### 17.5 Event Sourcing Proof

Required tests:

```text
state rebuilds from event stream
events are appended in order
old event version can be read or upcast
duplicate event is handled explicitly
read model can be rebuilt
invalid event sequence is rejected
```

No tests means no Event Sourcing.

---

## 18. Audit Log Rule

Audit Log is not Event Sourcing.

Audit log records what happened for inspection.

Event Sourcing uses events as the source of truth.

Use audit log when:

```text
you need traceability
you need compliance evidence
you need user/system action history
you do not need replay as truth
```

Name audit behavior clearly:

```text
Capabilities/
  AuditTrail/
    RecordAuditEntry.php
    ReadAuditTrail.php
    AuditEntry.php
```

Do not pretend audit log is Event Sourcing.

---

## 19. Failure Model Rule

Every serious integration, workflow, or runtime capability must define its failure model.

A failure model answers:

```text
What can fail?
How is failure detected?
How is failure represented?
Can the action be retried?
Can the action be compensated?
Is failure visible to operators?
What is the safe fallback?
What must never be hidden?
```

### 19.1 Placement

Place the failure model near the capability it protects.

Good:

```text
Capabilities/
  ReliableEventPublishing/
    EventPublicationFailure.php
    ClassifyEventPublicationFailure.php
```

Bad:

```text
Errors/
  Failure.php
```

### 19.2 Failure Proof

Required tests:

```text
known failure is classified
unknown failure is not swallowed
retryable failure is marked retryable
non-retryable failure is not retried forever
failure is observable
```

---

## 20. Retry Rule

Use Retry when a failure may be temporary.

Do not retry blindly.

Retry requires:

```text
maximum attempts
backoff strategy
retryable failure classification
non-retryable failure classification
observability
idempotency safety
```

Bad:

```text
while (true) retry
```

Good:

```text
Capabilities/
  EventPublicationRetry/
    ShouldRetryEventPublication.php
    CalculateNextRetryDelay.php
    RecordEventPublicationAttempt.php
```

Retry without idempotency can create duplicate side effects.

---

## 21. Timeout Rule

Every external call must have a timeout.

No timeout means the system can hang indefinitely.

Timeout must be explicit for:

```text
HTTP clients
message brokers
object storage
search indexes
payment gateways
email providers
AI providers
database calls where driver supports it
```

Naming:

```text
Capabilities/
  StripePaymentGateway/
    ChargeCardThroughStripe.php
    StripePaymentTimeout.php
```

Avoid:

```text
TimeoutService
TimeoutManager
```

---

## 22. Circuit Breaker Rule

Use Circuit Breaker when repeated failures to an external dependency would harm the system.

Circuit Breaker protects the caller from wasting resources on a dependency that is already failing.

Use it for:

```text
external HTTP services
payment gateways
search clusters
object storage
message brokers
AI providers
```

Do not use it for simple local function calls.

Naming:

```text
Capabilities/
  SearchIndexAvailability/
    OpenSearchIndexCircuit.php
    CloseSearchIndexCircuit.php
    CanCallSearchIndex.php
```

---

## 23. Bulkhead Rule

Use Bulkhead when one slow or failing dependency must not consume all system resources.

Examples:

```text
separate queue workers
separate connection pools
separate concurrency limits
separate rate limits
```

Naming:

```text
Capabilities/
  PaymentGatewayIsolation/
    LimitPaymentGatewayConcurrency.php
```

Do not create generic `Bulkheads/` folders.

---

## 24. Backpressure Rule

Use Backpressure when the system must slow input because downstream capacity is limited.

Examples:

```text
queue depth too high
projection lag too high
external dependency overloaded
worker pool saturated
memory pressure too high
```

Naming:

```text
Capabilities/
  QueueCapacity/
    RejectWorkWhenQueueIsFull.php
    DelayWorkWhenQueueIsBusy.php
```

Backpressure must be visible.

Silent dropping is not backpressure.

It is data loss.

---

## 25. Dead Letter Rule

Use Dead Letter when a message or job cannot be processed safely after allowed attempts.

Naming:

```text
Capabilities/
  FailedMessageRecovery/
    MoveMessageToDeadLetter.php
    ReadDeadLetterMessages.php
    RetryDeadLetterMessage.php
```

Do not hide poison messages.

Operators must be able to inspect them.

---

## 26. Policy Rule

A Policy is a named decision rule.

Use Policy when the same decision must be evaluated consistently.

Examples:

```text
ReleaseApprovalPolicy
DeprecationPolicy
RetryPolicy
PasswordPolicy
CacheEvictionPolicy
```

Do not use policy for every `if`.

Policy must answer:

```text
What decision does this rule make?
Which facts does it need?
What result does it produce?
Who depends on this decision?
```

Naming:

```text
Capabilities/
  ReleaseReadiness/
    ReleaseApprovalPolicy.php
    EvaluateReleaseApprovalPolicy.php
```

Avoid:

```text
Policies/
  Policy.php
  UserPolicy.php
```

---

## 27. Specification Rule

A Specification is a reusable condition with domain meaning.

Use it when a business condition must be composed, named, tested, or reused.

Examples:

```text
PublicApiChangeIsBreaking
ReleaseCanBeApproved
PaymentCanBeRefunded
UserCanAccessResource
```

Do not use Specification for simple private if-statements.

Placement:

```text
Capabilities/
  PublicApiCompatibility/
    PublicApiChangeIsBreaking.php
```

Not:

```text
Specifications/
  Specification.php
```

---

## 28. Architecture Decision Record Rule

Every serious architecture choice must have a short ADR.

ADR is required for:

```text
CQRS
Event Sourcing
Saga
Outbox
Inbox
public API compatibility model
aggregate boundary
runtime model
distributed consistency choice
major folder structure decision
breaking public API change
```

ADR must be short.

It must explain why.

Template:

```md
# ADR-0000-title

## Decision

## Reason

## Rejected Alternatives

## Risk

## Proof

## Revisit Condition
```

No ADR means no green status for that advanced pattern.

---

## 29. Architecture Fitness Test Rule

If an architecture rule can be automated, automate it.

Examples:

```text
PublicSurface must not own runtime machinery.
PublicSurface must not import runtime-specific implementation.
No root Domain/Entities/Services folders.
No generic Services/Managers/Helpers folders.
No production code depends on test fakes.
No V2/V3 components are promoted while V1 is red.
No request-scoped state in long-lived public surfaces.
No component imports another component internal implementation without explicit boundary.
```

Fitness tests belong in tooling or architecture tests.

They are stronger than review comments.

Project-specific example:

```text
configured public-surface checker
configured runtime-leak checker
configured namespace-drift checker
configured advanced-pattern checker
```

---

## 30. Compatibility Check Rule

Public API compatibility must be checked before public surface changes are called safe.

Use compatibility checks when:

```text
PublicSurface changes
public DTO changes
public value object changes
public event schema changes
public method signature changes
documented behavior changes
```

Naming:

```text
Capabilities/
  PublicApiCompatibility/
    DescribePublicApi.php
    DetectBreakingPublicApiChange.php
    CompatibilityReport.php
```

Avoid:

```text
Contracts/
  ContractChecker.php
```

The concern is compatibility.

Name it compatibility.

---

## 31. Pattern Placement Matrix

| Pattern         | Avoid Folder                        | Preferred Folder Language          | Good Unit Names                                   |
|-----------------|-------------------------------------|------------------------------------|---------------------------------------------------|
| CQRS            | `CQRS/`, `Commands/`, `Queries/`    | flow and read capability           | `RegisterUser`, `ReadUserProfile`                 |
| Event Sourcing  | `EventSourcing/`                    | history capability                 | `RecordPaymentEvent`, `RebuildPaymentState`       |
| Outbox          | `Outbox/` as dumping ground         | reliable publishing                | `StorePendingEvent`, `PublishPendingEvent`        |
| Inbox           | `Inbox/` as dumping ground          | incoming message processing        | `RememberReceivedMessage`, `MarkMessageProcessed` |
| Saga            | `Sagas/`                            | long-running flow                  | `CompleteCheckout`, `CompensateReservedInventory` |
| State Machine   | `StateMachine/`                     | lifecycle owner                    | `ReleaseState`, `ApproveReleaseForProduction`     |
| Policy          | `Policies/` as dumping ground       | decision capability                | `EvaluateDeprecationPolicy`                       |
| Specification   | `Specifications/` as dumping ground | named condition capability         | `PublicApiChangeIsBreaking`                       |
| Projection      | `Projectors/`                       | view building capability           | `UpdateReleaseReadinessView`                      |
| Read Model      | `ReadModels/`                       | read capability                    | `UserProfileView`, `ReadUserProfile`              |
| Retry           | `Retries/`                          | failure recovery capability        | `CalculateNextRetryDelay`                         |
| Circuit Breaker | `CircuitBreakers/`                  | dependency availability capability | `CanCallSearchIndex`                              |
| Dead Letter     | `DeadLetters/`                      | failed message recovery            | `MoveMessageToDeadLetter`                         |

---

## 32. Pattern Proof Matrix

| Pattern             | Required Proof                                                |
|---------------------|---------------------------------------------------------------|
| CQRS                | write invariant and read model separation are tested          |
| Event Sourcing      | state rebuilds from events and event versioning is handled    |
| Outbox              | state change stores pending event and event is published once |
| Inbox               | duplicate external message does not duplicate side effects    |
| Saga                | failure compensation and resume behavior are tested           |
| State Machine       | invalid transitions are rejected                              |
| Idempotency         | duplicate execution is safe                                   |
| Projection          | event updates view and duplicate does not corrupt view        |
| Retry               | retryable and non-retryable failures are separated            |
| Circuit Breaker     | repeated failure opens circuit and recovery closes it         |
| Backpressure        | overload causes explicit delay/rejection                      |
| Dead Letter         | poison message is moved and visible                           |
| Policy              | decision is tested with allowed and rejected cases            |
| Specification       | condition is tested directly                                  |
| Compatibility Check | breaking public API change is detected                        |
| Fitness Test        | architecture violation is caught by tooling                   |

---

## 33. Advanced Pattern Review Checklist

A design using advanced patterns passes only if all are true:

```text
The real pressure is named.
A simpler option was considered.
The pattern has an owner flow or capability.
Folder names still say flow or capability.
Unit names still say responsibility.
Function names still say exact action.
No generic pattern folders were introduced.
No Services/Managers/Handlers dumping ground was introduced.
Failure behavior is explicit.
Idempotency is addressed where needed.
State transitions are explicit where lifecycle exists.
Public compatibility is addressed where public API changes.
Tests prove the behavior.
ADR exists for serious choices.
```

If the design is harder to explain after adding the pattern, remove the pattern.

---

## 34. Review Classification

### GREEN

```text
Pattern solves real pressure.
Ownership is clear.
Naming follows project laws.
Behavior is proven by tests.
ADR exists when required.
```

### YELLOW

```text
Pattern is plausible.
Ownership is mostly clear.
Proof is incomplete.
May remain during active design.
```

### RED

```text
Pattern is decorative.
Pattern creates generic folders.
Pattern hides ownership.
Pattern has no proof.
```

### BLOCKER

```text
Pattern violates active stage lock.
Pattern creates skeleton production code.
Pattern introduces V2/V3 behavior while V1 is red.
Pattern leaks runtime internals into PublicSurface.
Pattern creates Commands/Queries/Adapters/Services dumping grounds.
Pattern is called production-ready without tests.
```

---

## 35. Existing Code Recovery Rule

When recovering old code from legacy backups (`Framework.txt`, `Components.txt`), or git history, do not restore
advanced pattern structure blindly.

Translate by pressure and ownership.

Old:

```text
CQRS/Commands/RegisterUserCommand.php
CQRS/Handlers/RegisterUserHandler.php
Infrastructure/Adapters/StripeAdapter.php
Domain/Events/UserEvent.php
Services/PaymentService.php
```

New:

```text
Flows/RegisterUser/RegisterUser.php
Capabilities/StripePaymentGateway/ChargeCardThroughStripe.php
Flows/RegisterUser/UserRegistered.php
Flows/ProcessPayment/ProcessPayment.php
```

Old code is evidence.

Current architecture is the target.

Tests are the judge.

---

## 36. Final Law

Advanced architecture patterns are allowed only when they reduce real pressure.

CQRS separates reads and writes only when they change for different reasons.

Event Sourcing is used only when event history is the source of truth.

Outbox protects reliable publishing.

Inbox protects reliable consumption.

Saga protects long-running distributed workflows.

Idempotency protects repeated execution.

State machines protect lifecycle transitions.

Policies protect named decisions.

Specifications protect reusable domain conditions.

Fitness tests protect architecture rules.

ADRs protect decision memory.

Patterns must not become decoration.

Patterns must not become folder religion.

Patterns must not hide ownership.

The system must still read as:

```text
flow
capability
responsibility
exact action
```

If the pattern makes that clearer, use it.

If the pattern makes that harder, remove it.

---

## 37. Fowler's PEAA Pattern Translation Rule

**Status:** MANDATORY  
**Severity:** BLOCKER  

This section defines how Fowler's *Patterns of Enterprise Application Architecture* (PEAA) map directly to the project's architectural boundaries.

### 37.1 Pattern Mapping

Every enterprise pattern used in the project **MUST** translate to screaming, flow-oriented units:
1. **Transaction Script:** Mapped directly to a single **Flow** orchestrator class (e.g., `Flows/RegisterUser/RegisterUser.php`).
2. **Domain Model (Rich):** Mapped to domain capabilities (Aggregates, Entities, and Value Objects) residing inside component capability namespaces (e.g., `Capabilities/ReleaseReadiness/`).
3. **Table Module / Table Data Gateway:** Mapped to persistence capabilities (Repositories or Query Builders) residing under component persistence boundaries (e.g., `Capabilities/Persistence/`).
4. **Service Layer:** Mapped strictly to the **PublicSurface** facade or coordinate **Flow** orchestrator, never to a folder named `Services/` or classes named `<Name>Service.php`.

### 37.2 Classifications

- **BLOCKER:** Creating folders or namespaces named after structural patterns (e.g., `TransactionScripts/`, `DomainModels/`, `TableModules/`, `Services/`).
- **RED:** Mixing transaction scripts or data mutation logic directly inside Active Record classes, or bypassing capability layers by mapping Table Data Gateways directly to controller actions.
