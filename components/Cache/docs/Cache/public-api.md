---
title: System-public-api
owner: AvaxCache Team
last_reviewed: 2026-04-25
classification: internal
---

# AvaxCache Public API

## Overview

AvaxCache provides a small, stable public API that minimizes misuse while maintaining flexibility for advanced use
cases.

## Core Interface

```php
interface System extends Psr\SimpleCache\CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;

    public function remember(string $key, null|int|\DateInterval $ttl, callable $loader): mixed;

    public function delete(string $key): bool;

    public function clear(): bool;

    public function has(string $key): bool;

    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;

    public function deleteMultiple(iterable $keys): bool;
}
```

## Usage Examples

### Basic Operations

```php
$cache = BuildCache::inMemory();

// Set with TTL
$cache->set('user:123', ['name' => 'John'], ttl: 3600);

// Get with default
$user = $cache->get('user:123', default: null);

// Check existence
if ($cache->has('user:123')) {
    // ...
}

// Delete
$cache->delete('user:123');

// Clear all
$cache->clear();
```

### Remember Pattern (Get-or-Load)

```php
$user = $cache->remember(
    key: 'user:123',
    ttl: 3600,
    loader: fn() => $userRepository->find(123)
);
```

### Batch Operations

```php
$cache->setMultiple([
    'user:1' => ['name' => 'Alice'],
    'user:2' => ['name' => 'Bob'],
    'user:3' => ['name' => 'Charlie'],
], ttl: 3600);

$users = $cache->getMultiple(['user:1', 'user:2', 'user:3']);
```

## Advanced Patterns

### Multi-tier System

```php
$l1 = new InMemoryCacheStore();
$l2 = new FileCacheStore('/var/cache/app');

$store = new ChainCacheStore($l1, $l2);
$cache = BuildCache::fromStore($store);
```

### Fallback Store

```php
$primary = new InMemoryCacheStore();
$fallback = new FileCacheStore('/var/cache/app');

$store = new FallbackCacheStore($primary, $fallback);
$cache = BuildCache::fromStore($store);
```

### Custom Configuration

```php
$config = new CacheConfiguration(
    name: 'users',
    defaultTtl: 3600,
    stalePolicy: StaleValuePolicy::SERVE_STALE_WHILE_REFRESHING,
    replacementPolicy: new LeastFrequentlyUsedReplacement()
);

$cache = BuildCache::inMemory($config);
```

## Result Types

For advanced users, `CacheResult` provides detailed operation outcomes:

```php
$result = $cache->get('key');

if ($result->isHit()) {
    $value = $result->value;
} elseif ($result->isMiss()) {
    $value = $loader();
}
```