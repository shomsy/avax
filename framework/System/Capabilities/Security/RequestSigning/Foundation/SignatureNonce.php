<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation;

final readonly class SignatureNonce
{
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('Signature nonce value must not be empty.');
        }
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(16)));
    }
}
