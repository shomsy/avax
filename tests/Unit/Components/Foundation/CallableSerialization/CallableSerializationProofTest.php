<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Foundation\CallableSerialization;

use Avax\Components\Foundation\CallableSerialization\System\Capabilities\ComputeHmacSignature\ComputeHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\RejectUnsafeCallable\RejectUnsafeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\SerializeCallable\SerializeClosureThroughLibrary;
use Avax\Components\Foundation\CallableSerialization\System\Capabilities\VerifyHmacSignature\VerifyHmacSignature;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\Builders\BuildCallableSerialization;
use Avax\Components\Foundation\CallableSerialization\System\Configuration\CallableSerializationConfig;
use Avax\Components\Foundation\CallableSerialization\System\Flows\DecodeCallable\DecodeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Flows\EncodeCallable\EncodeCallable;
use Avax\Components\Foundation\CallableSerialization\System\Foundation\Failure\CallablePayloadFailure;
use Avax\Components\Foundation\CallableSerialization\System\Foundation\Values\CallablePayload;
use Avax\Components\Foundation\CallableSerialization\System\PublicSurface\CallableSerialization;
use PHPUnit\Framework\TestCase;

/**
 * Proof tests for CallableSerialization component.
 *
 * These tests prove that:
 * - Closure serialize/deserialize round-trip works via laravel/serializable-closure
 * - HMAC-SHA256 signing and verification works
 * - Unsigned payloads are rejected when signing key is configured
 * - Corrupted payloads are rejected
 * - Signed payloads survive cross-process transport (JSON encode/decode)
 * - PublicSurface facade works correctly
 */
final class CallableSerializationProofTest extends TestCase
{
    private const string TEST_SECRET_KEY = 'test-secret-key-for-closure-serialization';

    // ============================================================
    // 1. CLOSURE SERIALIZATION ROUND-TRIP
    // ============================================================

