<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Identity\Credentials\Passkey;

use SensitiveParameter;

final readonly class ResolvedPasskeyCredential
{
    public function __construct(
        #[SensitiveParameter]
        public string $credentialId,
        public string $label,
    ) {
    }
}
