---
title: CacheCapabilities-how-this-works
owner: AvaxCache Team
last_reviewed: 2026-04-25
classification: internal
---

# Capabilities How This Works

Capabilities are **shared abilities** that support multiple flows. They are not technical leftovers but honest
cross-flow ownership of system-wide concerns.

## What This Folder Owns

The Capabilities folder contains:

- Shared system abilities
- Reusable mechanisms
- Stable boundaries
- System-wide infrastructure

## Child Folders

### IdentifyCachedValues/

Key identification and validation:

- `CacheKey`: Validated, normalized, namespaced keys
- `CacheKeyPrefix`: Key prefix management
- `CacheNamespace`: Namespace boundaries
- `CacheTag`: Tag-based organization
- `CacheTags`: Multi-tag support
- `CacheVersion`: Version-based key isolation

### ManageCacheLifecycle/

Value lifecycle management:

- `CachedValueState`: State enumeration
- `CachedValueLifecycle`: Lifecycle metadata tracking
- `CacheLifecyclePolicy`: State decision logic
- `ExpirationMethods/`: TTL, sliding, absolute expiration
- `InvalidationMethods/`: Key, tag, namespace, pattern, soft/hard
- `InvalidationStrategies/`: TTL-only, manual, event-driven
- `ReplacementPolicies/`: LRU, LFU, FIFO, Random
- `RefreshPolicies/`: On-read, ahead, when-stale
- `StaleValuePolicies/`: Serve-stale decisions

### StoreCachedValues/

Store abstractions and implementations:

- `CacheStore`: Store interface contract
- `StoredCacheRecord`: Value with lifecycle metadata
- `CacheStoreRecordWasFound`: Found result type
- `CacheStoreRecordWasMissing`: Missing result type
- `InMemoryCacheStore`: In-memory implementation
- `FileCacheStore`: File-based implementation
- `ChainCacheStore`: Multi-tier chain
- `FallbackCacheStore`: Primary + fallback

### ObserveCache/

Observability capabilities:

- `CacheMetrics`: Hit rate, latency, failure tracking
- `CacheOperation`: Operation tracing
- `CacheTrace`: Operation recording

### UseCacheTiers/

Multi-tier cache support (V1.5+).

### DistributeCachedValues/

Distributed partitioning (V3).

### ProtectCacheSource/

Stampede protection with locks (V1.5+).

### SyncWithSource/

Source synchronization (V2+).

## Ownership Questions

1. **Who owns this?** - The specific capability folder
2. **Why is it here?** - Shared across multiple flows
3. **Is it truly shared?** - Yes, if used by 2+ flows
4. **What breaks if it moves?** - Dependency scatter

## Shared Last Rule

A capability should exist only when:

- More than one flow genuinely depends on it
- Keeping it local would be misleading
- Extraction improves clarity

## Anti-Patterns to Avoid

- Collecting unrelated pieces in one folder
- Creating "Capabilities" bucket without real shared ownership
- Speculative extraction before evidence