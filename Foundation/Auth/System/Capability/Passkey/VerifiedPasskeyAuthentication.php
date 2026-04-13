<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

use SensitiveParameter;

final readonly class VerifiedPasskeyAuthentication
{
    public function __construct(
        public int                          $userId,
        #[SensitiveParameter] public string $credentialId
    ) {}
}
