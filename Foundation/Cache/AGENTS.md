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
├── Cache.php                  # Public runtime facade: Avax\Cache\Cache
├── CompiledCache.php          # Public compiled facade: Avax\Cache\CompiledCache
├── Providers/
│   └── CacheServiceProvider.php
│
├── System/
│   ├── CacheContract.php      # Runtime cache interface
│   ├── AvaxCache.php          # Default implementation
│   │
│   ├── PublicSurface/
│   │   ├── CacheFacade.php
│   │   ├── CacheRegistry.php
│   │   ├── RuntimeCacheTarget.php
│   │   ├── CompiledCacheTarget.php
│   │   ├── ReadFromCache.php
│   │   ├── CacheReadTarget.php
│   │   ├── CacheReadKind.php
│   │   └── CacheNotConfigured.php
│   │
│   ├── Flows/                  # 16 runtime flows + 4 compiled flows
│   ├── Capabilities/           # 10 runtime + 2 compiled capabilities
│   ├── Foundation/             # Time, Serialization
│   └── Configuration/          # BuildCache, BuildCompiledCache
│
├── tests/
├── docs/
└── examples/
```

### Three Public Entrypoints

1. **Avax\Cache\Cache** - static facade for runtime cache: `Cache::get()`, `Cache::put()`, `Cache::remember()`,
   `Cache::read()`
2. **Avax\Cache\CompiledCache** - static facade for compiled artifacts: `CompiledCache::read()`,
   `CompiledCache::compile()`
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
5. **Small public API, strong internal system** - Cache facade is a thin static router with routing logic
6. **Observability as first-class concern**
7. **Raw string in Cache::read() means runtime key** - compiled requires explicit target

### User Mental Model

```php
// Normal usage
Cache::get('key');
Cache::put('key', $value, ttl: 3600);
Cache::remember('key', 3600, fn() => $loader->load());
Cache::read('key'); // runtime
Cache::read(CompiledCacheTarget::artifact(...)); // compiled

// Testing
Cache::use($mockCache);
Cache::reset();
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

- All production stores must pass `CacheStoreContractTest` before promotion
- FrozenClock used for deterministic testing
- Metrics are optional (enable via config)
- No direct time() in core logic. The entire System/ folder is covered by NoTimeFunctionInSystemTest.
- All exceptions have error codes
- **FileCacheStore !== CompiledCache**: Runtime store stores values, CompiledCache stores framework-generated PHP
  artifacts
- **Cache facade is a thin static router** with routing logic for string/RuntimeCacheTarget/CompiledCacheTarget
- **Cache::read() string means runtime key** - compiled requires explicit target
- **Distributed cache is experimental** - routing skeleton only, excluded from contract tests until promoted
- **No Compatibility/LegacyAdapter folder** - legacy code removed