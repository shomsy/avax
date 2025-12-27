# Database Events and Telemetry

## What it is

The Database component emits events during its lifecycle to allow for observability, logging, and metrics gathering without coupling these concerns into the core logic.

## Why it exists

- **Observability**: You can track how long queries take and which parts of your app are database-intensive.
- **Security Audit**: Log whenever a connection is opened or failed.
- **Decoupling**: The core `QueryBuilder` doesn't need to know about your ELK stack or Prometheus metrics; it just fires an event, and a subscriber handles the rest.

## Core Events

- `QueryExecuted`: Fired after a SELECT or Statement finishes. Contains the SQL, bindings, time taken, and the connection name.
- `ConnectionOpened`: Fired when a physical link to the DB is established.
- `ConnectionAcquired`: Fired when a connection is borrowed from a pool.
- `TransactionBeginning`/`Committed`/`RolledBack`: lifecycle events for transactions.

## Execution Scope (Correlation)

To link disparate events (e.g., a query and a connection failure) to the same user request, we use an `ExecutionScope`.

- Every event carries a `correlationId`.
- This ID is passed from the `ConnectionManager` down to the `Executor`.
- It allows you to search your logs for a single ID and see the entire "story" of that request across multiple components.

## Examples

```php
// Subscribing to query telemetry
$eventBus->subscribe(QueryExecuted::class, function (QueryExecuted $event) {
    Log::info("SQL: {$event->sql} took {$event->durationMs}ms");
});
```

## Common pitfalls

- **Over-logging**: Logging every single query in a high-traffic app can overwhelm your log storage. Use sampling or only log slow queries.
- **Side effects in listeners**: Avoid performing database writes inside a database event listener, as this can lead to infinite loops or deadlocks.
