<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning;

use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureKeyId;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureNonce;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignaturePayload;
use Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation\SignatureTimestamp;

final readonly class SignInternalRequest
{
    public function __construct(
        private string $secretKey,
        private SignatureKeyId $keyId,
    ) {
    }

    /**
     * Sign an internal request with HMAC-SHA256.
     *
     * @param array<string, string> $headers
     */
    public function sign(
        string $method,
        string $path,
        string $body = '',
        array $headers = [], SignatureNonce|null $nonce = null, SignatureTimestamp|null $timestamp = null,
    ): SignaturePayload {
        $nonce ??= SignatureNonce::generate();
        $timestamp ??= SignatureTimestamp::now();

        $canonical = CanonicalizeSignedRequest::canonical($method, $path, $body, $headers);

        $signingString = implode("\n", [
            $nonce->value,
            (string) $timestamp->epochSeconds,
            $canonical,
        ]);

        $signature = hash_hmac('sha256', $signingString, $this->secretKey);

        return new SignaturePayload(
            $this->keyId,
            $timestamp,
            $nonce,
            $signature,
        );
    }
}
