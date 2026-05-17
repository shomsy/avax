# V5.8-08: Database Lifecycle Execution Semantics

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Execution Semantics

## Entity Lifecycle Semantics

| Phase            | When                                  | Condition                                         |
|------------------|---------------------------------------|---------------------------------------------------|
| `Creating`       | Before INSERT SQL execution           | Only on new entity (not yet persisted)            |
| `Created`        | After INSERT SQL success              | Only on new entity, after DB confirms insert      |
| `Updating`       | Before UPDATE SQL execution           | Only on existing entity (already persisted)       |
| `Updated`        | After UPDATE SQL success              | Only on existing entity, after DB confirms update |
| `Saving`         | Before INSERT or UPDATE SQL execution | Both new and existing entities                    |
| `Saved`          | After INSERT or UPDATE SQL success    | Both new and existing entities                    |
| `Deleting`       | Before DELETE SQL execution           | Before SQL DELETE runs                            |
| `Deleted`        | After DELETE SQL success              | After SQL DELETE confirms                         |
| `Restored`       | After soft-delete restore             | Only if soft deletes are active                   |
| `FailedToSave`   | When INSERT or UPDATE throws          | Exception still bubbles, event fires first        |
| `FailedToDelete` | When DELETE throws                    | Exception still bubbles, event fires first        |

### Event Object Contents

```php
// EntityCreating, EntityUpdating, EntitySaving
final readonly class EntityCreating
{
    public function __construct(
        public string $entityClass,
        public array $attributes,        // about to be persisted
        public string $connection,       // connection name
        public string $phase,            // 'creating'
    ) {}
}

// EntityCreated, EntityUpdated, EntitySaved
final readonly class EntityCreated
{
    public function __construct(
        public string $entityClass,
        public mixed $entity,            // persisted entity instance
        public array $attributes,        // as persisted
        public string $connection,
        public string $lastInsertId,     // for Created only
        public string $phase,            // 'created'
    ) {}
}

// EntityDeleting
final readonly class EntityDeleting
{
    public function __construct(
        public string $entityClass,
        public mixed $entity,            // entity about to be deleted
        public string $connection,
        public string $phase,            // 'deleting'
    ) {}
}

// EntityDeleted
final readonly class EntityDeleted
{
    public function __construct(
        public string $entityClass,
        public mixed $entity,            // deleted entity (may be detached)
        public string $connection,
        public string $phase,            // 'deleted'
    ) {}
}

// FailedToSave, FailedToDelete
final readonly class FailedToSave
{
    public function __construct(
        public string $entityClass,
        public array $attributes,
        public string $connection,
        public Throwable $exception,     // the original exception
        public string $phase,            // 'failedToSave'
    ) {}
}
```

### Key Rules

1. `Created` fires ONLY after INSERT success, NOT on UPDATE.
2. `Updated` fires ONLY after UPDATE success, NOT on INSERT.
3. `Saved` fires after either INSERT or UPDATE success.
4. `Creating`/`Updating` listeners may mutate attributes before persistence.
5. `Created`/`Updated` listeners receive the persisted entity instance.
6. Failed lifecycle events fire BEFORE exception bubbles — they do NOT swallow exceptions.
7. Listener failure during entity lifecycle bubbles by default — no silent swallowing.

## Transaction Lifecycle Semantics

| Phase           | When                           | Condition                      |
|-----------------|--------------------------------|--------------------------------|
| `Beginning`     | Before BEGIN SQL               | Before transaction starts      |
| `Committed`     | After COMMIT SQL success       | After DB confirms commit       |
| `AfterCommit`   | After TransactionCommitted     | Safe for external side effects |
| `RolledBack`    | After ROLLBACK SQL             | After DB confirms rollback     |
| `AfterRollback` | After TransactionRolledBack    | Cleanup after rollback         |
| `Failed`        | When COMMIT or ROLLBACK throws | Exception still bubbles        |

### Nested Transaction Behavior

- Only the **outermost** commit triggers `AfterCommit` by default.
- Savepoints do NOT trigger `AfterCommit` — they are internal transaction boundaries.
- Nested `afterCommit` registrations on inner transactions are **buffered** and run only when the outermost transaction
  commits.
- If any inner transaction rolls back (via savepoint), the outermost transaction is still pending.
- If the outermost transaction rolls back, ALL buffered `afterCommit` callbacks are discarded.

### No-Transaction Policy

If `afterCommit` is called when no transaction is active:

