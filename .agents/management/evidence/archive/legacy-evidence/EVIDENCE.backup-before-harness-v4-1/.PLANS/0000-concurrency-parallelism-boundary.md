# Plan: ADR-0008 Concurrency/Parallelism Boundary

**Date:** 2026-05-08
**Stage:** Proposed
**Owner:** Concurrency subsystem
**Area:** Operations

## Problem Statement

AvaX has `Concurrency` and `Tasks` components but their boundaries are undefined:

- `Concurrency` = current task runner with Fiber wrapper
- `Tasks` = scheduled/task queue operations
- `BackgroundProcesses` = OS-level process management
- No clear separation between these and Queue, Scheduler, Workflow, Runtime

This creates confusion about what each component owns.

## Scope Definition

### Concurrency

```
Concurrency = run multiple jobs now, within current process/request/worker, wait for result.
```

NOT Queue. NOT Scheduler. NOT Workflow. NOT Runtime.

### Parallelism (NEW)

```
Parallelism = execute work through workers/processes/threads.
```

Uses external processes, not cooperative multitasking within same process.

### Task Queue (EXISTING - separate)

```
TaskQueue = durable background execution of deferred work.
```

### Scheduler (EXISTING - separate)

```
Scheduler = execute work later or periodically.
```

### BackgroundProcesses (EXISTING - separate)

```
BackgroundProcesses = OS-level process lifecycle, supervision, restart policies.
```

### Workflow (NOT YET EXISTS)

```
Workflow = orchestrate multi-step flows with compensation/saga behavior.
```

### Runtime (EXISTING - framework)

```
Runtime = how framework lives: PHP-FPM, CLI, Worker, Swoole, RoadRunner.
```

## Architectural Decision

1. Concurrency and Parallelism are separate components.
2. Concurrency: cooperative/task-based parallelism within same process.
3. Parallelism: external process pool via Symfony Process.
4. TaskQueue, Scheduler, BackgroundProcesses, Workflow remain separate.
5. Runtime selection for Concurrency/Parallelism must not leak into PublicSurface.

## Next Steps

- [x] Create ADR-0008: docs/decisions/0008-operations-concurrency-parallelism-runtime-boundary.md
- [x] Implement Parallelism component (see .PLANS/0001-parallelism-component-impl.md)
- [x] Refactor Concurrency PublicSurface (see .PLANS/0002-concurrency-public-surface-refactor.md)
- [x] Validate with tests and PHPStan
