# HOW_THIS_WORKS — Observability Component

**Component**: `Operations/Observability`
**Stage**: V1 (core implemented, production gaps remain)
**Namespace**: `Avax\Components\Operations\Observability\System`

---

## What This Component Does

The Observability component provides the foundation for collecting and exporting telemetry data across AvaX applications. It covers three pillars:

**Tracing** — `Span` and `TraceTimeline` record operation duration, attributes, and parent-child relationships using `TraceId`, `SpanId`, and `CorrelationId` value objects. Spans use `hrtime()` for high-resolution timing.

**Metrics** — Three metric types via the `Metric` base class:
- `Counter` — monotonically increasing values (increment/add)
- `Gauge` — point-in-time values (set to a specific value)
- `Histogram` — observed value distributions (record/avg/count)

**Logging** — `StructuredLogRecord` provides structured log entries with level, message, context, timestamp, and request ID. `Logger` is an in-memory log writer with callable handlers.

**Audit** — `AuditEvent` captures security-relevant operations with actor, action, target, and metadata.

**Redaction** — `RedactSensitiveData` recursively redacts sensitive fields (`password`, `secret`, `token`, `api_key`, `authorization`) from log context before output.

**Correlation** — `CorrelationId`, `RequestId`, `SpanId`, and `TraceId` are immutable value objects for distributed tracing context propagation.

**Telemetry Export** — `TelemetryExporter` provides a callback-based export mechanism for collected data.

**Health** — `ObservabilityHealthCheck` reports the health of the observability subsystem.

The `Observability` facade provides static factory methods for the main types:

```php
$span = Observability::trace('db.query', 'SELECT * FROM users');
$counter = Observability::counter('http.requests');
$gauge = Observability::gauge('memory.usage');
$histogram = Observability::histogram('response.time');
$log = Observability::log('info', 'User logged in', ['user_id' => 123]);
$audit = Observability::audit('admin', 'delete_user', 'user:456');
```

The `RecordObservability` flow is the main entry point for unified observability — it composes correlation, tracing, metrics, and redaction into a single callback-wrapped execution:

```php
$recorder = new RecordObservability();
$result = $recorder->record('process_order', function () {
    // ... operation code
}, correlationId);
```

---

## What This Component Does NOT Do

- **No external exporters** — No Prometheus, Jaeger, OpenTelemetry SDK, or any production telemetry backend integration. `TelemetryExporter` uses in-memory storage with callback hooks.

- **No metric persistence** — All metrics are in-memory. Process restart loses all counters, gauges, and histograms. No aggregation across processes or workers.

- **No trace sampling** — Every trace is recorded. No sampling strategy, no head/tail sampling, no adaptive sampling.

- **No distributed trace context propagation** — No W3C TraceContext header parsing/generation. No B3 propagation. Correlation IDs exist but are not automatically propagated across HTTP/gRPC boundaries.

- **No production log writers** — `Logger` is in-memory with callbacks. No file rotation, syslog, JSON file output, or structured log shipping.

- **No alerting** — No alerting rules, thresholds, or notification mechanisms.

- **No OpenTelemetry compatibility** — The component is not an OpenTelemetry SDK implementation. The `ObservabilityAdapterInterface` is a local abstraction, not an OTel interface.

- **No thread safety guarantees** — PHP's request-per-process model means in-memory metrics are safe within a single request, but not across workers. Long-lived runtimes (RoadRunner, Swoole, FrankenPHP) would need explicit synchronization not currently implemented.

---

## Public API

### Facade

| Method | Returns | Description |
|--------|---------|-------------|
| `Observability::trace(string $name, string $operation)` | `Span` | Create a new trace span |
| `Observability::counter(string $name)` | `Counter` | Create a counter metric |
| `Observability::gauge(string $name)` | `Gauge` | Create a gauge metric |
| `Observability::histogram(string $name)` | `Histogram` | Create a histogram metric |
| `Observability::log(string $level, string $message, array $context = [])` | `StructuredLogRecord` | Create a structured log record |
| `Observability::audit(string $actor, string $action, string $target, array $metadata = [])` | `AuditEvent` | Create an audit event |

### Span

| Method | Returns | Description |
|--------|---------|-------------|
| `new Span($name, $operation, ?TraceId $traceId, ?SpanId $parentSpanId)` | `Span` | Creates span, starts timer via `hrtime()` |
| `setAttribute(string $key, mixed $value)` | `self` | Add attribute to span |
| `recordException(Throwable $throwable)` | `self` | Record exception (currently no-op) |
| `end()` | `float` | End span, return duration in milliseconds |
| `duration()` | `?float` | Get duration if span has ended |

### Metrics

| Class | Key Methods | Description |
|-------|-------------|-------------|
| `Counter` | `increment(float $amount)`, `getValue()` | Monotonically increasing counter |
| `Gauge` | `set(float $value)`, `getValue()` | Point-in-time value |
| `Histogram` | `record(float $value)`, `count()`, `avg()` | Value distribution tracking |

### Logs

