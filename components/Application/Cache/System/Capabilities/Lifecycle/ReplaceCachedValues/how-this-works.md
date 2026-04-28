---
title: ReplacementPolicies-how-this-works
owner: Lifecycle Team
last_reviewed: 2026-04-25
classification: internal
---

# ReplacementPolicies How This Works

ReplacementPolicies determines **which entry to evict when capacity is reached**.

## What This Folder Owns

- LRU (Least Recently Used)
- LFU (Least Frequently Used)
- FIFO (First In First Out)
- Random replacement
- No replacement

## Policy Interface

```php
interface ChooseCachedValueForReplacement
{
    /**
     * @param array<string, CachedValueLifecycle> $entries
     */
    public function choose(array $entries): ?string;
}
```

## Policies

### LeastRecentlyUsedReplacement

Evicts entry with oldest `lastAccessedAt`:

```php
$policy = new LeastRecentlyUsedReplacement();

// Entries with access times
$entries = [
    'key1' => lifecycle1,  // accessed 10 min ago
    'key2' => lifecycle2,  // accessed 5 min ago
    'key3' => lifecycle3,  // accessed 1 min ago
];

$toEvict = $policy->choose($entries);
// Returns: 'key1' (least recently accessed)
```

### LeastFrequentlyUsedReplacement

Evicts entry with lowest `hitCount`:

```php
$policy = new LeastFrequentlyUsedReplacement();

$entries = [
    'key1' => lifecycle1,  // hitCount: 5
    'key2' => lifecycle2,  // hitCount: 100
    'key3' => lifecycle3,  // hitCount: 2
];

$toEvict = $policy->choose($entries);
// Returns: 'key3' (least frequently used)
```

### FirstInFirstOutReplacement

Evicts entry with oldest `createdAt`:

```php
$policy = new FirstInFirstOutReplacement();

$entries = [
    'key1' => lifecycle1,  // created 1 hour ago
    'key2' => lifecycle2,  // created 30 min ago
    'key3' => lifecycle3,  // created 5 min ago
];

$toEvict = $policy->choose($entries);
// Returns: 'key1' (oldest)
```

### RandomReplacement

Evicts random entry:

```php
$policy = new RandomReplacement();
$toEvict = $policy->choose($entries);
// Returns random entry
```

### NoReplacement

Never evicts anything:

```php
$policy = new NoReplacement();
$toEvict = $policy->choose($entries);
// Always returns null
```

## Usage with EvictCachedValue

```php
$evictor = new EvictCachedValue(
    store: $store,
    clock: $clock,
    policy: new LeastRecentlyUsedReplacement(),
    metrics: $metrics
);

$evicted = $evictor->evictUntilCapacityIsSafe(
    entries: $currentEntries,
    maxCapacity: 1000
);
```

## Policy Selection Guide

| Policy | Best For               | Complexity |
|--------|------------------------|------------|
| LRU    | General caching        | O(n) scan  |
| LFU    | Stable access patterns | O(n) scan  |
| FIFO   | Simple eviction        | O(1)       |
| Random | Testing                | O(1)       |
| None   | Fixed size stores      | O(0)       |

## Debug First

1. **Check lifecycle metadata** - wrong metadata = wrong eviction
2. **Check policy type** - ensure correct policy selected
3. **Check capacity calculation** - maxCapacity may be wrong

## Dictionary

- `ChooseCachedValueForReplacement`: Policy interface
- `LeastRecentlyUsedReplacement`: Evict oldest accessed
- `LeastFrequentlyUsedReplacement`: Evict least accessed count
- `FirstInFirstOutReplacement`: Evict oldest created
- `RandomReplacement`: Random eviction
- `NoReplacement`: Never evict