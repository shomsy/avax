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

- `id`: V5.6-PHASE
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: multi-session
- `outcome`: V5.6 Declarative Failure Boundary — Core production-ready, extended policies deferred (Overall YELLOW). ReportFailure upgraded to Observability Logger with structured context + redaction. DeadLetter produces structured envelope. 65 tests / 129 assertions. 14 evidence documents. 4 gates GREEN.
- `acceptance`: 65 tests pass, PHPStan clean on FailureBoundary scope (5 pre-existing test warnings unrelated). OnFailure + ReportFailure adopted in real flow with E2E proof. Retry/Fallback/DeadLetter functional (Retry standalone, DeadLetter NDJSON transport). Timeout/RecoverWith deferred (DEFERRED_NOT_ENFORCED). See `EVIDENCE/failure-boundary/00-14-final-production-closure-report.md`, `EVIDENCE/failure-boundary/15-final-status-normalization.md`, `EVIDENCE/failure-boundary/final-acceptance-audit.md`.
- `links`: `EVIDENCE/failure-boundary/00-current-implementation-inventory.md`, `EVIDENCE/failure-boundary/01-ownership-and-duplication-audit.md`, `EVIDENCE/failure-boundary/02-try-catch-finally-inventory.md`, `EVIDENCE/failure-boundary/03-http-pipeline-integration.md`, `EVIDENCE/failure-boundary/04-attribute-adoption-proof.md`, `EVIDENCE/failure-boundary/05-compiled-metadata-proof.md`, `EVIDENCE/failure-boundary/06-dogfooding-proof.md`, `EVIDENCE/failure-boundary/07-mvp-production-gaps.md`, `EVIDENCE/failure-boundary/08-retry-decision.md`, `EVIDENCE/failure-boundary/09-timeout-recoverwith-deferred.md`, `EVIDENCE/failure-boundary/10-rethrow-cleanup-proof.md`, `EVIDENCE/failure-boundary/11-test-coverage-report.md`, `EVIDENCE/failure-boundary/12-tooling-gates-report.md`, `EVIDENCE/failure-boundary/13-production-readiness-assessment.md`, `EVIDENCE/failure-boundary/14-final-production-closure-report.md`, `EVIDENCE/failure-boundary/15-final-status-normalization.md`, `EVIDENCE/failure-boundary/final-acceptance-audit.md`, `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`, `docs/failure-boundary/declarative-failure-boundary.md`

- `id`: V5.6-Y7
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: small
- `outcome`: Fixed 5 pre-existing PHPStan test warnings — PHPStan 0 errors full scope
- `acceptance`: PHPStan 0 issues for full scope. `EVIDENCE/failure-boundary/deferred/V5.6-Y7-phpstan-warnings-resolution.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`, `EVIDENCE/failure-boundary/deferred/V5.6-Y7-phpstan-warnings-resolution.md`

- `id`: V5.6-Y6
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: OnFailure + ReportFailure adopted in real reference flow (RegistrationController). 9 E2E tests, 28 assertions. Domain exceptions mapped to HTTP status codes (422, 409, 503). FailureBoundary middleware wired into App pipeline.
- `acceptance`: Real route uses attributes with E2E proof. See `EVIDENCE/failure-boundary/deferred/V5.6-Y6-real-adoption.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`, `EVIDENCE/failure-boundary/deferred/V5.6-Y6-real-adoption.md`

- `id`: V5.6-Y3
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: CleanupAfterFailure uses FailureCleanupRegistry with hook registration, execution, failure isolation
- `acceptance`: Cleanup guaranteed via finally block with FailureCleanupRegistry. See
  `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y1
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: Retry delegates to canonical Resilience RetryExecutor with backoff strategies + jitter
- `acceptance`: Retry dogfooded through Resilience. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y2
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: DeadLetter uses Queue FailedJobsStore as primary transport with error_log NDJSON fallback
- `acceptance`: DeadLetter uses canonical Queue transport. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y5
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: RecoverWith enforced through RunRecoveryAction with FailureDecision::Recover
- `acceptance`: Recovery handler contract + enforcement proven. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y4
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: Timeout enforced via Resilience Timeout at action level; elapsed mode by default
- `acceptance`: Timeout enforced through Resilience. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.5-PHASE
- `created_at`: 2026-05-11
- `updated_at`: 2026-05-11
- `status`: done
- `estimate`: multi-session
- `outcome`: V5.5 Benchmark Proof & World-Class Hardening — GREEN. All 13 stages (V5.5-00 through V5.5-12) completed with valid evidence. V5.5-05 Reference App Benchmarks implemented — all 13 reference apps benchmarked.
- `acceptance`: 13 GREEN, 0 YELLOW. Sub-millisecond request handling (0.011ms avg, 90K+ RPS). 13 reference apps benchmarked (avg 0.012-0.015ms, 66K-85K RPS). Zero memory growth after 10K iterations. Zero state leaks. PHPStan 0 errors on benchmark code. Benchmark infrastructure tests: 35 tests covering edge cases. See `EVIDENCE/v5.5/v5.5-stage-ledger.md`, `EVIDENCE/v5.5/v5.5-12-final-readiness-report.md`, `EVIDENCE/v5.5/v5.5-final-acceptance-audit.md`, `EVIDENCE/v5.5/reference-app-benchmarks.json`.
- `links`: `EVIDENCE/v5.5/v5.5-stage-ledger.md`, `EVIDENCE/v5.5/v5.5-12-final-readiness-report.md`, `EVIDENCE/v5.5/v5.5-11-optimization-pass.md`, `EVIDENCE/v5.5/v5.5-final-acceptance-audit.md`, `EVIDENCE/v5.5/reference-app-benchmarks.json`, `docs/benchmarks/benchmark-infrastructure.md`

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
**Total:** 13 GREEN = 13 stages
**Overall: GREEN**

### GREEN_BY_EVIDENCE (13)
V5.5-00 Benchmark Methodology Lock, V5.5-01 Hardware/Environment Baseline, V5.5-02 Microbenchmarks, V5.5-03 Runtime Benchmarks, V5.5-04 HTTP Throughput, V5.5-05 Reference App Benchmarks (13 apps), V5.5-06 Long-Running Worker Soak Tests, V5.5-07 Memory Leak & State Leak Tests, V5.5-08 Database/Queue/Messaging Throughput, V5.5-09 Observability & Security Overhead, V5.5-10 Framework Comparison Suite, V5.5-11 Optimization Pass, V5.5-12 Final World-Class Readiness

### Key Metrics
- Request handling: 0.011ms avg, 0.020ms p99, 90K+ RPS
- Reference apps: 13/13 benchmarked, avg 0.012-0.015ms, 66K-85K RPS
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
