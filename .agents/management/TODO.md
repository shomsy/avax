# TODO

Canonical active implementation queue.

## Rules

- keep newest items first
- keep each item outcome-oriented
- include acceptance criteria
- include owner only when needed
- use timestamp and estimate fields from `TIMELINE.md`
- keep `ACTIVE.md` in sync for non-closed items

## Entry Format

- `id`:
- `created_at`:
- `updated_at`:
- `status`: todo | in_progress | blocked | done
- `estimate`:
- `actual`:
- `outcome`:
- `acceptance`:
- `links`:

## Current Items

- `id`: V5.5-PHASE
- `created_at`: 2026-05-11
- `updated_at`: 2026-05-11
- `status`: done
- `estimate`: multi-session
- `outcome`: V5.5 Benchmark Proof & World-Class Hardening — COMPLETE / GREEN. All 13 stages completed with valid evidence.
- `acceptance`: 12 GREEN, 1 YELLOW (reference apps not CLI-runnable). Sub-millisecond request handling (0.011ms avg, 90K+ RPS). Zero memory growth after 10K iterations. Zero state leaks. PHPStan 0 errors on benchmark code. See `EVIDENCE/v5.5/v5.5-stage-ledger.md`, `EVIDENCE/v5.5/v5.5-12-final-readiness-report.md`.
- `links`: `EVIDENCE/v5.5/v5.5-stage-ledger.md`, `EVIDENCE/v5.5/v5.5-12-final-readiness-report.md`, `EVIDENCE/v5.5/v5.5-11-optimization-pass.md`

- `id`: V5-PHASE
- `created_at`: 2026-05-10
- `updated_at`: 2026-05-11
- `status`: done
- `estimate`: multi-session
- `outcome`: V5 Internal Convergence — COMPLETE / GREEN. All 23 stages (V5-00 through V5-22) GREEN_BY_EVIDENCE. V5-23 Final V5 Truth Report produced.
- `acceptance`: 7711 tests GREEN, PHPStan 0 errors, all governance gates PASS. E2E suite covers parameterized routes, 405 behavior, compiled metadata pipeline. Reflection risks classified as non-hot-path ALLOWED. See `EVIDENCE/v5/v5-stage-ledger.md`, `EVIDENCE/v5/v5-23-final-truth-report.md`, `EVIDENCE/v5/v5-final-acceptance-audit.md`.
- `links`: `EVIDENCE/v5/v5-stage-ledger.md`, `EVIDENCE/v5/v5-23-final-truth-report.md`, `EVIDENCE/v5/v5-final-acceptance-audit.md`

## V5.5 Stage Ledger Status

**Date:** 2026-05-11
**Total:** 12 GREEN + 1 YELLOW = 13 stages

### GREEN_BY_EVIDENCE (12)
V5.5-00 Benchmark Methodology Lock, V5.5-01 Hardware/Environment Baseline, V5.5-02 Microbenchmarks, V5.5-03 Runtime Benchmarks, V5.5-04 HTTP Throughput, V5.5-06 Long-Running Worker Soak Tests, V5.5-07 Memory Leak & State Leak Tests, V5.5-08 Database/Queue/Messaging Throughput, V5.5-09 Observability & Security Overhead, V5.5-10 Framework Comparison Suite, V5.5-11 Optimization Pass, V5.5-12 Final World-Class Readiness

### YELLOW (1)
V5.5-05 Reference App Benchmarks — apps exist but not runnable in CLI (environment limitation)

### Key Metrics
- Request handling: 0.011ms avg, 0.020ms p99, 90,861 RPS
- Soak test: 10,000 iterations, 0 errors, 0KB memory growth
- Memory leak: stable, 0KB growth
- Overhead: logging +4.5%, signing +2.8%

## V5 Stage Ledger Status

**Date:** 2026-05-11
**Total:** 23 implementation stages + 1 final truth report = 24 entries
**Math:** 23 GREEN + 0 PARTIAL + 0 MISSING = 23 implementation stages

