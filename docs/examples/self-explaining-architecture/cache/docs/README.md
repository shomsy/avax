# Cache Component

## What This Component Owns

The Cache component owns fast, temporary data storage with explicit lifecycle management.

It gives components a way to store data that is expensive to compute and safe to reuse for a short time.

It does NOT own permanent data. Cache data can disappear at any time.

## Where It Belongs

```
components/Cache/
  System/
    PublicSurface/     # Cache facade: get, set, delete, forget
    Flows/             # Cache-specific flows: warm-up, invalidation, cleanup
    Capabilities/      # Reusable cache behavior: read-through, write-through, tagging
    Configuration/     # Cache driver registration, connection assembly
    Foundation/        # Cache primitives: Key, TTL, CacheEntry
```

## How It Works (The Simple Version)

1. A Flow needs data (like a user's profile)
2. The Flow asks Cache: "Do you have this?"
3. If Cache says "yes" (a HIT), the Flow uses the cached data — fast!
4. If Cache says "no" (a MISS), the Flow fetches the real data and tells Cache: "Remember this for 5 minutes"
5. Next time, step 3 happens again

## What It Does NOT Own

- Permanent data storage (that's the database/persistence layer)
- Session storage (sessions are identity, not cache)
- File storage (that's the Filesystem capability)
- Configuration values (config is deployment-time, not runtime cache)

## The Most Important Rule: Lifecycle and Invalidation

Cache WITHOUT lifecycle is a bug. Every cached item MUST have:

- **TTL (Time To Live)**: How long until it expires automatically
- **Invalidation strategy**: How do we remove it when the real data changes?
- **Stale data risk**: What happens if someone gets old data for 30 seconds?

```php
// WRONG: No TTL, data lives forever (until server restart)
$cache->set('user_42', $userData);

// RIGHT: TTL ensures data refreshes
$cache->set('user_42', $userData, ttl: 300); // 5 minutes

// BETTER: Tag-based invalidation
$cache->set('user_42', $userData, ttl: 300, tags: ['user:42']);
// When user 42 updates: $cache->invalidateTags(['user:42']);
```

## How It Fails

- Cache MISS is NOT a failure — it means "fetch the real data"
- Cache connection failure should degrade gracefully (fetch from source)
- Cache corruption should be detected and cleared (checksum validation)
- Cache must NEVER return stale data as if it were fresh

## How It Is Tested

- Unit tests for cache key generation, TTL handling
- Integration tests for cache drivers (Redis, Memcached, in-memory)
- Negative tests for cache miss, cache corruption, cache connection failure
- Lifecycle tests for invalidation, tag-based purge, TTL expiry
