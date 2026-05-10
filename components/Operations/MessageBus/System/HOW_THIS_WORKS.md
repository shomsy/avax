# HOW_THIS_WORKS.md — MessageBus Component

## What This Component Does

The MessageBus component provides an in-process message dispatching system for the AvaX framework. It implements three distinct bus types:

- **CommandBus**: Dispatches commands to exactly one handler (one command → one handler). Used for state-changing operations.
- **QueryBus**: Dispatches queries to handlers and returns query results. Used for read operations.
- **EventBus**: Publishes domain events to multiple registered listeners. Used for side-effects and cross-component communication.

The component supports middleware pipelines, message envelopes with correlation tracking, transactional command handling, event outbox for reliable delivery, and basic read-model projections from events.

## What This Component Does NOT Do

- Does not provide async or cross-process message delivery (all dispatching is synchronous in-process).
- Does not implement saga orchestration (sagas belong in ApplicationWorkflow).
- Does not provide persistent inbox or outbox drivers (interfaces exist, concrete implementations are incomplete).
- Does not perform automatic handler discovery (handlers must be registered explicitly).
- Does not serialize or deserialize messages for transport across process boundaries.
- Does not implement incremental projections or catch-up subscriptions.

## Public API

### Entry Points

**`components/Operations/MessageBus/System/PublicSurface/`**

| Class | Method | Purpose |
|-------|--------|---------|
| `MessageBus` | `dispatch($command)` | Send a command to its single handler |
| `MessageBus` | `ask($query)` | Send a query and return the result |
| `MessageBus` | `publish($event)` | Publish an event to all registered listeners |

### Base Message Types

| Class | Purpose |
|-------|---------|
| `Command` | Base class for all commands |
| `Query` | Base class for all queries |
| `DomainEvent` | Base class for all domain events |

### Envelope

| Class | Purpose |
|-------|---------|
| `MessageEnvelope` | Wraps any message with metadata: correlation ID, causation ID, timestamp |

### Exceptions

| Exception | Purpose |
|-----------|---------|
| `MessageBusException` | Base exception for all message bus errors |
| `MessageHandlerNotFound` | Thrown when no handler is registered for a message |
| `MessageHandlingFailed` | Thrown when a handler throws; wraps original exception as cause |

## Internal Flow

### Command Dispatch (`DispatchCommand` Flow)

1. Command is wrapped in a `MessageEnvelope` with correlation ID and timestamp.
2. Envelope passes through the middleware pipeline (`BusMiddleware`).
3. `BusValidationMiddleware` validates the command if validation rules exist.
4. `BusLoggingMiddleware` logs dispatch via the Observability component.
5. `BusTransactionMiddleware` optionally wraps handling in a database transaction.
6. Handler is resolved by command type (in-memory registry).
7. Handler executes; result (if any) is returned.
8. On failure, transaction rolls back and `MessageHandlingFailed` is thrown with the original exception as cause.

### Query Dispatch (`HandleQuery` Flow)

1. Query is wrapped in a `MessageEnvelope`.
2. Middleware pipeline runs (logging, validation if applicable).
3. Handler is resolved by query type.
4. Handler executes and returns the query result.

### Event Publication (`PublishEvent` Flow)

1. Event is wrapped in a `MessageEnvelope`.
2. Middleware pipeline runs (logging).
3. All registered listeners for the event type are invoked.
4. Listeners execute synchronously in registration order.
5. Listener failures do not stop other listeners unless configured.

### Transactional Dispatch (`DispatchTransactionally` Flow)

1. Command is dispatched through the middleware pipeline.
2. `BusTransactionMiddleware` opens a database transaction before handler execution.
3. On success, transaction commits.
4. On failure, transaction rolls back and exception propagates.

### Outbox Publishing (`PublishToOutbox` Flow)

1. Event is written to the outbox store before being published.
2. Outbox ensures the event is persisted even if publishing fails.
3. A background process (not provided by this component) reads and publishes outbox entries.

### Retry (`RetryFailedMessage` Flow)

1. Failed messages are tracked (typically by the Resilience component).
2. Retry flow re-dispatches a failed message through the normal pipeline.
3. Retry limits and backoff are managed externally.

### Projections

- `Projection` reads domain events and updates read-model state.
- Basic implementation: processes events and materializes a view.
- No incremental projection or catch-up subscription support.

