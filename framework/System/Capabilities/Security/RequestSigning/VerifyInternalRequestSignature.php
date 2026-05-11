<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning;

use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\NonceStore;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignaturePayload;
use InvalidArgumentException;

final readonly class VerifyInternalRequestSignature
{
    public function __construct(
        private string $secretKey,
        private int $toleranceSeconds = 300,
        private NonceStore $nonceStore = new NonceStore(),
        private int|null $currentTime = null,
    ) {
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, string> $expectedHeaders optional subset of headers to verify against
     */
    public function verify(
        string $method,
        string $path,
        string $body,
        array $headers,
        array $expectedHeaders = [],
    ): SignatureVerificationResult {
        try {
            $payload = SignaturePayload::fromHeaders($headers);
        } catch (InvalidArgumentException $e) {
            return SignatureVerificationResult::failure('Missing signature headers: ' . $e->getMessage());
        }

        $expiryResult = RejectExpiredSignature::check($payload->timestamp, $this->toleranceSeconds, $this->currentTime);
        if (!$expiryResult->valid) {
            return SignatureVerificationResult::failure($expiryResult->reason);
        }

        $replayResult = RejectReplayedNonce::check($payload->nonce->value, $this->nonceStore, $payload->timestamp->epochSeconds);
        if (!$replayResult->valid) {
            return SignatureVerificationResult::failure($replayResult->reason);
        }

        $verificationHeaders = $expectedHeaders !== [] ? $expectedHeaders : $headers;

        $canonical = CanonicalizeSignedRequest::canonical($method, $path, $body, $verificationHeaders);

        $signingString = implode("\n", [
            $payload->nonce->value,
            (string) $payload->timestamp->epochSeconds,
            $canonical,
        ]);

        $expectedSignature = hash_hmac('sha256', $signingString, $this->secretKey);

        if (!hash_equals($expectedSignature, $payload->signature)) {
            return SignatureVerificationResult::failure('Signature mismatch.');
        }

        $this->nonceStore->store($payload->nonce->value, $payload->timestamp->epochSeconds);

        return SignatureVerificationResult::success();
    }
}
