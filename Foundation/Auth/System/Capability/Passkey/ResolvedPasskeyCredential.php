<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

use SensitiveParameter;

final readonly class ResolvedPasskeyCredential
{
    public function __construct(
        #[SensitiveParameter] public string $credentialId,
        public string                       $label
    ) {}
}
