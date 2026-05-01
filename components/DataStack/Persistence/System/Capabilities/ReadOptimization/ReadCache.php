<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

use Avax\Components\DataStack\Database\System\Capabilities\Observability\QueryFingerprinter;

/**
 * A single cache entry with TTL support.
 */
final readonly class CacheEntry
{
    public function __construct(
        public mixed $value,
        public float $createdAt,
        public float $ttl,
        public string $fingerprint = '',
        public string $key = '',
    ) {}

    /**
     * Checks if this cache entry has expired.
     */
    public function isExpired(float $now = null) : bool
    {
        $now ??= microtime(true);

        return ($now - $this->createdAt) >= $this->ttl;
    }

    /**
     * Returns the remaining time-to-live in seconds.
     */
    public function remainingTtl(float $now = null) : float
    {
        $now ??= microtime(true);
        $elapsed = $now - $this->createdAt;

        return max(0.0, $this->ttl - $elapsed);
    }

    /**
     * Returns the age of this entry in seconds.
     */
    public function age(float $now = null) : float
    {
        $now ??= microtime(true);

        return $now - $this->createdAt;
    }
}

/**
 * Result of a cache operation.
 */
final readonly class CacheResult
{
    public function __construct(
        public bool $hit,
        public mixed $value = null,
        public string $key = '',
        public float $ttl = 0.0,
    ) {}

    /**
     * Creates a cache hit result.
     */
    public static function hit(mixed $value, string $key = '', float $ttl = 0.0): self
    {
        return new self(hit: true, value: $value, key: $key, ttl: $ttl);
    }

    /**
     * Creates a cache miss result.
     */
    public static function miss(string $key = ''): self
    {
        return new self(hit: false, key: $key);
    }
}

/**
 * Cache for read queries with TTL, invalidation strategies,
 * and cache key generation from query fingerprints.
 *
 * Supports multiple invalidation strategies:
 * - TTL-based: entries expire after a configured time
 * - Tag-based: entries can be invalidated by tags
 * - Fingerprint-based: entries can be invalidated by query pattern
 */
final class ReadCache
{
    /**
     * @var array<string, CacheEntry> The cache store
     */
    private array $store = [];

    /**
     * @var array<string, list<string>> Tag to key mapping
     */
    private array $tagIndex = [];

    /**
     * @var array<string, list<string>> Fingerprint to key mapping
     */
    private array $fingerprintIndex = [];

    /**
     * @var float Default TTL in seconds
     */
    private readonly float $defaultTtl;

    /**
     * @var int Maximum number of entries (0 = unlimited)
     */
    private readonly int $maxEntries;

    /**
     * @var QueryFingerprinter|null For generating cache keys from queries
     */
    private ?QueryFingerprinter $fingerprinter;

    /**
     * @var int Cache hit count
     */
    private int $hits = 0;

    /**
     * @var int Cache miss count
     */
    private int $misses = 0;

    public function __construct(
        float $defaultTtl = 60.0,
        int $maxEntries = 0,
        QueryFingerprinter $fingerprinter = null,
    ) {
        $this->defaultTtl = $defaultTtl;
        $this->maxEntries = $maxEntries;
        $this->fingerprinter = $fingerprinter;
    }

    /**
     * Retrieves or computes a value using a callback on cache miss.
     *
     * @template T
     *
     * @param callable() : T $callback
     *
     * @return T
     */
    public function remember(string $key, callable $callback, float $ttl = null, array $tags = []): mixed
    {
        $result = $this->get($key);

        if ($result->hit) {
            return $result->value;
        }

        $value = $callback();

        $this->put($key, $value, $ttl, $tags);

        return $value;
    }

    /**
     * Retrieves a value from the cache.
     *
     * @return CacheResult The cache result (hit or miss)
     */
    public function get(string $key): CacheResult
    {
        $entry = $this->store[$key] ?? null;

        if ($entry === null) {
            $this->misses++;

            return CacheResult::miss($key);
        }

        if ($entry->isExpired()) {
            $this->forget($key);
            $this->misses++;

            return CacheResult::miss($key);
        }

        $this->hits++;

        return CacheResult::hit(
            value: $entry->value,
            key  : $key,
            ttl  : $entry->remainingTtl(),
        );
    }

    /**
     * Removes a specific cache entry.
     */
    public function forget(string $key): void
    {
        unset($this->store[$key]);

        // Clean up indices
        foreach ($this->tagIndex as $tag => $keys) {
            $this->tagIndex[$tag] = array_values(array_filter(
                $keys,
                static fn (string $k): bool => $k !== $key,
            ));
        }

        foreach ($this->fingerprintIndex as $fp => $keys) {
            $this->fingerprintIndex[$fp] = array_values(array_filter(
                $keys,
                static fn (string $k): bool => $k !== $key,
            ));
        }
    }

