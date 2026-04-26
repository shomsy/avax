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

## Experimental

- **Distributed Cache**: Routing model (skeleton, excluded from contract tests until promoted)
- **Multi-tier Cache**: L1 + L2 promotion (planned)

## Planned

- **Stampede Protection**: Lock-based prevention
- **Stale-While-Revalidate**: Serve stale while refreshing
- **Replacement Policies**: LRU, LFU, FIFO, Random eviction
- **Source Sync**: Write-through / write-around / write-behind
- **Observability**: Metrics, tracing, hit rate tracking

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
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;

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
│   │   ├── CacheReadTarget.php
│   │   ├── CacheReadKind.php
│   │   ├── CacheNotConfigured.php
│   │   ├── CacheNotFound.php
│   │   └── CompiledCacheNotConfigured.php
│   │
│   ├── Flows/
│   │   ├── ReadCachedValue/
│   │   ├── StoreCachedValue/
│   │   ├── RememberCachedValue/
│   │   ├── ForgetCachedValue/
│   │   ├── ClearCache/
│   │   ├── InvalidateCachedValue/
│   │   ├── RefreshCachedValue/
│   │   ├── EvictCachedValue/
│   │   ├── WarmCache/
│   │   ├── ProtectCacheSource/
│   │   ├── RecoverCache/
│   │   ├── SyncCachedValue/
│   │   ├── CompileCache/
│   │   ├── ReadCompiledCache/
│   │   ├── ClearCompiledCache/
│   │   └── WarmCompiledCache/
│   │
│   ├── Capabilities/
│   │   ├── IdentifyCachedValues/
│   │   ├── ManageCacheLifecycle/
│   │   ├── StoreCachedValues/
│   │   ├── ObserveCache/
│   │   ├── ProtectCacheSource/
│   │   ├── ProtectCachedValues/
│   │   ├── UseCacheTiers/
│   │   ├── DistributeCachedValues/  # Experimental
│   │   ├── ReplicateCachedValues/
│   │   ├── SyncWithSource/
│   │   ├── SizeCachedValues/
│   │   └── ManageCompiledCache/
│   │
│   ├── Foundation/
│   │   ├── Time/
│   │   ├── Serialization/
│   │   ├── Compression/
│   │   └── Randomness/
│   │
│   └── Configuration/
│       ├── BuildCache.php
│       └── CompiledCacheConfiguration/
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