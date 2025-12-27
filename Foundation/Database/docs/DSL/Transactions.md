# Database Transactions

## What it does

`transaction()` allows you to group multiple database operations into an atomic unit. Either all operations succeed, or all are rolled back.

## Why it exists

- **Atomicity**: Ensure data integrity across multiple tables (e.g., creating an Order and deducting Inventory).
- **Isolation**: Prevent other processes from seeing partial state changes.
- **Unit of Work**: In many systems, transactions are the primary way to manage IdentityMap flushing and deferred mutations.

## When to use

- Any operation involving two or more related writes.
- When using `deferred()` execution to ensure all buffered jobs flush together.
- For business logic critical to consistency (Financial transfers, Order processing).

## When *not* to use

- Long-running processes (e.g., calling external APIs inside a transaction). This keeps database locks open too long.
- Simple single-row reads that don't depend on consistent state of others.

## Examples

```php
$builder->transaction(function (QueryBuilder $db) {
    $db->table('accounts')->where('id', 1)->decrement('balance', 100);
    $db->table('accounts')->where('id', 2)->increment('balance', 100);
});
```

## Common pitfalls

- **Unintended side effects**: Transactions do not roll back non-database actions (like sending emails or deleting files).
- **Deadlocks**: Complex transactions hitting the same tables in different orders can cause the database to hang.
- **IdentityMap Sync**: If NOT using the orchestrator's transaction manager, manually ensure your IdentityMap is flushed or cleared upon failure.

## Advanced: Nesting and Savepoints

The system supports **Nested Transactions** using database **Savepoints**.

- If you start a transaction while one is already active, it creates a `SAVEPOINT`.
- Rolling back an inner transaction only undoes changes made since that savepoint.
- Rolling back the *outer* transaction undoes everything, including successful inner transactions.

### Manual Savepoints

You can create named bookmarks manually:

```php
$db->transaction(function ($db) {
    $db->savepoint('before_risky_task');
    try {
        // risky biz
    } catch (Exception $e) {
        $db->rollbackTo('before_risky_task');
    }
});
```
