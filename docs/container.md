# Container Mental Model

## Resolve Precedence (Canonical)

```
Cache (scoped instance / singleton cache)
  -> Conditional override (consumer-specific)
  -> Binding factory
  -> Autowire (reflection)
  -> Lazy definition (proxy fallback)
  -> Fail (strict mode)
```

## Conditional vs Scoped vs Singleton vs Lazy

- Conditional: override per consumer + dependency pair ("when X needs Y, give Z"), applied before bindings for targeted
  consumers.
- Conditional supports interface and wildcard matching.
- Constructor injection is primary; property injection is convenience-only.
- Property injection is context-agnostic by design (no conditional overrides).
- Scoped: one instance per active scope (request, tenant, job).
- Singleton: one instance per container lifetime.
- Lazy: returns a proxy; real object is created on first use, used as fallback when other strategies do not match.
- Lazy TTL can invalidate cached instances (e.g., ttl: 60).
- Lazy layers can wrap resolvers (e.g., logging/caching decorators).

## When to Use Scoped Instead of Singleton

- Use scoped for per-request or per-tenant services that must not leak across executions.
- Use singleton for stable infrastructure services (logger, config, cache).

## Example (Conditional)

```php
Container::create()
    ->when(\App\Http\Controllers\ReportController::class)
        ->needs(\App\Contracts\Clock::class)
        ->give(\App\Time\FixedClock::class);
```

## Feature Status

- Core: Bind / Resolve / Autowire, Scoped, Conditional, Singleton, ServiceProviders, Lifecycle hooks
- Support: Lazy, Tags, Diagnostics
- Experimental: Deferred, DependencyGraph, ChildContainers (legacy only; not in resolve flow)

## API Lock

- Public entry: `Avax\Container\Core\Container` only.
- All other modules are internal and may change without notice.
- Public API changes require tests.
- Core DI behavior is FROZEN (changes require tests).

## Interfaces Quick Map

| Interface                  | Purpose                                              | Typical usage           |
|----------------------------|------------------------------------------------------|-------------------------|
| `ContainerReaderInterface` | Read-only access (`get`, `resolve`, `inject`)        | Inside application code |
| `ContainerWriterInterface` | Registration (`bind`, `singleton`, `scoped`, `lazy`) | Bootstrap and providers |
| `ContainerInterface`       | Combined read/write                                  | Full DI in runtime      |

## Diagnostics Snapshot

- Snapshot is full and includes bindings, instances, scoped, lazy, conditional, tags, activeScope, strictMode,
  strictInjection, hooksProfile.
- `everything(true)` returns a JSON schema for registered services.
- Metrics snapshot is included when a metrics collector is configured (enable via
  `Container::withMetricsCollector(new \Avax\Container\Validate\Telemetry\MetricsCollector())`).

## Optimization & Tools

- Reflection is cached and invalidated by file signature.
- Reflection metadata persists in `cache/reflector_meta.php`.
- Compiled container: `php bin/compile-container` → `cache/container_compiled.php`.
- Load compiled map at runtime: `Container::optimize()`.
- Watch & recompile when stale: `Container::watchOptimize()`.
- CLI: `php bin/container container:validate --report=json`, `php bin/container container:health`,
  `php bin/container container:graph`, `php bin/container container:diagnose`, `php bin/container container:optimize`,
  `php bin/container container:cache:clear`.

## Invariants

- Resolver is the single source of truth for resolution order.
- Precedence is defined only in Resolver.
- Register modules never instantiate objects.
- Store modules are pure state holders.
- Experimental features never affect resolve flow.
- ERROR hooks fire once per top-level resolve.
