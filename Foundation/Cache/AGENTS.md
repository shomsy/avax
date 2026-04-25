# AGENTS

## System Refactoring Summary

This component was refactored from a basic PSR-16 wrapper to an **enterprise-grade system-design cache framework**.

### What Changed

**Before:**

```
Foundation/System/
├── CacheManager.php
├── CacheBackendInterface.php
├── InMemoryCache.php
└── Exception/
```

**After:**

```
Foundation/System/
├── System/
│   ├── System.php              # Public API
│   ├── AvaxCache.php          # Main implementation
│   ├── CacheResult.php        # Result types
│   │
│   ├── Flows/                 # 12 runtime flows + 4 compiled flows
│   ├── Capabilities/          # 10 runtime + 1 compiled capability
│   ├── Foundation/            # Time, Serialization
│   └── Configuration/         # Building utilities
│       ├── CacheConfiguration/
│       └── CompiledCacheConfiguration/
│
├── tests/                     # Contract + Unit tests
├── docs/                      # System documentation with Mermaid
└── examples/
```

### Key Principles Applied

1. **Folder says flow or capability, unit says responsibility, function says exact action**
2. **Clock dependency everywhere** - no direct time() calls
3. **System miss !== stored null** - explicit distinction
4. **Expired !== Stale** - explicit state modeling
5. **Small public API, strong internal system**
6. **Observability as first-class concern**

### Version Roadmap

- **V1** (Complete): Core cache with InMemory, File stores, basic flows
- **V1.5**: Stampede protection, stale-while-revalidate, multi-tier
- **V2**: Compiled cache subsystem (routes, config, container artifacts)
- **V3**: LRU/LFU eviction, source sync, cache warming
- **V4**: Distributed partitioning, replication, consistency levels

### Testing Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run contract tests
./vendor/bin/phpunit tests/Contract/

# Run unit tests
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