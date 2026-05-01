<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow;

use SensitiveParameter;

final readonly class RefreshAuthenticationRequest
{
    public function __construct(
        #[SensitiveParameter]
        public string $refreshToken,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
