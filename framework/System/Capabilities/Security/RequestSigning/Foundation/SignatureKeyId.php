<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\RequestSigning\Foundation;

final readonly class SignatureKeyId
{
    /**
     * @throws \InvalidArgumentException When signature key ID is empty
     */
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('Signature key ID must not be empty.');
        }
    }
}
