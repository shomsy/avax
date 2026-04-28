---
title: System-system-design
owner: AvaxCache Team
last_reviewed: 2026-04-25
classification: internal
---

# AvaxCache System Design

## Architecture Overview

AvaxCache follows a **system-design approach** where cache is treated as a value lifecycle management system, not just a
key-value store.

```mermaid
flowchart TD
    subgraph Public["Public API"]
        System[System Interface]
        AvaxCache[AvaxCache]
    end

    subgraph Flows["Flows Layer"]
        Read[ReadCachedValue]
        Store[StoreCachedValue]
        Remember[RememberCachedValue]
        Forget[ForgetCachedValue]
        Invalidate[InvalidateCachedValue]
        Refresh[RefreshCachedValue]
        Evict[EvictCachedValue]
    end

    subgraph Capabilities["Capabilities Layer"]
        Identify[IdentifyCachedValues]
        Lifecycle[ManageCacheLifecycle]
        Store[StoreCachedValues]
        Observe[ObserveCache]
        Protect[ProtectCacheSource]
        Tiers[UseCacheTiers]
        Distribute[DistributeCachedValues]
    end

    subgraph Foundation["Foundation Layer"]
        Time[Time]
        Serialization[Serialization]
        Compression[Compression]
        Randomness[Randomness]
    end

    subgraph Stores["Store Backends"]
        InMemory[InMemoryCacheStore]
        File[FileCacheStore]
        Redis[RedisCacheStore]
        Chain[ChainCacheStore]
        Fallback[FallbackCacheStore]
    end

    System --> AvaxCache
    AvaxCache --> Flows
    Flows --> Capabilities
    Capabilities --> Stores
    Capabilities --> Foundation
```

## Value Lifecycle

```mermaid
stateDiagram-v2
    [*] --> Created: StoreCachedValue
    Created --> Active: Value stored
    Active --> ExpiringSoon: TTL < threshold
    ExpiringSoon --> Active: TTL extended
    ExpiringSoon --> Expired: TTL reached
    Active --> Stale: Idle time > threshold
    Stale --> Active: Value accessed
    Stale --> Refreshed: Refreshed
    Expired --> Miss: Value removed
    Stale --> Miss: Value removed
    Created --> Miss: Never accessed
    Miss --> [*]

    ExpiringSoon --> Invalidated: Explicit invalidation
    Stale --> Invalidated: Tag invalidation
    Active --> Invalidated: Namespace clear
    Invalidated --> Miss
```

## Multi-tier System Flow

```mermaid
sequenceDiagram
    autonumber
    participant Client as Client
    participant L1 as L1 (In-Memory)
    participant L2 as L2 (Redis/File)
    participant Source as Data Source

    Client->>L1: Read key
    L1-->>Client: Hit (return value)

    Client->>L1: Read key
    L1-->>Client: Miss
    L1->>L2: Read key
    L2-->>L1: Hit
    L1->>L1: Promote to L1
    L1-->>Client: Return value

    Client->>L1: Read key
    L1-->>Client: Miss
    L1->>L2: Read key
    L2-->>L1: Miss
    L1->>Source: Load value
    Source-->>L1: Value
    L1->>L2: Write to L2
    L1-->>Client: Return value
```

## Stampede Protection

```mermaid
sequenceDiagram
    autonumber
    participant Client as Client
    participant System as AvaxCache
    participant Lock as CacheLock
    participant Store as CacheStore
    participant Source as Data Source

    Client->>System: remember(key, loader)
    System->>Store: Check if key exists
    Store-->>System: Missing

    System->>Lock: Acquire lock for key
    alt Lock acquired
        Lock-->>System: Lock acquired
        System->>Source: Load value
        Source-->>System: Value
        System->>Store: Store value
        System->>Lock: Release lock
        System-->>Client: Value
    else Lock not acquired
        Lock-->>System: Lock timeout
        alt Stale policy allows
            System->>Store: Get stale value
            Store-->>System: Stale value
            System-->>Client: Stale value
        else Stale policy denies
            System-->>Client: Wait/Error
        end
    end
```

## Store Abstraction

```mermaid
classDiagram
    class CacheStore {
        <<interface>>
        +read(key, clock) CacheStoreRecordWasFound|CacheStoreRecordWasMissing
        +write(key, record) void
        +forget(key) void
        +clear() void
        +exists(key) bool
    }

    class InMemoryCacheStore {
        -records: array
        +read(key, clock) CacheStoreRecordWasFound|CacheStoreRecordWasMissing
        +write(key, record) void
        +forget(key) void
        +clear() void
        +exists(key) bool
    }

    class FileCacheStore {
        -basePath: string
        -serializer: CacheSerializer
        +read(key, clock) CacheStoreRecordWasFound|CacheStoreRecordWasMissing
        +write(key, record) void
        +forget(key) void
        +clear() void
        +exists(key) bool
    }

    class ChainCacheStore {
        -stores: CacheStore[]
        +read(key, clock) CacheStoreRecordWasFound|CacheStoreRecordWasMissing
        +write(key, record) void
    }

    class FallbackCacheStore {
        -primary: CacheStore
        -fallback: CacheStore
        +read(key, clock) CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    }

    CacheStore <|.. InMemoryCacheStore
    CacheStore <|.. FileCacheStore
    CacheStore <|.. ChainCacheStore
    CacheStore <|.. FallbackCacheStore
```

## Key Design Decisions

### 1. System Miss !== Stored Null

```php
$cache->set('key', null);
$cache->get('key'); // Returns null, NOT default

$cache->delete('key');
$cache->get('key'); // Returns default
```

### 2. Expired !== Stale

- **Expired**: Past TTL, must be refreshed or removed
- **Stale**: Old but potentially servable while refreshing
- **Missing**: Key does not exist in cache

### 3. Clock Dependency

All time-related operations use injected `Clock` to support:

- Deterministic testing with `FrozenClock`
- Time manipulation for cache testing
- Proper separation of concerns

### 4. Small Public API

The public API intentionally limits exposure to:

- Reduce cognitive load
- Prevent misuse
- Allow internal evolution without breaking changes
- Maintain clear ownership boundaries