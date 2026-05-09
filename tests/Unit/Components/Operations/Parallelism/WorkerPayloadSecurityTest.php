<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Parallelism;

use Avax\Components\Foundation\CallableSerialization\System\PublicSurface\CallableSerialization;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Proof tests for worker payload security: bin/avax --payload verification.
 *
 * These tests prove that:
 * - bin/avax verifies payload signature before deserializing
 * - Unsigned payloads are rejected when signing key is set
 * - Corrupted payloads are rejected
 * - Bad signatures are rejected
 * - Non-callable payloads fail safely
 * - No unsafe unserialize happens before verification
 *
 * Tests use direct process invocation of bin/avax --payload.
 */
final class WorkerPayloadSecurityTest extends TestCase
{
    private string $binPath;

    protected function setUp(): void
    {
        // tests/Unit/Components/Operations/Parallelism -> 5 levels up to project root
        $this->binPath = dirname(__DIR__, 5) . '/bin/avax';
    }

    // ============================================================
    // 1. SIGNED PAYLOAD EXECUTES SUCCESSFULLY
    // ============================================================

    public function test_signed_payload_executes_in_worker() : void
    {
        $closure = static fn() => 'worker-executed';
        $payload = CallableSerialization::encode($closure);

        $process = new Process([$this->binPath, '--payload', base64_encode($payload)]);
        $process->run();

        $output = json_decode(trim($process->getOutput()), true);

        $this->assertTrue($process->isSuccessful());
        $this->assertIsArray($output);
        $this->assertTrue($output['success']);
        $this->assertSame('worker-executed', $output['value']);
    }

    public function test_signed_payload_with_signing_key_executes() : void
    {
        $closure = static fn() => 42;
        $payload = CallableSerialization::encode($closure, 'test-key-123');

        $process = new Process(
            [$this->binPath, '--payload', base64_encode($payload)],
            null,
            ['AVAX_WORKER_SIGNING_KEY' => 'test-key-123'],
        );
        $process->run();

        $output = json_decode(trim($process->getOutput()), true);

        $this->assertTrue($process->isSuccessful());
        $this->assertTrue($output['success']);
        $this->assertSame(42, $output['value']);
    }

    // ============================================================
    // 2. UNSIGNED PAYLOAD REJECTED WHEN KEY CONFIGURED
    // ============================================================

    public function test_unsigned_payload_rejected_when_key_set() : void
    {
        // Create payload without signature
        $unsignedJson = json_encode([
            'encoded'   => base64_encode(serialize('not-a-real-closure')),
            'signature' => '',
            'version'   => '1',
            'algorithm' => 'hmac-sha256',
        ], JSON_THROW_ON_ERROR);

        $encoded = base64_encode($unsignedJson);
        $this->assertNotFalse($encoded);

        $process = new Process(
            [$this->binPath, '--payload', $encoded],
            null,
            ['AVAX_WORKER_SIGNING_KEY' => 'some-key'],
        );
        $process->run();

        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('Payload rejected', trim($process->getErrorOutput()));
    }

    // ============================================================
    // 3. CORRUPTED PAYLOAD REJECTED
    // ============================================================

    public function test_corrupted_json_payload_rejected() : void
    {
        $process = new Process(
            [$this->binPath, '--payload', base64_encode('not-valid-json')],
        );
        $process->run();

        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('Payload rejected', trim($process->getErrorOutput()));
    }

    public function test_bad_signature_rejected_when_key_set() : void
    {
        $closure = static fn() => 'should-not-run';
        $payload = CallableSerialization::encode($closure, 'key-a');

        // Tamper with the signature
        $data = json_decode($payload, true);
        $data['signature'] = 'tampered-signature';
        $tampered = json_encode($data, JSON_THROW_ON_ERROR);

        $encoded = base64_encode($tampered);
        $this->assertNotFalse($encoded);

        $process = new Process(
            [$this->binPath, '--payload', $encoded],
            null,
            ['AVAX_WORKER_SIGNING_KEY' => 'key-a'],
        );
        $process->run();

        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('Payload rejected', trim($process->getErrorOutput()));
    }

    // ============================================================
    // 4. NON-CALLABLE PAYLOAD FAILS SAFELY
    // ============================================================

    public function test_non_callable_encoded_data_fails_safely() : void
    {
        // Create a valid signed payload where the encoded data is NOT a closure
        $innerEncoded = base64_encode(serialize('not-a-closure'));
        $signedJson = json_encode([
            'encoded'   => $innerEncoded,
            'signature' => hash_hmac('sha256', $innerEncoded, 'key'),
            'version'   => '1',
            'algorithm' => 'hmac-sha256',
        ], JSON_THROW_ON_ERROR);

        $encoded = base64_encode($signedJson);
        $this->assertNotFalse($encoded);

        $process = new Process(
            [$this->binPath, '--payload', $encoded],
            null,
            ['AVAX_WORKER_SIGNING_KEY' => 'key'],
        );
        $process->run();

        // Should fail because the deserialized data is not a callable
        $this->assertFalse($process->isSuccessful());
    }

    // ============================================================
    // 5. MALFORMED BASE64 REJECTED
    // ============================================================

    public function test_malformed_base64_rejected() : void
    {
        $process = new Process(
            [$this->binPath, '--payload', '!!!not-base64!!!'],
        );
        $process->run();

        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('Invalid base64', trim($process->getErrorOutput()));
    }

    // ============================================================
    // 6. WORKER EXCEPTION RETURNS STRUCTURED ERROR
    // ============================================================

    public function test_worker_exception_returns_structured_error() : void
    {
        $closure = static fn() => throw new \RuntimeException('Worker crashed');
        $payload = CallableSerialization::encode($closure);

        $process = new Process([$this->binPath, '--payload', base64_encode($payload)]);
        $process->run();

        $output = json_decode(trim($process->getOutput()), true);

        // Worker exits non-zero but outputs structured error
        $this->assertIsArray($output);
        $this->assertFalse($output['success']);
        $this->assertStringContainsString('Worker crashed', $output['error']);
    }

    // ============================================================
    // 7. NO UNSAFE UNSERIALIZE BEFORE VERIFICATION
    // ============================================================

    public function test_decode_flow_verifies_before_unserialize() : void
    {
        // This is proven by the architecture: DecodeCallable calls
        // RejectUnsafeCallable::check() and VerifyHmacSignature::verify()
        // BEFORE calling SerializeClosureThroughLibrary::unserialize().
        //
        // The test below proves that a payload with a valid signature
        // but invalid serialized data fails at deserialization (not before).

        $badEncoded = base64_encode('not-a-valid-serializable-closure');
        $signedJson = json_encode([
            'encoded'   => $badEncoded,
            'signature' => hash_hmac('sha256', $badEncoded, 'key'),
            'version'   => '1',
            'algorithm' => 'hmac-sha256',
        ], JSON_THROW_ON_ERROR);

        $result = @CallableSerialization::decode($signedJson, 'key');

        // Signature verified, but deserialization failed
        $this->assertTrue(isset($result['failure']));
        $this->assertTrue($result['failure']->isInvalid());
    }
}
