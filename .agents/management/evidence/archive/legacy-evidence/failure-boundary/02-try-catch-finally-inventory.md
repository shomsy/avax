# FailureBoundary — Try/Catch/Finally Inventory

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Purpose

Full inventory of every try/catch/finally in production code (framework + components),
classified by whether it should use FailureBoundary or is a legitimate local boundary.

## Inventory

### Framework

| File | Class/Method | Classification | Action |
|------|-------------|----------------|--------|
| `framework/.../FailureBoundary/Flows/RunProtectedAction/RunProtectedAction.php` | `run()` — try/catch/finally | **CANONICAL** — this IS the failure boundary | Keep — this is the centralized boundary |
| `framework/.../FailureBoundary/Capabilities/RetryFailedAction/RetryFailedAction.php` | `execute()` — try/catch in retry loop | **LOCAL_DOMAIN_RECOVERY_KEEP** — retry-specific catch | Keep — part of retry semantics |
| `framework/.../FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php` | `execute()` — validation throws | **NO_TRY_CATCH** — throws on validation failure | N/A |
| `framework/.../HandleIncomingHttp/HandleIncomingHttp.php` | `handleInCurrentScope()` line 73 — catch(Throwable) → 500 | **SHOULD_USE_FAILURE_BOUNDARY** | Defer — requires full framework wiring; HttpFailureBoundaryMiddleware now wraps downstream |

### Components

| File | Class/Method | Classification | Action |
|------|-------------|----------------|--------|
| `components/DataStack/Database/System/Capabilities/Transactions/Transactions.php:65-114` | `transactionWithRetry`, `transaction` | **LOCAL_DOMAIN_RECOVERY_KEEP** — domain-specific retry/transaction semantics | Keep |
| `components/DataStack/Database/System/Capabilities/Execution/PDOExecutor.php:42-110` | `query`, `execute` | **EXTERNAL_ADAPTER_RECOVERY_KEEP** — wraps PDO exceptions | Keep |
| `components/SystemDesign/System/Capabilities/Worker/WorkerLoop.php:54-66` | `run()` — try/finally | **RESOURCE_CLEANUP_KEEP** — lifecycle guarantee | Keep |
| `components/SystemDesign/System/Capabilities/Concurrency/ConcurrentTask.php:55-68` | `execute()` | **LOCAL_DOMAIN_RECOVERY_KEEP** — fiber task boundary | Keep |
| `components/SystemDesign/System/Capabilities/Runtime/FiberTaskRuntime.php:128-132` | `createTaskFiber()` | **LOCAL_DOMAIN_RECOVERY_KEEP** — fiber error capture | Keep |
| `components/DeveloperTools/Console/System/Capabilities/Boot/BootApplication.php:20-24` | `boot()` | **LIFECYCLE_BOUNDARY_KEEP** — boot failure wrapping | Keep |
| `components/DeveloperTools/System/System/Capabilities/StateReset/StateResetRegistry.php:27-32` | `resetAll()` | **LOCAL_DOMAIN_RECOVERY_KEEP** — continues on failure | Keep |
| `components/DataStack/Database/System/Capabilities/Migrations/MigrationRunner.php:25-105` | `up`, `runMigration`, `rollback` | **LOCAL_DOMAIN_RECOVERY_KEEP** — migration-specific error handling | Keep |
| `components/DataStack/Database/System/Capabilities/Connection/RunWithConnection.php:46-50` | `pool()` — try/finally | **RESOURCE_CLEANUP_KEEP** — connection release guarantee | Keep |

## Classification Summary

| Classification | Count | Decision |
|----------------|-------|----------|
| CANONICAL (is the failure boundary) | 1 | Keep |
| LOCAL_DOMAIN_RECOVERY_KEEP | 8 | Keep — domain-specific semantics |
| RESOURCE_CLEANUP_KEEP | 2 | Keep — lifecycle/resource guarantees |
| EXTERNAL_ADAPTER_RECOVERY_KEEP | 1 | Keep — wraps external system exceptions |
| LIFECYCLE_BOUNDARY_KEEP | 1 | Keep — boot boundary |
| SHOULD_USE_FAILURE_BOUNDARY | 1 | Defer — HandleIncomingHttp outer catch; now wrapped by middleware |

## Key Finding

**HandleIncomingHttp's catch-all at line 73 is the only try/catch that should ideally use FailureBoundary.**
However, since `HttpFailureBoundaryMiddleware` now wraps all downstream middleware (including router dispatch),
the HandleIncomingHttp catch serves as a lifecycle safety net for failures that escape the middleware stack entirely.

This is acceptable: the middleware catches the normal request path, and the outer catch catches bootstrap/infrastructure failures.

## No Mechanical Removal Needed

No try/catch needs mechanical removal. All local boundaries serve legitimate purposes:
- Domain-specific retry semantics (Transactions)
- External system wrapping (PDOExecutor)
- Resource cleanup (WorkerLoop, RunWithConnection)
- Fiber/concurrency boundaries (ConcurrentTask, FiberTaskRuntime)
- Lifecycle boundaries (BootApplication, StateResetRegistry, MigrationRunner)
- Centralized failure boundary (RunProtectedAction)

## Conclusion

| Check | Status |
|-------|--------|
| All try/catch classified | GREEN |
| No unnecessary catch-all swallowing | GREEN |
| FailureBoundary is the canonical boundary | GREEN |
| HandleIncomingHttp outer catch justified | GREEN |
