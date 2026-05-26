# Cache Key

## What It Is

A Cache Key is the unique name used to find a specific piece of cached data.

Think of it like a locker number at the gym. You put something in locker #42, and later you use #42 to get it back. The key must be unique (no one else should use #42 for their stuff) and consistent (you always use #42 for your stuff).

Good cache keys look like:
- `user:profile:42` — user profile for user ID 42
- `product:price:EUR:123` — product price in Euros for product 123
- `api:response:GET:/users?page=2` — API response for this specific request

## What It Is NOT

- It is NOT the data itself (the key is the name, not the value)
- It is NOT a database query (keys should be meaningful strings, not SQL)
- It is NOT a file path (cache keys are logical, not filesystem paths)
- It is NOT a session ID (session IDs are security tokens, not cache lookups)

## Common Confusion

**Confusion**: "I'll use the SQL query as the cache key."
**Reality**: SQL queries make terrible cache keys. They're long, change with formatting, and reveal your database structure. Use meaningful names like `user:profile:{id}` instead.

**Confusion**: "I'll cache everything with the same key and just overwrite it."
**Reality**: If two different Flows use the same key, they'll overwrite each other's data. Keys must be unique per cached item. Use namespaces like `user:`, `product:`, `api:` to prevent collisions.

**Confusion**: "The cache key can contain sensitive data like passwords."
**Reality**: NEVER put secrets in cache keys. Cache keys may be logged, monitored, or visible in debugging tools. Keys should identify WHAT is cached, not contain the data itself.
