# ProtectCacheSource

Prevents cache stampede/thundering herd when cache is empty or expired.

## What This Owns

- CacheLock - distributed lock for key
- CacheLockOwner - lock holder identity
- CacheLockStore - lock storage (InMemory, Redis)
- CacheLockTimeout - lock TTL
- RequestCoalescing - deduplicate concurrent requests

## Triggers

System miss + concurrent requests to same key

## Main Flow

```mermaid
flowchart TD
    A[Request for Key] --> B{Lock Exists?}
    B -->|No| C[Acquire Lock]
    B -->|Yes| D[Wait + Retry on Stale]
    C --> E[Load from Source]
    E --> F[Release Lock + Store]
    D --> G[Return Stale or Wait]
```

## Debug

- Check lock TTL not too short (causes stampede)
- Check lock TTL not too long (blocks other requests)