---
title: CacheFoundation-how-this-works
owner: AvaxCache Team
last_reviewed: 2026-04-25
classification: internal
---

# Foundation How This Works

Foundation contains **tiny, stable, neutral primitives** that are too small to deserve their own capability slice.

## What This Folder Owns

Small, boring, foundational building blocks:

- Time primitives (Clock, Timestamp, Duration)
- Serialization utilities
- Compression utilities
- Randomness utilities

## What Foundation Is NOT

Foundation is **NOT** for:

- Random utilities
- Domain logic
- Cross-cutting concerns
- Speculative reuse
- Generic helpers

## Child Folders

### Time/

```php
Clock          // Interface for time access
SystemClock    // Real system time
FrozenClock    // Deterministic test time
Timestamp      // Point in time
Duration       // Time span
```

### Serialization/

```php
CacheSerializer              // Serialization interface
SerializedCachePayload       // Serialized data container
PhpCacheSerializer           // PHP serialize()
JsonCacheSerializer          // JSON encode/decode
CachePayloadCouldNotBeSerialized // Error type
```

### Compression/

```php
CacheCompressor             // Compression interface
CompressedCachePayload       // Compressed data container
DeflateCompressor           // zlib deflate
GzipCompressor              // gzip compression
```

### Randomness/

```php
RandomJitter                 // TTL jitter application
GenerateJitteredTtl          // TTL jitter generator
```

## Clock Dependency Rule

**All time access must go through Clock.**

```php
// WRONG - Direct time call
$expiresAt = time() + 3600;

// CORRECT - Through Clock
$expiresAt = $clock->now()->add(Duration::ofSeconds(3600));
```

This enables:

- Deterministic testing
- Time manipulation
- Proper separation of concerns
- No direct time() calls in core logic

## FrozenClock for Testing

```php
$clock = new FrozenClock(Timestamp::now());

// Advance time deterministically
$clock->advance(3600);

// Check expiration
$lifecycle->isExpired($clock);
```

## Foundation Naming

Foundation names must be:

- Boring and predictable
- Domain-neutral
- Small and focused
- Stable over time

## Dictionary

- `Clock`: Interface for time access, abstracts system time
- `Timestamp`: Immutable point in time with nanosecond precision
- `Duration`: Immutable time span with nanosecond precision
- `CacheSerializer`: Converts values to/from storable format