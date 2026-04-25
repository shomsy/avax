# AGENTS

## System Refactoring Summary

This component was refactored from a basic PSR-16 wrapper to an **enterprise-grade system-design cache framework** with
clean public surface.

### What Changed

**Before:**

```
Foundation/Cache/
├── CacheManager.php
├── CacheBackendInterface.php
├── InMemoryCache.php
└── Exception/
```

**After:**

```
Foundation/Cache/
├── System/
│   ├── Cache.php               # Public facade (Cache::get/put/remember)
│   ├── CacheContract.php       # Runtime cache interface
│   ├── CompiledCache.php       # Compiled artifacts facade
│   ├── AvaxCache.php           # Default implementation
│   │
│   ├── PublicSurface/          # Registry, facade, helpers
│   │   ├── CacheRegistry.php
│   │   ├── CacheFacade.php
│   │   └── CacheNotConfigured.php
│   │
│   ├── Flows/                  # 12 runtime flows + 4 compiled flows
│   ├── Capabilities/           # 10 runtime + 1 compiled capability
│   ├── Foundation/             # Time, Serialization
│   ├── Configuration/          # BuildCache, BuildCompiledCache
│   │
│   └── Compatibility/LegacyAdapter/  # Deprecated legacy classes
│
├── tests/
├── docs/
└── examples/
```

### Three Public Entrypoints

1. **Cache** - static facade for runtime cache: `Cache::get()`, `Cache::put()`, `Cache::remember()`
2. **CompiledCache** - static facade for compiled artifacts: `CompiledCache::read()`, `CompiledCache::compile()`
3. **BuildCache / BuildCompiledCache** - builders for manual composition and tests

### Version Roadmap

- **V1** (Complete): Core cache with InMemory, File stores, basic flows
- **V1.5**: Stampede protection, stale-while-revalidate, multi-tier
- **V2** (Complete): Compiled cache subsystem + public surface facade
- **V3**: LRU/LFU eviction, source sync, cache warming
- **V4**: Distributed partitioning, replication, consistency levels

### Key Principles Applied

1. **Folder says flow or capability, unit says responsibility, function says exact action**
2. **Clock dependency everywhere** - no direct time() calls
3. **Cache miss !== stored null** - explicit distinction
4. **Expired !== Stale** - explicit state modeling
5. **Small public API, strong internal system** - facade has no real logic
6. **Observability as first-class concern**

### User Mental Model

```php
// Normal usage
Cache::get('key');
Cache::put('key', $value, ttl: 3600);
Cache::remember('key', 3600, fn() => $loader->load());

// Testing
Cache::use($mockCache);
Cache::reset();

// Compiled artifacts
CompiledCache::read('routes', $builder, $sources);
```

### Testing Commands

```bash
./vendor/bin/phpunit
./vendor/bin/phpunit tests/Contract/
./vendor/bin/phpunit tests/Unit/
```

### Documentation Structure

Each folder contains `how-this-works.md` explaining:

- What the folder owns
- What triggers it
- The main flow
- Debug guidance
- Key concepts

### Important Notes

- All stores must pass `CacheStoreContractTest`
- FrozenClock used for deterministic testing
- Metrics are optional (enable via config)
- No direct time() in core logic
- All exceptions have error codes
- **FileCacheStore !== CompiledCache**: Runtime store stores values, CompiledCache stores framework-generated PHP
  artifacts
- **Cache facade delegates to CacheRegistry** - facade has no real business logic