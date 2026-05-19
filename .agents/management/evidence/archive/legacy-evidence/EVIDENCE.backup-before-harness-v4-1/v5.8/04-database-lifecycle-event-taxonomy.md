# V5.8-04: Database Lifecycle Event Taxonomy

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Lifecycle Event Taxonomy

## Taxonomy Categories

### 1. Entity Lifecycle Events

These events observe entity persistence operations. They are database/application lifecycle events, NOT automatically
domain events.

| Event            | When                              | Notes                                        |
|------------------|-----------------------------------|----------------------------------------------|
| `BeforeValidate` | Before entity validation          | Optional, only if validation pipeline exists |
| `AfterValidate`  | After entity validation passes    | Optional                                     |
| `BeforeSave`     | Before insert or update           | Runs for both create and update              |
| `Saving`         | Alias for BeforeSave              | Consistent naming                            |
| `Saved`          | After successful insert or update | Does not distinguish create vs update        |
| `BeforeCreate`   | Before insert only                | Only on new entity                           |
| `Creating`       | Alias for BeforeCreate            | Consistent naming                            |
| `Created`        | After successful insert only      | Only on new entity                           |
| `AfterCreate`    | Alias for Created                 | Consistent naming                            |
| `BeforeUpdate`   | Before update only                | Only on existing entity                      |
| `Updating`       | Alias for BeforeUpdate            | Consistent naming                            |
| `Updated`        | After successful update only      | Only on existing entity                      |
| `AfterUpdate`    | Alias for Updated                 | Consistent naming                            |
| `BeforeDelete`   | Before delete                     | Runs before SQL DELETE                       |
| `Deleting`       | Alias for BeforeDelete            | Consistent naming                            |
| `Deleted`        | After successful delete           | Runs after SQL DELETE                        |
| `AfterDelete`    | Alias for Deleted                 | Consistent naming                            |
| `Restored`       | After soft-delete restore         | Only if soft deletes are active              |
| `FailedToSave`   | When save operation fails         | Must NOT swallow exception                   |
| `FailedToDelete` | When delete operation fails       | Must NOT swallow exception                   |

**Naming rule:** Preferred names are `Created`, `Updated`, `Deleted`, `Saved`, `Deleting`, `Updating`, `Creating`,
`Saving`. The `After`/`Before` variants are accepted aliases for clarity.

**Key rules:**

- Entity lifecycle events are database/application lifecycle events.
- They are NOT automatically domain events.
- `Created` fires only after INSERT success, not UPDATE.
- `Updated` fires only after UPDATE success, not INSERT.
- `Saved` fires after either INSERT or UPDATE success.
- `FailedToSave` / `FailedToDelete` must NOT hide or swallow exceptions.

### 2. Query Lifecycle Events

These events observe query execution. They are telemetry/observability first.

| Event               | When                           | Notes                   |
|---------------------|--------------------------------|-------------------------|
| `QueryExecuting`    | Before SQL execution           | Before statement runs   |
| `QueryExecuted`     | After successful SQL execution | Includes duration       |
| `SlowQueryDetected` | When duration >= threshold     | Threshold configurable  |
| `QueryFailed`       | After SQL exception            | Exception still bubbles |

**Key rules:**

- Query lifecycle events are telemetry/observability first.
- `QueryFailed` fires but does NOT prevent exception from bubbling.
- Slow query threshold is configurable per-listener registration.
- Bindings must be redacted by default — no sensitive data in query events.

### 3. Transaction Lifecycle Events

These events observe transaction boundaries.

| Event                   | When                          | Notes                                |
|-------------------------|-------------------------------|--------------------------------------|
| `TransactionBeginning`  | Before transaction starts     | Before BEGIN                         |
| `TransactionCommitted`  | After successful commit       | After COMMIT                         |
| `TransactionRolledBack` | After rollback                | After ROLLBACK                       |
| `TransactionFailed`     | When commit or rollback fails | Exception still bubbles              |
| `AfterCommit`           | AFTER TransactionCommitted    | Safe place for external side effects |
| `AfterRollback`         | AFTER TransactionRolledBack   | Cleanup after rollback               |

**Key rules:**

- `AfterCommit` is the safe place for external side effects.
- `AfterCommit` must NOT run if transaction fails.
- Nested transaction behavior must be explicit: only outermost commit triggers AfterCommit by default.
- If no transaction is active, AfterCommit runs immediately (documented behavior).

### 4. Bulk Operation Lifecycle Events

These events observe bulk database operations.

| Event                 | When                          | Notes                   |
|-----------------------|-------------------------------|-------------------------|
| `BulkInsertStarted`   | Before bulk insert begins     | Before batch execution  |
| `BulkInsertCompleted` | After bulk insert succeeds    | Includes affected count |
| `BulkUpdateStarted`   | Before bulk update begins     | Before batch execution  |
| `BulkUpdateCompleted` | After bulk update succeeds    | Includes affected count |
| `BulkDeleteStarted`   | Before bulk delete begins     | Before batch execution  |
| `BulkDeleteCompleted` | After bulk delete succeeds    | Includes affected count |
| `BulkOperationFailed` | When any bulk operation fails | Exception still bubbles |

**Key rules:**

- Bulk operations fire bulk lifecycle events by default.
- Per-entity events require explicit policy AND loaded entities.
- Do NOT fake per-entity events for bulk SQL UPDATE/DELETE.
- Bulk events include affected row count, not per-entity details.

### 5. Outbox Bridge Events (Future)

These events observe outbox message lifecycle. Not V5.8 production scope.

| Event                       | When                             | Notes                       |
|-----------------------------|----------------------------------|-----------------------------|
| `OutboxMessageStored`       | Message stored in DB transaction | Part of same transaction    |
| `OutboxMessagePublished`    | Message successfully published   | External delivery confirmed |
| `OutboxMessageFailed`       | Publication failed               | Retryable                   |
| `OutboxMessageRetried`      | Retry attempt made               | Includes attempt count      |
| `OutboxMessageDeadLettered` | Max retries exceeded             | Moved to dead letter        |

### 6. Projection Bridge Events (Future)

These events observe projection lifecycle. Not V5.8 production scope.

| Event               | When                            | Notes                    |
|---------------------|---------------------------------|--------------------------|
| `ProjectionStarted` | Projection begins               | Includes projection name |
| `ProjectionApplied` | Projection successfully applied | Idempotent               |
| `ProjectionFailed`  | Projection failed               | Observable failure       |
| `ProjectionRebuilt` | Projection rebuilt from scratch | Full replay complete     |

## Event Naming Convention

All events follow AvaX convention:

- Past-tense for completed facts: `Created`, `Updated`, `Deleted`, `Saved`, `QueryExecuted`
- Present participle for in-progress: `Creating`, `Updating`, `Deleting`, `Saving`, `QueryExecuting`
- `Before`/`After` prefixes accepted for clarity: `BeforeCreate`, `AfterCommit`
- No generic `Event` suffix — the event class itself is the event
- No `Handler`, `Listener`, `Processor` suffix on event classes

## Next Allowed Action

V5.8-05 Fluent API Design.
