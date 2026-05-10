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

- `id`: V5-PHASE
- `created_at`: 2026-05-10
- `updated_at`: 2026-05-10
- `status`: todo
- `estimate`: TBD
- `outcome`: V5 dogfooding / performance convergence — next planned major phase
- `acceptance`: V4 production-ready, V5 scope defined

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