### GREEN_BY_EVIDENCE (23)
V5-00 Final V4 Truth Lock, V5-01 Governance Resolution, V5-02 Security Blocker Cleanup, V5-03 Capability Ownership Scan, V5-04 Dogfooding Adoption Matrix, V5-05 Filesystem/Storage/Cache Adoption, V5-06 DataTransfer/SecureRequest/Schema Metadata Compilation, V5-07 Modern PHP 8.x Language Adoption, V5-08 Attribute/Annotation Runtime, V5-09 DI & Autowiring Clean Code, V5-10 Data Structures Adoption, V5-11 Arrhae/Collection/JSON Productization, V5-12 Enum & Domain Value Cleanup, V5-13 Superglobal Isolation, V5-14 Router Completion, V5-15 Naming & Structure Convergence, V5-16 Traits/Multi-Class/Empty Classes Cleanup, V5-17 Serialization & Payload Safety, V5-18 Async/Concurrency/Parallelism Adoption, V5-19 Pooling & Resource Lifecycle, V5-20 Hot Path Cache & Compiled Metadata, V5-21 Tooling Gates & Custom Rector Rules, V5-22 E2E Tests / Reference Runtime Proof

### COMPLETE (1)
V5-23 Final V5 Truth Report — `EVIDENCE/v5/v5-23-final-truth-report.md`

## Completed

### V4 Final Closure Pass — COMPLETE / GREEN

**Date:** 2026-05-10

**V4-12 Security & Policy Runtime:** COMPLETE / GREEN
HMAC request signing, replay protection, default-deny policy engine, feature flags, service discovery.

**V4-13 System Design Runtime Kit:** COMPLETE / GREEN
Architecture reports, capacity estimation, failure simulation.

**V4-14 Runtime Doctor & Control Plane:** COMPLETE / GREEN
Liveness, readiness, health endpoints wired into HTTP runtime, doctor foundation.

**V4-15 Reference Applications:** COMPLETE / GREEN
13 reference apps with 36 smoke tests.

**V4-16 Benchmarks & Production Proof:** COMPLETE / GREEN
7 benchmark workloads, evidence report produced.

**V4-17 Optional Runtime Adapters:** COMPLETE / GREEN
Adapter interface + ReactPhpAdapter proved. RoadRunner/Swoole/FrankenPHP/Workerman ROADMAP.

**Final validation:** 7451 tests, 21635 assertions, 0 failures. PHPStan 0 errors. 7/7 governance GREEN.

### V4 Second-Half Execution Queue

**Date:** 2026-05-10

All V4-12 through V4-17 items completed. See V4 Final Closure Pass section above.

## Completed

### DataStack/Data Structure Universe Plan — CLOSED / PLANNED

**Date:** 2026-05-09
**Outcome:** Added the AvaX-normalized master plan for implementing the DataStack/Data structure universe without
creating production scaffolding, fake runtime guarantees, or forbidden generic folder buckets.
**Evidence:** `EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md`

### V2 Engine Implementation Phase — CLOSED / GREEN

**Date:** 2026-05-07
**Outcome:** All 72 components production-ready with canonical structure. All 4 previously LOCKED_NON_V1 components
promoted to COMPLETE.
**Validation:** composer (6887 classes), phpunit (598 tests, 2482 assertions), phpstan (0 errors), all governance checks
GREEN.

**Components completed:**

1. API/Surface — REST, JSON:API, Webhooks, RPC slices with canonical naming
2. Integration/ObjectStorage — promoted from labs with full canonical shape
3. Operations/Resilience — Timeout, Bulkhead, DeadLetter, Outbox, Backpressure, LoadShedding
4. Operations/Observability — Logging, Telemetry, Redaction, Tracing capabilities
5. Operations/RuntimeSupervision — Supervisor, WorkerLifecycle, WorkerRestart, Health (promoted from LOCKED_NON_V1)
6. Operations/MessageBus — Command, Query, Event, Transactional dispatch flows
7. Operations/Delivery — Build, Compile, SmokeChecks, Evidence, Rollback flows (promoted from LOCKED_NON_V1)
8. Operations/Realtime — ConnectClient, DisconnectClient, BroadcastToChannel, SubscribeToChannel flows + Configuration +
   Foundation/Failure (promoted from LOCKED_NON_V1)
9. Operations/MemoryLifecycle — AllocateMemory, ReleaseMemory, CheckMemoryHealth, RunGarbageCollection flows (promoted
   from LOCKED_NON_V1)
10. Operations/Tasks — TaskRunner, TaskQueue, TaskScheduler, TaskRetry capabilities
11. Operations/Filesystem — ReadFile, WriteFile, DeleteFile, ListDirectory flows
12. V2 API Engine — ApiBlueprint, OpenAPI, GraphQL (previously closed)

**Status:** All 72 components COMPLETE. LOCKED_NON_V1: 0. V2 Platform Baseline CLOSED / GREEN.

### V3 Implementation — LOCKED

V3 planning may continue. V3 production implementation remains locked until V2 platform baseline is proven GREEN and
SystemDesignKit scope is approved.
