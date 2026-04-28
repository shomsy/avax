---
title: InvalidationMethods-how-this-works
owner: Lifecycle Team
last_reviewed: 2026-04-25
classification: internal
---

# InvalidationMethods How This Works

InvalidationMethods provides **multiple ways to invalidate cached values**.

## What This Folder Owns

- Single key invalidation
- Batch key invalidation
- Tag-based invalidation
- Namespace-based invalidation
- Pattern-based invalidation
- Version-based invalidation
- Soft vs hard invalidation

## Invalidation Strategies

### InvalidateByKey

```php
interface InvalidateByKey
{
    public function invalidate(CacheKey $key): void;
    public function isInvalidated(CacheKey $key): bool;
}

// Usage
$cache->delete('user:123');  // Simple delete
```

### InvalidateByKeys

```php
interface InvalidateByKeys
{
    public function invalidateMany(iterable $keys): int;
    public function areAllInvalidated(iterable $keys): bool;
}

// Usage
$invalidate->invalidateMany([
    CacheKey::create('user:1'),
    CacheKey::create('user:2'),
    CacheKey::create('user:3'),
]);
```

### InvalidateByTag

```php
interface InvalidateByTag
{
    public function invalidateByTag(CacheTag $tag): int;
    public function isTagInvalidated(CacheTag $tag): bool;
}

// Usage - invalidate all user-related cache
$invalidate->invalidateByTag(CacheTag::create('users'));
```

### InvalidateByPattern

```php
interface InvalidateByPattern
{
    public function invalidateByPattern(string $pattern): int;
    public function matchesPattern(string $key, string $pattern): bool;
}

// Usage - invalidate all user profiles
$invalidate->invalidateByPattern('user:profile:*');
```

### InvalidateByVersion

```php
interface InvalidateByVersion
{
    public function invalidateVersion(CacheVersion $version): int;
    public function isVersionInvalidated(CacheVersion $version): bool;
}

// Usage - when app version changes
$newVersion = CacheVersion::current()->incrementMajor();
$invalidate->invalidateVersion($newVersion);
```

## Soft vs Hard Invalidation

### Hard Invalidation

```php
interface HardInvalidateCachedValue
{
    public function delete(CacheKey $key): void;
    public function exists(CacheKey $key): bool;
}

// Immediately removes from store
$cache->delete('key');
```

### Soft Invalidation

```php
interface SoftInvalidateCachedValue
{
    public function markStale(CacheKey $key): void;
    public function isMarkedStale(CacheKey $key): bool;
}

// Marks as stale but still servable
$invalidate->markStale(CacheKey::create('key'));

// Stale-while-revalidate kicks in
```

## Invalidation Reasons

```php
enum InvalidationReason: string
{
    case EXPLICIT = 'explicit';           // Manual delete
    case TTL_EXPIRED = 'ttl_expired';       // Time-based
    case STALE_POLICY = 'stale_policy';     // Policy-based
    case VERSION_CHANGE = 'version_change'; // Version bump
    case TAG_INVALIDATION = 'tag_invalidation';
    case NAMESPACE_CLEAR = 'namespace_clear';
    case PATTERN_MATCH = 'pattern_match';
    case CAPACITY_PRESSURE = 'capacity_pressure';
    case SOURCE_UPDATED = 'source_updated';
    case MAINTENANCE = 'maintenance';
}
```

## Debug First

1. **Check which strategy** - tag vs key vs pattern
2. **Check tracking** - tags must be stored with keys
3. **Check soft/hard** - hard deletes immediately

## Dictionary

- `InvalidateByKey`: Single key removal
- `InvalidateByTag`: Tag-based bulk removal
- `InvalidateByPattern`: Pattern matching removal
- `SoftInvalidateCachedValue`: Mark stale
- `HardInvalidateCachedValue`: Immediate delete
- `InvalidationReason`: Why invalidation occurred