| Method | Returns | Description |
|--------|---------|-------------|
| `new StructuredLogRecord($level, $message, $context, $timestamp, $requestId)` | `StructuredLogRecord` | Immutable log record |
| `withContext(array $context)` | `self` | Return new record with merged context |
| `withRequestId(string $requestId)` | `self` | Return new record with request ID |
| `toArray()` | `array` | Convert to array representation |

### Logger

| Method | Returns | Description |
|--------|---------|-------------|
| `handle(callable $handler)` | `self` | Register a log handler |
| `log(string $level, string $message, array $context = [])` | `StructuredLogRecord` | Write log entry |
| `info/warning/error/debug(...)` | `StructuredLogRecord` | Convenience level methods |
| `records()` | `array` | Get all recorded entries |
| `clear()` | `void` | Clear recorded entries |

### Correlation IDs

All correlation ID types (`CorrelationId`, `RequestId`, `SpanId`, `TraceId`) share the same pattern:

| Method | Returns | Description |
|--------|---------|-------------|
| `generate()` | `self` | Generate a new ID (24 hex chars = 12 random bytes) |
| `fromString(string $value)` | `self` | Create from existing string |
| `__toString()` | `string` | Get the raw value |

### Flows

| Flow | Purpose |
|------|---------|
| `RecordObservability` | Unified observability recording — wraps callback with correlation, tracing, metrics, redaction |
| `RecordLog` | Records a structured log entry via Logger |
| `FinishTrace` | Completes a trace span |
| `ExportTelemetry` | Exports collected telemetry data |
| `ReadRuntimeTimeline` | Reads the trace timeline |
| `RedactSensitiveData` | Redacts sensitive data from records |
| `CheckObservabilityHealth` | Health check for observability subsystem |

### Adapter Interface

`ObservabilityAdapterInterface` defines the contract for external telemetry backends:

```php
interface ObservabilityAdapterInterface
{
    public function recordSpan(Span $span): void;
    public function incrementCounter(string $name, float $value = 1.0): void;
    public function setGauge(string $name, float $value): void;
    public function writeLog(StructuredLogRecord $structuredLogRecord): void;
    public function reset(): void;
}
```

`Fake` driver implements this interface for testing.

---

## Internal Flow

The main recording flow through `RecordObservability` follows this sequence:

```
RecordObservability::record()
    |
    +-- Generate CorrelationId (or use provided)
    +-- Generate TraceId
    +-- Generate SpanId
    |
    +-- Create Span (starts hrtime timer)
    +-- Set correlationId attribute on span
    |
    +-- Increment operations.total counter
    +-- Increment operations.{name} counter
    |
    +-- Execute callback
    |   |
    |   +-- On success: end span, record duration histogram
    |   +-- On exception: set exception attributes, end span, increment errors counter, re-throw
    |
    +-- finally: add span to TraceTimeline
    +-- Return callback result
```

Redaction is applied in `RecordObservability::log()` before returning the log array — the `RedactSensitiveData` capability recursively scans context arrays and replaces sensitive keys with `***REDACTED***`.

---

## Dependencies

| Dependency | Type | Usage |
|------------|------|-------|
| `DataTransfer` | Component | DataObjects for log records, audit events, and telemetry payloads |
| `Standalone` | Component | Core types and abstractions used by adapter exports |
| `hrtime()` | PHP built-in | High-resolution timing for span duration |
| `random_bytes()` | PHP built-in | ID generation for correlation IDs |

No external packages or SDKs are required. The component is self-contained.

---

## Failure Behavior

**Export failures are non-fatal** — `TelemetryExporter` returns a boolean success indicator. Export failures do not crash the application (fail-safe design).

**`TraceExportFailed` exception** — Defined but not actively thrown by current code. Available for export pipeline failure signaling.

**`ObservabilityException`** — Base exception class for all observability errors.

**Metrics are lost on restart** — All metrics are in-memory. A PHP process restart loses all collected data. No persistence, no WAL, no snapshot/restore.

**Redaction applied before logging** — Sensitive fields are redacted before any log output to prevent secret leaks. The default sensitive key list covers common patterns.

**`recordException()` is currently a no-op** — The `Span::recordException()` method accepts a `Throwable` but does not store or process it. This is a known gap.

---

## Runtime Safety

- **Correlation ID propagation** — `CorrelationId` is propagated across all observability types within a single `RecordObservability::record()` call, enabling trace correlation.

- **Span parent-child tracking** — `Span` accepts an optional `parentSpanId` parameter for hierarchical trace construction.

- **Structured log schema** — `StructuredLogRecord` enforces a consistent schema (level, message, context, timestamp, request ID) across all log entries.

- **Audit event capture** — `AuditEvent` provides a structured format for security-relevant operations with actor, action, target, and metadata.

- **In-memory isolation** — Metrics and logs are scoped to the current process/request. No cross-contamination between requests in standard PHP-FPM mode.

---

## Examples

### Basic Tracing

