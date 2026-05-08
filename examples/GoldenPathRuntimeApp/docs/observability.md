# Observability

## Correlation ID Flow

Every request in the Webhook Ingestion Pipeline carries a correlation ID
that ties together all log entries, metrics, and traces for that request.

### Generation

```
Request arrives
  ├─ Has X-Correlation-ID header? → reuse it
  └─ No header? → generate new ID (bin2hex(random_bytes(8)))
```

### Propagation

```
CorrelationIdMiddleware
  ├─ Extract/generate ID
  ├─ Pass request down middleware pipeline
  └─ Add X-Correlation-ID header to response
```

### Logging

```php
$logger->info('Webhook received', [
    'webhook_id' => $webhookId,
    'source' => $source,
])->withRequestId($correlationId);
```

`StructuredLogRecord::withRequestId()` attaches the correlation ID to the log record.
When serialized via `toArray()`, the ID appears as `request_id` in the output.

### Structured Log Record

Each log entry contains:

| Field        | Source                                      |
|--------------|---------------------------------------------|
| `level`      | info / warning / error / debug              |
| `message`    | Human-readable description                  |
| `context`    | Key-value data (webhook_id, source, etc.)   |
| `timestamp`  | ISO 8601 timestamp                          |
| `request_id` | Correlation ID (when set via withRequestId) |

### Debugging with Correlation IDs

When investigating an issue:

1. Find any log entry with the correlation ID
2. Filter all logs by that `request_id`
3. See the complete request journey: middleware → route → handler → queue → resilience

### Limitations

- Correlation ID is not automatically attached to all logs — handlers must call `withRequestId()`
- No distributed tracing across services — the ID is local to this application
- No metrics aggregation — logging is the only observability channel proven here
- No log persistence — logs are stored in-memory and cleared on reset
