# V5.8-14: QueryBuilder/ORM Integration Audit

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — QueryBuilder/ORM Integration Audit

## QueryBuilder Integration Points

### Query Execution

**File:** `components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php`

The QueryOrchestrator executes compiled SQL via ExecutorInterface. This is the natural integration point for query
lifecycle events.

| Operation         | File/Method                                           | Lifecycle Hook Possible? | Risk                                    | Recommended Integration Point                         |
|-------------------|-------------------------------------------------------|--------------------------|-----------------------------------------|-------------------------------------------------------|
| select            | QueryOrchestrator::execute()                          | YES — wrap execution     | Low — single execution path             | Before/after execute()                                |
| insert            | QueryBuilder::insert() → ExecutorInterface::execute() | YES                      | Medium — insert may have multiple paths | ExecutorInterface::execute()                          |
| update            | QueryBuilder::update() → ExecutorInterface::execute() | YES                      | Medium — update may have multiple paths | ExecutorInterface::execute()                          |
| delete            | QueryBuilder::delete() → ExecutorInterface::execute() | YES                      | Medium — delete may have multiple paths | ExecutorInterface::execute()                          |
| transaction begin | DatabaseConnection::beginTransaction()                | YES                      | Low — single entry point                | Before/after beginTransaction()                       |
| commit            | DatabaseConnection::commit()                          | YES                      | Low — single entry point                | Before/after commit(), then afterCommit callbacks     |
| rollback          | DatabaseConnection::rollBack()                        | YES                      | Low — single entry point                | Before/after rollBack(), then afterRollback callbacks |

### Entity Lifecycle Integration Points

**ORM UnitOfWork:** `components/DataStack/Database/System/ORM/UnitOfWork/UnitOfWork.php`

| Operation          | File/Method                                       | Lifecycle Hook Possible? | Risk                                 | Recommended Integration Point               |
|--------------------|---------------------------------------------------|--------------------------|--------------------------------------|---------------------------------------------|
| persist new entity | UnitOfWork::persist() → EntityPersister::insert() | YES                      | Medium — must detect new vs existing | EntityPersister::insert(), before/after     |
| update entity      | UnitOfWork::flush() → EntityPersister::update()   | YES                      | Medium — must detect dirty entities  | EntityPersister::update(), before/after     |
| delete entity      | UnitOfWork::remove() → EntityPersister::delete()  | YES                      | Medium — cascade behavior            | EntityPersister::delete(), before/after     |
| find entity        | UnitOfWork::find() → EntityPersister::find()      | YES                      | Low — read operation                 | EntityPersister::find(), optional telemetry |

## Integration Strategy

### Query Lifecycle

**Integration point:** `ExecutorInterface::execute()` or `QueryOrchestrator::execute()`

```php
// QueryOrchestrator::execute() (conceptual)
public function execute(string $sql, array $bindings = []): Result
{
    $phase = $this->resolveQueryPhase($sql);  // select, insert, update, delete

    // Fire QueryExecuting
    $this->invokeQueryLifecycle(QueryLifecyclePhase::Executing, new QueryExecuting(...));

    $startTime = microtime(true);
    try {
        $result = $this->executor->execute($sql, $bindings);
        $duration = (microtime(true) - $startTime) * 1000;

        // Fire QueryExecuted
        $this->invokeQueryLifecycle(QueryLifecyclePhase::Executed, new QueryExecuted(...));

        // Check slow threshold
        $this->checkSlowQuery($duration, new QueryExecuted(...));

        return $result;
    } catch (Throwable $e) {
        // Fire QueryFailed
        $this->invokeQueryLifecycle(QueryLifecyclePhase::Failed, new QueryFailed(...));
        throw $e;
    }
}
```

### Entity Lifecycle

**Integration point:** `EntityPersister::insert()`, `EntityPersister::update()`, `EntityPersister::delete()`

```php
// EntityPersister::insert() (conceptual)
public function insert(string $entityClass, object $entity): void
{
    // Fire Creating
    $this->invokeEntityLifecycle($entityClass, EntityLifecyclePhase::Creating, new EntityCreating(...));

    // Build and execute INSERT
    $sql = $this->buildInsertSql($entityClass, $entity);
    $this->connection->execute($sql, $this->extractBindings($entity));

    // Fire Created
    $this->invokeEntityLifecycle($entityClass, EntityLifecyclePhase::Created, new EntityCreated(...));
}
```

### Transaction Lifecycle

**Integration point:** `Transaction::begin()`, `Transaction::commit()`, `Transaction::rollback()`

```php
// Transaction::commit() (conceptual)
public function commit(): void
{
    // Fire TransactionBeginning (only for outermost)
    if ($this->nestingLevel === 0) {
        $this->invokeTransactionLifecycle(TransactionLifecyclePhase::Beginning, new TransactionBeginning(...));
    }

    try {
        // ... existing commit logic ...
        $this->connection->commit();

        // Fire TransactionCommitted
        if ($this->nestingLevel === 0) {
            $this->invokeTransactionLifecycle(TransactionLifecyclePhase::Committed, new TransactionCommitted(...));

            // Run afterCommit callbacks
            $this->invokeTransactionLifecycle(TransactionLifecyclePhase::AfterCommit, new AfterCommit(...));
        }
    } catch (Throwable $e) {
        if ($this->nestingLevel === 0) {
            $this->invokeTransactionLifecycle(TransactionLifecyclePhase::Failed, new TransactionFailed(...));
        }
        throw $e;
    }
}
```

## Rules

1. **Choose integration points where behavior naturally happens.**
2. **Avoid duplicating hooks across multiple execution paths.**
3. **If multiple write paths exist, design canonical write lifecycle boundary.**
4. **Raw SQL paths may get query telemetry, not entity lifecycle events.**
5. **QueryBuilder bulk operations fire bulk events, NOT per-entity events.**
6. **ORM UnitOfWork fires entity lifecycle events for loaded entities.**

## Risk Assessment

| Risk                                                   | Level  | Mitigation                                                                                |
|--------------------------------------------------------|--------|-------------------------------------------------------------------------------------------|
| Multiple insert paths (QueryBuilder vs ORM vs raw SQL) | Medium | Canonical integration at ExecutorInterface for QueryBuilder, EntityPersister for ORM      |
| Performance overhead in hot path                       | Medium | No-listener fast path, compiled registry, minimal allocation                              |
| Lifecycle listener throws during persist               | Low    | Exception bubbles, entity NOT persisted (pre-commit hook) or persisted (post-commit hook) |
| Transaction state leaks between requests               | Medium | Warm worker safety — reset transaction-scoped buffers                                     |
| Bulk operations faking per-entity events               | Low    | Design rule: bulk events only, per-entity requires loaded entities                        |

## Next Allowed Action

V5.8-15 Runtime and Performance Design.
