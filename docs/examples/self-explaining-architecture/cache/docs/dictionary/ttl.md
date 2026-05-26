# TTL (Time To Live)

## What It Is

TTL is the expiration timer for a cached item. It answers the question: "How long is this cached data good for?"

When you put data in cache with a TTL of 300 seconds, you're saying: "This data is valid for 5 minutes. After that, it's stale and should be refreshed."

TTL can be:
- **Absolute**: Expires at a specific time (e.g., "expires at 3:00 PM")
- **Relative**: Expires after a duration (e.g., "expires in 5 minutes from now")
- **Sliding**: Expires after N seconds since the LAST access (keeps popular items alive)

## What It Is NOT

- It is NOT a guarantee that data will be in cache for the full TTL (cache may evict it early due to memory pressure)
- It is NOT a replacement for proper invalidation (TTL is a safety net, not the primary invalidation strategy)
- It is NOT a session timeout (session timeout is about user activity, cache TTL is about data freshness)

## Common Confusion

**Confusion**: "If I set TTL to 1 hour, the data will definitely be there for 1 hour."
**Reality**: TTL means "DO NOT keep this longer than 1 hour." The cache MAY remove it sooner if it needs memory. TTL is a maximum, not a guarantee.

**Confusion**: "I'll set TTL to forever and never worry about invalidation."
**Reality**: Cache with no TTL and no invalidation is a bug. When the real data changes, the cache will serve old data forever. Either set a TTL OR use explicit invalidation. Prefer both.

**Confusion**: "TTL and cache invalidation are the same thing."
**Reality**: TTL is automatic expiry (time-based). Invalidation is manual removal (event-based). Good cache strategy uses both: "Invalidate when data changes, AND expire after N minutes as a safety net."
