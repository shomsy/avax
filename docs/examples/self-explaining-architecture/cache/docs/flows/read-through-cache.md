# Flow: Read-Through Cache

## Description

This flow describes the read-through cache pattern: check cache first, fall back to source on miss, cache the result for next time.

## Steps

1. **Cache Lookup**
   - Flow constructs cache key (e.g., `user:profile:42`)
   - Flow asks Cache for the key
   - Cache returns data (HIT) or null (MISS)

2. **Cache HIT Path**
   - Data found in cache
   - Flow validates cached data is not corrupted (optional checksum)
   - Flow uses cached data
   - Flow completes — fast path!

3. **Cache MISS Path**
   - Data not in cache
   - Flow fetches from real source (database, API, filesystem)
   - Flow validates the fetched data
   - Flow stores in cache with TTL and tags
   - Flow uses the fetched data

4. **Invalidation (Separate Flow)**
   - When data changes, the mutation Flow invalidates cache tags
   - All cached items with those tags are removed
   - Next read will MISS and fetch fresh data

## Negative Paths

| What Goes Wrong | Response | Where It's Handled |
|----------------|----------|-------------------|
| Cache connection fails | Fall back to source | Cache capability |
| Cache returns corrupted data | Discard, fall back to source | Cache capability |
| Source returns null | Throw NotFoundException | Flow |
| Cache set fails | Log warning, continue | Cache capability (cache is optional) |
| Tag invalidation fails | Log warning, TTL will eventually expire | Cache capability |

## Key Principle

Cache failures must NEVER break the application. Cache is an optimization, not a requirement. If cache is down, the application should work slower but still correctly.
