---
title: Serialization-how-this-works
owner: Foundation Team
last_reviewed: 2026-04-25
classification: internal
---

# Serialization How This Works

Serialization provides **value serialization** for cache storage.

## What This Folder Owns

- Serializer interface
- Payload wrapper
- PHP and JSON serializers
- Error types

## CacheSerializer Interface

```php
interface CacheSerializer
{
    public function serialize(mixed $value): SerializedCachePayload;
    public function unserialize(SerializedCachePayload $payload): mixed;
    public function supportedType(): string;
}
```

## SerializedCachePayload

```php
final readonly class SerializedCachePayload
{
    public function __construct(
        public string $data,
        public string $format,
        public Timestamp $createdAt,
        public ?string $checksum = null
    ) {}

    public function verify(): bool
    {
        return hash_equals(
            $this->checksum,
            hash_hmac('sha256', $this->data, self::class)
        );
    }
}
```

## Serializers

### PhpCacheSerializer

```php
$serializer = new PhpCacheSerializer();

// Serialize
$payload = $serializer->serialize(['complex' => 'data']);

// Unserialize
$value = $serializer->unserialize($payload);

// Check support
$serializer->supportedType();  // 'php-serialized'
```

### JsonCacheSerializer

```php
$serializer = new JsonCacheSerializer();

// Serialize
$payload = $serializer->serialize(['user' => ['name' => 'John']]);

// Unserialize
$value = $serializer->unserialize($payload);

// Check support
$serializer->supportedType();  // 'json'
```

## Security

### Checksum Verification

```php
$payload = SerializedCachePayload::create(
    data: 'serialized data',
    format: 'json',
    clock: $clock
);

// Verify integrity
if ($payload->verify()) {
    // Data is intact
}

// Verify fails on tampering
$payload->data = 'tampered';
$payload->verify();  // false
```

## Usage with Stores

```php
$store = new FileCacheStore(
    basePath: '/tmp/cache',
    clock: $clock,
    serializer: new JsonCacheSerializer($clock)
);
```

## Debug First

1. **Check format** - correct serializer for data type?
2. **Check checksum** - is payload verified?
3. **Check errors** - serialization might fail

## Dictionary

- `CacheSerializer`: Serialization interface
- `SerializedCachePayload`: Serialized data with metadata
- `PhpCacheSerializer`: PHP serialize()
- `JsonCacheSerializer`: JSON encode/decode
- `CachePayloadCouldNotBeSerialized`: Serialization error