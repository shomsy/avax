<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning;

final readonly class SignatureVerificationResult
{
    public function __construct(
        public bool $valid,
        public string $reason = '',
    ) {
    }

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(string $reason): self
    {
        return new self(false, $reason);
    }
}
