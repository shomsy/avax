<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Foundation\Serialization;

use Avax\Components\Application\Cache\System\Foundation\Serialization\PhpCacheSerializer;
use Avax\Components\Application\Cache\System\Foundation\Serialization\SerializedCachePayload;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Security boundary tests for PhpCacheSerializer.
 *
 * These tests prove that:
 * - Object injection via serialized payloads is rejected
 * - Malicious class payloads do not instantiate objects
 * - Invalid/corrupted payloads fail safely
 * - Valid scalar/array payloads still work
 */
final class PhpCacheSerializerSecurityTest extends TestCase
{
    // ============================================================
    // 1. VALID SCALAR/ARRAY PAYLOADS STILL WORK
    // ============================================================

    public function test_serialize_and_unserialize_scalar_string() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = $serializer->serialize('hello');
        $result = $serializer->unserialize($payload);

        self::assertSame('hello', $result);
    }

    public function test_serialize_and_unserialize_integer() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = $serializer->serialize(42);
        $result = $serializer->unserialize($payload);

        self::assertSame(42, $result);
    }

    public function test_serialize_and_unserialize_array() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = $serializer->serialize(['key' => 'value', 'num' => 123]);
        $result = $serializer->unserialize($payload);

        self::assertSame(['key' => 'value', 'num' => 123], $result);
    }

    public function test_serialize_and_unserialize_boolean_false() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = $serializer->serialize(false);
        $result = $serializer->unserialize($payload);

        self::assertFalse($result);
    }

    public function test_serialize_and_unserialize_null() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = $serializer->serialize(null);
        $result = $serializer->unserialize($payload);

        self::assertNull($result);
    }

    // ============================================================
    // 2. MALICIOUS OBJECT PAYLOADS ARE REJECTED
    // ============================================================

    public function test_malicious_serialized_object_payload_is_rejected() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        // Simulate an attacker-crafted serialized object payload
        $maliciousData = 'O:8:"stdClass":0:{}';
        $payload = new SerializedCachePayload(
            data      : $maliciousData,
            format    : PhpCacheSerializer::FORMAT,
            timestamp : Timestamp::fromUnixTime(1_000_000),
            checksum  : null,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to unserialize payload data');

        $serializer->unserialize($payload);
    }

    public function test_unexpected_class_payload_does_not_instantiate() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        // Try to instantiate a common PHP class through serialized payload
        $maliciousData = 'O:11:"DateTimeImmutable":1:{s:6:"date";s:26:"2026-01-01 00:00:00.000000";}';
        $payload = new SerializedCachePayload(
            data      : $maliciousData,
            format    : PhpCacheSerializer::FORMAT,
            timestamp : Timestamp::fromUnixTime(1_000_000),
            checksum  : null,
        );

        $this->expectException(RuntimeException::class);

        $serializer->unserialize($payload);
    }

    // ============================================================
    // 3. INVALID/CORRUPTED PAYLOADS FAIL SAFELY
    // ============================================================

    public function test_invalid_serialized_data_fails_safely() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = new SerializedCachePayload(
            data      : 'this is not valid serialized php data!!!',
            format    : PhpCacheSerializer::FORMAT,
            timestamp : Timestamp::fromUnixTime(1_000_000),
            checksum  : null,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to unserialize payload data');

        $serializer->unserialize($payload);
    }

    public function test_corrupted_payload_data_fails_safely() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        // Start with valid serialized data then corrupt it
        $valid = serialize(['key' => 'value']);
        $corrupted = substr($valid, 0, -5) . 'XXXXX';

        $payload = new SerializedCachePayload(
            data      : $corrupted,
            format    : PhpCacheSerializer::FORMAT,
            timestamp : Timestamp::fromUnixTime(1_000_000),
            checksum  : null,
        );

        $this->expectException(RuntimeException::class);

        $serializer->unserialize($payload);
    }

    public function test_wrong_format_is_rejected() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = new SerializedCachePayload(
            data      : 's:5:"hello";',
            format    : 'json', // wrong format
            timestamp : Timestamp::fromUnixTime(1_000_000),
            checksum  : null,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot unserialize payload with format "json"');

        $serializer->unserialize($payload);
    }

    // ============================================================
    // 4. EMPTY AND EDGE CASE PAYLOADS
    // ============================================================

    public function test_empty_string_data_fails_safely() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $payload = new SerializedCachePayload(
            data      : '',
            format    : PhpCacheSerializer::FORMAT,
            timestamp : Timestamp::fromUnixTime(1_000_000),
            checksum  : null,
        );

        $this->expectException(RuntimeException::class);

        $serializer->unserialize($payload);
    }

    public function test_nested_array_payload_works() : void
    {
        $clock = new class implements Clock {
            #[\Override]
            public function now(): Timestamp { return Timestamp::fromUnixTime(1_000_000); }
        };
        $serializer = new PhpCacheSerializer($clock);

        $data = ['level1' => ['level2' => ['level3' => 'deep']]];
        $payload = $serializer->serialize($data);
        $result = $serializer->unserialize($payload);

        self::assertSame($data, $result);
    }
}
