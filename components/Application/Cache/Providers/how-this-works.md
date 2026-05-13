---
title: CacheServiceProvider
owner: AvaxCache Team
last_reviewed: 2026-04-26
classification: internal
---

# CacheServiceProvider

Registers cache services in the Avax Container.

## What This Owns

- CacheRegistry singleton
- CacheFacade singleton
- ReadFromCache singleton
- Default CacheContract binding
- CompiledCache configuration

## Usage

```php
use Avax\Cache\Cache;
use Avax\Cache\CompiledCache;
use Avax\Cache\Providers\CacheServiceProvider;
use Avax\Cache\System\CacheContract;
use Avax\Cache\System\PublicSurface\CacheRegistry;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;
use Avax\Container\Container;

$container = new Container();

$container->register(
    (new CacheServiceProvider())
        ->defaultStore('in_memory', ['ttl' => 3600])
        ->store('api', 'redis', ['host' => '127.0.0.1', 'port' => 6379])
        ->compiledCacheDirectory('var/cache/compiled')
);

// Resolve from container
$cache = $container->make(CacheContract::class);
$registry = $container->make(CacheRegistry::class);

// Named stores through facade
$redis = Cache::store('redis');

// Static facade reads
Cache::get('key');
Cache::put('key', $value, 3600);

// Compiled cache
$routes = CompiledCache::read(
    'routes',
    fn () => ['GET /users' => ['controller' => UserController::class]],
    CompiledCacheSources::fromPaths($filesystem, 'routes/web.php')
);
```

## Methods

- `defaultStore($store, $options)` - Configure default cache store (default: in_memory)
- `store($name, $store, $options)` - Configure named store
- `compiledCacheDirectory($directory)` - Configure compiled cache directory

## Supported Stores

- `in_memory` - In-memory cache
- `file` - File-based cache
- `redis` - Redis cache

## Bootstrap Behavior

The provider calls `Cache::use(default)` to enable standalone static operations. Named stores require the provider's
`CacheFacade` binding. Call `Cache::reset()` before testing.
