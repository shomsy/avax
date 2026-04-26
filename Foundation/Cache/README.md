# AvaxCache

Enterprise-grade cache system for PHP 8.5+ with comprehensive value lifecycle management, storage abstraction,
and consistency guarantees across multiple store backends.

## Implemented

- **Runtime Cache Facade**: `Cache::get()`, `Cache::put()`, `Cache::remember()`, `Cache::forget()`, `Cache::clear()`,
  `Cache::read()`
- **CacheContract Interface**: PSR-16 compatible runtime cache contract
- **In-Memory Store**: Fast in-process cache store
- **File Store**: Persistent file-based cache store
- **Redis Store**: Redis-backed cache store
- **Unified Read Routing**: `Cache::read()` with explicit `RuntimeCacheTarget` / `CompiledCacheTarget`
- **Compiled Cache Subsystem**: Framework artifact compilation with atomic writes and manifest freshness
- **Cache Registry**: Named cache instances with `Cache::store($name)`
- **Contract Tests**: Store implementations validated by `CacheStoreContractTest`
- **Clock Dependency**: Deterministic testing with injected clocks
- **Stampede Protection**: Lock-based prevention via `AcquireCacheStampedeLock` flow
- **Stale-While-Revalidate**: `StaleValuePolicy` enum (DO_NOT_SERVE_STALE, SERVE_STALE_WHILE_REVALIDATING,
  SERVE_STALE_FOREVER)
- **Refresh-Ahead**: `RefreshPolicy` enum (DO_NOT_REFRESH, REFRESH_ON_READ, REFRESH_AHEAD, REFRESH_WHEN_STALE,
  REFRESH_AFTER_WRITE)
- **Capacity Control**: `InMemoryCacheStore` with max entries and `CacheCapacityWasExceeded` exception
- **Replacement Policies**: `LeastRecentlyUsedReplacement`, `FirstInFirstOutReplacement`, `NoReplacement`
- **Observability**: `CacheMetrics` with hit/miss/eviction/stale/lock-wait tracking
- **Multi-tier Cache**: `TieredCache` with L1 + L2 promotion via `CacheTier`
- **Source Sync**: `SourceSyncPolicy` enum (WRITE_THROUGH, WRITE_AROUND, WRITE_BEHIND, CACHE_ASIDE)
- **LFU Replacement**: `LeastFrequentlyUsedReplacement` with frequency decay
- **Distributed Cache**: `CacheCluster` with node health detection and consistent hash rebalancing
- **Cache Warming**: `WarmCache` flow for proactive cache population
- **Metrics Backends**: `PrometheusBackend`, `StatsDBackend`, `MetricsSink` for observability

## Experimental

- **Distributed Cache**: Consistent hash ring with stable node stores (experimental)

## Planned

- **Cache tier auto-scaling**: Dynamic tier promotion based on access patterns

## Quick Start: Standalone

```php
use Avax\Cache\Cache;
use Avax\Cache\System\Configuration\BuildCache;

$cache = (new BuildCache())->inMemory();

Cache::use($cache);

Cache::put('user:123', ['name' => 'John'], ttl: 3600);

$user = Cache::get('user:123');

Cache::forget('user:123');

$user = Cache::remember(
    'user:123',
    3600,
    fn () => $userRepository->find(123)
);

Cache::reset();
```

## Named Stores (Provider Required)

```php
use Avax\Cache\Cache;
use Avax\Cache\Providers\CacheServiceProvider;
use Avax\Container\Container;

$container = new Container();

$container->register(
    (new CacheServiceProvider())
        ->defaultStore('in_memory', ['ttl' => 3600])
        ->store('redis', 'redis', [
            'host' => '127.0.0.1',
            'port' => 6379,
            'ttl' => 1800,
        ])
);

$redis = Cache::store('redis');

$redis->set('session', $data, 1800);
```

## Unified Read

```php
use Avax\Cache\Cache;
use Avax\Cache\System\PublicSurface\RuntimeCacheTarget;
use Avax\Cache\System\PublicSurface\CompiledCacheTarget;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;

// Runtime key (raw string is always runtime)
$value = Cache::read('user:123');

// Explicit runtime target with own default
$value = Cache::read(RuntimeCacheTarget::key('user:123', 'default_value'));

// Named store
$value = Cache::read(RuntimeCacheTarget::key('user:123', store: 'redis'));

// Compiled artifact
$routes = Cache::read(CompiledCacheTarget::artifact(
    name: 'routes',
    builder: fn () => $routeCompiler->compile(),
    sources: CompiledCacheSources::fromPaths('routes/web.php')
));
```

