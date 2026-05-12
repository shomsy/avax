# FailureBoundary — Timeout and RecoverWith Deferred

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Timeout

### Attribute Definition

```php
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Timeout
{
    public function __construct(public int $milliseconds = 1000) {}
}
```

### Compiled Status

`CompileFailurePolicies` reads `Timeout` attributes and stores `$timeoutMs` in `FailurePolicy`.

### Runtime Enforcement: NONE

`RunFailurePipeline` never reads `$policy->timeoutMs`. The timeout is compiled but never enforced.

### Why Deferred

Timeout enforcement requires one of:
1. **PHP Fibers** (`Fiber` class) — suspend/resume execution, kill on timeout
2. **pcntl signals** — Unix-only, not portable, interferes with other signal handlers
3. **Async runtime** — ReactPHP, Swoole, FrankenPHP with native timeout support
4. **Process isolation** — Fork process, kill on timeout (heavy, not suitable for HTTP)

None of these are available in the current AvaX runtime baseline.

### Dependency Chain

```
Timeout enforcement
  → Fiber/async runtime (V4-02 ReactPHP, V4-17 optional runtime adapters)
  → Runtime abstraction layer
  → TimeoutCapability in FailureBoundary
```

### Decision: DEFERRED to V4 runtime adapters

Documented in policy. Attribute compiles cleanly so it's ready when runtime support arrives.

## RecoverWith

### Attribute Definition

```php
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class RecoverWith
{
    public function __construct(public string $handlerClass) {}
}
```

### Compiled Status

`CompileFailurePolicies` reads `RecoverWith` attributes and stores `$recoverWithClass` in `FailurePolicy`.

### Runtime Enforcement: NONE

`RunFailurePipeline` never reads `$policy->recoverWithClass`. The attribute is compiled but never executed.

### Why Deferred

RecoverWith requires:
1. A **recovery strategy component** that knows how to restore system state
2. A **handler interface** that recovery classes must implement
3. **Context awareness** — what "recovery" means depends on the failure domain
4. **State management** — recovery may need to clean up partial state, reset connections, etc.

This is the domain of a reliability engine, not a failure boundary.

### Dependency Chain

```
RecoverWith enforcement
  → Reliability engine (V5.6)
  → Recovery handler interface
  → State reset integration (StateResetRegistry exists but needs recovery semantics)
```

### Decision: DEFERRED to V5.6 reliability engine

Documented in policy. Attribute compiles cleanly so it's ready when reliability engine arrives.

## Honest Classification

| Attribute | Compiled | Enforced | Status |
|-----------|----------|----------|--------|
| Timeout | Yes | No | RED/deferred |
| RecoverWith | Yes | No | RED/deferred |

Both attributes are **not decorative** — they compile into the policy and will be ready for enforcement
when the runtime/reliability infrastructure exists. They are **forward-compatible declarations**.
