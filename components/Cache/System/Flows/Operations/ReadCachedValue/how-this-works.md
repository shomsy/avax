---
title: ReadCachedValue-flow
owner: CacheFlows Team
last_reviewed: 2026-04-25
classification: internal
---

# ReadCachedValue Flow

ReadCachedValue is the flow for **reading values from cache**.

## What This Flow Does

1. Check store for key
2. Verify key exists
3. Check expiration
4. Return value or default

## First Important Path

```mermaid
sequenceDiagram
    autonumber
    participant Client as Client Code
    participant Flow as ReadCachedValue
    participant Store as CacheStore
    participant Metrics as CacheMetrics

    Client->>Flow: read(key, default)
    Flow->>Flow: Start timing
    Flow->>Store: read(key, clock)
    alt Found valid record
        Store-->>Flow: CacheStoreRecordWasFound
        Flow->>Metrics: recordHit()
        Flow-->>Client: value
    else Not found or expired
        Store-->>Flow: CacheStoreRecordWasMissing
        Flow->>Metrics: recordMiss()
        Flow-->>Client: default
    end
    Flow->>Flow: Record latency
```

## Direct Files

### ReadCachedValue.php

```php
final readonly class ReadCachedValue
{
    public function __construct(
        private CacheStore $store,
        private Clock $clock,
        private ?CacheMetrics $metrics = null
    ) {}

    public function read(CacheKey $key, mixed $default = null): mixed
    {
        $startTime = hrtime(as_integer: true);

        try {
            $result = $this->store->read($key, $this->clock);

            if ($result instanceof CacheStoreRecordWasMissing) {
                $this->recordLatency($startTime, 'miss');
                $this->metrics?->recordMiss();
                return $default;
            }

            if ($result->record->isExpired($this->clock)) {
                $this->recordLatency($startTime, 'miss');
                $this->metrics?->recordMiss();
                return $default;
            }

            $this->recordLatency($startTime, 'hit');
            $this->metrics?->recordHit();

            return $result->value();
        } catch (\Throwable $e) {
            $this->metrics?->recordStoreFailure();
            return $default;
        }
    }
}
```

## Debug First

1. **Start here** when cache returns unexpected value
2. **Check store** - is the value actually stored?
3. **Check expiration** - is it expired?
4. **Check metrics** - is it counting hits correctly?

## Dictionary

- `ReadCachedValue`: Flow for retrieving cached values
- `CacheStoreRecordWasFound`: Value exists and is valid
- `CacheStoreRecordWasMissing`: Key doesn't exist