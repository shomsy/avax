<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

final readonly class VerifiedPasskeyAuthentication
{
    public function __construct(
        public int $userId,
        public string $credentialId
    ) {}
}
