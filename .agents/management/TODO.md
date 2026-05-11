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
- `updated_at`: 2026-05-11
- `status`: in_progress
- `estimate`: multi-session
- `outcome`: V5 Internal Convergence — 16/24 stages GREEN, ledger corrected
- `acceptance`: V5 stage ledger internally consistent: 16 GREEN, 6 PARTIAL, 1 MISSING, 1 NOT_ALLOWED_YET. Proof report updated. TODO.md and CURRENT_TRUTH.md synced.
- `links`: `EVIDENCE/v5/v5-stage-ledger.md`, `EVIDENCE/v5/v5-current-state-proof-report.md`

## V5 Stage Ledger Status

**Date:** 2026-05-11
**Total:** 24 stages (V5-00 through V5-23)
**Math:** 16 + 6 + 1 + 1 = 24

### GREEN_BY_EVIDENCE (16)
V5-00 Final V4 Truth Lock, V5-01 Governance Resolution, V5-02 Security Blocker Cleanup, V5-03 Capability Ownership Scan, V5-04 Dogfooding Adoption Matrix, V5-05 Filesystem/Storage/Cache Adoption, V5-07 Modern PHP 8.x Language Adoption, V5-09 DI & Autowiring Clean Code, V5-10 Data Structures Adoption, V5-11 Arrhae/Collection/JSON Productization, V5-12 Enum & Domain Value Cleanup, V5-13 Superglobal Isolation, V5-15 Naming & Structure Convergence, V5-16 Traits/Multi-Class/Empty Classes Cleanup, V5-17 Serialization & Payload Safety, V5-18 Async/Concurrency/Parallelism Adoption

### PARTIAL_BY_PREVIOUS_MEGA_PASS (6)
V5-08 Attribute/Annotation Runtime (attributes exist, not compiled), V5-14 Router Completion (partial features), V5-19 Pooling & Resource Lifecycle (abstract only), V5-20 Hot Path Cache & Compiled Metadata (not used in hot path), V5-21 Tooling Gates & Custom Rector Rules (no custom Rector rules), V5-22 E2E Tests / Reference Runtime Proof (gaps: no CLI/compiled metadata/parameterized route tests)

### MISSING_IMPLEMENTATION (1)
V5-06 DataTransfer/SecureRequest/Schema Metadata Compilation — **next allowed action**

### NOT_ALLOWED_YET (1)
V5-23 Final V5 Truth Report — blocked until all previous stages GREEN

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
