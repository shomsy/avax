<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\PasskeyCredentialCeremony;

use SensitiveParameter;

final readonly class VerifiedPasskeyAuthentication
{
    public function __construct(
        public int    $userId,
        #[SensitiveParameter]
        public string $credentialId,
    ) {}
}
