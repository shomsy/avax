---
name: avax-runtime-performance-cache
description: Enforces runtime performance, caching discipline, hot-path safety, long-lived worker safety, compilation/warmup preference, and benchmark-backed decisions. Use for performance, cache, memoization, hot path, runtime, long-lived worker, Swoole, RoadRunner, FrankenPHP, async, container, reflection, metadata, compilation, warmup, preload, routing, database/query compilation, filesystem scanning, benchmark, latency, throughput, memory, optimization, high performance, AvaX JIT, compiled runtime plans.
---

# AvaX Runtime Performance and Cache

## Purpose

This skill governs performance-sensitive AvaX implementation work.

It applies to:

- runtime execution
- boot lifecycle
- container/DI
- metadata compilation
- routing
- event dispatching
- database/query compilation
- filesystem access
- HTTP/request handling
- cache usage
- config loading
- reflection/class discovery
- worker/server runtime
- async/concurrent execution
- preload/warmup
- benchmark-sensitive code

The goal is:

- fast hot paths
- predictable memory
- no worker leaks
- no accidental stale state
- no unnecessary filesystem/reflection work at runtime
- correct cache invalidation
- benchmark-backed decisions
- production-safe performance improvements

## Activation Triggers

Activate when task involves:

- performance, cache, caching, memoization
- hot path, runtime, long-lived worker
- Swoole, RoadRunner, FrankenPHP, async
- container, reflection, metadata
- compilation, warmup, preload
- routing, database/query compilation
- filesystem scanning, benchmark
- latency, throughput, memory, optimization
- "runtime mora biti performantan"
- "high performance"
- "AvaX JIT"
- "compiled runtime plans"

## Performance-First Questions

Before changing production code, the agent must answer:

- Is this code on a hot path?
- Is it boot-time, compile-time, warmup-time, request-time, worker-time, or shutdown-time?
- How often does this code run?
- What is the expected cost: CPU, memory, IO, allocation, reflection, filesystem, network?
- Can this work be moved from runtime to compile/warmup?
- Can this work be precomputed?
- Is caching justified by measured or obvious repeated cost?
- What is the invalidation model?
- What is the cache scope?
- What happens in long-lived workers?
- What happens across tenants/requests/users?
- What happens under concurrency?
- What happens on config/code deploy?
- What is the failure mode?
- How do we prove this is faster or at least not slower?

## Runtime Hot-Path Forbidden Patterns

Forbid in hot paths unless explicitly justified:

- reflection
- filesystem scan
- glob/recursive directory scan
- config file parsing
- env access
- dynamic class discovery
- class_exists as runtime discovery
- service locator lookup
- container compilation
- regex-heavy repeated parsing
- repeated metadata parsing
- repeated route compilation
- repeated attribute scanning
- repeated closure binding
- unbounded array growth
- per-request static mutable cache without reset lifecycle
- hidden singleton state
- broad catch-and-ignore around performance/cache failure

## Cache Design Gate

Every cache must define:

- cache owner
- cache purpose
- cache key shape
- key namespace
- scope: process, request, worker, tenant, user, application, deployment
- lifecycle: immutable, TTL, explicit invalidation, warmup-only, reset-per-request, reset-per-worker
- invalidation trigger
- stale-data risk
- memory growth bound
- concurrency behavior
- serialization format, if any
- observability/debugging strategy
- fallback behavior
- failure behavior

Cache without invalidation/lifecycle is forbidden unless it is immutable deployment-time compiled data.

## Long-Lived Worker Safety Gate

For Swoole/RoadRunner/FrankenPHP/worker runtimes, the agent must check:

- Does state leak between requests?
- Does cache include request/user/session/tenant data?
- Is reset lifecycle registered?
- Is mutable static state avoided?
- Is object reuse safe?
- Are closures capturing request state?
- Are buffers cleared?
- Are per-request services scoped?
- Is memory growth bounded?
- Is the cache safe under concurrent requests/coroutines/fibers?

Any worker-state leak is BLOCKER.

## Compilation and Warmup Preference

Prefer:

- compiled metadata over runtime reflection
- compiled route tables over route scanning
- compiled container over runtime assembly
- precomputed attribute maps over hot-path attribute reads
- warmup/preload over lazy runtime discovery
- immutable plans over repeated runtime decisions

But compiled artifacts must preserve semantics.

The skill requires:

- same behavior with and without compiled/warmup mode
- explicit fallback mode
- invalidation on config/code change
- tests for compiled and non-compiled paths where applicable

## Benchmark/Proof Requirement

For performance-sensitive changes, require at least one of:

- microbenchmark
- before/after timing script
- allocation/memory comparison
- proof that work moved from request-time to boot/compile-time
- test proving no repeated expensive work
- static gate proving no reflection/filesystem scan in hot path

Evidence must include:

`performance-proof.md`

With:

- baseline
- changed behavior
- command used
- result
- interpretation
- limitations

## Component Dogfooding Integration

Before adding local caching/performance logic, check whether AvaX already has:

- Cache component
- Configuration component
- Runtime lifecycle
- StateResetRegistry
- Metadata/Compilation capability
- Filesystem component
- Logger/Observability component
- Clock/Time component

Use existing AvaX capabilities through correct boundaries.

Do not create local ad-hoc cache arrays if Cache/Runtime/Configuration should own it.

## Evidence Requirements

Every performance/cache-sensitive production task must write:

- `performance-design.md`
- `cache-design.md` if caching is involved
- `runtime-safety-proof.md`
- `performance-proof.md`
- `validation-output.md`
- `governance-review.md`

If caching is not used, explicitly write:

`cache-not-used.md`

explaining why caching was rejected.

## Classification

Classify every performance-sensitive change:

- HOT_PATH_IMPROVED
- HOT_PATH_UNCHANGED
- BOOT_TIME_COST_ACCEPTED
- COMPILE_TIME_COST_ACCEPTED
- CACHE_DESIGN_VALID
- CACHE_NOT_JUSTIFIED
- WORKER_SAFE
- WORKER_UNSAFE_BLOCKER
- PERFORMANCE_UNPROVEN_YELLOW
- PERFORMANCE_REGRESSION_BLOCKER
- CACHE_INVALIDATION_MISSING_BLOCKER
- MEMORY_GROWTH_UNBOUNDED_BLOCKER

Commit is forbidden for:

- WORKER_UNSAFE_BLOCKER
- PERFORMANCE_REGRESSION_BLOCKER
- CACHE_INVALIDATION_MISSING_BLOCKER
- MEMORY_GROWTH_UNBOUNDED_BLOCKER

## Integration with Other Skills

This skill must be loaded together with:

- `avax-enterprise-remediation`
- `avax-autonomous-backlog-loop` for autonomous sweeps
- `avax-enterprise-codecraft` for production-code changes
- `avax-component-dogfooding` when component reuse may apply
- `testing` skill
- `validation` skill
- `security` skill when secrets/user data/cache/session are involved
- `review` skill

It does not replace how-to rules.
It enforces performance/runtime/cache quality.

## Final Rule

No hot-path analysis, no optimization.

No cache design, no caching.

No benchmark proof, no GREEN.

No worker safety, no merge.
