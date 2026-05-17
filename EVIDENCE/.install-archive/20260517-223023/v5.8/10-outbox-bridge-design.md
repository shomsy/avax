# V5.8-10: Outbox Bridge Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Outbox Bridge Design

## V5.8 Scope Decision

**Decision: A — Design only, no implementation.**

Do not build full Outbox engine until DB lifecycle afterCommit design is stable.

V5.8 designs the outbox bridge concept, interfaces, and integration path. Implementation is deferred to V5.8-10 (later stage) or future V5.9+.

## Target Concept

```
1. During transaction: collect integration/domain events that must be published after commit
2. Store outbox message in same DB transaction
3. Publish after commit
4. Retry safely
5. Deduplicate where needed
```

## Required Concepts

### OutboxMessage

```php
final readonly class OutboxMessage
{
    public function __construct(
        public OutboxMessageId $id,
        public string $eventType,       // FQCN of the event
        public string $payload,         // serialized event payload
        public OutboxMessageStatus $status,
        public int $attempts,
        public ?string $lastError,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $publishedAt,
        public ?DateTimeImmutable $nextAttemptAt,
        public string $correlationId,
    ) {}
}
```

### OutboxMessageStatus

```php
enum OutboxMessageStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Failed = 'failed';
    case DeadLettered = 'dead_lettered';
}
```

### Value Objects

```php
final readonly class OutboxMessageId
{
    public function __construct(public string $value) {}
    public static function generate(): self;
}

interface OutboxSerializer
{
    public function serialize(object $event): string;
    public function deserialize(string $payload, string $eventType): object;
}
```

### Interfaces (Design Only)

```php
interface StoreOutboxMessage
{
    /**
     * Store outbox message within current transaction.
     * Must be transactional — same DB transaction as the business operation.
     */
    public function store(OutboxMessage $message, string $connection): void;
}

interface OutboxPublisher
{
    /**
     * Publish pending outbox messages.
     * Called by afterCommit listener or background worker.
     */
    public function publishPending(string $connection, OutboxRetryPolicy $retryPolicy): int;
}

interface OutboxRetryPolicy
{
    public function maxAttempts(): int;
    public function backoffMs(int $attempt): int;
    public function shouldRetry(int $attempts, string $error): bool;
}
```

## V5.8 Integration Path

### Step 1: AfterCommit Hook (V5.8 implementation)

```php
onTransaction()
    ->afterCommit(PublishOutboxMessages::class);

final readonly class PublishOutboxMessages
{
    public function __construct(
        private StoreOutboxMessage $storeOutboxMessage,
        private OutboxPublisher $outboxPublisher,
    ) {}

    public function __invoke(AfterCommit $event): void
    {
        $this->outboxPublisher->publishPending(
            connection: $event->connection,
            retryPolicy: DefaultOutboxRetryPolicy::new(),
        );
    }
}
```

### Step 2: Storing During Transaction (V5.8 implementation)

When `emit()` is called inside a transaction, the event can be stored as an outbox message:

```php
// Inside a transaction
emit(new UserRegistered($userId, $email));

// The emit() flow, when inside a transaction, stores the event
// in the outbox table as part of the same transaction.
// After commit, the outbox publisher publishes it.
```

### Step 3: Outbox Table (Design, not V5.8 implementation)

```sql
CREATE TABLE outbox_messages (
    id VARCHAR(36) PRIMARY KEY,
    event_type VARCHAR(255) NOT NULL,
    payload JSON NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    attempts INT NOT NULL DEFAULT 0,
    last_error TEXT NULL,
    created_at TIMESTAMP NOT NULL,
    published_at TIMESTAMP NULL,
    next_attempt_at TIMESTAMP NULL,
    correlation_id VARCHAR(36) NOT NULL
);
```

## Rules

1. **External side effects should happen after commit** — outbox publication is an external side effect.
2. **Outbox storage must be transactional** — same DB transaction as the business operation.
3. **Outbox publication must be retryable** — failures are expected.
4. **Outbox messages must be idempotent** — duplicate publication is safe.
5. **Outbox must not be confused with EventStore** — Outbox is delivery reliability, EventStore is source of truth.
6. **Outbox is separate from DB lifecycle** — DB lifecycle triggers outbox, but outbox is its own capability.

## V5.8 Scope Summary

| Aspect | V5.8 Scope | Future Scope |
|--------|-----------|-------------|
| OutboxMessage value object | Design | Implement |
| OutboxMessageStatus enum | Design | Implement |
| OutboxMessageId value object | Design | Implement |
| OutboxSerializer interface | Design | Implement |
| StoreOutboxMessage interface | Design | Implement |
| OutboxPublisher interface | Design | Implement |
| OutboxRetryPolicy interface | Design | Implement |
| Outbox table schema | Design | Implement migration |
| Outbox storage in transaction | Design | Implement |
| AfterCommit → publish integration | Design | Implement |
| Background outbox worker | Design | V5.9+ |
| Outbox deduplication | Design | V5.9+ |
| Outbox monitoring | Design | V5.9+ |

## Next Allowed Action

V5.8-11 Projection Bridge Design.
