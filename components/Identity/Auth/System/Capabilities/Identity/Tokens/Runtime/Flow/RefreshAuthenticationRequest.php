<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow;

use SensitiveParameter;

final readonly class RefreshAuthenticationRequest
{
    public function __construct(
        #[SensitiveParameter] public string      $refreshToken,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    ) {}
}
