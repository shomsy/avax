---
title: RefreshPolicies-how-this-works
owner: Lifecycle Team
last_reviewed: 2026-04-25
classification: internal
---

# RefreshPolicies How This Works

RefreshPolicies controls **when cached values are proactively refreshed**.

## What This Folder Owns

- Refresh decision logic
- Refresh timing strategies
- Stale value serving policies

## RefreshPolicy Enum

```php
enum RefreshPolicy: string
{
    case DO_NOT_REFRESH = 'do_not_refresh';
    case REFRESH_ON_READ = 'refresh_on_read';
    case REFRESH_AHEAD = 'refresh_ahead';
    case REFRESH_AFTER_WRITE = 'refresh_after_write';
    case REFRESH_WHEN_STALE = 'refresh_when_stale';
}
```

## Refresh Decision Logic

```php
class ShouldRefreshCachedValue
{
    public function shouldRefresh(?CachedValueLifecycle $lifecycle): bool
    {
        return match ($this->policy) {
            RefreshPolicy::DO_NOT_REFRESH => false,
            RefreshPolicy::REFRESH_ON_READ => $this->shouldRefreshOnRead($lifecycle),
            RefreshPolicy::REFRESH_AHEAD => $this->shouldRefreshAhead($lifecycle),
            RefreshPolicy::REFRESH_WHEN_STALE => $this->shouldRefreshWhenStale($lifecycle),
            RefreshPolicy::REFRESH_AFTER_WRITE => true,
        };
    }
}
```

### DO_NOT_REFRESH

```php
$strategy = new ShouldRefreshCachedValue(
    clock: $clock,
    policy: RefreshPolicy::DO_NOT_REFRESH
);

$strategy->shouldRefresh($lifecycle);  // false - always
```

### REFRESH_ON_READ

Refreshes when value is expired:

```php
$strategy = new ShouldRefreshCachedValue(
    clock: $clock,
    policy: RefreshPolicy::REFRESH_ON_READ
);

$strategy->shouldRefresh($lifecycle);
// true if lifecycle->isExpired($clock)
```

### REFRESH_AHEAD

Refreshes before expiration:

```php
$strategy = new ShouldRefreshCachedValue(
    clock: $clock,
    policy: RefreshPolicy::REFRESH_AHEAD,
    refreshAheadWindowSeconds: 60  // 1 minute before
);

$strategy->shouldRefresh($lifecycle);
// true if TTL <= 60 seconds
```

### REFRESH_WHEN_STALE

Refreshes when idle too long:

```php
$strategy = new ShouldRefreshCachedValue(
    clock: $clock,
    policy: RefreshPolicy::REFRESH_WHEN_STALE,
    staleThresholdSeconds: 300  // 5 minutes idle
);

$strategy->shouldRefresh($lifecycle);
// true if idleTime > 5 minutes
```

## Usage

```php
$refreshFlow = new RefreshCachedValue(
    store: $store,
    clock: $clock
);

// Proactive refresh
$value = $refreshFlow->refreshIfNeeded(
    key: $key,
    loader: fn() => $source->load(),
    ttl: 3600,
    policy: RefreshPolicy::REFRESH_AHEAD
);
```

## Debug First

1. **Check policy** - wrong policy = wrong refresh timing
2. **Check thresholds** - window may be too large/small
3. **Check lifecycle metadata** - timeToLive must be accurate

## Dictionary

- `RefreshPolicy`: When to refresh values
- `ShouldRefreshCachedValue`: Refresh decision logic
- `RefreshOnRead`: Refresh on expiration
- `RefreshAhead`: Refresh before expiration
- `RefreshWhenStale`: Refresh when idle too long