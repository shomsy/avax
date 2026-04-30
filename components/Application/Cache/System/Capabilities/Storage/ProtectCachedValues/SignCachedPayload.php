<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\ProtectCachedValues;

final readonly class SignCachedPayload
{
    public function __construct(
        private string $secret,
    ) {}

    public function verify(string $payload, string $signature) : bool
    {
        return hash_equals($this->sign(payload: $payload), $signature);
    }

    public function sign(string $payload) : string
    {
        return hash_hmac('sha256', $payload, $this->secret);
    }
}
