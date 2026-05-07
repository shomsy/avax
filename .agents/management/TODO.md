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

No active items.

## Completed

### V2 Engine Implementation Phase — CLOSED / GREEN

**Date:** 2026-05-07
**Outcome:** All V2 engine components production-ready with canonical structure.
**Validation:** composer (6804 classes), phpunit (598 tests, 2482 assertions), phpstan (0 errors), all evidence checks
GREEN.

**Components completed:**

1. API/Surface — REST, JSON:API, Webhooks, RPC slices with canonical naming
2. Integration/ObjectStorage — promoted from labs with full canonical shape
3. Operations/Resilience — Timeout, Bulkhead, DeadLetter, Outbox, Backpressure, LoadShedding
4. Operations/Observability — Logging, Telemetry, Redaction, Tracing capabilities
5. Operations/RuntimeSupervision — Supervisor, WorkerLifecycle, WorkerRestart, Health
6. Operations/MessageBus — Command, Query, Event, Transactional dispatch flows
7. Operations/Delivery — Build, Compile, SmokeChecks, Evidence, Rollback flows
8. Operations/Realtime — ConnectClient, DisconnectClient, BroadcastToChannel, SubscribeToChannel flows + Configuration +
   Foundation/Failure
9. Operations/MemoryLifecycle — AllocateMemory, ReleaseMemory, CheckMemoryHealth, RunGarbageCollection flows
10. Operations/Tasks — TaskRunner, TaskQueue, TaskScheduler, TaskRetry capabilities
11. Operations/Filesystem — ReadFile, WriteFile, DeleteFile, ListDirectory flows
12. V2 API Engine — ApiBlueprint, OpenAPI, GraphQL (previously closed)

**Status:** All 72 components COMPLETE. V2 Engine Implementation Phase CLOSED.

### V3 Implementation — LOCKED

V3 planning may continue. V3 production implementation remains locked until V2 platform baseline is proven GREEN and
SystemDesignKit scope is approved.
