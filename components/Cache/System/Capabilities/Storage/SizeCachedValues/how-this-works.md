# SizeCachedValues

Tracks and limits cache storage size.

## What This Owns

- CacheSize - byte measurement
- SizeLimit - capacity enforcement
- EstimateSize - rough estimation

## Triggers

Storage quota configuration

## Main Flow

```mermaid
flowchart TD
    A[Write Value] --> B[Measure Size]
    B --> C{Under Limit?}
    C -->|Yes| D[Store]
    C -->|No| E[Evict LRU]
    E --> C
```

## Debug

- Check eviction policy
- Verify size tracking accuracy
- Monitor capacity warnings