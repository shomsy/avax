<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Capabilities\VerifyHmacSignature;

final readonly class VerifyHmacSignature
{
    public function __construct(
        private string $algorithm = 'sha256',
    ) {}

    public function verify(string $data, string $key, string $expectedSignature) : bool
    {
        $computed = hash_hmac($this->algorithm, $data, $key);

        return hash_equals($computed, $expectedSignature);
    }
}
