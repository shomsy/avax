# Route Cache Plan — V4-04

## Problem

In production, loading and parsing route definitions on every request adds unnecessary latency.
Route caching stores a compiled, optimized route table that can be loaded faster than executing the full route
registration logic.

## What Gets Cached

1. **Route Table** — The final compiled mapping of HTTP method + path → handler
2. **Route Metadata** — Middleware, constraints, and route-level configuration
3. **Route Index** — A fast-lookup structure (e.g., radix tree or hash map)

## What Does NOT Get Cached

1. **Route Closures** — Closures cannot be serialized. Handlers must be referenced by class::method or route ID.
2. **Dynamic Route Definitions** — Routes that depend on runtime state (e.g., database-driven routes) must be excluded.
3. **Environment-Sensitive Routes** — Routes that change behavior per environment should not be cached.

## Cache Storage

```
storage/framework/cache/routes/
  routes.php              — Compiled route table (returns array)
  routes.php.meta         — Cache metadata (timestamp, route count, environment)
```

## Cache Lifecycle

### Generation

```bash
php avax route:cache
```

1. Boot the application in isolation (no HTTP server)
2. Execute all route registration callbacks
3. Serialize the compiled route table to `storage/framework/cache/routes/routes.php`
4. Write metadata file with timestamp, route count, environment hash

### Loading

1. Check if `routes.php` exists and is valid
2. Verify cache freshness (environment hash matches, file not stale)
3. Load compiled route table directly via `include`
4. Skip route registration callbacks entirely

### Invalidation

```bash
php avax route:clear
```

Cache is invalidated when:

- `route:clear` is called explicitly
- Route definition files change (detected via file modification time)
- Environment changes (production vs development)
- Application version changes

## Warm Worker Safety

In warm workers (ReactPHP, RoadRunner, FrankenPHP, Swoole):

1. Route cache is loaded **once** at worker boot → stays warm as immutable state
2. This is classified as `AllowedWarmState::CompiledRouteTable`
3. Route cache must **not** be reloaded per request
4. Route cache invalidation requires worker recycle

## Implementation Plan

### Phase 1: Route Cache Foundation (V4-04 — this stage)

- [x] Route cache plan document (this file)
- [ ] `CacheRouteTable` capability — compiles and writes route cache
- [ ] `LoadCachedRoutes` capability — loads and validates cached routes
- [ ] `route:cache` CLI command
- [ ] `route:clear` CLI command

### Phase 2: Route Cache Integration (V4-05+)

- [ ] ApplicationBuilder detects and uses cached routes when available
- [ ] Route cache invalidation on file change detection
- [ ] Route cache warm-up hook for worker boot
- [ ] Performance benchmarks proving cache benefit

### Phase 3: Route Cache Optimization (Future)

- [ ] Radix tree compilation for O(log n) route lookup
- [ ] Pre-compiled middleware chains
- [ ] Route-level cache warming

## Security Considerations

1. Route cache files must **never** be served publicly
2. Cache directory must be outside web root
3. Cache files must be validated before loading (check return type is array)
4. No user input should influence cache generation

## Rollback

If route cache causes issues:

```bash
php avax route:clear
```

Application falls back to normal route registration automatically.

## Evidence Requirements

A route cache implementation must prove:

1. Cached routes produce identical behavior to uncached routes
2. Cache load is measurably faster than full route registration
3. Cache invalidation works correctly when source files change
4. Route cache does not leak state between requests in warm workers
5. Invalid or corrupted cache files are detected and rejected
