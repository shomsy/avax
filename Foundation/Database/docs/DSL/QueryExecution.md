# Query Retrieval (Fetch operations)

## What it does

Retrieval methods (`get()`, `first()`, `pluck()`, `value()`, `exists()`, `count()`) translate the builder state into a SELECT query and return synchronized data results.

## Why it exists

- Provides a high-level API for data access without writing manual PDO fetch loops.
- Automatically handles dialect differences in LIMIT/OFFSET and aggregation.
- Integrates with soft-delete filters and other global query scopes.

## When to use

- `get()`: Retrieve a full collection of matching records.
- `first()`: Retrieve the most relevant single record (applies LIMIT 1).
- `exists()`: Efficiently check presence without data transfer.
- `pluck()`: Extract specific columns into flat arrays.

## When *not* to use

- Use `find($id)` for simple primary key lookups instead of building complex `where()` clauses.
- Avoid large `get()` calls on unindexed columns or massive tables; use `count()` or `exists()` if you only need metadata.

## Examples

```php
// Basic collection
$users = $builder->table('users')->where('active', true)->get();

// Single column map
$emails = $builder->table('users')->pluck('email', 'id'); // [id => email]

// Metadata
if ($builder->table('orders')->where('status', 'pending')->exists()) {
    $count = $builder->count();
}
```

## Common pitfalls

- **Memory exhaustion**: Calling `get()` on millions of rows. Use pagination or chunking (if available).
- **Null results**: `first()` returns `null` or a default value, not a collection.
- **Aggregates are terminal**: Calling `count()` executes the query immediately; you cannot chain more filters after it.
