---
title: IdentifyCachedValues-how-this-works
owner: Identity Team
last_reviewed: 2026-04-25
classification: internal
---

# IdentifyCachedValues How This Works

IdentifyCachedValues provides **key identification and organization** for cache entries.

## What This Folder Owns

- CacheKey validation and normalization
- Key prefix management
- Namespace boundaries
- Tag-based organization
- Version-based isolation

## Key Types

### CacheKey

```php
CacheKey {
    original: string          // Original key string
    namespace: string|null    // Optional namespace
    version: CacheVersion|null // Optional version
}
```

Validation rules:

- Length: 1-256 characters
- Characters: alphanumeric, underscore, dash, dot, colon
- Automatic normalization (trim, lowercase, whitespace to underscore)

### CacheKeyPrefix

```php
CacheKeyPrefix {
    prefix: string
    separator: string = ':'
}
```

Used for namespace-prefixed operations.

### CacheNamespace

```php
CacheNamespace {
    name: string  // max 128 chars, alphanumeric + underscore + dash
}
```

### CacheTag

```php
CacheTag {
    name: string  // max 64 chars
}
```

Tags allow grouping related cache entries for bulk invalidation.

### CacheTags

```php
CacheTags {
    tags: CacheTag[]
}
```

Set of tags with union/intersection operations.

### CacheVersion

```php
CacheVersion {
    major: int
    minor: int
    patch: int
}
```

Used for version-based cache invalidation.

## Key Patterns

### Standard Key

```
user:profile:123
```

### Namespaced Key

```
users:user:profile:123
```

### Versioned Key

```
app:v2:user:profile:123
```

## Full Key Resolution

```php
$key->fullKey()  // Returns: users:user:profile:123:v1.2.3
```

## Validation

```php
// This throws InvalidCacheKey
CacheKey::create('invalid key with spaces');
CacheKey::create('');  // Too short
CacheKey::create(str_repeat('a', 257));  // Too long
CacheKey::create('key with $pecial/chars');
```

## Pattern Matching

```php
$key = CacheKey::create('user:profile:123');

// Matches
$key->matchesPattern('user:*');        // true
$key->matchesPattern('user:profile:*'); // true
$key->matchesPattern('*');              // true

// No match
$key->matchesPattern('product:*');      // false
$key->matchesPattern('user:???');       // false (wildcards only)
```

## Debug First

1. **Start in CacheKey** when keys behave unexpectedly
2. **Check validation** - invalid keys throw exceptions
3. **Check namespace** - namespace affects full key

## Dictionary

- `CacheKey`: Validated, normalized, namespaced cache key
- `CacheKeyPrefix`: Key prefix for namespace operations
- `CacheNamespace`: Namespace boundary
- `CacheTag`: Tag for grouping entries
- `CacheTags`: Set of tags
- `CacheVersion`: Semantic version for key isolation
- `InvalidCacheKey`: Exception for invalid keys