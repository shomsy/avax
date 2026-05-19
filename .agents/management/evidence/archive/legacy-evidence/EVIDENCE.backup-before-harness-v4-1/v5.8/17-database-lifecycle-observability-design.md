# V5.8-17: Database Lifecycle Observability Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Observability Design

## Metrics

| Metric                           | Type      | Description                                      |
|----------------------------------|-----------|--------------------------------------------------|
| `db.lifecycle.listener.count`    | Counter   | Number of lifecycle listeners invoked            |
| `db.lifecycle.listener.duration` | Histogram | Duration of lifecycle listener execution         |
| `db.query.count`                 | Counter   | Number of queries executed                       |
| `db.query.duration`              | Histogram | Query execution duration                         |
| `db.query.slow`                  | Counter   | Number of slow queries detected                  |
| `db.transaction.commit.count`    | Counter   | Number of successful commits                     |
| `db.transaction.rollback.count`  | Counter   | Number of rollbacks                              |
| `db.outbox.pending`              | Gauge     | Number of pending outbox messages                |
| `db.outbox.published`            | Counter   | Number of outbox messages published              |
| `db.outbox.failed`               | Counter   | Number of outbox messages that failed to publish |

## Correlation ID Propagation

- Correlation ID should propagate where request context exists.
- Trace context must not require DB component to depend on HTTP.
- Correlation ID is passed through lifecycle event objects.

```php
final readonly class QueryExecuted
{
    public function __construct(
        public string $sql,
        public array $bindings,
        public string $connection,
        public float $durationMs,
        public int $rowCount,
        public ?string $correlationId = null,  // propagated from request context
    ) {}
}
```

## Observability Integration

```php
// Observability listener
final readonly class RecordQueryTelemetry
{
    public function __construct(
        private MetricsCollector $metrics,
    ) {}

    public function __invoke(QueryExecuted $event): void
    {
        $this->metrics->incrementCounter('db.query.count', [
            'connection' => $event->connection,
        ]);
        $this->metrics->recordHistogram('db.query.duration', $event->durationMs, [
            'connection' => $event->connection,
        ]);
    }
}

// Slow query listener
final readonly class ReportSlowQuery
{
    public function __construct(
        private MetricsCollector $metrics,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(QueryExecuted $event): void
    {
        $this->metrics->incrementCounter('db.query.slow', [
            'connection' => $event->connection,
        ]);
        $this->logger->warning('Slow query detected', [
            'duration_ms' => $event->durationMs,
            'sql' => $event->sql,  // redacted
            'correlation_id' => $event->correlationId,
        ]);
    }
}
```

## Rules

1. **Correlation ID propagates where request context exists.**
2. **Trace context must not require DB component to depend on HTTP.**
3. **Observability integration must not create circular dependencies.**
4. **Logs must be redacted** — no sensitive data in logs.

## Observability Gate Design (Future Tooling)

`check-db-lifecycle-observability.php` — fails if:

- No observability listeners are registered for query lifecycle
- Metrics are not emitted for transaction commit/rollback
- Logs contain unredacted SQL bindings

## Next Allowed Action

V5.8-18 Dogfooding Design.