    /**
     * Stores a value in the cache.
     *
     * @param string       $key         Cache key
     * @param mixed        $value       Value to cache
     * @param float|null   $ttl         Time-to-live in seconds (uses default if null)
     * @param list<string> $tags        Tags for grouped invalidation
     * @param string|null  $fingerprint Query fingerprint for pattern invalidation
     */
    public function put(
        string $key,
        mixed $value,
        float $ttl = null,
        array $tags = [],
        string $fingerprint = null,
    ): void {
        $ttl ??= $this->defaultTtl;

        $entry = new CacheEntry(
            value      : $value,
            createdAt  : microtime(true),
            ttl        : $ttl,
            fingerprint: $fingerprint ?? '',
            key        : $key,
        );

        $this->store[$key] = $entry;

        // Update tag index
        foreach ($tags as $tag) {
            $this->tagIndex[$tag][] = $key;
        }

        // Update fingerprint index
        if ($fingerprint !== null) {
            $this->fingerprintIndex[$fingerprint][] = $key;
        }

        // Enforce max entries
        if ($this->maxEntries > 0 && count($this->store) > $this->maxEntries) {
            $this->evictOldest();
        }
    }

    /**
     * Evicts the oldest entry from the cache.
     */
    private function evictOldest(): void
    {
        if (empty($this->store)) {
            return;
        }

        $oldestKey = null;
        $oldestTime = PHP_FLOAT_MAX;

        foreach ($this->store as $key => $entry) {
            if ($entry->createdAt < $oldestTime) {
                $oldestTime = $entry->createdAt;
                $oldestKey  = $key;
            }
        }

        if ($oldestKey !== null) {
            $this->forget($oldestKey);
        }
    }

    /**
     * Caches a read query result using the query as cache key.
     *
     * @param mixed        $value Query result
     * @param string       $sql   The SQL query
     * @param float|null   $ttl   TTL in seconds
     * @param list<string> $tags  Tags for invalidation
     */
    public function cacheQuery(
        string $sql,
        mixed $value,
        float $ttl = null,
        array $tags = [],
    ): void
    {
        $key = $this->generateQueryKey($sql);
        $fingerprint = $this->fingerprinter !== null
            ? $this->fingerprinter->fingerprint($sql)->hash
            : '';

        $this->put($key, $value, $ttl, $tags, $fingerprint);
    }

    /**
     * Generates a cache key from a SQL query.
     */
    private function generateQueryKey(string $sql): string
    {
        if ($this->fingerprinter !== null) {
            $fingerprint = $this->fingerprinter->fingerprint($sql);

            return "query:{$fingerprint->hash}";
        }

        return 'query:' . hash('sha256', $sql);
    }

    /**
     * Retrieves a cached query result.
     */
    public function getQuery(string $sql): CacheResult
    {
        $key = $this->generateQueryKey($sql);

        return $this->get($key);
    }

    /**
     * Invalidates all cache entries matching a fingerprint pattern.
     */
    public function invalidateFingerprint(string $fingerprint): int
    {
        $keys = $this->fingerprintIndex[$fingerprint] ?? [];
        $count = 0;

        foreach ($keys as $key) {
            $this->forget($key);
            $count++;
        }

        unset($this->fingerprintIndex[$fingerprint]);

        return $count;
    }

    /**
     * Invalidates all entries related to tables.
     *
     * Useful when a write operation affects cached reads.
     *
     * @param list<string> $tables Table names that were modified
     */
    public function invalidateTables(array $tables): int
    {
        $count = 0;

        foreach ($tables as $table) {
            $count += $this->invalidateTag("table:{$table}");
        }

        return $count;
    }

    /**
     * Invalidates all cache entries with the given tag.
     */
    public function invalidateTag(string $tag): int
    {
        $keys = $this->tagIndex[$tag] ?? [];
        $count = 0;

        foreach ($keys as $key) {
            $this->forget($key);
            $count++;
        }

        unset($this->tagIndex[$tag]);

        return $count;
    }

    /**
     * Removes all expired entries from the cache.
     */
    public function prune(): int
    {
        $count = 0;
        $now   = microtime(true);

        foreach ($this->store as $key => $entry) {
            if ($entry->isExpired($now)) {
                $this->forget($key);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Checks if a key exists and is not expired.
     */
    public function has(string $key): bool
    {
        $entry = $this->store[$key] ?? null;

        if ($entry === null) {
            return false;
        }

        return ! $entry->isExpired();
    }

    /**
     * Returns cache statistics.
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        return [
            'hits'           => $this->hits,
            'misses'         => $this->misses,
            'hit_rate'       => $this->hitRate(),
            'total_entries'  => count($this->store),
            'active_entries' => $this->count(),
            'tags'           => count($this->tagIndex),
            'fingerprints'   => count($this->fingerprintIndex),
        ];
    }

    /**
     * Returns the cache hit rate (0.0 to 1.0).
     */
    public function hitRate(): float
    {
        $total = $this->hits + $this->misses;

        if ($total === 0) {
            return 0.0;
        }

        return $this->hits / $total;
    }

    /**
     * Returns the number of active (non-expired) entries.
     */
    public function count(): int
    {
        $now = microtime(true);

        return count(array_filter(
            $this->store,
            static fn (CacheEntry $entry): bool => ! $entry->isExpired($now),
        ));
    }

    /**
     * Clears the entire cache.
     */
    public function flush(): void
    {
        $this->store            = [];
        $this->tagIndex         = [];
        $this->fingerprintIndex = [];
        $this->hits             = 0;
        $this->misses           = 0;
    }
}
