# Database Lifecycle Events

**Status:** V5.8 Design Lock — NOT IMPLEMENTED YET
**Version:** V5.8 Design Draft
**Date:** 2026-05-13

## What Database Lifecycle Events Are

Database lifecycle events observe database operations — entity persistence, query execution, and transaction boundaries. They provide hooks to run code before and after these operations.

```php
// Entity lifecycle
onEntity(User::class)
    ->created(EmitUserRegistered::class)
    ->deleting(PreventAdminDeletion::class);

// Transaction lifecycle
onTransaction()
    ->afterCommit(PublishOutboxMessages::class)
    ->afterRollback(ClearPendingEvents::class);

// Query lifecycle
onQuery()
    ->executed(RecordQueryTelemetry::class)
    ->slow(ReportSlowQuery::class, thresholdMs: 100);
```

## What They Are NOT

- **Not Event Sourcing** — they observe operations, they are not the source of truth.
- **Not domain events** — they are database/application facts. A listener may emit a domain event.
- **Not a replacement for V5.7 Events** — they integrate WITH Events, they do not replace it.
- **Not automatic** — you must register listeners. No behavior is hidden.
- **Not async by default** — V5.8 execution mode is sync only.

## Entity Lifecycle

Entity lifecycle events fire when entities are persisted through the ORM.

### Phases

| Phase | When | Fires |
|-------|------|-------|
| `creating` | Before INSERT | Before SQL runs |
| `created` | After INSERT success | After DB confirms insert |
| `updating` | Before UPDATE | Before SQL runs |
| `updated` | After UPDATE success | After DB confirms update |
| `saving` | Before INSERT or UPDATE | Before SQL runs |
| `saved` | After INSERT or UPDATE success | After DB confirms |
| `deleting` | Before DELETE | Before SQL runs |
| `deleted` | After DELETE success | After DB confirms |
| `restored` | After soft-delete restore | Only if soft deletes active |
| `failedToSave` | When save fails | Exception still bubbles |
| `failedToDelete` | When delete fails | Exception still bubbles |

### Example

```php
onEntity(User::class)
    ->creating(ValidateUser::class)
    ->created(EmitUserRegistered::class)
    ->updating(RecordUserAuditTrail::class)
    ->deleting(PreventAdminDeletion::class);
```

### Listener Signature

```php
final readonly class EmitUserRegistered
{
    public function __invoke(EntityCreated $event): void
    {
        emit(new UserRegistered(
            userId: $event->entity->id,
            email: $event->entity->email,
        ));
    }
}
```

## Transaction Lifecycle

Transaction lifecycle events fire at transaction boundaries.

### Phases

| Phase | When | Notes |
|-------|------|-------|
| `beginning` | Before BEGIN | Before transaction starts |
| `committed` | After COMMIT | After DB confirms commit |
| `afterCommit` | After TransactionCommitted | **Safe for external side effects** |
| `rolledBack` | After ROLLBACK | After DB confirms rollback |
| `afterRollback` | After TransactionRolledBack | Cleanup after rollback |
| `failed` | When commit/rollback fails | Exception still bubbles |

### afterCommit — Safe External Side Effects

`afterCommit` is the safe place for external side effects:

- Publishing events to message queues
- Sending emails
- Calling external APIs
- Writing to outbox tables

```php
onTransaction()
    ->afterCommit(PublishOutboxMessages::class);
```

### Nested Transactions

- Only the **outermost** commit triggers `afterCommit`.
- Savepoints do NOT trigger `afterCommit`.
- If the outermost transaction rolls back, ALL `afterCommit` callbacks are discarded.

### No-Transaction Policy

If `afterCommit` is called with no active transaction, the callback runs immediately.

## Query Lifecycle

Query lifecycle events fire around SQL execution.

### Phases

| Phase | When | Notes |
|-------|------|-------|
| `executing` | Before SQL | Before statement runs |
| `executed` | After SQL success | Includes duration |
| `slow` | When duration >= threshold | Configurable per listener |
| `failed` | After SQL exception | Exception still bubbles |

### Redaction

Query events **redact sensitive bindings by default**:

```php
onQuery()
    ->executed(RecordQueryTelemetry::class);
```

SQL and bindings are redacted — passwords, tokens, and secrets are NOT logged.

## Bulk Operations

Bulk operations (QueryBuilder insert/update/delete for multiple rows) fire bulk lifecycle events:

