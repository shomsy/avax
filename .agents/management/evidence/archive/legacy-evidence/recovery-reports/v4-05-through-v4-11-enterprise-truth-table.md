# V4-05 through V4-11 Core Enterprise Completion Pass — Truth Table

Version: 1.0.0
Status: Planning / Architecture Honesty
Scope: Core Enterprise Components

---

## Principle

This table is not optimism.
This table is not marketing.
This table is the honest state of each component and the specific work required to reach GREEN or honestly ROADMAP.

If a component cannot be GREEN in this pass, it says ROADMAP with a clear reason.
If a component is fundamentally experimental, it says LABS.

---

## Component Truth Table

| Component | Current Status | Honest Problem | Required Fix | Final Target |
|-----------|---------------|----------------|--------------|--------------|
| SchemaGeneration | YELLOW | Docs say OpenAPI not supported. Only generates schemas from DTOs. | Add `GenerateOpenApiFromRoutes` flow that reads router definitions + schema metadata and produces valid OpenAPI 3.0 spec. Must use existing Schema, Route, DTO components. | GREEN |
| Storage | YELLOW | Static mutable registry in PublicSurface. No reset-safe contract. LocalDisk only, no S3. | Remove static mutable state from PublicSurface. Add reset() to PublicSurface contract. Document local-only scope. S3 = ROADMAP. | GREEN (local) / ROADMAP (cloud) |
| ConnectionPool (Pdo/SQLite) | YELLOW | PdoConnectionPool builds MySQL/PostgreSQL DSN only. No SQLite pool. | Add SQLite-specific connection pool or honest documentation that pool targets server databases. SQLite = single-connection by design. Document honestly. | GREEN (MySQL/Pg) / ROADMAP (SQLite pool) |
| Queue | RED | Empty DeadLetter/ and FailedJobs/ directories. Static mutable state in PublicSurface. No worker loop. | Implement DeadLetter capability. Wire FailedJobsStore to PublicSurface. Add Worker loop with heartbeat, max attempts, backoff. Remove static mutable state. | GREEN |
| Timeout | GREEN | Dual-mode (elapsed + pcntl) already documented honestly. | No action required. Already honest about CLI-only pcntl mode. | GREEN |
| Backpressure | RED | Only a value object (BackpressurePolicy). No enforcement mechanism. No gate or flow. | Add `EnforceBackpressure` flow that checks thresholds and rejects/delays work. Wire to Queue worker and message bus consumer. Must actually gate, not only calculate. | GREEN |
| Span | RED | `recordException()` is no-op returning `$this`. No Tracer. No export pipeline. | Implement `recordException()` to actually store exception data. Add Tracer capability. Wire spans to FileTraceExporter. Must export, not only accumulate. | GREEN |
| Lock | YELLOW | InMemory only. No Redis or DB lock. No distributed lock. | Document InMemory as local-only. Redis/DB distributed lock = ROADMAP. Add reset() for long-lived workers. No new distributed implementation in this pass. | GREEN (local) / ROADMAP (distributed) |
| Observability | YELLOW | File writers exist (FileMetricWriter, FileTraceWriter, FileAuditWriter) but not wired to PublicSurface. | Wire all three writers to PublicSurface. Add `ExportMetrics`, `ExportTraces`, `WriteAudit` flows. Must be usable through public API, not only internal classes. | GREEN |
| Messaging | YELLOW | Inbox exists but not consumed by worker. Outbox exists but not transactional. No dead-letter for poison messages. | Wire Inbox to consumer loop. Add transactional outbox flush. Wire DeadLetter to poison message handling. Must use Queue DeadLetter capability. | GREEN |
| Filesystem vs Storage | YELLOW | Fragmented abstractions. Some overlap between Filesystem and Storage capabilities. | Document honest layer boundary: Filesystem = raw I/O, Storage = application objects with visibility/metadata. No new code if boundary is already honest in docs. | GREEN (documentation) |
| Route/config/cache using Filesystem | YELLOW | May use raw PHP file I/O instead of Filesystem component. | Audit route config cache writers. Migrate to Filesystem component where applicable. | GREEN |
| Queue using Reliability | YELLOW | Queue may not use CallableSerialization or Observability. | Wire CallableSerialization to job payload. Wire Observability to worker loop metrics. | GREEN |
| Messaging using Database/Queue | YELLOW | Messaging may not use Database for outbox persistence or Queue for consumer. | Wire outbox to Database. Wire consumer to Queue worker. | GREEN |
| SchemaGeneration using DataTransfer/Router | YELLOW | SchemaGeneration may not use existing DataTransfer or Router components. | Wire DTO schema extraction. Wire route scanning for OpenAPI. | GREEN |
| Runtime using existing components | YELLOW | App builder may not wire all V4 components. | Wire Router, Container, ErrorHandling, WarmSafety through ApplicationBuilder. | GREEN |
| Observability using Redaction | YELLOW | File writers may not use Redaction component consistently. | Verify all file writers use Redaction before writing NDJSON. | GREEN |

