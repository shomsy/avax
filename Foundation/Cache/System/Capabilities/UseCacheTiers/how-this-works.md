# UseCacheTiers

Multi-tier caching (L1 memory, L2 Redis, L3 disk).

## What This Owns

- CacheTier - tier configuration
- TieredCacheStore - multi-tier implementation
- TierLookup - search tiers in order
- TierWrite - write to appropriate tier

## Triggers

Multiple cache stores configured

## Main Flow

```mermaid
flowchart TD
    A[Read Key] --> B[Tier 1 Memory]
    B -->|Hit| C[Return]
    B -->|Miss| D[Tier 2 Redis]
    D -->|Hit| E[Promote to Tier 1]
    D -->|Miss| F[Tier 3 Disk]
    F -->|Hit| G[Promote Up]
    F -->|Miss| H[Load Source]
```

## Debug

- Check tier promotion works
- Verify eviction cascades
- Monitor hit rates per tier