# Common Cache Mistakes

## Mistake 1: Cache Without TTL

**Wrong:**
```php
$cache->set('user:profile:42', $userData); // Lives forever!
```

**Right:**
```php
$cache->set('user:profile:42', $userData, ttl: 300); // Expires in 5 minutes
```

**Why it matters:** Without TTL, cached data lives until manually removed. When the real data changes, the cache serves old data forever. TTL is your safety net.

---

## Mistake 2: Cache as the Only Data Source

**Wrong:**
```php
$user = $cache->get('user:profile:42');
if ($user === null) {
    throw new Exception("User not found"); // WRONG: cache miss is not "not found"!
}
```

**Right:**
```php
$user = $cache->get('user:profile:42');
if ($user === null) {
    $user = $this->userRepository->findById(42); // Fetch from real source
    if ($user === null) {
        throw new UserNotFoundException(42);
    }
    $cache->set('user:profile:42', $user, ttl: 300); // Cache for next time
}
```

**Why it matters:** Cache MISS means "I don't know" not "it doesn't exist." Always fall back to the real source.

---

## Mistake 3: Mutable Static Cache in Long-Lived Workers

**Wrong:**
```php
class LocalCache {
    private static array $data = []; // Stale across requests in workers!

    public static function get(string $key): mixed {
        return self::$data[$key] ?? null;
    }
}
```

**Right:**
```php
class LocalCache implements ResettableState {
    private array $data = [];

    public function reset(): void {
        $this->data = []; // Clears between requests in workers
    }

    public function get(string $key): mixed {
        return $this->data[$key] ?? null;
    }
}
```

**Why it matters:** In FrankenPHP/RoadRunner, workers handle many requests. Static data from request 1 leaks into request 2. Implement `ResettableState` to clear between requests.

---

## Mistake 4: Caching Sensitive Data Without Protection

**Wrong:**
```php
$cache->set('user:credentials:42', $userData->passwordHash); // NEVER cache secrets!
```

**Right:**
```php
// Cache non-sensitive data only
$cache->set('user:profile:42', [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    // NO password, NO tokens, NO secrets
], ttl: 300);
```

**Why it matters:** Cache may be shared, logged, or visible in monitoring tools. Never cache secrets. Cache only what is safe to expose.

---

## Mistake 5: No Invalidation on Data Change

**Wrong:**
```php
// Update user in database
$this->userRepository->update($userId, $newData);
// Cache still has old data! Next read returns stale data until TTL expires.
```

**Right:**
```php
// Update user in database
$this->userRepository->update($userId, $newData);
// Invalidate cache immediately
$this->cache->invalidateTags(['user:' . $userId]);
```

**Why it matters:** When data changes, the cache becomes a lie. Invalidate immediately to prevent serving stale data.
