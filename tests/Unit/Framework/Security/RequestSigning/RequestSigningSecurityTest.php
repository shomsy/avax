<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\Security\RequestSigning;

use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\NonceStore;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureKeyId;
use Avax\Framework\System\Capabilities\Security\RequestSigning\SignInternalRequest;
use Avax\Framework\System\Capabilities\Security\RequestSigning\VerifyInternalRequestSignature;
use PHPUnit\Framework\TestCase;

final class RequestSigningSecurityTest extends TestCase
{
    private const string SECRET_KEY = 'this-is-a-32-byte-secret-key-!!';
    private SignInternalRequest $signer;
    private VerifyInternalRequestSignature $verifier;
    private NonceStore $nonceStore;

    public function test_sign_and_verify_roundtrip() : void
    {
        $payload = $this->signer->sign('POST', '/api/data', '{"key":"value"}');

        $headers = $payload->toHeaders();
        $result = $this->verifier->verify('POST', '/api/data', '{"key":"value"}', $headers);

        $this->assertTrue($result->valid);
    }

    public function test_verify_rejects_tampered_body() : void
    {
        $payload = $this->signer->sign('POST', '/api/data', '{"key":"value"}');

        $headers = $payload->toHeaders();
        $result = $this->verifier->verify('POST', '/api/data', '{"key":"tampered"}', $headers);

        $this->assertFalse($result->valid);
        $this->assertSame('Signature mismatch.', $result->reason);
    }

    public function test_verify_rejects_tampered_method() : void
    {
        $payload = $this->signer->sign('POST', '/api/data', 'body');

        $headers = $payload->toHeaders();
        $result = $this->verifier->verify('GET', '/api/data', 'body', $headers);

        $this->assertFalse($result->valid);
    }

    public function test_verify_rejects_expired_signature() : void
    {
        $verifier = new VerifyInternalRequestSignature(
            secretKey: self::SECRET_KEY,
            nonceStore: $this->nonceStore,
            toleranceSeconds: 1,
            currentTime: PHP_INT_MAX,
        );

        $payload = $this->signer->sign('GET', '/api/resource');
        $headers = $payload->toHeaders();

        $result = $verifier->verify('GET', '/api/resource', '', $headers);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('expired', strtolower($result->reason));
    }

    public function test_verify_rejects_replayed_nonce() : void
    {
        $payload = $this->signer->sign('GET', '/api/resource');

        $headers = $payload->toHeaders();

        $this->verifier->verify('GET', '/api/resource', '', $headers);

        $result = $this->verifier->verify('GET', '/api/resource', '', $headers);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('nonce', strtolower($result->reason));
    }

    public function test_verify_rejects_missing_signature_headers() : void
    {
        $result = $this->verifier->verify('GET', '/api/resource', '', []);

        $this->assertFalse($result->valid);
        $this->assertStringContainsString('signature headers', strtolower($result->reason));
    }

    protected function setUp(): void
    {
        $this->nonceStore = new NonceStore();
        $this->signer = new SignInternalRequest(
            secretKey: self::SECRET_KEY,
            keyId: new SignatureKeyId('test-key-1'),
        );
        $this->verifier = new VerifyInternalRequestSignature(
            secretKey: self::SECRET_KEY,
            nonceStore: $this->nonceStore,
        );
    }
}
