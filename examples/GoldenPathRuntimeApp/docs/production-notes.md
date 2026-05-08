# Production Notes

## Deployment Assumptions

| Aspect        | Assumption                                                              |
|---------------|-------------------------------------------------------------------------|
| Runtime       | PHP-FPM, FrankenPHP, RoadRunner, or Swoole                              |
| Queue         | In-memory array — not suitable for production without persistence       |
| MessageBus    | In-memory static registries — not distributed                           |
| Logging       | In-memory log storage — needs external log driver                       |
| State Reset   | Per-request reset assumes single-process or shared-nothing architecture |
| Configuration | PHP arrays — no hot-reload or external config service                   |

## Known Limitations

### Queue

- In-memory array — jobs are lost on process restart
- No max retry limit — failed jobs are released indefinitely with 5-second delays
- No priority ordering — FIFO only
- No delayed job execution beyond `time() + delay`
- No distributed queue — single-process only

### MessageBus

- Static singleton buses — listeners persist across requests unless manually cleared
- No message persistence — messages are lost if process crashes during dispatch
- No retry or redelivery — failed handlers throw and propagate up
- No distributed bus — single-process only

### Resilience

- CircuitBreaker cooldown uses real `time()` — not testable without sleep
- Timeout is post-execution only — does not interrupt blocking operations
- Fallback has no automatic retry — each callable runs once
- No bulkhead pattern — no resource isolation between operations

### Observability

- In-memory logging only — no file, syslog, or remote log driver
- No metrics collection — no counters, histograms, or gauges
- No distributed tracing — correlation ID is local to this application
- No alerting — no threshold-based alerts or notification channels

### HTTP

- Router uses exact URI match — no path parameters, no regex, no pattern matching
- No request validation — body parsing is manual in route actions
- No content negotiation — responses are always JSON for arrays
- No file upload handling in route actions

## Production Risks

### Critical

| Risk                         | Impact                         | Mitigation                                    |
|------------------------------|--------------------------------|-----------------------------------------------|
| No persistent queue          | Job loss on restart            | Use Redis, RabbitMQ, or database-backed queue |
| No persistent message bus    | Message loss on crash          | Use external broker (Kafka, RabbitMQ)         |
| No auth on webhook endpoints | Unauthorized webhook injection | Add authentication middleware                 |
| No rate limiting on ingest   | DoS via webhook flooding       | Add RateLimiterMiddleware to ingest route     |

### High

| Risk                                 | Impact                       | Mitigation                                |
|--------------------------------------|------------------------------|-------------------------------------------|
| Infinite job retries                 | Resource exhaustion          | Add max retry count and dead letter queue |
| No idempotency enforcement           | Duplicate webhook processing | Use correlation ID as idempotency key     |
| No schema validation on webhook body | Malformed payload processing | Add request validation layer              |
| No health check endpoint monitoring  | Silent failures              | Add external health monitoring            |

### Medium

| Risk                                 | Impact                             | Mitigation                                   |
|--------------------------------------|------------------------------------|----------------------------------------------|
| CircuitBreaker state lost on restart | No cross-request failure memory    | Use persistent circuit breaker state         |
| No log persistence                   | Debugging impossible after restart | Use file/syslog/remote log driver            |
| No metrics                           | Performance unknown                | Add metrics collection                       |
| No distributed tracing               | Cross-service debugging hard       | Propagate correlation ID to downstream calls |

### Low

| Risk                   | Impact                           | Mitigation                 |
|------------------------|----------------------------------|----------------------------|
| Exact URI routing only | No dynamic routes                | Add pattern-based router   |
| No content negotiation | Limited response formats         | Add Accept header handling |
| No file uploads        | Cannot handle multipart webhooks | Add multipart body parsing |

## V3 Model Validation

Before deployment, run V3 validation:

```php
$config = require 'config/capacity.php';
$model = CapacityModel::fromConfig($config);
$result = $model->validate();

$config = require 'config/messaging.php';
$model = MessagingModel::fromConfig($config);
$result = $model->validate();
```

This validates:

- Traffic model is internally consistent
- Queue capacity matches projected load
- Latency budgets are achievable
- Messaging architecture has no critical risks (missing outbox, DLQ, etc.)

## Recommended Next Steps

1. Persistent queue (Redis, database-backed)
2. Persistent message bus (Kafka, RabbitMQ adapter)
3. External log driver (file, syslog, ELK)
4. Metrics collection (Prometheus counters/histograms)
5. Authentication middleware for webhook endpoints
6. Request validation layer (JSON schema)
7. Pattern-based router with path parameters
8. Distributed tracing propagation
