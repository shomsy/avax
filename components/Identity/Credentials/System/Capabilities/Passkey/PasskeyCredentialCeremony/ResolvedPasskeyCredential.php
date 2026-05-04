<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony;

use SensitiveParameter;

final readonly class ResolvedPasskeyCredential
{
    public function __construct(
        #[SensitiveParameter]
        public string $credentialId,
        public string $label,
    ) {}
}
