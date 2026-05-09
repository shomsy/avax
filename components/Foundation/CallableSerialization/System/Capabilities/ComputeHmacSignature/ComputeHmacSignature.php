<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Capabilities\ComputeHmacSignature;

final readonly class ComputeHmacSignature
{
    public function __construct(
        private string $algorithm = 'sha256',
    ) {}

    public function sign(string $data, string $key) : string
    {
        return hash_hmac($this->algorithm, $data, $key);
    }
}
