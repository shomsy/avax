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

- `id`: DATA-STRUCTURES-WAVE-0
- `created_at`: 2026-05-09
- `updated_at`: 2026-05-09
- `status`: blocked
- `estimate`: 1 session
- `actual`: partial execution complete; governance validators blocked by Docker socket access in sandbox
- `outcome`: Wave 0 docs and initial Structure Kernel implemented with focused DataStack/Data PHPUnit and PHPStan green.
  Remaining project governance validators could not run in this environment.
- `acceptance`: docs exist under `docs/DataStack/Data`; changed docs pass whitespace and forbidden-name checks; no
  production scaffolding is created; focused PHPUnit/PHPStan pass; blocked governance commands are reported honestly.
- `links`: `EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md`,
  `docs/DataStack/Data/STRUCTURE_ATLAS.md`,
  `EVIDENCE/datastack-data-structure-universe-wave-0-kernel-report.md`

## Completed

### V4 Second-Half Execution Queue

**Date:** 2026-05-10

- `id`: V4-12
- `created_at`: 2026-05-10
- `status`: in_progress
- `outcome`: Security & Policy Runtime — request signing, policy engine, feature flags, service discovery, security doctor
- `acceptance`: HMAC signing with replay protection, default-deny policy engine, feature flags, service registry, security:doctor command, all tested

- `id`: V4-13
- `created_at`: 2026-05-10
- `status`: todo
- `outcome`: System Design Runtime Kit — runtime architecture reports, capacity recommendations, consistency diagnostics, failure simulation
- `acceptance`: Reuses V3 SystemDesignKit, produces runtime-derived reports, CLI commands, tested

- `id`: V4-14
- `created_at`: 2026-05-10
- `status`: todo
- `outcome`: Runtime Doctor & Control Plane — health/live/ready endpoints, php avax doctor commands, production certification
- `acceptance`: HTTP health endpoints, CLI doctor commands share same engine, no secret leakage, tested

- `id`: V4-15
- `created_at`: 2026-05-10
- `status`: todo
- `outcome`: Reference Applications — 13 small apps proving framework capabilities
- `acceptance`: Each app uses Avax::create(), has tests, README, proves V4 capability

- `id`: V4-16
- `created_at`: 2026-05-10
- `status`: todo
- `outcome`: Benchmarks & Production Proof — cold/warm, route matching, container, database, queue, messaging, observability overhead
- `acceptance`: Benchmark CLI, evidence reports, production readiness proof, no marketing claims

- `id`: V4-17
- `created_at`: 2026-05-10
- `status`: blocked
- `outcome`: Optional Runtime Adapters — gated on V4-03 + V4-14 GREEN
- `acceptance`: Runtime adapter boundary, doctor checks, ReactPHP primary, others ROADMAP

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
