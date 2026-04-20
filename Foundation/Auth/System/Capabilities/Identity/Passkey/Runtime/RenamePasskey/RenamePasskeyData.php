<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey;

use SensitiveParameter;

final readonly class RenamePasskeyData
{
    public string $label;
    public string $credentialId;

    public function __construct(
        #[SensitiveParameter] string $credentialId,
        string                       $label
    )
    {
        $this->credentialId = $credentialId;
        $this->label        = $label;
    }
}
