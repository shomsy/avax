# Deferred Execution (IdentityMap)

## What “deferred” means here
`deferred()` tells the builder to buffer mutation statements (INSERT/UPDATE/DELETE) into an IdentityMap instead of sending them to the database immediately. The statements are scheduled with their bindings and later executed as a batch.

## Why it exists
- **Unit of Work:** Group related changes so they can be committed atomically and in a predictable order.
- **Performance:** Reduce round-trips by batching writes.
- **Consistency:** Keep mutations aligned with the transaction lifecycle managed by `Transaction`/`IdentityMap`.

## When to use
- You have multiple dependent writes that should succeed or fail together.
- You want to batch many small mutations to reduce driver chatter.
- You already have an IdentityMap on the orchestrator or can supply one ad hoc.

## When *not* to use
- You need to read back the mutated rows immediately in the same flow (reads are never deferred).
- You don’t have an IdentityMap wired (or cannot provide one).
- You depend on side effects that must happen before the current request finishes (e.g., triggers that must fire right away).

## How to use (builder-level)
```php
$builder = new QueryBuilder($grammar, $orchestrator);

// provide a map explicitly (per-call)
$deferred = $builder->deferred($identityMap);
$deferred->table('users')->insert(['email' => 'a@example.com']);
$deferred->table('users')->where('id', 5)->update(['active' => false]);

// later, flush via the map (often inside a transaction)
$identityMap->execute();
```

If the orchestrator already has an IdentityMap, you can call `deferred()` without arguments; it will reuse the existing map.

## How it flushes
- `IdentityMap::execute()` runs all buffered jobs inside a transaction.
- When `QueryOrchestrator::transaction()` completes and the orchestrator has an IdentityMap, it calls `IdentityMap::execute()` automatically.

## Common pitfalls
- **No map available:** `deferred()` throws if no IdentityMap is present or provided.
- **Reads are live:** `get()`, `first()`, etc. always hit the database immediately; they do not see buffered mutations.
- **Long-lived buffers:** Keep IdentityMap lifetimes scoped (per request/unit-of-work) to avoid unbounded queues.

## Security/logging
- Query telemetry redacts bindings by default; set `DB_LOG_BINDINGS=raw` or `logging.include_raw_bindings` only in controlled, non-production debugging sessions.
