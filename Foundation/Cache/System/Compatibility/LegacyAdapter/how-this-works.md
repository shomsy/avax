---
title: Legacy Compatibility Layer
owner: AvaxCache Team
last_reviewed: 2026-04-25
classification: internal
---

# Legacy Compatibility Layer

This folder contains deprecated legacy classes for backward compatibility.

## Files

- **CacheManager.php** - deprecated, use `Cache` facade
- **CacheBackendInterface.php** - deprecated, use `CacheStore` capability
- **InMemoryCache.php** - deprecated, use `BuildCache::inMemory()`
- **Exception/** - legacy exception classes

## Migration

Replace legacy usage:

```php
// OLD (deprecated)
$cache = new CacheManager($backend);
$cache->get($key);

// NEW
Cache::use($yourCache);
Cache::get($key);
```

## Deprecation Status

All classes in this folder are deprecated and will be removed in V3.

Do not use these in new code.