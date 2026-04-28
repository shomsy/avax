<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Passkey\Support;

use SensitiveParameter;

final readonly class ResolvedPasskeyCredential
{
    public function __construct(
        #[SensitiveParameter] public string $credentialId,
        public string                       $label
    ) {}
}
