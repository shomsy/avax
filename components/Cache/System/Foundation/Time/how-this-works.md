---
title: Time-how-this-works
owner: Foundation Team
last_reviewed: 2026-04-25
classification: internal
---

# Time How This Works

Time provides **time primitives** with deterministic testing support.

## What This Folder Owns

- Clock interface and implementations
- Timestamp value object
- Duration value object

## Clock Interface

```php
interface Clock
{
    public function now(): Timestamp;
}
```

### SystemClock

```php
$clock = new SystemClock();
$timestamp = $clock->now();  // Real system time
```

### FrozenClock

```php
$clock = new FrozenClock(Timestamp::now());

// Move time forward
$clock->moveForward(Duration::ofSeconds(60));

// Reset
$clock->reset();
```

## Timestamp

```php
$timestamp = Timestamp::now();
$timestamp = Timestamp::fromUnixTime(1700000000);
$timestamp = Timestamp::fromMilliseconds(1700000000000);

// Operations
$timestamp->add(Duration::ofSeconds(60));
$timestamp->subtract(Duration::ofSeconds(30));
$timestamp->difference($otherTimestamp);
$timestamp->isAfter($other);
$timestamp->isBefore($other);

// Conversion
$timestamp->toUnixTime();
$timestamp->toMilliseconds();
```

## Duration

```php
$duration = Duration::ofSeconds(60);
$duration = Duration::ofMilliseconds(1500);
$duration = Duration::ofMicroseconds(500000);
$duration = Duration::fromDateInterval(new \DateInterval('PT1H'));

// Operations
$duration->add($other);
$duration->subtract($other);
$duration->multiply(2);

// Conversion
$duration->toSeconds();
$duration->toMilliseconds();
$duration->toDateInterval();
```

## Clock Dependency Rule

**All time access in core logic must use Clock.**

```php
// WRONG
$expiresAt = time() + 3600;

// CORRECT
$expiresAt = $clock->now()->add(Duration::ofSeconds(3600));
```

## Debug First

1. **Use FrozenClock** in tests for deterministic behavior
2. **Check timestamp precision** - nanosecond support
3. **Check duration overflow** - ensure no overflow in calculations

## Dictionary

- `Clock`: Interface for time access
- `SystemClock`: Real system time
- `FrozenClock`: Deterministic test time
- `Timestamp`: Immutable point in time
- `Duration`: Immutable time span