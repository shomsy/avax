<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\RenamePasskey;

final readonly class RenamePasskeyData
{
    public function __construct(
        public string $credentialId,
        public string $label
    ) {}
}