---

## Dogfooding Adoption Matrix

| Consumer Component | Should Use | Current Usage | Gap | Action |
|-------------------|------------|---------------|-----|--------|
| Storage | Filesystem | Partial | Storage should layer on Filesystem for local I/O | Wire Filesystem as local disk backend |
| Route registry | Filesystem | Unknown | Route cache may use raw file_put_contents | Migrate to Filesystem |
| Config cache | Filesystem | Unknown | Config cache may use raw file I/O | Migrate to Filesystem |
| Cache | Storage or Filesystem | Unknown | Cache may bypass Storage/Filesystem | Audit and migrate |
| Queue Worker | Reliability (CallableSerialization) | No | Job serialization may be naive | Wire CallableSerialization |
| Queue Worker | Observability (metrics) | No | Worker loop has no metrics | Wire InMemoryMetricExporter |
| Queue Worker | Timeout | Partial | Worker may timeout but not through Timeout component | Wire Timeout |
| Message Consumer | Database (inbox) | No | Inbox not consumed by worker | Wire Inbox to consumer loop |
| Message Consumer | Queue (worker) | No | Consumer may not use Queue worker | Wire Queue worker |
| Message Consumer | Reliability | No | No dead-letter for poison messages | Wire DeadLetter |
| Message Consumer | Observability | No | No consumer metrics | Wire metrics |
| Outbox | Database | Partial | Outbox exists but may not be transactional | Add transactional flush |
| Outbox | Queue | No | Outbox dispatch may not use Queue | Wire Queue for dispatch |
| SchemaGeneration | DataTransfer (DTO) | Partial | Generates from DTOs but may not use DataTransfer component fully | Wire DataTransfer schema extraction |
| SchemaGeneration | Router | No | No OpenAPI from routes | Add route scanning for OpenAPI |
| ApplicationBuilder | Router | Unknown | May not wire Router | Verify Router wiring |
| ApplicationBuilder | Container | Unknown | May not wire Container | Verify Container wiring |
| ApplicationBuilder | ErrorHandling | Unknown | May not wire ErrorHandling | Verify ErrorHandling wiring |
| ApplicationBuilder | WarmSafety | Unknown | May not wire WarmSafety | Verify WarmSafety wiring |
| FileMetricWriter | Redaction | Partial | Uses redactor but may not use Observability Redaction component | Verify consistent redaction |
| FileTraceWriter | Redaction | Partial | Uses redactor but may not use Observability Redaction component | Verify consistent redaction |
| FileAuditWriter | Redaction | Partial | Uses redactor but may not use Observability Redaction component | Verify consistent redaction |

---

## Forbidden in This Pass

```text
No S3/cloud drivers for Storage — that is ROADMAP.
No distributed Redis/DB Lock — that is ROADMAP.
No SQLite connection pool — SQLite is single-connection by design, ROADMAP.
No speculative features beyond honest enterprise completion.
No skeleton classes without behavior.
No static mutable state in PublicSurface.
```

---

## Evidence Required for GREEN

Each component must prove:

```text
SchemaGeneration: OpenAPI generated from routes matches OpenAPI 3.0 spec.
Storage: No static mutable state. reset() available. Path safety tests pass.
ConnectionPool: MySQL/Pg pool tests pass. SQLite limitation documented.
Queue: Worker loop runs jobs. DeadLetter captures poison messages. FailedJobsStore works.
Backpressure: EnforceBackpressure actually rejects/delays work under threshold.
Span: recordException() stores exception. Tracer exports to FileTraceWriter.
Lock: InMemory lock works. reset() available. Distributed lock documented as ROADMAP.
Observability: ExportMetrics, ExportTraces, WriteAudit flows work through PublicSurface.
Messaging: Inbox consumed by worker. Outbox transactional. DeadLetter handles poison.
Filesystem vs Storage: Layer boundary documented and tested.
Dogfooding: Each consumer component uses specified producer component.
```

---

## Next Allowed Action

Implement fixes in priority order:
1. Remove static mutable state (Storage, Queue) — architectural anti-pattern
2. Implement DeadLetter and FailedJobs (Queue) — RED components
3. Implement EnforceBackpressure — RED component
4. Implement Span recordException + Tracer + export — RED component
5. Wire Observability writers to PublicSurface
6. Add SchemaGeneration OpenAPI from routes
7. Wire Messaging inbox consumer + transactional outbox
8. Wire all dogfooding gaps
9. Full validation
