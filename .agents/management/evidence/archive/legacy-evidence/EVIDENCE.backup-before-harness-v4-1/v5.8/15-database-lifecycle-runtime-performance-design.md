# V5.8-15: Database Lifecycle Runtime & Performance Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Runtime & Performance Design

## Rules

1. **No hot-path reflection** — lifecycle registry is compiled/frozen at boot time.
2. **If no listeners are registered, overhead must be minimal** — fast path check.
3. **Query lifecycle must be cheap** — minimal allocation in hot path.
4. **Slow query measurement must not be expensive** — simple time difference check.
5. **Lifecycle events must not allocate large objects unnecessarily in hot path.**
6. **Long-lived runtimes must reset request/transaction scoped buffers.**
7. **No hidden static request state.**
8. **afterCommit callback buffers must be transaction-scoped.**

## Performance Expectations

### No-Listener Fast Path

```php
// In hot path (e.g., EntityPersister::insert)
if (!$this->registry->hasEntityListeners($entityClass, EntityLifecyclePhase::Creating)) {
    // Skip lifecycle entirely — just one array lookup
    return $this->executeInsert($sql, $bindings);
}

// Only when listeners exist:
$this->invokeEntityLifecycle(...);
```

**Overhead:** Single array key lookup (O(1)) when no listeners are registered.

### Listener Count Lookup

```php
public function hasEntityListeners(string $entityClass, EntityLifecyclePhase $phase): bool
{
    return isset($this->entityListeners[$entityClass][$phase->value])
        && count($this->entityListeners[$entityClass][$phase->value]) > 0;
}
```

### Duration Measurement Strategy

```php
$startTime = microtime(true);
// ... execute query ...
$durationMs = (microtime(true) - $startTime) * 1000;
```

**Overhead:** Two `microtime(true)` calls when query lifecycle listeners are registered. Zero calls when no listeners.

### Memory Safety

- Event objects are `readonly` — no mutation, safe to share.
- Event objects are created only when listeners exist — no allocation in fast path.
- afterCommit callback buffers are cleared after commit/rollback — no leak.
- Transaction-scoped buffers are instance variables, NOT static — safe per-connection.

### Long-Lived Runtime Safety

```php
// Transaction-scoped buffers live on Transaction object
// Transaction object is created per-request/per-connection
// After commit/rollback, buffers are cleared:
$this->afterCommitCallbacks = [];
$this->afterRollbackCallbacks = [];
```

**Warm worker safety:** Transaction object must not survive between requests. Each request creates a new Transaction
scope.

## Failure Behavior

| Scenario                                | Behavior                                                         |
|-----------------------------------------|------------------------------------------------------------------|
| No listeners registered                 | Fast path — single array lookup, no allocation                   |
| Listener throws during pre-commit hook  | Exception bubbles, operation does NOT proceed                    |
| Listener throws during post-commit hook | Exception bubbles, commit is already done                        |
| Listener throws during query execution  | Exception bubbles, query already failed                          |
| afterCommit callback throws             | Exception bubbles — commit already succeeded, side effect failed |
| afterRollback callback throws           | Exception bubbles — rollback already succeeded                   |

## Performance Expectations Table

| Scenario                            | Expected Overhead          | Measurement                          |
|-------------------------------------|----------------------------|--------------------------------------|
| No lifecycle listeners              | < 0.001ms per operation    | Single array lookup                  |
| 1 entity lifecycle listener         | < 0.01ms per operation     | Listener invocation + event creation |
| 1 query lifecycle listener          | < 0.005ms per query        | Listener invocation + event creation |
| 1 transaction lifecycle listener    | Negligible per transaction | Listener invocation at boundary only |
| afterCommit with 1 callback         | Negligible per commit      | Callback invocation after commit     |
| Slow query check (no slow listener) | < 0.001ms                  | Single comparison                    |
| Slow query check (slow detected)    | < 0.01ms                   | Listener invocation + event creation |

## Next Allowed Action

V5.8-16 Security Design.
