<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey\BeginAuthentication;

use SensitiveParameter;

final readonly class BeginPasskeyAuthenticationData
{
    public function __construct(
        public string|null                       $identifier = null,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    ) {}
}
