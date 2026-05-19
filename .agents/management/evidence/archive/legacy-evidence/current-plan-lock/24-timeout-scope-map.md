# 24 — Timeout Scope Map

**Date:** 2026-05-12
**Branch:** main
**Commit:** 21d6f69fd
**Scope:** Map all timeout kinds in AvaX to their owners, scopes, and V5.7/V6 blocking status

## Timeout Scope Table

| Timeout Kind | Owner | Scope | Enforced How | Supported Runtime | Current Status | Blocks V5.7? | Blocks V6 Async/Parallel? |
|---|---|---|---|---|---|---|---|
| **FailureBoundary timeout** | `framework/System/Capabilities/FailureBoundary/Capabilities/EnforceTimeout/` | Per-action timeout via `#[Timeout]` attribute. Delegates to Resilience Timeout. | Elapsed-mode post-hoc check. OperationTimedOut on exceed. Cleanup in finally. | All (PHP-FPM, FrankenPHP, ReactPHP, RoadRunner, Swoole, Workerman) | GREEN_ELAPSED_DOCUMENTED | NO | NO — but pre-emptive mode would be better for V6 |
| **Resilience timeout** | `components/Operations/Resilience/System/Capabilities/Timeout/Timeout.php` | Generic operation timeout. Used by FailureBoundary and any component needing timeout. | Dual-mode: elapsed (default) + pcntl pre-emptive (CLI only). | All runtimes; pcntl pre-emptive CLI only | GREEN | NO | NO — pcntl pre-emptive available for V6 |
| **HTTP/client timeout** | `components/HTTP/Client/System/Capabilities/Resilience/TimeoutPolicy.php` | Per-request HTTP timeout. Applied to CurlClient/Transport. | Delegated to underlying HTTP client (curl timeout). | All | GREEN — separate scope | NO | NO |
| **DB timeout** | `components/DataStack/Database/System/Capabilities/Connections/Pools/` | Connection pool acquisition timeout. | PDO/driver-level timeout. | All | GREEN — separate scope | NO | NO |
| **Cache lock timeout** | `components/Application/Cache/System/Capabilities/Source/ProtectCacheSource/CacheLockTimeout.php` | Cache stampede lock timeout. | Lock acquisition timeout. | All (Redis extension optional) | GREEN — separate scope | NO | NO |
| **Saga workflow timeout** | `components/Operations/ApplicationWorkflow/System/Capabilities/Timeouts/SagaTimeout.php` | Saga step execution timeout. | Workflow-level timeout. | All | GREEN — separate scope | NO | NO |
| **Concurrency task deadline** | `components/Operations/Concurrency/System/Capabilities/Cancellation/CancellationToken.php` | Fiber task deadline/cancellation. | Fiber-based cancellation. | PHP 8.1+ Fibers | GREEN — separate scope | NO | NO — this IS the V6 async prerequisite |
| **Worker/process timeout** | `framework/System/Capabilities/Runtime/GracefulShutdown/` | Worker shutdown timeout. | Graceful shutdown sequence. | Long-lived workers | GREEN — separate scope | NO | NO |

## Conclusions

### Which timeout is V4-09?

V4-09 is the **Resilience Timeout** (`components/Operations/Resilience/.../Timeout.php`). It is the canonical timeout capability that all other timeouts delegate to or reference. It provides dual-mode enforcement (elapsed + pcntl pre-emptive).

### Which timeout is V5.6?

V5.6 is the **FailureBoundary Timeout** (`framework/System/Capabilities/FailureBoundary/.../EnforceTimeout/`). It delegates to Resilience Timeout. It provides the `#[Timeout]` attribute, compilation into FailurePolicy, and enforcement at the action boundary level.

### Which timeout is future V6 async/parallel prerequisite?

**Concurrency task deadline** (`CancellationToken`) and the **Resilience pcntl pre-emptive mode** are V6 async/parallel prerequisites. V6.4 (Async/Future DSL), V6.5 (Async QueryBuilder), V6.6 (Parallel Work), and V6.7 (Async/Parallel data structures) need pre-emptive timeout that can interrupt mid-flight operations. Elapsed-mode alone is insufficient for true async cancellation.

### Does any timeout issue block V5.7 Events DSL?

**NO.** V5.7 Events DSL is about event registration, dispatch, and listener compilation. It does not require pre-emptive timeout. Event dispatch is synchronous (or compiled), and timeout enforcement is not a prerequisite.

### Does any timeout issue block V5.8 Database Lifecycle Events?

**NO.** V5.8 is about entity and transaction events. DB timeout (connection pool level) is already GREEN. Transaction events do not require pre-emptive timeout.

### Does any timeout issue block V6.4-V6.7 async/parallel?

**PARTIALLY YES (ROADMAP).** For V6 async/parallel, the elapsed-mode default is insufficient. True async cancellation requires:
1. Pre-emptive timeout (pcntl for CLI, or runtime adapter support for HTTP)
2. CancellationToken propagation through async work
3. Runtime adapter timeout support (ReactPHP, Swoole, FrankenPHP)

These are V6 prerequisites, documented in ROADMAP. They do NOT block V5.7 or V5.8.

## Remaining Risks

- Elapsed-mode cannot interrupt mid-flight blocking I/O. Per-client timeouts (HTTP, DB) must be set independently.
- pcntl pre-emptive is CLI-only. Not available in PHP-FPM or most production SAPIs.
- V6 async work will need runtime adapter timeout support beyond elapsed mode.

## Next Allowed Action

Proceed to V4-05 through V4-11 documentation verification.
