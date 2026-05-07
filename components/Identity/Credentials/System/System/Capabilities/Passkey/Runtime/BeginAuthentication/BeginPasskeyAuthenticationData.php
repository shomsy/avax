<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Passkey\Runtime\BeginAuthentication;

use SensitiveParameter;

final readonly class BeginPasskeyAuthenticationData
{
    public function __construct(
        public ?string $identifier = null,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
