# Telemetry & Events

This document covers the event system and logging capabilities of the Database component, explaining how to monitor database activity.

---

## Table of Contents

### Event System

- [Event (Base Class)](#event)
- [EventBus](#eventbus)
- [EventSubscriberInterface](#eventsubscriberinterface)

### Database Events

- [ConnectionOpened](#connectionopened)
- [ConnectionFailed](#connectionfailed)
- [ConnectionAcquired](#connectionacquired)
- [QueryExecuted](#queryexecuted)

### Subscribers

- [DatabaseLoggerSubscriber](#databaseloggersubscriber)

### Support

- [ExecutionScope](#executionscope)

---

# Event System

## Event

**Base class for all database events.**

All events extend this class, which provides common functionality:

- **Timestamp** — When the event occurred
- **Correlation ID** — Links related events together (same request, same transaction)
- **Sequence Number** — Order within a correlation group

Think of events as "postcards" that get sent whenever something happens in the database layer. Each postcard has a timestamp and tracking number.

```php
class Event
{
    public readonly string $id;           // Unique event ID
    public readonly float $occurredAt;    // Unix timestamp with microseconds
    public readonly string $correlationId; // Groups related events
    public readonly int $sequence;        // Order within group
}
```

---

## EventBus

**Central dispatcher for database events.**

The "post office" that receives events and delivers them to interested subscribers. Components dispatch events to the bus, and the bus notifies all registered listeners.

**Dispatching Events:**

```php
$eventBus = new EventBus();

// Dispatch an event
$eventBus->dispatch(new QueryExecuted(
    sql: 'SELECT * FROM users WHERE id = ?',
    bindings: [42],
    timeMs: 1.5,
    connectionName: 'primary',
    correlationId: 'req_abc123'
));
```

**Registering Subscribers:**

```php
$eventBus->subscribe(new DatabaseLoggerSubscriber($logger));
$eventBus->subscribe(new MetricsCollector());
$eventBus->subscribe(new SlowQueryAlerter());
```

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `dispatch($event)` | Send an event to all subscribers |
| `subscribe($subscriber)` | Register a new listener |
| `unsubscribe($subscriber)` | Remove a listener |

---

## EventSubscriberInterface

**Contract for event listeners.**

Any class that wants to receive events must implement this interface. It defines which events the subscriber handles.

```php
interface EventSubscriberInterface
{
    /**
     * Get the list of events this subscriber handles.
     * @return array<class-string, string> Event class => handler method
     */
    public function getSubscribedEvents(): array;
}

// Example implementation
class MySubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            QueryExecuted::class => 'onQueryExecuted',
            ConnectionOpened::class => 'onConnectionOpened',
        ];
    }

    public function onQueryExecuted(QueryExecuted $event): void
    {
        // Handle the event
    }

    public function onConnectionOpened(ConnectionOpened $event): void
    {
        // Handle the event
    }
}
```

---

# Database Events

## ConnectionOpened

**Fired when a new database connection is established.**

This event indicates that a fresh PDO connection was successfully created. Useful for monitoring connection frequency and pool misses.

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `connectionName` | string | Which connection was opened |
| `timeMs` | float | How long it took to connect |
| `correlationId` | string | Request/trace identifier |

```php
// Listening example
public function onConnectionOpened(ConnectionOpened $event): void
{
    $this->logger->info('New database connection opened', [
        'connection' => $event->connectionName,
        'time_ms' => $event->timeMs,
        'correlation_id' => $event->correlationId
    ]);
}
```

---

## ConnectionFailed

**Fired when a connection attempt fails.**

An exception occurred while trying to connect to the database. The event includes the exception for debugging.

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `connectionName` | string | Which connection failed |
| `exception` | Throwable | The error that occurred |
| `correlationId` | string | Request/trace identifier |

```php
public function onConnectionFailed(ConnectionFailed $event): void
{
    $this->logger->error('Database connection failed', [
        'connection' => $event->connectionName,
        'error' => $event->exception->getMessage(),
        'correlation_id' => $event->correlationId
    ]);
    
    // Maybe alert on-call engineer
    $this->alertingService->critical('Database unreachable');
}
```

---

## ConnectionAcquired

**Fired when a connection is borrowed from a pool.**

Tracks pool activity. The `isRecycled` flag indicates whether the connection was reused (good) or freshly created (pool miss).

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `connectionName` | string | Which pool the connection came from |
| `isRecycled` | bool | Was this a cached connection? |
| `correlationId` | string | Request/trace identifier |

```php
public function onConnectionAcquired(ConnectionAcquired $event): void
{
    $type = $event->isRecycled ? 'recycled' : 'new';
    
    $this->metrics->increment("pool.acquisitions.{$type}");
    
    if (!$event->isRecycled) {
        // Track pool misses - might need to increase pool size
        $this->metrics->increment('pool.misses');
    }
}
```

---

## QueryExecuted

**Fired after every SQL query is executed.**

The most important event for debugging and performance monitoring. Contains the SQL, bindings, and execution time.

⚠️ **Security:** Bindings are marked with `#[SensitiveParameter]` to prevent them from appearing in stack traces (they might contain passwords or PII).

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `sql` | string | The executed SQL statement |
| `bindings` | array | Parameter values (sensitive!) |
| `timeMs` | float | Execution time in milliseconds |
| `connectionName` | string | Which connection was used |
| `correlationId` | string | Request/trace identifier |

```php
public function onQueryExecuted(QueryExecuted $event): void
{
    // Log all queries
    $this->logger->debug('Query executed', [
        'sql' => $event->sql,
        'time_ms' => $event->timeMs,
        'connection' => $event->connectionName,
        'correlation_id' => $event->correlationId
        // Note: bindings intentionally omitted for security
    ]);
    
    // Alert on slow queries
    if ($event->timeMs > 1000) {
        $this->logger->warning('Slow query detected', [
            'sql' => $event->sql,
            'time_ms' => $event->timeMs
        ]);
    }
}
```

---

# Subscribers

## DatabaseLoggerSubscriber

**Built-in PSR-3 logging subscriber.**

A ready-to-use subscriber that logs all database events using any PSR-3 compatible logger (Monolog, etc.).

**Configuration:**

```php
use Psr\Log\LoggerInterface;

$subscriber = new DatabaseLoggerSubscriber($logger);
$eventBus->subscribe($subscriber);

// Now all database events are automatically logged
```

**What it logs:**

| Event | Log Level | Message |
|-------|-----------|---------|
| ConnectionOpened | DEBUG | "Database connection opened" |
| ConnectionFailed | ERROR | "Database connection failed" |
| ConnectionAcquired | DEBUG | "Connection acquired from pool" |
| QueryExecuted | DEBUG | "Query executed" |

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `onConnectionOpened($event)` | Logs new connections |
| `onConnectionFailed($event)` | Logs connection errors |
| `onConnectionAcquired($event)` | Logs pool acquisitions |
| `onQueryExecuted($event)` | Logs query execution |

---

# Support

## ExecutionScope

**Container for request-level tracing context.**

Carries the "luggage tag" that links all events from the same request together. Typically contains a correlation ID (trace ID) that's passed through all layers.

Think of it as an airline luggage tag: it travels with your request and helps you track all related activity.

```php
$scope = new ExecutionScope(
    correlationId: 'req_abc123',
    startedAt: microtime(true)
);

// Attach to connection flow
$connection = DirectConnectionFlow::begin()
    ->using($config)
    ->withScope($scope)
    ->connect();

// Attach to pool
$pool->withScope($scope);

// Now all events from this connection/pool include the correlation ID
```

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `correlationId` | string | Unique request/trace identifier |
| `startedAt` | float | When this scope began |

---

## Best Practices

1. **Always attach a correlation ID** — Makes debugging distributed systems much easier.

2. **Use the built-in logger subscriber** — Quick way to get visibility into database activity.

3. **Monitor slow queries** — Set up alerts for queries exceeding a threshold.

4. **Watch pool metrics** — High miss rates indicate the pool is too small.

5. **Don't log sensitive bindings** — They may contain passwords or personal data.

6. **Use structured logging** — Include all event properties as context, not just the message.

---

## See Also

- [Connection Management](Connections.md)
- [Architecture Overview](Architecture.md)
- [QueryBuilder](../DSL/QueryBuilder.md)
