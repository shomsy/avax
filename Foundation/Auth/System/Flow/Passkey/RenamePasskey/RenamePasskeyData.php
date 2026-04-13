<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\RenamePasskey;

use SensitiveParameter;

final readonly class RenamePasskeyData
{
    public function __construct(
        #[SensitiveParameter] public string $credentialId,
        public string                       $label
    ) {}
}
