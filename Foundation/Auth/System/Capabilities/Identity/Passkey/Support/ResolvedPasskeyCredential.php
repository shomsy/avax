<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Passkey;

use SensitiveParameter;

final readonly class ResolvedPasskeyCredential
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
