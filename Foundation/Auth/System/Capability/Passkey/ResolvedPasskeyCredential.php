<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Passkey;

final readonly class ResolvedPasskeyCredential
{
    public function __construct(
        public string $credentialId,
        public string $label
    ) {}
}
