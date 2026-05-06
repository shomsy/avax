<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey;

use SensitiveParameter;

final readonly class RenamePasskeyData
{
    public function __construct(
        #[SensitiveParameter]
        public string $credentialId,
        public string $label,
    ) {
    }
}
