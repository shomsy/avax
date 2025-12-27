# Identity Map (Unit of Work)

## What it is

The `IdentityMap` is a "Memory" and "To-Do List" for database operations. It serves two primary roles:

1. **Object Tracking**: It ensures that if you load the same record multiple times, you get the exact same object instance.
2. **Buffer (Unit of Work)**: it schedules mutations (`INSERT`/`UPDATE`/`DELETE`) to be executed as a single batch later, typically at the end of a transaction.

## Why it exists

- **Performance**: Talking to the database is slow. By buffering 100 changes and sending them in one go, we reduce network round-trips significantly.
- **Consistency**: It prevents "Partial Writes". Without an IdentityMap, if your script fails halfway through 10 updates, half your data is saved and half isn't. With the map, they all commit or fail together.
- **Memory Efficiency**: Reusing objects instead of creating new ones for every query saves RAM.

## When to use

- Use it whenever you are performing multiple related changes to the database.
- It is automatically used when you call `deferred()` on the `QueryBuilder`.

## When *not* to use

- Don't rely on it for "dirty reads". If you buffer an update, a subsequent `SELECT` query will **not** see that change until the map is flushed (`execute()`).

## How it flushes

When you call `execute()` on the `IdentityMap`:

1. It starts a database transaction.
2. It executes every scheduled SQL statement in the order they were added.
3. It commits the transaction if all operations succeed.
4. It clears its internal buffer.

## Common pitfalls

- **Stale Data**: Forgetting that `SELECT` queries bypass the buffer.
- **Unbounded Growth**: If you buffer thousands of changes over a long-running process (like a worker), you might hit memory limits. Flush often.
- **Order of operations**: If your changes depend on each other (e.g. creating a parent then a child), make sure they are scheduled in the correct order.
