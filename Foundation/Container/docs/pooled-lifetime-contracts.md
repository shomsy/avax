# Pooled Lifetime Contracts

This document defines canonical contracts for pooled lifetime semantics in the DI system.

## What Is Pooled Lifetime?

**Pooled lifetime** is a service lifetime model where the container maintains a reusable pool of service instances that
are reset and recycled between uses rather than being disposed after each use. It is distinct from both `shared` (one
instance forever) and `transient` (new instance every time).

Pooled lifetime is designed for:

- **High-frequency short-lived workers**: HTTP handlers, queue jobs, CLI commands
- **Connection pooling**: Database connections, HTTP clients, gRPC channels
- **Resource efficiency**: Reusing expensive-to-create objects without holding them permanently

## When Pooled Lifetime Is Valid

Pooled lifetime is valid when:

1. **Instance state is stateless or resettable**: The service can be safely reused after `reset()` clears its internal
   state
2. **No cross-request contamination**: No user data, tenant context, or request-scoped data leaks between uses
3. **Resource efficiency matters**: Creating a new instance is expensive (connections, parsers, validators)
4. **Deterministic lifecycle**: The service has clear `reset-before-reuse` semantics

### Valid Examples

```php
// Valid: Stateless HTTP client with reset capability
$container->pooled(HttpClient::class)
    ->resettable()
    ->resetWith(function($instance) {
        $instance->clearHeaders();
        $instance->clearCookies();
    });

// Valid: Connection pool entry
$container->pooled(Connection::class)
    ->resettable()
    ->resetWith(fn($c) => $c->disconnect());

// Valid: Parser with clear() method
$container->pooled(JsonParser::class)
    ->resettable()
    ->resetWith(fn($p) => $p->clear());
```

## When Pooled Lifetime Is Unsafe

Pooled lifetime is unsafe when:

1. **Stateful with user data**: Services that hold user credentials, session data, or tenant context
2. **Non-resettable state**: Objects with internal state that cannot be safely cleared
3. **Thread-unsafe implementation**: Services not designed for concurrent reuse
4. **Leaky abstractions**: Services that hide stateful internals behind stateless-looking interfaces

### Unsafe Examples

```php
// UNSAFE: User context that cannot be cleared safely
$container->pooled(UserContext::class); // BAD - holds user data

// UNSAFE: System with sensitive data
$container->pooled(ResponseCache::class); // BAD - may leak response data

// UNSAFE: Non-thread-safe logger
$container->pooled(AsyncLogger::class); // BAD - not safe for reuse
```

## Reset-Before-Reuse Semantics

Each pooled service must declare its reset behavior:

### Via Closure

```php
$container->pooled(Parser::class)
    ->resetWith(function(Parser $parser) {
        $parser->clearTokenBuffer();
        $parser->resetPosition();
        $parser->clearErrors();
    });
```

### Via ResettableInterface

```php
class ResettableParser implements \Avax\Container\Capabilities\Runtime\Scopes\ResettableInterface
{
    public function reset(): void
    {
        $this->clearTokenBuffer();
        $this->resetPosition();
        $this->clearErrors();
    }
}

$container->pooled(ResettableParser::class);
```

### Via `resettable()` Marker

```php
$container->pooled(Parser::class)
    ->resettable(); // Uses class's own reset() method
```

## Disposal Expectations

Pooled services have special disposal rules:

1. **On worker termination**: All pooled services are disposed via `ServicePool::drain()`
2. **On container reset**: Pool is flushed, triggering disposal of marked services
3. **No per-request disposal**: Pooled services live across requests until reset
4. **Leak detection**: Undisposed pooled services at worker end trigger warnings

```php
// Disposal happens automatically
$container->reset(); // Resets pooled services, disposes marked ones

// Manual drain for inspection
$pooled = $servicePool->drain(); // Returns items, clears pool, triggers disposal
```

## Overflow Behavior