### Idempotency

- `InMemoryInbox` tracks processed message IDs to detect and skip duplicates.
- In-memory only; no persistent inbox implementation.

## Dependencies

| Component | Usage |
|-----------|-------|
| **Observability** | Logging middleware writes to Observability loggers; correlation IDs flow through envelopes for tracing |
| **Resilience** | Retry for failed messages is coordinated with the Resilience component |
| **Database** | Transaction middleware uses database transactions for command handling |
| **DataTransfer** | Commands, queries, and requests use DataTransfer DataObjects for structured input |

## Failure Behavior

| Scenario | Behavior |
|----------|----------|
| No handler registered | `MessageHandlerNotFound` exception thrown |
| Handler throws an exception | `MessageHandlingFailed` thrown with original exception as `cause` |
| Transaction middleware active | Transaction rolls back on handler failure |
| Listener throws during event publish | Other listeners continue unless configured otherwise |
| Duplicate message detected | `InMemoryInbox` skips re-processing |
| Retry invoked | Failed message is re-dispatched through the normal pipeline |

## Runtime Safety

- **Correlation tracing**: Every message envelope carries a correlation ID that flows through the entire dispatch pipeline, enabling distributed tracing across logs and events.
- **Middleware pipeline**: All cross-cutting concerns (logging, validation, transactions) run through a consistent middleware pipeline, ensuring uniform behavior.
- **Idempotency**: `InMemoryInbox` prevents duplicate message processing within the same process lifetime.
- **Transaction boundaries**: `BusTransactionMiddleware` ensures command handlers either fully commit or fully roll back, with no partial state.
- **Exception chaining**: `MessageHandlingFailed` always preserves the original exception as its cause, ensuring debuggability.

## Examples

### Dispatching a Command

```php
use AvaX\Operations\MessageBus\System\PublicSurface\MessageBus;
use AvaX\Operations\MessageBus\System\PublicSurface\Command;

class PlaceOrder extends Command {
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId,
    ) {}
}

$bus->dispatch(new PlaceOrder('ord-1', 'cust-1'));
```

### Asking a Query

```php
use AvaX\Operations\MessageBus\System\PublicSurface\Query;

class GetOrderTotal extends Query {
    public function __construct(
        public readonly string $orderId,
    ) {}
}

$total = $bus->ask(new GetOrderTotal('ord-1'));
```

### Publishing an Event

```php
use AvaX\Operations\MessageBus\System\PublicSurface\DomainEvent;

class OrderPlaced extends DomainEvent {
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId,
    ) {}
}

$bus->publish(new OrderPlaced('ord-1', 'cust-1'));
```

### Transactional Command Dispatch

```php
// Dispatches the command within a database transaction.
// Rolls back automatically if the handler or any middleware fails.
$bus->dispatch(new PlaceOrder('ord-1', 'cust-1'));
// Transaction middleware is applied via configuration.
```

## Known Limits

| Limit | Detail |
|-------|--------|
| **Handler resolution** | In-memory registration only; no automatic handler discovery or scanning |
| **Async publishing** | Not supported; all event publishing is synchronous and in-process |
| **Outbox driver** | Interface exists but concrete driver implementation is incomplete |
| **Inbox persistence** | `InMemoryInbox` only; no persistent inbox (Redis, database, etc.) |
| **Message serialization** | No serialization for cross-process or cross-boundary message delivery |
| **Saga orchestration** | Not part of MessageBus; sagas are handled by ApplicationWorkflow |
| **Projections** | Basic only; no incremental projection, no catch-up subscription, no projection versioning |

## Current Status

**YELLOW**

What works:
- Core `CommandBus`, `QueryBus`, and `EventBus` with full middleware pipeline
- `MessageEnvelope` with correlation ID and causation ID
- `BusLoggingMiddleware`, `BusValidationMiddleware`, `BusTransactionMiddleware`
- `InMemoryInbox` for idempotency
- `Projection` for basic read-model materialization
- Retry flow for failed messages
- Outbox interface and `PublishToOutbox` flow
- 89 unit tests in `tests/Unit/Components/Operations/MessageBus/`

What is missing:
- Concrete outbox driver implementation (database, Redis, etc.)
- Persistent inbox implementation
- Automatic handler discovery / registration
- Async event publishing
- Incremental projections and catch-up subscriptions
- Component documentation beyond this file