- `bulkInsertStarted` / `bulkInsertCompleted`
- `bulkUpdateStarted` / `bulkUpdateCompleted`
- `bulkDeleteStarted` / `bulkDeleteCompleted`
- `bulkOperationFailed`

**Important:** Bulk operations do NOT fire per-entity lifecycle events. The database does not load entities for bulk SQL. Per-entity events require loaded entities through the ORM UnitOfWork.

## Outbox Bridge

The outbox bridge provides reliable external event publication:

1. During a transaction, store events in an outbox table (same DB transaction)
2. After commit, publish outbox messages
3. Retry on failure
4. Deduplicate where needed

**V5.8 status:** Design only. Interfaces and integration path defined. No production implementation.

The outbox is **delivery reliability**, not a source of truth. It ensures events are published exactly once.

## Projection Bridge

Projections build read models from events:

```php
// V5.7 already proves this
onEvent(UserRegistered::class)
    ->do(ProjectRegisteredUser::class);
```

The `ProjectRegisteredUser` listener builds `RegisteredUserView` from `UserRegistered` events.

**V5.8 status:** Existing CQRS proof from V5.7. Future `onProjection()` DSL designed but not implemented.

## Difference Between Event Types

| Event Type | Purpose | Example | Source of Truth? |
|------------|---------|---------|-----------------|
| **DB Lifecycle Event** | Observe DB operations | `EntityCreated`, `QueryExecuted` | No |
| **Domain Event** | Business fact | `UserRegistered`, `OrderPaid` | No (usually) |
| **Integration Event** | Cross-system communication | `UserRegisteredV1` | No |
| **EventStore Event** | Source of truth for event sourcing | `UserRegistered` in event stream | YES |
| **Outbox Message** | Durable delivery of any event | Serialized `UserRegistered` | No (copy for delivery) |

## Relation to V5.7 Events

```
DB Lifecycle → Listener calls emit() → V5.7 Events → Listeners
```

- DB lifecycle has its own registry (`CompiledDatabaseLifecycleRegistry`)
- DB lifecycle listeners are invoked by DB runtime, not by Events dispatcher
- Listeners MAY call `emit()` to bridge into V5.7 Events
- V5.7 Events remains the only canonical event dispatcher

## Event Sourcing Boundary

**V5.8 does NOT implement Event Sourcing.**

Event Sourcing makes event history the source of truth. This requires:

- EventStore (append-only event log)
- Aggregate replay
- Stream versioning
- Expected version checking
- Snapshots
- Upcasters
- Projection rebuild

These are **future capabilities**, explicitly marked as ROADMAP.

V5.8 prepares the road by:
- Defining DB lifecycle events
- Defining outbox bridge interfaces
- Defining projection bridge design
- Setting the boundary between lifecycle, outbox, projections, and event sourcing

## Security

- Query bindings are **redacted by default**
- Sensitive fields (passwords, tokens, secrets) are masked
- Raw bindings are only available in explicit diagnostic mode
- Event payloads must not contain secrets
- Outbox messages use safe serialization (JSON, not PHP serialize)

## Observability

The following metrics are designed for DB lifecycle observability:

| Metric | Type |
|--------|------|
| `db.lifecycle.listener.count` | Counter |
| `db.lifecycle.listener.duration` | Histogram |
| `db.query.count` | Counter |
| `db.query.duration` | Histogram |
| `db.query.slow` | Counter |
| `db.transaction.commit.count` | Counter |
| `db.transaction.rollback.count` | Counter |
| `db.outbox.pending` | Gauge |
| `db.outbox.published` | Counter |
| `db.outbox.failed` | Counter |

## ROADMAP

The following are NOT production-ready in V5.8:

| Feature | Status |
|---------|--------|
| DB lifecycle events | DESIGN LOCK — implementation planned |
| Entity lifecycle hooks | DESIGN LOCK |
| Transaction afterCommit/afterRollback | DESIGN LOCK |
| Query lifecycle hooks | DESIGN LOCK |
| Outbox bridge | DESIGN ONLY — interfaces defined |
| Projection bridge | DESIGN ONLY |
| EventStore | ROADMAP — future capability |
| Event Sourcing Kit | ROADMAP — future capability |
| Async lifecycle listeners | ROADMAP |
| DB lifecycle attributes | DEFERRED to V5.8.x |
| onProjection() DSL | DESIGN ONLY |
