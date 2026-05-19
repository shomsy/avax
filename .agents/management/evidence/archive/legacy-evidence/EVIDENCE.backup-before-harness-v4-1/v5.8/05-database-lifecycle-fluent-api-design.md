# V5.8-05: Database Lifecycle Fluent API Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Fluent API Design

## Decision: DB-Specific DSL

DB lifecycle should have a DB-specific DSL: `onEntity()`, `onQuery()`, `onTransaction()`.

Internally it may compile into the same registry pattern as AvaX Events, but the public API is DB-specific.

Users should NOT manually register low-level DB lifecycle events through generic `onEvent()` unless intentionally
advanced.

## Public API Design

### Entity Lifecycle DSL

```php
onEntity(User::class)
    ->creating(ValidateUser::class)
    ->created(EmitUserRegistered::class)
    ->updating(RecordUserAuditTrail::class)
    ->updated(NotifyUserChanged::class)
    ->deleting(PreventAdminDeletion::class)
    ->deleted(CleanUserCache::class)
    ->failedToSave(ReportUserSaveFailure::class);
```

**Methods:**

- `creating(ListenerClass)` — fires before INSERT
- `created(ListenerClass)` — fires after INSERT success
- `updating(ListenerClass)` — fires before UPDATE
- `updated(ListenerClass)` — fires after UPDATE success
- `saving(ListenerClass)` — fires before INSERT or UPDATE
- `saved(ListenerClass)` — fires after INSERT or UPDATE success
- `deleting(ListenerClass)` — fires before DELETE
- `deleted(ListenerClass)` — fires after DELETE success
- `restored(ListenerClass)` — fires after soft-delete restore
- `failedToSave(ListenerClass)` — fires when save fails
- `failedToDelete(ListenerClass)` — fires when delete fails

**Listener signature:**

```php
final readonly class ValidateUser
{
    public function __invoke(EntityCreating $event): void
    {
        // $event->entity, $event->attributes, $event->connection
    }
}
```

### Query Lifecycle DSL

```php
onQuery()
    ->executed(RecordQueryTelemetry::class)
    ->slow(ReportSlowQuery::class, thresholdMs: 100);
```

**Methods:**

- `executing(ListenerClass)` — fires before query execution
- `executed(ListenerClass)` — fires after query success
- `slow(ListenerClass, thresholdMs: int)` — fires when query exceeds threshold
- `failed(ListenerClass)` — fires when query fails

**Listener signature:**

```php
final readonly class RecordQueryTelemetry
{
    public function __invoke(QueryExecuted $event): void
    {
        // $event->sql (redacted), $event->bindings (redacted), $event->durationMs
    }
}
```

### Transaction Lifecycle DSL

```php
onTransaction()
    ->committed(RecordTransactionAudit::class)
    ->afterCommit(PublishOutboxMessages::class)
    ->rolledBack(ClearPendingEvents::class)
    ->afterRollback(LogTransactionFailure::class);
```

**Methods:**

- `beginning(ListenerClass)` — fires before BEGIN
- `committed(ListenerClass)` — fires after COMMIT
- `afterCommit(ListenerClass)` — fires after successful commit, safe for external side effects
- `rolledBack(ListenerClass)` — fires after ROLLBACK
- `afterRollback(ListenerClass)` — fires after rollback, cleanup
- `failed(ListenerClass)` — fires when transaction fails

**Listener signature:**

```php
final readonly class PublishOutboxMessages
{
    public function __invoke(AfterCommit $event): void
    {
        // $event->connection, $event->transactionId
    }
}
```

## DSL Rules

1. `onEntity()` registers entity lifecycle listeners for a specific entity class.
2. `onQuery()` registers query lifecycle listeners (global, not per-query).
3. `onTransaction()` registers transaction lifecycle listeners (global, not per-transaction).
4. These are **declaration APIs**, not execution APIs.
5. Do NOT make lifecycle events look like event sourcing.
6. PublicSurface must be thin — DSL functions only.
7. Real behavior lives in Flows/Capabilities.
8. No generic `DBLifecycleManager`.
9. No service/helper/util names.
10. DSL compiles into the compiled lifecycle registry at boot time.
11. DSL registration order is preserved within same priority.

## Global Functions (PublicSurface)

```php
// components/DataStack/Database/System/PublicSurface/functions.php

function onEntity(string $entityClass): EntityLifecycleDsl;
function onQuery(): QueryLifecycleDsl;
function onTransaction(): TransactionLifecycleDsl;
```

## DSL Interface Sketch

```php
interface EntityLifecycleDsl
{
    public function creating(string $listener): self;
    public function created(string $listener): self;
    public function updating(string $listener): self;
    public function updated(string $listener): self;
    public function saving(string $listener): self;
    public function saved(string $listener): self;
    public function deleting(string $listener): self;
    public function deleted(string $listener): self;
    public function restored(string $listener): self;
    public function failedToSave(string $listener): self;
    public function failedToDelete(string $listener): self;
}

interface QueryLifecycleDsl
{
    public function executing(string $listener): self;
    public function executed(string $listener): self;
    public function slow(string $listener, int $thresholdMs = 100): self;
    public function failed(string $listener): self;
}

interface TransactionLifecycleDsl
{
    public function beginning(string $listener): self;
    public function committed(string $listener): self;
    public function afterCommit(string $listener): self;
    public function rolledBack(string $listener): self;
    public function afterRollback(string $listener): self;
    public function failed(string $listener): self;
}
```

## Integration with V5.7 Events

DB lifecycle DSL compiles into its own registry (`CompiledDatabaseLifecycleRegistry`).

DB lifecycle listeners are invoked by the DB runtime (not by the Events dispatcher).

DB lifecycle listeners MAY emit canonical AvaX events internally:

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

This keeps DB lifecycle and Events as separate systems with a controlled bridge.

## Next Allowed Action

V5.8-06 Attributes Design.
