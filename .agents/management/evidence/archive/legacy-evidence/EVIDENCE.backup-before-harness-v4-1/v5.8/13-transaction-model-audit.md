# V5.8-13: Transaction Model Audit

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Transaction Model Audit

## Current Transaction Implementation

### Transaction Manager

**File:** `components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php`

- Nested transactions with savepoints
- RAII scope (transaction scope object)
- Rollback and commit support
- Connection-aware

### Transaction Runner

**File:** `components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/RunTransaction.php`

- Executes callbacks in transactions
- Wraps Transaction scope

### Transaction On Connection

**File:** `components/DataStack/Database/System/Capabilities/Transactions/OnConnection/OnConnection.php`

- Resolves transaction manager per connection
- Multi-connection transaction support

### Connection Interface

**File:** `components/DataStack/Database/System/Capabilities/Connections/Contracts/DatabaseConnection.php`

- `beginTransaction()` — starts transaction
- `commit()` — commits transaction
- `rollBack()` — rolls back transaction
- `exec()` — executes SQL

## Audit Answers

| Question                                           | Answer                                                                                       | Location                                    | Readiness                   |
|----------------------------------------------------|----------------------------------------------------------------------------------------------|---------------------------------------------|-----------------------------|
| Where are transactions started?                    | `Transaction.php` — `begin()` method calls `DatabaseConnection::beginTransaction()`          | Transactions/RunTransaction/Transaction.php | READY                       |
| Where are commits performed?                       | `Transaction.php` — `commit()` method calls `DatabaseConnection::commit()`                   | Transactions/RunTransaction/Transaction.php | READY                       |
| Where are rollbacks performed?                     | `Transaction.php` — `rollback()` method calls `DatabaseConnection::rollBack()`               | Transactions/RunTransaction/Transaction.php | READY                       |
| Are nested transactions/savepoints supported?      | YES — savepoints used for nested transactions                                                | Transaction.php                             | READY                       |
| Is there a transaction context object?             | YES — Transaction object acts as scope                                                       | Transaction.php                             | READY                       |
| Is there a unit-of-work?                           | YES — UnitOfWork in ORM                                                                      | ORM/UnitOfWork/UnitOfWork.php               | NEEDS_INTEGRATION           |
| Can afterCommit callbacks be safely buffered?      | YES — Transaction object can buffer callbacks before commit                                  | Transaction.php (extension point)           | READY_FOR_AFTER_COMMIT      |
| Can afterRollback callbacks be safely buffered?    | YES — Transaction object can buffer callbacks before rollback                                | Transaction.php (extension point)           | READY_FOR_AFTER_COMMIT      |
| Can callbacks leak between requests/workers?       | RISK — Transaction object may survive between requests in long-lived runtimes                | Transaction.php + warm worker safety        | NEEDS_REQUEST_SCOPE_SAFETY  |
| What happens if commit fails?                      | Exception bubbles from `Transaction::commit()`                                               | Transaction.php                             | READY                       |
| What happens if listener fails after commit?       | Listener failure bubbles — afterCommit callbacks run after commit, so failure is post-commit | Lifecycle design                            | READY (documented behavior) |
| Does rollback clear pending afterCommit callbacks? | YES — rollback discards all buffered afterCommit callbacks                                   | Lifecycle design                            | READY                       |

## Classification

| Area                              | Classification                 | Notes                                                     |
|-----------------------------------|--------------------------------|-----------------------------------------------------------|
| Transaction start/commit/rollback | READY_FOR_AFTER_COMMIT         | Existing Transaction.php can be extended                  |
| Nested transactions/savepoints    | READY                          | Already supported                                         |
| Transaction context object        | READY                          | Transaction object exists                                 |
| Unit-of-work integration          | NEEDS_UNIT_OF_WORK_INTEGRATION | UnitOfWork exists but not integrated with lifecycle       |
| Request scope safety              | NEEDS_REQUEST_SCOPE_SAFETY     | Long-lived runtimes must reset transaction-scoped buffers |
| AfterCommit callback buffering    | READY_FOR_AFTER_COMMIT         | Transaction object can buffer callbacks                   |
| AfterRollback callback buffering  | READY_FOR_AFTER_COMMIT         | Transaction object can buffer callbacks                   |
| Callback leak prevention          | NEEDS_REQUEST_SCOPE_SAFETY     | Must ensure callbacks don't survive between requests      |
| Commit failure handling           | READY                          | Exception bubbles correctly                               |
| Post-commit listener failure      | READY                          | Documented: bubbles, doesn't undo commit                  |
| Rollback clears callbacks         | READY                          | Documented: rollback discards afterCommit buffer          |

## Integration Points

### AfterCommit Buffering in Transaction.php

```php
final class Transaction
{
    private array $afterCommitCallbacks = [];
    private array $afterRollbackCallbacks = [];

    public function afterCommit(callable $callback): void
    {
        if ($this->nestingLevel === 0) {
            // No active transaction — run immediately
            $callback();
            return;
        }
        $this->afterCommitCallbacks[] = $callback;
    }

    public function afterRollback(callable $callback): void
    {
        $this->afterRollbackCallbacks[] = $callback;
    }

    public function commit(): void
    {
        // ... existing commit logic ...
        if ($this->nestingLevel === 0) {
            // Outermost commit — run afterCommit callbacks
            foreach ($this->afterCommitCallbacks as $callback) {
                $callback();
            }
            $this->afterCommitCallbacks = [];
        }
    }

    public function rollback(): void
    {
        // ... existing rollback logic ...
        if ($this->nestingLevel === 0) {
            // Outermost rollback — run afterRollback callbacks
            foreach ($this->afterRollbackCallbacks as $callback) {
                $callback();
            }
            // Clear afterCommit callbacks — they won't run
            $this->afterCommitCallbacks = [];
        }
    }
}
```

## Next Allowed Action

V5.8-14 QueryBuilder/ORM Integration Audit.
