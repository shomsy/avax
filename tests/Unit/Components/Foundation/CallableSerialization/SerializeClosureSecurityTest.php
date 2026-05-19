<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Foundation\CallableSerialization;

use Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable\SerializeClosureThroughLibrary;
use Laravel\SerializableClosure\SerializableClosure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Security boundary tests for SerializeClosureThroughLibrary.
 *
 * These tests prove that:
 * - A secret key is required (fail-closed)
 * - Tampered payloads are rejected
 * - Wrong secret keys fail to deserialize
 * - Malicious serialized payloads cannot instantiate arbitrary objects
 */
final class SerializeClosureSecurityTest extends TestCase
{
    private const string VALID_KEY = 'valid-secret-key-for-testing';
    private const string OTHER_KEY = 'different-secret-key';

    // ============================================================
    // 1. SECRET KEY ENFORCEMENT
    // ============================================================

    public function test_constructor_rejects_empty_secret_key() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires a non-empty secret key');

        new SerializeClosureThroughLibrary(secretKey: '');
    }

    // ============================================================
    // 2. VALID ROUND-TRIP WITH SECRET KEY
    // ============================================================

    public function test_closure_round_trip_with_secret_key() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::VALID_KEY);
        $closure = fn() => 'secure-closure';

        $encoded = $serializer->serialize($closure);
        $restored = $serializer->unserialize($encoded);

        self::assertSame('secure-closure', $restored());
    }

    // ============================================================
    // 3. TAMPERED PAYLOAD REJECTION
    // ============================================================

    public function test_tampered_closure_payload_is_rejected() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::VALID_KEY);
        $closure = fn() => 'original';

        $encoded = $serializer->serialize($closure);

        // Tamper with the encoded data
        $tampered = substr($encoded, 0, -10) . str_repeat('X', 10);

        $this->expectException(\Throwable::class);

        $serializer->unserialize($tampered);
    }

    public function test_wrong_secret_key_fails_to_deserialize() : void
    {
        $encryptor = new SerializeClosureThroughLibrary(secretKey: self::VALID_KEY);
        $decryptor = new SerializeClosureThroughLibrary(secretKey: self::OTHER_KEY);

        $closure = fn() => 'secret-data';
        $encoded = $encryptor->serialize($closure);

        $this->expectException(\Throwable::class);

        $decryptor->unserialize($encoded);
    }

    // ============================================================
    // 4. MALICIOUS PAYLOAD REJECTION
    // ============================================================

    public function test_malicious_base64_payload_is_rejected() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::VALID_KEY);

        // Try to feed a non-closure serialized object
        $malicious = base64_encode(serialize(new \stdClass()));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('did not resolve to a SerializableClosure');

        $serializer->unserialize($malicious);
    }

    public function test_invalid_base64_payload_is_rejected() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::VALID_KEY);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid base64 encoded closure payload');

        $serializer->unserialize('not-valid-base64!!!');
    }

    public function test_empty_string_payload_is_rejected() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::VALID_KEY);

        $this->expectException(\InvalidArgumentException::class);

        $serializer->unserialize('');
    }

    // ============================================================
    // 5. PAYLOAD TYPE RESTRICTION
    // ============================================================

    public function test_unserialize_only_accepts_SerializableClosure_class() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::VALID_KEY);

        // Serialize a non-SerializableClosure object with valid base64
        $objectPayload = base64_encode(serialize(['not' => 'a closure']));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('did not resolve to a SerializableClosure');

        $serializer->unserialize($objectPayload);
    }
}
