<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCachedValues;

final readonly class VerifyCachedPayloadSignature
{
    public function __construct(
        private SignCachedPayload $signer
    ) {}

    public function verifyOrFail(string $payload, string $signature) : void
    {
        if (! $this->verify($payload, $signature)) {
            throw new CachePayloadWasTampered(
                message: 'System payload signature verification failed'
            );
        }
    }

    public function verify(string $payload, string $signature) : bool
    {
        return $this->signer->verify($payload, $signature);
    }
}