# Cache Read-Through Pattern

```mermaid
sequenceDiagram
    participant Flow
    participant Cache
    participant Source

    Flow->>Cache: get('user:profile:42')
    alt Cache HIT
        Cache-->>Flow: Cached data (fast!)
    else Cache MISS
        Cache-->>Flow: null
        Flow->>Source: Fetch from database/API
        Source-->>Flow: Real data
        Flow->>Cache: set('user:profile:42', data, ttl: 300, tags: ['user:42'])
        Flow-->>Flow: Use real data
    end
```

## What This Diagram Shows

The read-through pattern is the most common cache usage pattern:
1. The Flow asks Cache for data
2. If Cache has it (HIT), the Flow uses it immediately — very fast
3. If Cache doesn't have it (MISS), the Flow fetches from the real source
4. The Flow stores the fetched data in Cache for next time
5. The Flow uses the data regardless of whether it came from cache or source

## When Data Changes

```mermaid
sequenceDiagram
    participant UpdateFlow
    participant Source
    participant Cache

    UpdateFlow->>Source: Update user 42
    Source-->>UpdateFlow: Updated
    UpdateFlow->>Cache: invalidateTags(['user:42'])
    Cache-->>Cache: Remove all user:42 items
    Note over Cache: Next get('user:profile:42') will MISS
```

When data changes, tag invalidation removes all stale cache items. The next read will MISS and fetch fresh data from the source.
