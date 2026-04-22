<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Support;

use SensitiveParameter;

final readonly class VerifiedPasskeyAuthentication
{
    public function __construct(
        public int                          $userId,
        #[SensitiveParameter] public string $credentialId
    )
    {
    }
}
