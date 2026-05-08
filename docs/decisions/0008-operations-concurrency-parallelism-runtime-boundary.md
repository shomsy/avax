# ADR 0008: Operations Concurrency/Parallelism Runtime Boundary

## Status

Accepted

## Date

2026-05-08

## Context

AvaX has multiple components dealing with deferred, background, or multi-work execution, but their boundaries are
undefined and overlapping:

- **Concurrency** = current task runner with Fiber wrapper
- **Tasks** = scheduled/task queue operations
- **BackgroundProcesses** = OS-level process management
- No clear separation between these and Queue, Scheduler, Workflow, Runtime

This creates confusion about what each component owns and leads to architectural drift.

## Decision

Define strict boundaries:

### Concurrency

```
Concurrency = run multiple jobs now, within current process/request/worker, wait for result.
```

- Cooperative/task-based parallelism within same process/request/worker
- NOT Queue. NOT Scheduler. NOT Workflow. NOT Runtime.
- Examples: Fiber-based async, sync fallback, Amp-based async

### Parallelism (NEW)

```
Parallelism = execute work through workers/processes/threads.
```

- External process pool (Symfony Process, pcntl_fork, FFI)
- NOT Concurrency. NOT Queue. NOT Scheduler.
- Examples: process pool workers, CLI batch execution

### TaskQueue (EXISTING - separate)

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

## Boundaries

1. **Concurrency** and **Parallelism** are separate components.
2. **Concurrency**: cooperative/task-based work within same process.
3. **Parallelism**: external process/thread pool via Symfony Process.
4. **TaskQueue**, **Scheduler**, **BackgroundProcesses**, **Workflow** remain separate.
5. Runtime selection for Concurrency/Parallelism must not leak into PublicSurface.
6. PublicSurface must only delegate to Flows. No business logic in PublicSurface.

## Consequences

### Positive

- Clear ownership per component
- Runtime-agnostic design preserved
- Parallelism can use Symfony Process without polluting Concurrency
- Concurrency can use Fibers without leaking into Parallelism
- Each component can evolve independently

### Negative

- Refactoring required for existing Concurrency component
- User migration path needed (new API shapes)

### Risks

- Concurrency refactor may break existing users
- Parallelism requires careful serialization handling
- Symfony Process has limitations for closures

## Implementation

See plans:

- `EVIDENCE/.PLANS/0001-parallelism-component-impl.md`
- `EVIDENCE/.PLANS/0002-concurrency-public-surface-refactor.md`

## References

- ADR-0001: Avax Is a Runtime-Agnostic Framework
- ADR-0005: Runtime Adapters Must Not Leak Into Core
- `components/Operations/Concurrency/` - current implementation
- `components/Operations/Tasks/` - task queue
- `components/Operations/BackgroundProcesses/` - process management
