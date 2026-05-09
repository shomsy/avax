# AvaX Production Roadmap

Status: V3 CLOSED / GREEN
Execution control: `EVIDENCE/EXECUTION.md` is the active stage lock.
Next active stage: None — all stages complete

## 1. Current Truth
```text
V1 Kernel Green: PROVEN
V2 Platform Baseline: CLOSED / GREEN (all 72 components complete)
V3 Implementation: CLOSED / GREEN (SystemDesignKit complete)

Composer validate: GREEN
Autoload integrity: GREEN (7219 classes)
PHPStan: GREEN (V3 files: 0 errors)
Tests: GREEN (227 SystemDesignKit tests, 1382 assertions)
Component suite structure: GREEN
Component completion: PROVEN
Muscle restoration: COMPLETE
```

## 2. V2 Enterprise Platform Engines (CLOSED / GREEN)

V1 recovery is formally CLOSED. V2 is formally CLOSED.

[x] Stage 12: Public API and Compatibility Governance
[x] Stage 13: Extension and Plugin Architecture
[x] Stage 14: Benchmark and Performance Budget Suite
[x] Stage 15: Observability Contract
[x] Stage 16: Security Threat Model
[x] Stage 17: Failure Simulation and Runtime Resilience
[x] Stage 18: Package Split Readiness
[x] Stage 19: Release, Upgrade and Migration Policy

**V2 Engines (ALL COMPLETE):**

- [x] API Surface Engine — REST, JSON:API, Webhooks, RPC, OpenAPI, GraphQL — all COMPLETE
- [x] Integration Engine — ObjectStorage COMPLETE
- [x] Reliability Engine — Resilience COMPLETE
- [x] Observability Engine — Observability COMPLETE
- [x] Runtime Supervision Engine — RuntimeSupervision COMPLETE
- [x] Memory Lifecycle Engine — MemoryLifecycle COMPLETE
- [x] Delivery Engine — Delivery COMPLETE

## 3. V3 Executable System Design Framework (CLOSED / GREEN)

[x] Stage 20: System Design Kit — COMPLETE
[x] Stage 21: Reference Architectures — COMPLETE (URL shortener, e-commerce)
[x] Stage 22: System Design Example Applications — COMPLETE
[x] Stage 23: Final Documentation and Positioning — COMPLETE

### V3 Deliverables

- [x] V3-01: Schema Validation (YAML parser, 3 schema validators)
- [x] V3-02: Capacity Engine (12 value objects)
- [x] V3-03: Consistency Engine (10 classes + 3 enums)
- [x] V3-04: Messaging & CQRS (12 classes + 2 enums)
- [x] V3-05: Runtime Integration Proof (5 integration tests)
- [x] V3-06: Reference Architectures (2 architectures with capacity/scenarios/tests YAML)
- [x] V3-07: Runnable Example (end-to-end validation through public API)
- [x] V3-08: Failure Simulations (6 failure modes)
- [x] V3-09: Architecture Tests (13 assertion types)
- [x] V3-10: Scenario Runner (27 scenario assertions, all passing)

## 4. V4 Product Runtime & Enterprise Muscle (STAGE LOCKED — V4-00 COMPLETE)

V4 master plan: `EVIDENCE/.PLANS/V4_PRODUCT_RUNTIME_AND_ENTERPRISE_MUSCLE.md`

V4 North Star: Make AvaX usable as a real framework for building production-grade, system-design-grade applications.

### V4 Stages

- [x] V4-00: Integrity Lock & Stage Definition — master plan written, stage locked
- [ ] V4-01: Runtime App Layer — Avax::create(), App API, route registration, controller invocation, response
  normalization
- [ ] V4-02: Reactive HTTP Runtime — ReactPHP HTTP server, `php avax serve`
- [ ] V4-03: Warm Worker Safety — request scope, reset lifecycle, leak detection
- [ ] V4-04: Developer Experience — scaffolding, doctor, inspect, validate commands
- [ ] V4-05: Data Platform Productization — OpenAPI, JSON Schema, DataTransfer schema
- [ ] V4-06: Storage Platform — Filesystem/Storage split, LocalDisk
- [ ] V4-07: Database Muscle — query builder, migrations, transactions, data mapper
- [ ] V4-08: Queue & Worker Runtime — jobs, workers, retry, dead letter
- [ ] V4-09: Reliability Engine — retry, timeout, circuit breaker, bulkhead, fallback
- [ ] V4-10: Messaging & Consistency — outbox, inbox, relay, projection, DLQ
- [ ] V4-11: Observability & Telemetry — correlation, logging, metrics, tracing
- [ ] V4-12: Security & Policy Runtime — request signing, policy engine, feature flags
- [ ] V4-13: System Design Runtime Kit — capacity recommendations, architecture reports
- [ ] V4-14: Runtime Doctor & Control Plane — doctor command, health, status
- [ ] V4-15: Reference Applications — 13 reference apps proving framework works
- [ ] V4-16: Benchmarks & Production Proof — performance evidence
- [ ] V4-17: Optional Runtime Adapters — RoadRunner/Swoole/FrankenPHP (after V4-03)

### V4 Hard Gates

```text
V4-01 requires: V4-00 GREEN
V4-02 requires: V4-01 GREEN
V4-03 requires: V4-01 GREEN
V4-17 requires: V4-03 GREEN (NO RoadRunner/Swoole before Warm Worker Safety)
```

## 5. V4 Branch Policy

```text
master = stable protected branch (current clean baseline + official V4 plan).
main   = active V4 development / integration branch.

Rules:
- master keeps the current clean baseline, including the official V4 plan.
- main must be updated by merging master.
- V4 development happens on main through stage branches.
- No direct feature work on master.
- No V4 implementation directly on master.
- master receives V4 only when V4 is production-ready.
- Every V4 stage branch starts from main.
- Every merge into main must pass full validation.
- Every merge into master must be a release-grade merge.

Feature branch format: v4/01-runtime-app-layer, v4/02-reactphp-runtime, etc.

Required validation before merging any V4 branch into main:
- composer validate --no-check-publish
- composer dump-autoload -o
- composer test:full
- vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G
- php tooling/refactor/check-component-suite-structure.php
- php tooling/refactor/check-duplicate-owners.php
- php tooling/refactor/check-namespace-drift.php
- php tooling/refactor/check-public-surface.php
- php tooling/refactor/check-runtime-leaks.php
- php tooling/refactor/check-component-canonical-shape.php
- php tooling/refactor/check-advanced-pattern-folder-violations.php
```

## 6. Next Allowed Actions

1. Merge master into main.
2. Start V4-01 Runtime App Layer from main only after validation passes.
