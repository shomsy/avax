# ADR-0001: Cache Invalidation Strategy

## Status

**ACCEPTED**

## Context

Cache invalidation is one of the hardest problems in software engineering. When real data changes, cached copies become stale. We need a strategy that:
- Keeps cached data fresh
- Does not require every Flow to manually invalidate caches
- Works correctly in long-lived workers (FrankenPHP, RoadRunner, Swoole)
- Handles cache driver failures gracefully

Options considered:
1. **TTL-only**: Let everything expire naturally. Simple but can serve stale data for up to TTL duration.
2. **Event-driven invalidation**: Emit events when data changes, cache listens and invalidates. More complex but accurate.
3. **Tag-based invalidation**: Tag cached items by entity (e.g., `user:42`). When entity changes, invalidate all items with that tag.

## Decision

We use **tag-based invalidation with TTL safety net**.

Cached items are tagged by entity identifier. When an entity changes, all cache items tagged with that entity are invalidated. Additionally, every item has a TTL as a safety net for items that were missed by tag invalidation.

```php
// Cache with tags
$cache->set('user:profile:42', $data, ttl: 300, tags: ['user:42']);

// When user 42 updates, invalidate all user:42 cache items
$cache->invalidateTags(['user:42']);
```

### Why This Decision

- Tag invalidation is precise (only stale items are removed)
- TTL safety net catches items missed by invalidation
- Tags are composable (an item can have multiple tags: `['user:42', 'team:5']`)
- Works correctly in long-lived workers (tags are driver-managed, not local static)

### Trade-offs

- Tag support depends on cache driver (Redis supports tags, Memcached does not)
- Tag operations add slight overhead to cache writes
- In-memory cache cannot efficiently support tags (acceptable: in-memory is for dev/test)

## Consequences

- All cache writes MUST include tags for entity-tagged items
- All data mutations MUST trigger tag invalidation
- Cache drivers without tag support require adapter layer
- TTL is mandatory even for tagged items (safety net)
