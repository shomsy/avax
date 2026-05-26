# How to Govern Concurrency and Runtime Safety

## Purpose
This document operationalizes async, concurrency, and runtime safety within the execution runtime. It ensures that components designed to run in long-lived environments (like Swoole, RoadRunner, FrankenPHP, ReactPHP, or Amp Event Loops) preserve state isolation, prevent memory leaks, and handle backpressure.

## Role in the Runtime Governance
The framework aims to be runtime-agnostic but production-ready under any runtime model. Traditional PHP requests are short-lived and clean all state on exit. Modern high-performance runtimes run continuously, meaning request pollution, resource leaks, and concurrent race conditions can bring down the entire application process.

## Request Scope
Every request must execute within a clean, isolated scope. 
- Container services must classify stateful dependencies.
- Services that hold request-scoped state (e.g., current user, active session, request transaction) must be explicitly reset or created via factory per request.
- Global variables (`$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`) are forbidden inside flows or capabilities. Use PSR-7 ServerRequestInterface or the framework's request abstraction.

## Long-Lived Worker Safety
Long-lived workers process thousands of requests. You must guarantee:
1. **No Memory Leaks**: Avoid static collections or registry patterns that grow indefinitely.
2. **Resource Lifecycle**: Connections (SQL, Redis, files) must be released, put back in connection pools, or closed.
3. **Reset State**: Implement resetting mechanisms (e.g., `ResetInterface`) for any service that caches lookup state.

## State Reset
If a service caches data or retains state:
- It must implement an explicit reset/flush method.
- The runtime wrapper must call this method between request cycles.
- Static class properties must not accumulate request-specific data.

## Shared Mutable State
Shared mutable state is the root of all concurrency issues.
- State sharing between Fibers, Threads, or Processes must use safe concurrency primitives (e.g., atomic locks, channels, or serialized state).
- Read-only data is safe to share; mutable data is not.

## Race Conditions
- Guard database mutations with transaction isolation levels or optimistic/pessimistic locking.
- Concurrent updates to the same cache key must use transaction tokens (CAS - Compare-And-Swap) or distributed locks.

## Process, Thread, and Fiber Assumptions
Do not assume sequential execution when writing async handlers:
- A fiber yield point (like an async database call or file read) allows other tasks to run.
- Guard against concurrent modification of internal arrays while yielding.

## Async Task Lifecycle
- Every async task must have a bounded lifespan.
- Tasks must not run "detached" without error handlers; unhandled exceptions in background loops can crash the event loop or CLI process.

## Queue/Stream Backpressure
- When processing event streams or queues, enforce a max limit of concurrent workers.
- Avoid memory exhaustion when producer rate is higher than consumer rate by applying backpressure (pausing source reading).

## Timeout/Cancellation
- Every external I/O operation must specify a connection and read timeout.
- Async tasks should support cancellation tokens to prevent zombie tasks when requests abort.

## Retry/Idempotency
- Concurrency failures (deadlocks, lock wait timeouts) must be retried with exponential backoff.
- The handler must prove that retries are safe by verifying idempotency.

## External Resource Lifecycle
- File descriptors, socket connections, and database handles must fail closed.
- Always use `try...finally` structures to release locks and close resources.

## Runtime Adapter Boundary
- Keep all Swoole, ReactPHP, or RoadRunner specific APIs strictly behind Adapters.
- The framework core and component domains must never import classes from Swoole, ReactPHP, or RoadRunner.

## Observability for Runtime Failure
- Log event loop delay or block times.
- Monitor active memory consumption.
- Log thread/fiber dump on timeout.

## Concurrency Safety Matrix

| Pattern | Risk | Safe Mitigation |
|---|---|---|
| Dependency Injection Singleton | Request contamination | Keep singleton stateless; resolve stateful dependencies from request context |
| Static Caching | Memory leaks | Implement flush/reset hooks and limit cache maximum size |
| Fiber / Coroutine Yielding | Race conditions / State drift | Verify class variables are not mutated by concurrent yields |
| Persistent DB Connections | Connection drop / Leak | Wrap in pool with automatic keepalive and validation checks |

## Severity Rules

| Finding | Severity |
|---|---|
| Mutable request state saved in DI singleton | BLOCKER |
| Unhandled exception in background loop / fiber | HIGH |
| Missing I/O timeout configuration | HIGH |
| Swoole/ReactPHP imports inside domain/core code | HIGH |
| Missing reset implementation for cache-holding service | MEDIUM |

## Stop Conditions
- **BLOCKER**: Shared state leaked across distinct concurrent requests.
- **BLOCKER**: Memory leak detected on request cycle.
- **HIGH**: No timeout configured for network I/O.
