# WarmApplication — How This Works

## What "Warm State" Means

In long-lived runtimes (ReactPHP, RoadRunner, Swoole, FrankenPHP), the application boots once and serves many requests.
Between requests, some state can safely stay in memory ("warm") while other state must be completely reset.

## What Is Allowed to Stay Warm

Defined in `WarmStateContract` / `AllowedWarmState`:

- Compiled container definitions
- Stateless singletons
- Compiled route table
- Immutable configuration and metadata
- Route metadata, DataTransfer class-shape metadata, attribute metadata
- Cached schema metadata
- Logger instances (without request context)
- Connection pool objects (not dirty connection state)
- Middleware pipeline definitions (without request data)
- Feature flag definitions
- Policy definitions

These are compiled/immutable once and reused across all requests.

## What Must Reset After Every Request

Defined in `WarmStateContract` / `MustResetState`:

- Current request / response objects
- User/auth context, session context
- Correlation ID, request ID, trace context
- Scoped container instances
- Request-scoped cache
- Validation error context
- Middleware runtime context
- Temporary runtime state
- Per-request container scope
- Query builder bound parameters
- Database transaction state

## Request Lifecycle Order

1. Request enters runtime
2. Request scope opens
3. Controller/middleware executes
4. Response is produced and returned
5. **Reset lifecycle runs** (flush scoped instances, reset registry, reset callbacks)
6. Request scope closes and reopens
7. Memory snapshot is recorded
8. No request state remains

## Reset Lifecycle Order

1. `FlushScopedInstances::flush()` — runs StateResetRegistry, custom flush callbacks, closes/reopens RequestScope
2. `ResetWarmRequestState::reset()` — runs all registered reset callbacks
3. Memory snapshot recorded (if MemoryGuard attached)

## Why ReactPHP Also Needs Warm Safety

ReactPHP is a long-running event loop. Without reset, each request accumulates state:
scoped container instances, request objects, correlation IDs, etc.
Over hundreds of requests, this causes memory growth and incorrect behavior
(request 500 seeing data from request 1).

## MemoryGuard Behavior

`MonitorWorkerMemory` tracks:
- Memory before/after each request
- Peak memory across all requests
- Memory delta per request
- Growth rate via `CalculateMemoryGrowthRate` (linear regression over snapshots)
- Soft threshold → warning/recycle decision
- Hard threshold → urgent recycle decision
- Max requests → recycle decision

`RequestWorkerRecycle` is a **decision/finding object**, not an actual process restart.
Real process reload is future work for RoadRunner/Swoole/FrankenPHP adapters (V4-17).

MemoryGuard never terminates a request mid-flight.

## What Is Real Now

- Warm State Contract (AllowedWarmState, MustResetState, WarmStateContract) — complete and tested
- Request reset lifecycle (HandleWarmRequest, FlushScopedInstances, ResetWarmRequestState) — complete and tested
- State leak detection (DetectLeakedState, RuntimeStateLeak) — complete and tested
- MemoryGuard (MonitorWorkerMemory, CalculateMemoryGrowthRate, RequestWorkerRecycle) — complete and tested
- ReactPHP runtime integration (startWarmSmoke, setWarmHandler, setMemoryGuard) — complete and tested
- Architecture checks — tested

## What Remains for RoadRunner/Swoole/FrankenPHP

V4-17 is blocked until V4-03 is GREEN. When V4-17 starts:
- Implement actual process restart based on RequestWorkerRecycle decisions
- Add RoadRunner-specific worker lifecycle hooks
- Add Swoole coroutine context reset
- Add FrankenPHP worker context reset
- Prove no state leakage through each adapter

## Component Files

```
framework/System/Runtime/WarmApplication/
  WarmStateContract.php        — Formal contract defining warm vs reset state
  AllowedWarmState.php         — Enum of state categories safe to keep warm
  MustResetState.php           — Enum of state categories that must reset
  HandleWarmRequest.php        — Orchestrates complete warm request lifecycle
  FlushScopedInstances.php     — Flushes scoped instances and resets request scope
  ResetWarmRequestState.php    — Callback-based reset registry
  DetectLeakedState.php        — Detects state leaks between requests
  RuntimeStateLeak.php         — Value object describing a specific state leak

framework/System/Runtime/MemoryGuard/
  RecordMemorySnapshot.php     — Records a simple memory snapshot
  CheckMemoryThreshold.php     — Checks memory against a threshold
  MonitorWorkerMemory.php      — Full memory tracking with before/after/peak
  CalculateMemoryGrowthRate.php — Linear regression growth rate calculation
  RequestWorkerRecycle.php     — Decision/finding object for worker recycle
```
