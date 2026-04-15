<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\BeginAuthentication;

use SensitiveParameter;

final readonly class BeginPasskeyAuthenticationData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string|null $identifier;

    public function __construct(
        string|null                       $identifier = null,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->identifier = $identifier;
        $this->ipAddress  = $ipAddress;
        $this->userAgent  = $userAgent;
    }
}
