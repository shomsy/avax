---
title: ObserveCache-how-this-works
owner: Observability Team
last_reviewed: 2026-04-25
classification: internal
---

# ObserveCache How This Works

ObserveCache provides **observability capabilities** for cache operations.

## What This Folder Owns

- System metrics collection
- Operation tracing
- Hit/miss tracking
- Latency measurement

## Key Types

### CacheMetrics

```php
CacheMetrics {
    // Counters
    hits: int
    misses: int
    writes: int
    deletes: int
    evictions: int
    invalidations: int
    refreshes: int
    staleServed: int
    lockWaits: int
    sourceFailures: int
    storeFailures: int

    // Aggregates
    totalLatencyMicroseconds: int
    operationCount: int
}
```

### CacheOperation

```php
CacheOperation {
    operation: string       // read, write, delete, etc.
    key: CacheKey
    timestamp: float
    ttlSeconds: int|null
    durationMicroseconds: int|null
    storeName: string|null
    tier: string|null
}
```

### CacheTrace

Interface for recording operation history.

## Metrics Calculations

```php
// Hit rate: hits / (hits + misses)
$metrics->hitRate()  // 0.0 to 1.0

// Miss rate
$metrics->missRate()  // 0.0 to 1.0

// Average latency in microseconds
$metrics->averageLatencyMicroseconds()

// Total operations
$metrics->totalOperations()
```

## Enabling Metrics

```php
// Via configuration
$config = new CacheConfiguration(enableMetrics: true);
$cache = BuildCache::inMemory($config);

// Check metrics
$cache->set('key', 'value');
$cache->get('key');
$cache->get('missing');

print_r($cache->metrics->toArray());
```

Output:

```php
[
    'hits' => 1,
    'misses' => 1,
    'writes' => 1,
    'deletes' => 0,
    'evictions' => 0,
    'hit_rate' => 0.5,
    'miss_rate' => 0.5,
    'average_latency_ms' => 0.15,
]
```

## Debug First

1. **Check hit rate** - low rate indicates problems
2. **Check latency** - high latency means performance issues
3. **Check failure counts** - high failures means instability

## Dictionary

- `CacheMetrics`: Metrics collection and aggregation
- `CacheOperation`: Single cache operation record
- `CacheTrace`: Operation history recording interface