- **Policy: Run immediately.**
- Documented behavior: `afterCommit` without active transaction executes the callback immediately.
- This allows code to work both inside and outside transactions transparently.

### Event Object Contents

```php
final readonly class TransactionBeginning
{
    public function __construct(
        public string $connection,
        public int $nestingLevel,
        public string $transactionId,
    ) {}
}

final readonly class TransactionCommitted
{
    public function __construct(
        public string $connection,
        public int $nestingLevel,
        public string $transactionId,
        public float $durationMs,
    ) {}
}

final readonly class AfterCommit
{
    public function __construct(
        public string $connection,
        public string $transactionId,
    ) {}
}

final readonly class TransactionRolledBack
{
    public function __construct(
        public string $connection,
        public int $nestingLevel,
        public string $transactionId,
        public ?Throwable $reason,
    ) {}
}

final readonly class AfterRollback
{
    public function __construct(
        public string $connection,
        public string $transactionId,
        public ?Throwable $reason,
    ) {}
}
```

### Key Rules

1. `AfterCommit` is the safe place for external side effects (email, HTTP, queue, outbox).
2. `AfterCommit` must NOT run if transaction fails or rolls back.
3. Rollback clears all pending `AfterCommit` callbacks.
4. `AfterRollback` runs only after actual rollback, NOT on commit failure.
5. Listener failure during transaction lifecycle bubbles by default.

## Query Lifecycle Semantics

| Phase       | When                       | Condition                           |
|-------------|----------------------------|-------------------------------------|
| `Executing` | Before SQL execution       | Before statement runs               |
| `Executed`  | After SQL success          | After statement completes           |
| `Slow`      | When duration >= threshold | Configurable threshold per listener |
| `Failed`    | After SQL exception        | Exception still bubbles             |

### Event Object Contents

```php
final readonly class QueryExecuting
{
    public function __construct(
        public string $sql,           // redacted by default
        public array $bindings,       // redacted by default
        public string $connection,
        public float $startTime,
    ) {}
}

final readonly class QueryExecuted
{
    public function __construct(
        public string $sql,           // redacted by default
        public array $bindings,       // redacted by default
        public string $connection,
        public float $durationMs,
        public int $rowCount,
    ) {}
}

final readonly class QueryFailed
{
    public function __construct(
        public string $sql,           // redacted by default
        public array $bindings,       // redacted by default
        public string $connection,
        public Throwable $exception,
        public float $durationMs,
    ) {}
}
```

### Key Rules

1. Query lifecycle events are telemetry/observability first.
2. SQL and bindings MUST be redacted by default.
3. `QueryFailed` fires but does NOT prevent exception from bubbling.
4. `Slow` listener receives threshold at registration time.
5. Duration measurement adds minimal overhead — no reflection, no allocation in hot path.

## Bulk Operation Semantics

| Phase                 | When                      | Condition                   |
|-----------------------|---------------------------|-----------------------------|
| `BulkInsertStarted`   | Before bulk INSERT begins | Before batch execution      |
| `BulkInsertCompleted` | After bulk INSERT success | Includes affected row count |
| `BulkUpdateStarted`   | Before bulk UPDATE begins | Before batch execution      |
| `BulkUpdateCompleted` | After bulk UPDATE success | Includes affected row count |
| `BulkDeleteStarted`   | Before bulk DELETE begins | Before batch execution      |
| `BulkDeleteCompleted` | After bulk DELETE success | Includes affected row count |
| `BulkOperationFailed` | When bulk operation fails | Exception still bubbles     |

### Key Rules

1. Bulk operations fire bulk lifecycle events by default.
2. Per-entity events require **explicit policy** AND **loaded entities**.
3. Do NOT fake per-entity events for bulk SQL UPDATE/DELETE — the database does not load entities for bulk operations.
4. Bulk events include affected row count, NOT per-entity details.
5. If per-entity events are needed, use ORM UnitOfWork with loaded entities, not QueryBuilder bulk operations.

## Failure Semantics

1. **Listener failure bubbles by default** — no silent swallowing.
2. **Failed lifecycle events fire BEFORE exception bubbles** — they report, they do not catch.
3. **No retries in lifecycle listeners** — retries are handled by canonical Resilience component if needed.
4. **No dead-letter in lifecycle listeners** — dead-letter is handled by canonical Queue component if needed.
5. **Exception is never swallowed** — lifecycle events are observability, not error handling.

## Next Allowed Action

V5.8-09 Database Events Integration Design.