## Compiled Cache

Separate subsystem for **framework-generated PHP artifacts**: routes, config, container, events, metadata.

```php
use Avax\Cache\CompiledCache;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;

$routes = CompiledCache::read(
    'routes',
    fn () => ['GET /users' => ['controller' => UserController::class]],
    CompiledCacheSources::fromPaths('routes/web.php')
);

CompiledCache::clear('routes');
CompiledCache::clearAll();
```

## Architecture

```
Foundation/Cache/
├── Cache.php                  # Public runtime facade: Avax\Cache\Cache
├── CompiledCache.php          # Public compiled facade: Avax\Cache\CompiledCache
├── Providers/
│   └── CacheServiceProvider.php
│
├── System/
│   ├── CacheContract.php      # Runtime cache interface
│   ├── AvaxCache.php          # Default implementation
│   ├── CacheResult.php
│   ├── CacheFailure.php
│   │
│   ├── PublicSurface/
│   │   ├── CacheFacade.php
│   │   ├── CacheRegistry.php
│   │   ├── RuntimeCacheTarget.php
│   │   ├── CompiledCacheTarget.php
│   │   ├── ReadFromCache.php
│   │   └── CacheNotConfigured.php
│   ��
│   ├── Flows/
│   │   ├── Operations/          # Read, Store, Forget, Clear
│   │   ├── Lifecycle/           # Remember, Invalidate, Refresh, Evict, Warm
│   │   ├── Protection/         # Protect, Sync, Recover
│   │   └── Compiled/          # Compile, Read, Clear, Warm
│   │
│   ├── Capabilities/
│   │   ├── Lifecycle/            # Value lifecycle management
│   │   │   ├── CachedValues/
│   │   │   ├── ExpireCachedValues/
│   │   │   ├── InvalidateCachedValues/
│   │   │   ├── RefreshCachedValues/
│   │   │   └── ReplaceCachedValues/
│   │   │
│   │   ├── Storage/             # Physical storage
│   │   │   ├── StoreCachedValues/
│   │   │   ├── SizeCachedValues/
│   │   │   └── ProtectCachedValues/
│   │   │
│   │   ├── Distribution/        # Horizontal scaling
│   │   │   ├── DistributeCachedValues/
│   │   │   ├── ReplicateCachedValues/
│   │   │   └── UseCacheTiers/
│   │   │
│   │   ├── Source/              # Source sync & protection
│   │   │   ├── ProtectCacheSource/
│   │   │   ├── SyncWithSource/
│   │   │   └── ControlConsistency/
│   │   │
│   │   ├── Observability/      # Metrics & tracing
│   │   │   ├── ObserveCache/
│   │   │   └── IdentifyCachedValues/
│   │   │
│   │   └── CompiledCache/       # Framework artifacts
│   │       └── ManageCompiledCache/
│   │
│   ├── Foundation/
│   │   ├── Time/
│   │   ├── Serialization/
│   │   ├── Compression/
│   │   └── Randomness/
│   │
│   └── Configuration/
│       └── BuildCache.php
│
├── tests/
└── docs/
```
│
├── tests/
└── examples/
```

## Key Design Decisions

### Cache Miss !== Stored Null

```php
Cache::put('key', null);
Cache::get('key'); // Returns null, NOT default

Cache::forget('key');
Cache::get('key'); // Returns default
```

### Expired !== Stale

- **Expired**: Past TTL, must be refreshed or removed
- **Stale**: Old but potentially servable while refreshing
- **Missing**: Key does not exist in cache

### Clock Dependency

All time operations use injected `Clock` for deterministic testing. No direct `time()` calls in core logic.

### Static Bootstrap

`CacheServiceProvider` calls `Cache::use(default)` to enable standalone static operations without container. Named
stores still require `CacheFacade`/`CacheRegistry` through the provider.

## Documentation

- `docs/` folder contains detailed documentation for each subsystem

## Requirements

- PHP 8.5+
- PSR-16 Simple Cache

## Testing

```bash
./vendor/bin/phpunit
```

## License

MIT