### Pool Size Limits

```php
// Maximum 10 instances in pool
$container->pooled(HttpClient::class)
    ->maxPoolSize(10);

// Overflow strategy: fail fast
$container->pooled(HttpClient::class)
    ->maxPoolSize(10)
    ->onOverflow(\Avax\Container\OverflowStrategy::FAIL);
```

### Overflow Strategies

| Strategy     | Behavior                                                 |
|:-------------|:---------------------------------------------------------|
| `FAIL`       | Throw `ContainerException` when pool is full             |
| `EVICT`      | Evict oldest instance to make room                       |
| `CREATE_NEW` | Allow unbounded growth (not recommended)                 |
| `BLOCK`      | Block until a slot is available (careful with deadlocks) |

### Default Behavior

- Default max pool size: configurable via `ContainerSettings`
- Default overflow strategy: `EVICT` (balanced safety/performance)
- Overflow is logged as warning in diagnostics mode

## Diagnostics Vocabulary

### Runtime Diagnostics

```php
// Pool state
$container->debugScope()['pooled']; // List of pooled services

// Pool statistics
$container->runtimeReport()['poolStats']; // hits, misses, resets, disposals

// Reset tracking
$container->debugService(Parser::class)['resetCount'];
```

### Metrics

| Metric           | Description              |
|:-----------------|:-------------------------|
| `pool_hits`      | Reuses from pool         |
| `pool_misses`    | Created new (pool empty) |
| `pool_resets`    | Reset-before-reuse calls |
| `pool_disposals` | Dispose on drain/reset   |
| `pool_evictions` | Evicted due to overflow  |
| `pool_size`      | Current pool size        |

## Benchmark Expectations

Pooled lifetime benchmarks should show:

1. **Reuse efficiency**: Pooled vs transient for same service
2. **Reset overhead**: Time to reset vs new instance
3. **Memory stability**: No growth over repeated uses
4. **Concurrency safety**: Thread-safe pooled access

### Benchmark Metrics

```
bench_pooled_reuse:      reuse from pool (baseline)
bench_pooled_reset:      reset and reuse
bench_transient_create:  new instance each time
bench_transient_ratio:   pooled/reuse ratio (should be < 1.0)
```

### Expected Ratios

- `pooled_reuse / transient_create` < 0.3 (70%+ savings)
- `pooled_reset / transient_create` < 0.5 (50%+ savings)
- `pool_evictions` ≈ 0 under normal load

## API Surface

```php
// Registration
$container->pooled(Service::class);
$container->pooled(Abstract::class, Concrete::class);

// Configuration
$container->pooled(Service::class)
    ->maxPoolSize(10)
    ->onOverflow(OverflowStrategy::EVICT)
    ->resettable()
    ->resetWith(fn($s) => $s->reset());

// Diagnostics
$container->describeService(Service::class)['poolConfig'];
$container->debugScope()['pooled'];
$container->runtimeReport()['poolStats'];
```

## Acceptance Criteria

### AC-103: Pooled Lifetime Is First-Class, Safe, Benchmarked, and Explainable

- **Criterion**: Pooled lifetime is a documented, validated, benchmarked lifetime with clear safety rules
- **Evidence**:
    - `pooled()` registration works with config
    - `resetWith()` or `ResettableInterface` required
    - Unsafe usage produces validation warnings
    - Benchmarks show reuse efficiency
- **Tests**: `PooledLifetimeSmokeTest.php` (to be created)

### AC-106: Structural Diff Explains Ownership and Dependency Changes

- **Criterion**: Pooled services are included in structural diff output
- **Evidence**:
    - `debugGraph()` includes pool configuration
    - Compile report shows pooled metadata
    - Structural diff includes pool size changes
- **Tests**: `PooledDiagnosticsSmokeTest.php` (to be created)

---

*This contract is owned by the architecture-contract agent. Codex implements runtime enforcement.*
