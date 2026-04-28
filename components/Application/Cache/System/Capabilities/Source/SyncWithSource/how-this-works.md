# SyncWithSource

Synchronizes cache with origin data source for freshness.

## What This Owns

- SourceSyncPolicy - when to sync (eager, lazy, background)
- CacheWarmer - pre-populate cache
- RefreshPolicy - when to refresh

## Triggers

System miss or staleness detection

## Main Flow

```mermaid
flowchart TD
    A[System Miss] --> B[Load from Source]
    B --> C[Store and Return]
    
    D[Stale Detected] --> E{Mode?}
    E -->|EAGER| F[Sync Now]
    E -->|LAZY| G[Return Stale, Refresh Background]
    E -->|BACKGROUND| H[Queue Refresh]
```

## Debug

- Check sync policy matches requirements
- Verify source availability
- Monitor refresh queue