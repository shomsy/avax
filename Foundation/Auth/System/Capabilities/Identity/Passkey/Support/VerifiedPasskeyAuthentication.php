<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Passkey;

use SensitiveParameter;

final readonly class VerifiedPasskeyAuthentication
{
    public string $credentialId;
    public int    $userId;

    public function __construct(
        int                          $userId,
        #[SensitiveParameter] string $credentialId
    )
    {
        $this->userId       = $userId;
        $this->credentialId = $credentialId;
    }
}
