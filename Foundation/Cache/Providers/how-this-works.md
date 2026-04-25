---
title: CacheServiceProvider
owner: AvaxCache Team
last_reviewed: 2026-04-25
classification: internal
---

# CacheServiceProvider

Registers cache services in the Avax Container.

## What This Owns

- CacheRegistry singleton
- CacheFacade singleton
- Default CacheContract binding
- CompiledCache configuration

## Usage

```php
use Avax\Cache\Providers\CacheServiceProvider;
use Avax\Container\Container;

$container = new Container();

$container->register(new CacheServiceProvider()
    ->defaultStore('in_memory', ['ttl' => 3600])
    ->store('api', 'redis', ['host' => '127.0.0.1', 'port' => 6379])
    ->compiledCacheDirectory('var/cache/compiled')
);

// Resolve from container
$cache = $container->make(CacheContract::class);
$registry = $container->make(CacheRegistry::class);

// Or use static facade
Cache::get('key');
CompiledCache::read('routes', $builder, $sources);
```

## Methods

- `defaultStore($store, $options)` - Configure default cache store
- `store($name, $store, $options)` - Configure named store
- `compiledCacheDirectory($directory)` - Configure compiled cache directory

## Supported Stores

- `in_memory` - In-memory cache
- `file` - File-based cache
- `redis` - Redis cache