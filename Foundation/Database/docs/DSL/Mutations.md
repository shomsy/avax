# Database Mutations (Writing data)

## What it does

Mutation methods (`insert()`, `update()`, `delete()`, `upsert()`, `statement()`) transform the builder state into modification queries.

## Why it exists

- Abstract away SQL syntax for complex writes (UPSERT).
- Protect against SQL injection via automatic binding normalization.
- Enable hooks like soft-deletes and deferred execution.

## When to use

- `insert()`: Create new records.
- `update()`: Modify existing records matching the criteria.
- `delete()`: Remove records (or mark them as deleted if using soft-deletes).
- `statement()`: Run administrative DDL or maintenance commands.

## When *not* to use

- Do not use `update()` to change a single field if atomic `increment()`/`decrement()` is more appropriate for thread safety.
- Avoid large bulk `insert()` calls without batching (check `upsert()` or driver-specific batch methods).

## Examples

```php
// Simple Insert
$builder->table('logs')->insert(['event' => 'login', 'user_id' => 1]);

// Criteria-based Update
$builder->table('users')->where('id', 1)->update(['last_login' => now()]);

// Soft or Hard Delete
$builder->table('sessions')->where('expired', true)->delete();
```

## Common pitfalls

- **Missing criteria**: Calling `update()` or `delete()` without a `where()` clause might affect the entire table. The builder usually requires explicit criteria.
- **DDL Success**: `statement()` returns success based on driver acceptance, which might be `true` even if 0 rows were altered (e.g., `CREATE TABLE`).
- **Deferred writes**: If `deferred()` is active, these methods buffer the job rather than hitting the database. See [Deferred Execution](DeferredExecution.md).