    public function test_serialize_and_unserialize_simple_closure() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::TEST_SECRET_KEY);
        $closure = fn() => 'hello world';

        $encoded = $serializer->serialize($closure);
        $restored = $serializer->unserialize($encoded);

        self::assertSame('hello world', $restored());
    }

    public function test_serialize_closure_with_captured_variables() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::TEST_SECRET_KEY);
        $name = 'AvaX';
        $closure = fn() => "Hello, {$name}!";

        $encoded = $serializer->serialize($closure);
        $restored = $serializer->unserialize($encoded);

        self::assertSame('Hello, AvaX!', $restored());
    }

    public function test_serialize_closure_with_use_keyword() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::TEST_SECRET_KEY);
        $x = 10;
        $y = 20;
        $closure = fn() => $x + $y;

        $encoded = $serializer->serialize($closure);
        $restored = $serializer->unserialize($encoded);

        self::assertSame(30, $restored());
    }

    public function test_serialize_closure_returning_array() : void
    {
        $serializer = new SerializeClosureThroughLibrary(secretKey: self::TEST_SECRET_KEY);
        $closure = fn() => ['a', 'b', 'c'];

        $encoded = $serializer->serialize($closure);
        $restored = $serializer->unserialize($encoded);

        self::assertSame(['a', 'b', 'c'], $restored());
    }

    // ============================================================
    // 2. HMAC SIGNING AND VERIFICATION
    // ============================================================

    public function test_compute_and_verify_hmac_signature() : void
    {
        $compute = new ComputeHmacSignature();
        $verify = new VerifyHmacSignature();
        $key = 'secret-key-123';
        $data = 'some callable data';

        $signature = $compute->sign($data, $key);

        self::assertTrue($verify->verify($data, $key, $signature));
    }

    public function test_verify_fails_with_wrong_key() : void
    {
        $compute = new ComputeHmacSignature();
        $verify = new VerifyHmacSignature();
        $data = 'some callable data';

        $signature = $compute->sign($data, 'correct-key');

        self::assertFalse($verify->verify($data, 'wrong-key', $signature));
    }

    public function test_verify_fails_with_tampered_data() : void
    {
        $compute = new ComputeHmacSignature();
        $verify = new VerifyHmacSignature();
        $key = 'secret-key';

        $signature = $compute->sign('original data', $key);

        self::assertFalse($verify->verify('tampered data', $key, $signature));
    }

    // ============================================================
    // 3. ENCODE/DECODE FLOW WITH SIGNING
    // ============================================================

    public function test_encode_produces_signed_json_payload() : void
    {
        $config = (new CallableSerializationConfig())->withSigningKey('test-key');
        $serializer = new SerializeClosureThroughLibrary(secretKey: 'test-key');
        $compute = new ComputeHmacSignature();
        $encoder = new EncodeCallable($serializer, $compute, $config);

        $closure = fn() => 42;
        $jsonPayload = $encoder->encode($closure);

        $payload = CallablePayload::fromJson($jsonPayload);

        self::assertNotEmpty($payload->signature);
        self::assertSame('hmac-sha256', $payload->algorithm);
        self::assertNotEmpty($payload->encoded);
    }

    public function test_decode_verifies_signature_and_restores_closure() : void
    {
        $config = (new CallableSerializationConfig())->withSigningKey('test-key');
        $serializer = new SerializeClosureThroughLibrary(secretKey: 'test-key');
        $compute = new ComputeHmacSignature();
        $verify = new VerifyHmacSignature();
        $reject = new RejectUnsafeCallable();

        $encoder = new EncodeCallable($serializer, $compute, $config);
        $decoder = new DecodeCallable($serializer, $verify, $reject, $config);

        $closure = fn() => 'signed-and-verified';
        $jsonPayload = $encoder->encode($closure);

        $result = $decoder->decode($jsonPayload);

        self::assertTrue(isset($result['closure']));
        self::assertSame('signed-and-verified', $result['closure']());
    }

    public function test_decode_rejects_tampered_payload() : void
    {
        $config = (new CallableSerializationConfig())->withSigningKey('test-key');
        $serializer = new SerializeClosureThroughLibrary(secretKey: 'test-key');
        $compute = new ComputeHmacSignature();
        $verify = new VerifyHmacSignature();
        $reject = new RejectUnsafeCallable();

        $encoder = new EncodeCallable($serializer, $compute, $config);
        $decoder = new DecodeCallable($serializer, $verify, $reject, $config);

        $closure = fn() => 'original';
        $jsonPayload = $encoder->encode($closure);

        // Tamper with the signature
        $payload = CallablePayload::fromJson($jsonPayload);
        $tampered = new CallablePayload(
            encoded   : $payload->encoded,
            signature : 'fake-signature',
            version   : $payload->version,
            algorithm : $payload->algorithm,
        );

        $result = $decoder->decode($tampered->toJson());

        self::assertTrue(isset($result['failure']));
        self::assertTrue($result['failure']->isCorrupted());
    }

    public function test_decode_rejects_unsigned_payload_when_key_configured() : void
    {
        $config = (new CallableSerializationConfig())->withSigningKey('test-key');
        $serializer = new SerializeClosureThroughLibrary(secretKey: 'test-key');
        $verify = new VerifyHmacSignature();
        $reject = new RejectUnsafeCallable();

        $decoder = new DecodeCallable($serializer, $verify, $reject, $config);

        $unsignedPayload = (new CallablePayload(
            encoded   : 'some-data',
            signature : '',
        ))->toJson();

        $result = $decoder->decode($unsignedPayload);

        self::assertTrue(isset($result['failure']));
        self::assertTrue($result['failure']->isUnsigned());
    }

    // ============================================================
    // 4. REJECT UNSAFE CALLABLE
    // ============================================================

    public function test_reject_unsafe_accepts_signed_payload() : void
    {
        $reject = new RejectUnsafeCallable();
        $payload = new CallablePayload(
            encoded   : 'data',
            signature : 'sig',
        );

        self::assertNull($reject->check($payload));
    }

    public function test_reject_unsafe_rejects_unsigned() : void
    {
        $reject = new RejectUnsafeCallable();
        $payload = new CallablePayload(
            encoded   : 'data',
            signature : '',
        );

        $failure = $reject->check($payload);

        self::assertNotNull($failure);
        self::assertTrue($failure->isUnsigned());
    }

    public function test_reject_unsafe_rejects_empty_encoded() : void
    {
        $reject = new RejectUnsafeCallable();
        $payload = new CallablePayload(
            encoded   : '',
            signature : 'sig',
        );

        $failure = $reject->check($payload);

        self::assertNotNull($failure);
        self::assertTrue($failure->isInvalid());
    }

    // ============================================================
    // 5. BUILD FACTORY
    // ============================================================

    public function test_build_creates_encoder_decoder_pair() : void
    {
        $builder = new BuildCallableSerialization();
        $pair = $builder->build(['signing_key' => 'test-key']);

        self::assertInstanceOf(EncodeCallable::class, $pair->encoder);
        self::assertInstanceOf(DecodeCallable::class, $pair->decoder);
    }

    public function test_build_round_trip() : void
    {
        $builder = new BuildCallableSerialization();
        $pair = $builder->build(['signing_key' => 'round-trip-key']);

        $closure = fn() => ['built', 'by', 'factory'];
        $jsonPayload = $pair->encoder->encode($closure);
        $result = $pair->decoder->decode($jsonPayload);

        self::assertTrue(isset($result['closure']));
        self::assertSame(['built', 'by', 'factory'], $result['closure']());
    }

    // ============================================================
    // 6. PUBLIC SURFACE FACADE
    // ============================================================

    public function test_public_surface_encode_decode() : void
    {
        $closure = fn() => 'via-facade';
        $jsonPayload = CallableSerialization::encode($closure, 'facade-key');
        $result = CallableSerialization::decode($jsonPayload, 'facade-key');

        self::assertTrue(isset($result['closure']));
        self::assertSame('via-facade', $result['closure']());
    }

    public function test_public_surface_rejects_wrong_key() : void
    {
        $closure = fn() => 'secret';
        $jsonPayload = CallableSerialization::encode($closure, 'key-a');
        $result = CallableSerialization::decode($jsonPayload, 'key-b');

        self::assertTrue(isset($result['failure']));
        self::assertTrue($result['failure']->isCorrupted());
    }

    public function test_public_surface_works_without_signing_key() : void
    {
        // Reset static state to test default key behavior
        CallableSerialization::reset();

        $closure = fn() => 'no-signing';
        // When no key is passed, the builder uses a default key internally
        $jsonPayload = CallableSerialization::encode($closure);
        $result = CallableSerialization::decode($jsonPayload);

        self::assertTrue(isset($result['closure']));
        self::assertSame('no-signing', $result['closure']());
    }

    // ============================================================
    // 7. CALLABLE PAYLOAD VALUE OBJECT
    // ============================================================

    public function test_callable_payload_to_array_and_from_array() : void
    {
        $payload = new CallablePayload(
            encoded   : 'encoded-data',
            signature : 'sig-123',
            version   : '2',
            algorithm : 'hmac-sha256',
        );

        $array = $payload->toArray();
        $restored = CallablePayload::fromArray($array);

        self::assertSame('encoded-data', $restored->encoded);
        self::assertSame('sig-123', $restored->signature);
        self::assertSame('2', $restored->version);
        self::assertSame('hmac-sha256', $restored->algorithm);
    }

    public function test_callable_payload_json_round_trip() : void
    {
        $payload = new CallablePayload(
            encoded   : 'data',
            signature : 'sig',
        );

        $json = $payload->toJson();
        $restored = CallablePayload::fromJson($json);

        self::assertSame('data', $restored->encoded);
        self::assertSame('sig', $restored->signature);
    }

    // ============================================================
    // 8. FAILURE TYPE
    // ============================================================

    public function test_failure_type_reason_helpers() : void
    {
        $unsigned = new CallablePayloadFailure('unsigned', 'no sig');
        $corrupted = new CallablePayloadFailure('corrupted', 'bad sig');
        $invalid = new CallablePayloadFailure('invalid', 'bad data');
        $expired = new CallablePayloadFailure('expired', 'too old');

        self::assertTrue($unsigned->isUnsigned());
        self::assertFalse($unsigned->isCorrupted());

        self::assertTrue($corrupted->isCorrupted());
        self::assertFalse($corrupted->isUnsigned());

        self::assertTrue($invalid->isInvalid());
        self::assertTrue($expired->isExpired());
    }

    // ============================================================
    // 9. CROSS-PROCESS TRANSPORT SIMULATION
    // ============================================================

    public function test_payload_survives_json_encode_decode_transport() : void
    {
        $config = (new CallableSerializationConfig())->withSigningKey('transport-key');
        $serializer = new SerializeClosureThroughLibrary(secretKey: 'transport-key');
        $compute = new ComputeHmacSignature();
        $verify = new VerifyHmacSignature();
        $reject = new RejectUnsafeCallable();

        $encoder = new EncodeCallable($serializer, $compute, $config);
        $decoder = new DecodeCallable($serializer, $verify, $reject, $config);

        $closure = fn() => 'transported';

        // Simulate cross-process transport: encode -> parse JSON to verify structure -> re-encode -> decode
        $encoded = $encoder->encode($closure);
        $transported = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($transported);
        self::assertArrayHasKey('encoded', $transported);
        self::assertArrayHasKey('signature', $transported);

        // Re-encode to JSON (simulates transport layer) and decode
        $reEncoded = json_encode($transported, JSON_THROW_ON_ERROR);
        $result = $decoder->decode($reEncoded);

        self::assertTrue(isset($result['closure']));
        self::assertSame('transported', $result['closure']());
    }
}