```php
use Avax\Components\Operations\Observability\System\PublicSurface\Observability;

$span = Observability::trace('http.request', 'GET /api/users');
$span->setAttribute('http.method', 'GET');
$span->setAttribute('http.path', '/api/users');

// ... do work ...

$duration = $span->end(); // milliseconds
```

### Metrics Collection

```php
$counter = Observability::counter('http.requests');
$counter->increment();
$counter->increment(5); // bulk increment

$gauge = Observability::gauge('memory.usage_mb');
$gauge->set(memory_get_usage(true) / 1024 / 1024);

$histogram = Observability::histogram('response_time_ms');
$histogram->record(42.5);
$histogram->record(38.1);
echo $histogram->avg(); // 40.3
echo $histogram->count(); // 2
```

### Structured Logging

```php
$log = Observability::log('info', 'User login successful', [
    'user_id' => 123,
    'ip' => '192.168.1.1',
]);

echo json_encode($log->toArray());
// {"level":"info","message":"User login successful","context":{"user_id":123,"ip":"192.168.1.1"},"timestamp":"...","request_id":null}
```

### Unified Recording with RecordObservability

```php
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\CorrelationId;
use Avax\Components\Operations\Observability\System\Flows\RecordObservability\RecordObservability;

$recorder = new RecordObservability();
$correlationId = CorrelationId::generate();

$result = $recorder->record('process_payment', function () {
    // ... payment processing ...
    return $paymentResult;
}, $correlationId);

// Access collected data
$timeline = $recorder->getTimeline();
$metrics = $recorder->getMetrics();
```

### Log Redaction

```php
use Avax\Components\Operations\Observability\System\Flows\RedactSensitiveData\RedactSensitiveData;

$redactor = new RedactSensitiveData();
$redacted = $redactor->redact([
    'username' => 'john',
    'password' => 'secret123',
    'api_key' => 'ak_12345',
    'nested' => ['token' => 'tok_abc'],
]);

// Result:
// [
//   'username' => 'john',
//   'password' => '***REDACTED***',
//   'api_key' => '***REDACTED***',
//   'nested' => ['token' => '***REDACTED***'],
// ]
```

### Health Check

```php
use Avax\Components\Operations\Observability\System\Capabilities\Health\ObservabilityHealthCheck;

$health = new ObservabilityHealthCheck();
$status = $health->check();

// ['healthy' => true, 'drivers' => [...], 'issues' => []]
```

---

## Known Limits

| Limit | Impact | Priority |
|-------|--------|----------|
| No external exporters | Metrics/traces cannot be sent to Prometheus, Jaeger, OpenTelemetry, Datadog, etc. | High |
| In-memory metrics only | No persistence, no cross-process aggregation, data lost on restart | High |
| No trace sampling | All traces recorded — potential memory/CPU overhead in production | Medium |
| Basic log writers | No file rotation, syslog, JSON output, or structured log shipping | Medium |
| No alerting | No thresholds, rules, or notification mechanisms | Medium |
| No W3C TraceContext | No automatic distributed trace propagation across HTTP boundaries | High |
| `recordException()` no-op | Exceptions recorded on spans are not stored or processed | Medium |
| Health check is stub | Always returns healthy — no actual driver verification | Low |
| No long-lived runtime safety | No synchronization for RoadRunner/Swoole/FrankenPHP workers | High |
| No metric tags on operations | `RecordObservability` does not attach tags/dimensions to metrics | Low |
| `TraceExportFailed` unused | Exception defined but not thrown anywhere in the codebase | Low |

---

## Current Status

**YELLOW**

**What works:**
- Core `Span` with hrtime timing and attributes
- `Counter`, `Gauge`, `Histogram` metric types
- `StructuredLogRecord` with context merging and array conversion
- `Logger` with callable handlers
- `AuditEvent` for security events
- `CorrelationId`, `RequestId`, `SpanId`, `TraceId` value objects
- `RedactSensitiveData` with recursive redaction
- `TelemetryExporter` with callback-based export
- `RecordObservability` flow for unified recording
- `Observability` facade for ergonomic access
- `ObservabilityHealthCheck` (stub)
- `ObservabilityAdapterInterface` and `Fake` driver
- 158 unit tests in `tests/Unit/Components/Operations/Observability/`

**What is missing:**
- External metric exporters (Prometheus, OpenTelemetry, etc.)
- External trace exporters (Jaeger, Zipkin, OpenTelemetry, etc.)
- W3C TraceContext header parsing and generation
- Trace sampling strategy
- Production log writers (file, syslog, JSON)
- Persistent metrics (cross-process, cross-request)
- Alerting rules and thresholds
- `Span::recordException()` implementation
- Actual health check driver verification
- Long-lived runtime worker safety (RoadRunner/Swoole/FrankenPHP)
- Documentation in `docs/`

**Next actions:**
1. Implement at least one production exporter (Prometheus metrics or OpenTelemetry)
2. Implement W3C TraceContext propagation for distributed tracing
3. Add persistent metrics storage or aggregation
4. Implement `Span::recordException()` behavior
5. Add production log writers
6. Document in `docs/observability/`
