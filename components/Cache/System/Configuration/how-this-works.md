---
title: Configuration-how-this-works
owner: Configuration Team
last_reviewed: 2026-04-25
classification: internal
---

# Configuration How This Works

Configuration provides **building and wiring** utilities for cache instantiation.

## What This Folder Owns

- System configuration
- Store configuration
- System builder

## CacheConfiguration

```php
final readonly class CacheConfiguration
{
    public function __construct(
        public string $name = 'default',
        public null|int $defaultTtl = 3600,
        public null|int $maxCapacity = null,
        public StaleValuePolicy $stalePolicy = StaleValuePolicy::DO_NOT_SERVE_STALE,
        public ChooseCachedValueForReplacement $replacementPolicy = new LeastRecentlyUsedReplacement(),
        public bool $enableMetrics = true,
        public bool $enableTracing = false,
        public array $serializerOptions = []
    ) {}
}
```

## CacheStoreConfiguration

```php
// In-memory store
$config = CacheStoreConfiguration::inMemory();

// File store
$config = CacheStoreConfiguration::file('/var/cache/app');

// Redis store
$config = CacheStoreConfiguration::redis('127.0.0.1', 6379);

// Build store
$store = $config->build();
```

## BuildCache

```php
$builder = new BuildCache(new SystemClock());

// In-memory cache
$cache = $builder->inMemory();

// File-based cache
$cache = $builder->file('/var/cache/app');

// From existing store
$cache = $builder->fromStore($store);

// Tiered cache
$cache = $builder->tiered($l1Store, $l2Store);
```

## Usage Examples

### Simple Setup

```php
$cache = BuildCache::inMemory();

$cache->set('key', 'value', ttl: 3600);
$value = $cache->get('key');
```

### With Configuration

```php
$config = new CacheConfiguration(
    name: 'users',
    defaultTtl: 3600,
    stalePolicy: StaleValuePolicy::SERVE_STALE_WHILE_REFRESHING,
    replacementPolicy: new LeastRecentlyUsedReplacement()
);

$cache = BuildCache::inMemory($config);
```

### Multi-tier

```php
$l1 = new InMemoryCacheStore($clock);
$l2 = new FileCacheStore('/var/cache', $clock);

$cache = BuildCache::tiered($l1, $l2);
```

## Debug First

1. **Check configuration** - are options correct?
2. **Check store type** - right store for use case?
3. **Check TTL defaults** - appropriate defaults?

## Dictionary

- `CacheConfiguration`: System-level options
- `CacheStoreConfiguration`: Store-level options
- `BuildCache`: Factory for cache creation