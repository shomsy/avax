<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Identity\Credentials\Passkey;

use SensitiveParameter;

final readonly class VerifiedPasskeyAuthentication
{
    public function __construct(
        public int $userId,
        #[SensitiveParameter]
        public string $credentialId,
    ) {
    }
}
